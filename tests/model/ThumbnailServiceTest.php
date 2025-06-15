<?php

namespace Fotorama\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ThumbnailServiceTest extends TestCase
{
    public function setUp(): void
    {
        vfsStream::setup("root");
        mkdir(vfsStream::url("root/cache"), 0777);
    }

    private function sut(): ThumbnailService
    {
        return new ThumbnailService(vfsStream::url("root/cache/"));
    }

    public function testMakesHorizontalThumbnail(): void
    {
        $path = __DIR__ . "/../data/XH2.jpg";
        $this->sut()->thumbnail($path, 64);
        $path = vfsStream::url("root/cache/" . md5($path) . "_64.jpg");
        $this->assertFileExists($path);
        $size = getimagesize($path);
        $this->assertSame(144, $size[0]);
        $this->assertSame(64, $size[1]);
    }

    public function testMakesVerticalThumbnail(): void
    {
        $path = __DIR__ . "/../data/XH2_vertical.jpg";
        $this->sut()->thumbnail($path, 64);
        $path = vfsStream::url("root/cache/" . md5($path) . "_64.jpg");
        $this->assertFileExists($path);
        $size = getimagesize($path);
        $this->assertSame(64, $size[0]);
        $this->assertSame(144, $size[1]);
    }
}
