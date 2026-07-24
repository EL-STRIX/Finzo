<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Application\Services\PdfService;
use App\Infrastructure\Repositories\PdfRepository;
use PHPUnit\Framework\MockObject\MockObject;

class PdfServiceTest extends TestCase {
    
    private PdfService $service;
    /** @var PdfRepository&MockObject */
    private $repoMock;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repoMock = $this->createMock(PdfRepository::class);
        $this->service = new PdfService($this->repoMock);
        
        if (!defined('FINZO_ROOT')) {
            define('FINZO_ROOT', dirname(dirname(dirname(__DIR__))));
        }
    }
    
    public function testGetReceiptDataReturnsNullIfNotFound() {
        $this->repoMock->expects($this->once())
            ->method('getTransactionForReceipt')
            ->with('TXN123')
            ->willReturn(null);
            
        $result = $this->service->getReceiptData('TXN123', 'admin', 1);
        $this->assertNull($result);
    }
    
    public function testGetReceiptDataReturnsNullIfCustomerNotAuthorized() {
        $txn = [
            'from_account_id' => 2,
            'to_account_id' => 3
        ];
        
        $this->repoMock->expects($this->once())
            ->method('getTransactionForReceipt')
            ->with('TXN123')
            ->willReturn($txn);
            
        $this->repoMock->expects($this->once())
            ->method('getUserAccounts')
            ->with(1)
            ->willReturn([1, 4]); // Mismatch! User's accounts are 1,4, txn involves 2 and 3.
            
        $result = $this->service->getReceiptData('TXN123', 'customer', 1);
        $this->assertNull($result);
    }

    public function testGetReceiptDataReturnsTxnIfCustomerAuthorized() {
        $txn = [
            'from_account_id' => 1,
            'to_account_id' => 3
        ];
        
        $this->repoMock->expects($this->once())
            ->method('getTransactionForReceipt')
            ->with('TXN123')
            ->willReturn($txn);
            
        $this->repoMock->expects($this->once())
            ->method('getUserAccounts')
            ->with(1)
            ->willReturn([1, 4]); // Match on 1!
            
        $result = $this->service->getReceiptData('TXN123', 'customer', 1);
        $this->assertNotNull($result);
        $this->assertEquals(1, $result['from_account_id']);
    }
}
