<?php
namespace App\Tests\Media;

use App\Entity\Media;
use App\Media\Adapter\Exception\FileExistsException;
use App\Media\Adapter\Exception\FileNotFoundException;
use App\Media\Adapter\LocalAdapter;
use App\Media\Exception\AdapterNotFoundException;
use App\Media\WebHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class WebHandlerTest
 */
class WebHandlerTest extends TestCase
{
    /**
     * @var WebHandler
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
        $this->savePath = __DIR__ . '/Adapter/files';
        $this->adapter  = new WebHandler(new LocalAdapter($this->savePath), [
            'local' => 'http://cdn.nsfwdiscordme.com'
        ]);
    }

    /**
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function testWrite()
    {
        try {
            $actual = $this->adapter->write('banner', 'background.png', __DIR__ . '/Adapter/assets/background.png');
            $this->assertInstanceOf(Media::class, $actual);
            $this->assertEquals('banner', $actual->getName());
            $this->assertEquals('background.png', $actual->getPath());
            $this->assertEquals('local', $actual->getAdapter());
        } finally {
            @unlink($this->savePath . '/background.png');
        }
    }

    /**
     * @throws AdapterNotFoundException
     */
    public function testGetWebURL()
    {
        $media = new Media();
        $media->setAdapter('local')
            ->setPath('banners/banner.png')
            ->setName('banner');

        $actual = $this->adapter->getWebURL($media);
        $this->assertEquals('http://cdn.nsfwdiscordme.com/banners/banner.png', $actual);
    }
}
