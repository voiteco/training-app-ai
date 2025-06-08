<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;
use Redis;

#[AsCommand(
    name: 'app:test-connections',
    description: 'Test database and Redis connections',
)]
class TestConnectionsCommand extends Command
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Test database connection
        $io->section('Testing Database Connection');
        try {
            $this->connection->connect();
            $io->success('Database connection successful!');
            
            // Get database name from connection parameters
            $params = $this->connection->getParams();
            $dbName = $params['dbname'] ?? 'unknown';
            $io->info("Connected to database: $dbName");
            
            // Check if we can execute a query
            $result = $this->connection->executeQuery('SELECT 1')->fetchOne();
            $io->info("Query result: $result");
        } catch (\Exception $e) {
            $io->error('Database connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Test Redis connection
        $io->section('Testing Redis Connection');
        try {
            $redis = new Redis();
            $redis->connect('redis', 6379);
            $io->success('Redis connection successful!');
            
            // Test setting and getting a value
            $redis->set('test_key', 'Hello from Symfony!');
            $value = $redis->get('test_key');
            $io->info("Redis test: set and get 'test_key' = '$value'");
            
            $redis->close();
        } catch (\Exception $e) {
            $io->error('Redis connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}