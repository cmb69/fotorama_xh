<?php

namespace Fotorama\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImageFinderTest extends TestCase
{
    protected function setUp(): void
    {
        vfsStream::setup("root");
        mkdir(vfsStream::url("root/images/test/foo/bar"), 0777, true);
        $img = imagecreate(100, 100);
        imagejpeg($img, vfsStream::url("root/images/test/foo.jpg"));
        imagejpeg($img, vfsStream::url("root/images/test/bar.jpg"));
    }

    private function sut(): ImageFinder
    {
        return new ImageFinder(vfsStream::url("root/images/"));
    }

    public function testFindsFolders()
    {
        $this->assertEquals(["test", "test/foo", "test/foo/bar"], $this->sut()->folders());
    }

    public function testHasImageFolder()
    {
        $this->assertTrue($this->sut()->isFolder("test"));
        $this->assertFalse($this->sut()->isFolder("foo"));
    }

    public function testFindsImages()
    {
        $this->assertEquals(["bar.jpg", "foo.jpg"], $this->sut()->images("test"));
    }

    public function testImageFolderName()
    {
        $this->assertEquals("vfs://root/images/test", $this->sut()->filename("test"));
    }
}
