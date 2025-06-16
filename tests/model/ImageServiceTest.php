<?php

namespace Fotorama\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        vfsStream::setup("root");
        mkdir(vfsStream::url("root/images/test"), 0777, true);
        $img = imagecreate(100, 100);
        imagejpeg($img, vfsStream::url("root/images/test/foo.jpg"));
        imagejpeg($img, vfsStream::url("root/images/test/bar.jpg"));
    }

    private function sut(): ImageService
    {
        return new ImageService(vfsStream::url("root/images/"));
    }

    public function testFindsAllImageFolders()
    {
        $this->assertEquals(["test"], $this->sut()->findImageFolders());
    }

    public function testHasImageFolder()
    {
        $this->assertTrue($this->sut()->hasImageFolder("test"));
        $this->assertFalse($this->sut()->hasImageFolder("foo"));
    }

    public function testFindsAllImages()
    {
        $this->assertEquals(["bar.jpg", "foo.jpg"], $this->sut()->findImagesIn("test"));
    }

    public function testImageFolderName()
    {
        $this->assertEquals("vfs://root/images/test", $this->sut()->getImageFolderName("test"));
    }
}
