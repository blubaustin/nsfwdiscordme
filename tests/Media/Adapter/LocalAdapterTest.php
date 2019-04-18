<?php
namespace App\Tests\Media\Adapter;

use App\Media\Adapter\Exception\FileExistsException;
use App\Media\Adapter\Exception\FileNotFoundException;
use App\Media\Adapter\LocalAdapter;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Class LocalAdapterTest
 */
class LocalAdapterTest extends TestCase
{
    /**
     * @var LocalAdapter
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $savePath;

    /**
     * Called before each test is run
     */
    public function setUp()
    {
        $this->savePath = __DIR__ . '/files';
        $this->adapter  = new LocalAdapter($this->savePath);
    }

    /**
     * @throws Exception
     */
    public function testWrite()
    {
        try {
            $actual = $this->adapter->write('background.png', __DIR__ . '/assets/background.png');
            $this->assertTrue($actual);
            $this->assertTrue(file_exists($this->savePath . '/background.png'));

            $actual = $this->adapter->write('background.png', __DIR__ . '/assets/background.png', true);
            $this->assertTrue($actual);
            $this->assertTrue(file_exists($this->savePath . '/background.png'));
        } finally {
            @unlink($this->savePath . '/background.png');
        }
    }

    /**
     * @throws Exception
     */
    public function testExists()
    {
        try {
            file_put_contents($this->savePath . '/test.txt', 'Hello, World');
            $this->assertTrue($this->adapter->exists('test.txt'));
            $this->assertFalse($this->adapter->exists('test2.txt'));
        } finally {
            @unlink($this->savePath . '/test.txt');
        }
    }

    /**
     * @throws Exception
     */
    public function testRemove()
    {
        try {
            file_put_contents($this->savePath . '/test.txt', 'Hello, World');
            $this->assertTrue($this->adapter->remove('test.txt'));
        } finally {
            @unlink($this->savePath . '/test.txt');
        }
    }

    /**
     * @expectedException \App\Media\Adapter\Exception\FileNotFoundException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function testThrowsFileNotFoundException()
    {
        $this->adapter->write('background.png', __DIR__ . '/assets/not_found.png');
    }

    /**
     * @expectedException \App\Media\Adapter\Exception\FileExistsException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function testThrowsFileExistsException()
    {
        try {
            file_put_contents($this->savePath . '/test.txt', 'Hello, World');
            $this->adapter->write('test.txt', __DIR__ . '/assets/background.png');
        } finally {
            @unlink($this->savePath . '/test.txt');
        }
    }
}
