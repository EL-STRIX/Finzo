<?php

namespace Tests\Unit\Core;

use Tests\TestCase;
use Tests\Helpers\DummySuccessJob;
use Tests\Helpers\DummyFailingJob;
use App\Shared\Core\Queue\QueueManager;

class QueueManagerTest extends TestCase
{
    private QueueManager $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queue = QueueManager::getInstance();
    }

    public function testSyncJobExecutesSuccessfully(): void
    {
        $job = new DummySuccessJob();
        $this->queue->dispatch($job);

        $this->assertTrue($job->handled);
        $this->assertEquals(1, $job->getAttempts());
    }

    public function testFailingJobRetriesAndFailsPermanently(): void
    {
        $job = new DummyFailingJob();
        $this->queue->dispatch($job);

        $this->assertEquals(2, $job->executionCount);
        $this->assertEquals(2, $job->getAttempts());
    }
}
