<?php

namespace App\Tests\Service;

use App\Service\GoogleSheetsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleSheetsServiceTest extends TestCase
{
    private $parameterBag;
    
    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag->method('get')
            ->with('kernel.project_dir')
            ->willReturn(__DIR__ . '/../..');
    }
    
    public function testServiceInitialization(): void
    {
        // Skip this test if credentials file doesn't exist
        if (!file_exists(__DIR__ . '/../../config/google/credentials.json')) {
            $this->markTestSkipped('Google API credentials not found. Skipping test.');
        }
        
        $service = new GoogleSheetsService($this->parameterBag);
        $this->assertInstanceOf(GoogleSheetsService::class, $service);
    }
}
