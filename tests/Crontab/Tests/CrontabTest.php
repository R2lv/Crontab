<?php

use Crontab\Crontab;
use Crontab\Job;
use PHPUnit\Framework\TestCase;

/**
 * CrontabTest
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class CrontabTest extends TestCase
{
    private $crontab;

    private $job1;

    private $job2;

    public function setUp(): void
    {
        $this->crontab = new Crontab();

        $this->job1 = new Job();
        $this->job1->setCommand('cmd');

        $this->job2 = new Job();
        $this->job2->setCommand('cmd2');
    }

    public function testSetterGetter()
    {
        $this->assertNull($this->crontab->getUser());
        $this->assertEquals('root', $this->crontab->setUser('root')->getUser());

        $this->assertCount(0, $this->crontab->getJobs());
        $this->crontab->setJobs(array($this->job1, $this->job2));
        $this->assertCount(2, $this->crontab->getJobs());

        $this->crontab->removeAllJobs();
        $this->assertCount(0, $this->crontab->getJobs());

        $job = new Job();
        $this->crontab->addJob($job);
        $this->assertCount(1, $this->crontab->getJobs());
        $this->crontab->addJob($job);
        $this->assertCount(1, $this->crontab->getJobs());

        $job = new Job();
        $job->setCommand('test');
        $this->crontab->addJob($job);
        $this->assertCount(2, $this->crontab->getJobs());

        $this->crontab->removeAllJobs();
        $this->crontab->setJobs(array($this->job1, $this->job2));
        $this->crontab->removeJob($this->job1);
        $this->assertCount(1, $this->crontab->getJobs());
        $job = $this->crontab->getJobs();
        $this->assertEquals(array_shift($job), $this->job2);
    }

    public function testRender()
    {
        $this->crontab
            ->addJob($this->job1)
            ->addJob($this->job2)
        ;
        $this->assertEquals(
            "0 * * * * cmd" . PHP_EOL . "0 * * * * cmd2",
            $this->crontab->render()
        );
    }

    public function testParseExistingCrontabByDefault()
    {
        $mockCrontab = $this->getMockBuilder('Crontab\Crontab')
            ->onlyMethods(['getCrontabFileHandler'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockCrontabFileHandler = $this->getMockBuilder('Crontab\CrontabFileHandler')
            ->getMock();

        $mockCrontab->expects($this->once())
            ->method('getCrontabFileHandler')
            ->willReturn($mockCrontabFileHandler);

        $mockCrontab->__construct();
    }

    public function testOptionallyDoNotParseExistingCrontab()
    {
        $mockCrontab = $this->getMockBuilder('Crontab\Crontab')
            ->onlyMethods(['getCrontabFileHandler'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockCrontab->expects($this->never())
            ->method('getCrontabFileHandler');

        $mockCrontab->__construct(false);
    }
}
