<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\UploadService;
use App\Infrastructure\Repositories\UploadRepository;
use PHPUnit\Framework\MockObject\MockObject;

class UploadServiceTest extends TestCase {
    
    private UploadService $service;
    /** @var UploadRepository&MockObject */
    private $repoMock;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repoMock = $this->createMock(UploadRepository::class);
        $this->service = new UploadService($this->repoMock);
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
    }
    
    public function testHandleBranchCertUploadFailsOnLargeFile() {
        $file = ['size' => 6000000, 'tmp_name' => 'dummy', 'error' => UPLOAD_ERR_OK];
        
        $result = $this->service->handleBranchCertUpload($file, 1);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('File size must be less than 5MB', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testHandleIdDocumentUploadFailsOnLargeFile() {
        $file = ['size' => 6000000, 'tmp_name' => 'dummy', 'error' => UPLOAD_ERR_OK];
        
        $result = $this->service->handleIdDocumentUpload($file, 1);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('File size must be less than 5MB', $result['message']);
        $this->assertEquals(422, $result['status']);
    }
}
