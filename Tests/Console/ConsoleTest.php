<?php
namespace Kitpages\FileBundle\Tests\Console;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group console
 */
class ConsoleTest
    extends WebTestCase
{
    private $client;
    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testConsole()
    {
        $output = $this->runCommand('list');
        $this->assertContains('Display this help message', $output);
    }
}