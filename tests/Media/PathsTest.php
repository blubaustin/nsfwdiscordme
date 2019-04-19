<?php
namespace App\Tests\Media;

use App\Media\Paths;
use PHPUnit\Framework\TestCase;

/**
 * Class PathsTest
 */
class PathsTest extends TestCase
{
    /**
     *
     */
    public function testGetPathByType()
    {
        $paths = new Paths();
        $this->assertEquals('banners/123/123.jpg', $paths->getPathByType('banner', 123, 123, 'jpg'));
        $this->assertEquals('icons/123/123.jpg', $paths->getPathByType('icon', 123, 123, 'jpg'));
    }

    /**
     *
     */
    public function testGetIconPath()
    {
        $paths = new Paths();
        $this->assertEquals('icons/123/123.jpg', $paths->getIconPath(123, 123, 'jpg'));
    }

    /**
     *
     */
    public function testGetBannerPath()
    {
        $paths = new Paths();
        $this->assertEquals('banners/123/123.jpg', $paths->getBannerPath(123, 123, 'jpg'));
    }
}
