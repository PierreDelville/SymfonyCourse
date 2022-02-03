<?php

namespace App\Services;

use App\Controller\PostController;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class RemovePost
{
    private PostController $postController;

    public function __construct(PostController $postController)
    {
        $this->postController = $postController;
    }

    public function remove($id, ManagerRegistry $manager, PostRepository $postRepository): Response
    {
        $this->postController->remove($id, $manager, $postRepository);
    }
}
