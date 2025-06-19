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

    public function testCreatesThumbnails(): void
    {
        $sut = $this->sut();
        $sut->createThumbnails(__DIR__ . "/../data/", "white.jpg");
        $thumbs = $sut->thumbnails("white.jpg");
        $this->assertEquals(
            ["320w" => "vfs://root/cache/./white-320w.jpg", "640w" => "vfs://root/cache/./white-640w.jpg"],
            $thumbs,
        );
        $size = getimagesize($thumbs["320w"]);
        $this->assertSame(320, $size[0]);
        $this->assertSame(240, $size[1]);
        $size = getimagesize($thumbs["640w"]);
        $this->assertSame(640, $size[0]);
        $this->assertSame(480, $size[1]);
    }

    /**
     * @requires extension exif
     * @dataProvider orientation
     */
    public function testHeedsOrientation(string $basename, string $col1, string $col2, string $col3, string $col4): void
    {
        $this->sut()->createThumbnails(__DIR__ . "/../data/", "$basename.jpg");
        $im = imagecreatefromjpeg(vfsStream::url("root/cache/$basename-400w.jpg"));
        $this->assertSame(400, imagesx($im));
        $this->assertSame(200, imagesy($im));
        imagetruecolortopalette($im, false, 4);
        $colors = $this->colors($im);
        $this->assertSame($colors[$col1], imagecolorat($im, 100, 50));
        $this->assertSame($colors[$col2], imagecolorat($im, 300, 50));
        $this->assertSame($colors[$col3], imagecolorat($im, 100, 150));
        $this->assertSame($colors[$col4], imagecolorat($im, 300, 150));
    }

    public function orientation(): array
    {
        return [
            ["orientation1", "red", "green", "blue", "white"],
            ["orientation2", "green", "red", "white", "blue"],
            ["orientation3", "white", "blue", "green", "red"],
            ["orientation4", "blue", "white", "red", "green"],
            ["orientation5", "red", "blue", "green", "white"],
            ["orientation6", "blue", "red", "white", "green"],
            ["orientation7", "white", "green", "blue", "red"],
            ["orientation8", "green", "white", "red", "blue"],
        ];
    }

    private function colors($im): array
    {
        return [
            "red" => imagecolorclosest($im, 0xff, 0x00, 0x00),
            "green" => imagecolorclosest($im, 0x00, 0xff, 0x00),
            "blue" => imagecolorclosest($im, 0x00, 0x00, 0xff),
            "white" => imagecolorclosest($im, 0xff, 0xff, 0xff),
        ];
    }

    public function testRetainsIccProfile(): void
    {
        $icc = <<<'EOS'
            SUNDX1BST0ZJTEUAAQEAAAIwQURCRQIQAABtbnRyUkdCIFhZWiAH0AAIAAsAEwA3ACdhY3NwQVBQTAAAAABub25lAAAAAAAAAAAAAA
            AAAAAAAAAA9tYAAQAAAADTLUFEQkUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAApjcHJ0AAAA
            /AAAADJkZXNjAAABMAAAAGl3dHB0AAABnAAAABRia3B0AAABsAAAABRyVFJDAAABxAAAAA5nVFJDAAAB1AAAAA5iVFJDAAAB5AAAAA
            5yWFlaAAAB9AAAABRnWFlaAAACCAAAABRiWFlaAAACHAAAABR0ZXh0AAAAAENvcHlyaWdodCAyMDAwIEFkb2JlIFN5c3RlbXMgSW5j
            b3Jwb3JhdGVkAAAAZGVzYwAAAAAAAAAPV2lkZSBHYW11dCBSR0IAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
            AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWFlaIAAAAAAAAPbcAAEAAAAA0zpYWVogAAAAAAAAAAAA
            AAAAAAAAAGN1cnYAAAAAAAAAAQIzAABjdXJ2AAAAAAAAAAECMwAAY3VydgAAAAAAAAABAjMAAFhZWiAAAAAAAAC3agAAQjsAAAAAWF
            laIAAAAAAAABnbAAC5hwAADRxYWVogAAAAAAAAJZEAAAQ+AADGEQ==
            EOS;
        $this->sut()->createThumbnails(__DIR__ . "/../data/", "Momiji-WideRGB-yes.jpg");
        getimagesize(vfsStream::url("root/cache/Momiji-WideRGB-yes-300w.jpg"), $info);
        $this->assertSame(str_replace("\n", "", $icc), base64_encode($info["APP2"]));
    }

    public function testClearsCache(): void
    {
        touch(vfsStream::url("root/cache/foo.jpg"));
        mkdir(vfsStream::url("root/cache/bar", 0777));
        touch(vfsStream::url("root/cache/bar/baz.jpg"));
        $this->assertTrue($this->sut()->clearCache());
        $this->assertFileDoesNotExist(vfsStream::url("root/cache/foo.jpg"));
        $this->assertFileDoesNotExist(vfsStream::url("root/cache/bar"));
    }

    public function testFailsToClearCache(): void
    {
        mkdir(vfsStream::url("root/cache/foo", 0777));
        touch(vfsStream::url("root/cache/foo/bar.jpg"));
        chmod(vfsStream::url("root/cache/foo/bar.jpg"), 0000);
        chmod(vfsStream::url("root/cache/foo"), 0555);
        $this->assertFalse($this->sut()->clearCache());
        $this->assertFileExists(vfsStream::url("root/cache/foo/bar.jpg"));
        $this->assertFileExists(vfsStream::url("root/cache/foo"));
    }
}
