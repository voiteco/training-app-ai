<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Redis;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Connection $connection): Response
    {
        $dbStatus = 'Unknown';
        $redisStatus = 'Unknown';
        
        // Test database connection
        try {
            $connection->connect();
            $dbStatus = 'Connected';
        } catch (\Exception $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
        }
        
        // Test Redis connection
        try {
            $redis = new Redis();
            $redis->connect('redis', 6379);
            $redisStatus = 'Connected';
            $redis->close();
        } catch (\Exception $e) {
            $redisStatus = 'Error: ' . $e->getMessage();
        }
        
        return $this->json([
            'message' => 'Welcome to your new Symfony application!',
            'php_version' => phpversion(),
            'database_status' => $dbStatus,
            'redis_status' => $redisStatus,
        ]);
    }
}
