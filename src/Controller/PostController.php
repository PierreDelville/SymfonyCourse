<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Services\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/post', name: 'post.')]
class PostController extends AbstractController
{
    private FileUploader $uploader;

    public function __construct(FileUploader $uploader, ManagerRegistry $em)
    {
        $this->uploader = $uploader;
    }

    #[Route('/', name: 'index')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $this->addFlash('success', 'Affiche OK');
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, ManagerRegistry $manager, FileUploader $fileUploader, SluggerInterface $slugger): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $manager->getManager();
            /** @var UploadedFile $file */
            $file = $form->get('attachment')->getData();
            if ($file) {
                $filename = $fileUploader->uploadFile($file, $slugger);
            }
            $post->setImage($filename);
            $em->persist($post);
            $em->flush();
            return $this->redirect($this->generateUrl('post.index'));
        }

        return $this->render(
            'post/create.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     * @return Response
     */
    public function show($id, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($id);
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("delete/{id}", name="delete")
     */
    public function remove($id, ManagerRegistry $manager, PostRepository $postRepository): Response
    {
        $user = $this->getUser()->getUserIdentifier();

        $em = $manager->getManager();

        $post = $postRepository->find($id);

        $em->remove($post);

        $em->flush();

        $this->addFlash("success", "The post $id was removed by $user!");

        return $this->redirect($this->generateUrl('post.index'));
    }


    #[Route('/removeall', name: 'removeall')]
    public function removeAll(ManagerRegistry $manager, PostRepository $postRepository): Response
    {
        $em = $manager->getManager();

        $posts = $postRepository->findAll();

        foreach ($posts as $post) {
            $em->remove($post);
        }
        $em->flush();

        return $this->redirect($this->generateUrl('post.index'));
    }

}
