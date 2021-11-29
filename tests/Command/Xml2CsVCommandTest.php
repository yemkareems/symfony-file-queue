<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class Xml2CsvCommandTest extends KernelTestCase
{
    protected function setUp()
    {
        exec('stty 2>&1', $output, $exitcode);
        $isSttySupported = 0 === $exitcode;

        if ('Windows' === \PHP_OS_FAMILY || !$isSttySupported) {
            $this->markTestSkipped('`stty` is required to test this command.');
        }
    }

    public function testXMLFileExists(){
        $fileName = 'data/coffee_feed.xml';
        $this->assertFileExists($fileName);
    }

    public function testXMLFileNotExists(){
        $fileName = 'data/coffee_feed_nofile.xml';
        $this->assertFileNotExists($fileName);
    }

    public function testFileReadable() {
        $fileName = 'data/coffee_feed.xml';
        $this->assertFileIsReadable($fileName);
    }

    public function testDirectoryWritable(){
        $dirName = 'data/';
        $this->assertDirectoryIsWritable($dirName);
    }

    public function testExecuteLocal(){
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $command = $application->find('app:xml-csv');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'  => $command->getName(),
                'remotefile' => '0',
            )
        );
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('file created successfully', $output);
    }
}
