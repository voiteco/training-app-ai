<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): Response
    {
        return $this->json([
            'message' => 'API test endpoint',
            'status' => 'success',
            'timestamp' => new \DateTime(),
        ]);
    }
}