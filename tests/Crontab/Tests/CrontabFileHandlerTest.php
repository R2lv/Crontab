<?php

use Crontab\Crontab;
use Crontab\CrontabFileHandler;
use PHPUnit\Framework\TestCase;

/**
 * CrontabFileHandlerTest
 *
 * @author Jacob Kiers <jacob@alphacomm.nl>
 */
class CrontabFileHandlerTest extends TestCase
{
    /**
     * @var Crontab
     */
    private $crontab;

    /**
     * @var CrontabFileHandler
     */
    private $crontabFileHandler;

    /**
     * @var string with the path to the temporary file
     */
    private $tempFile;

    /**
     * @var string with the path to the fixture file
     */
    private $fixtureFile;

    public function setUp(): void
    {
        $fixturesDir = __DIR__.'/../../fixtures';
        $this->fixtureFile = $fixturesDir."/crontab";
        $this->tempFile = tempnam(sys_get_temp_dir(), 'cron');
        $this->crontabFileHandler = new CrontabFileHandler();
        $this->crontab = new Crontab();
    }

    public function tearDown(): void
    {
        if(file_exists($this->tempFile)) 
        {
            chmod($this->tempFile, 0600);
            unlink($this->tempFile);
        }
    }


    public function testParseFromFile()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);
        $this->assertCount(3, $this->crontab->getJobs());

        $jobs = $this->crontab->getJobs();
        $job1 = array_shift($jobs);
        $job2 = array_shift($jobs);
        $job3 = array_shift($jobs);

        $this->assertEquals('cmd', $job1->getCommand());
        $this->assertEquals('cmd2', $job2->getCommand());
        $this->assertEquals('indentedCommand with whitespaces', $job3->getCommand());
    }

    public function testWriteToFileIsSuccessfulWhenFileIsWritable()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);

        $this->crontabFileHandler->writeToFile($this->crontab, $this->tempFile);

        $this->assertSame($this->crontab->render().PHP_EOL, file_get_contents($this->tempFile));
    }

    public function testWriteToFileThrowsExceptionWhenFileIsNotWritable()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);

        touch($this->tempFile);
        chmod($this->tempFile, 0400);

        $this->crontabFileHandler->writeToFile($this->crontab, $this->tempFile);
        // Expected an InvalidArgumentException because the file is not writable.
    }
}
