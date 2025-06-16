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

    /** @dataProvider orientation */
    public function testHeedsOrientation(string $basename, string $col1, string $col2, string $col3, string $col4): void
    {
        $path = __DIR__ . "/../data/$basename";
        $this->sut()->thumbnail($path, 64);
        $path = vfsStream::url("root/cache/" . md5($path) . "_64.jpg");
        $im = imagecreatefromjpeg($path);
        $this->assertSame(128, imagesx($im));
        $this->assertSame(64, imagesy($im));
        imagetruecolortopalette($im, false, 4);
        $colors = $this->colors($im);
        $this->assertSame($colors[$col1], imagecolorat($im, 31, 15));
        $this->assertSame($colors[$col2], imagecolorat($im, 95, 15));
        $this->assertSame($colors[$col3], imagecolorat($im, 31, 47));
        $this->assertSame($colors[$col4], imagecolorat($im, 95, 47));
    }

    public function orientation(): array
    {
        return [
            ["orientation1.jpg", "red", "green", "blue", "white"],
            ["orientation2.jpg", "green", "red", "white", "blue"],
            ["orientation3.jpg", "white", "blue", "green", "red"],
            ["orientation4.jpg", "blue", "white", "red", "green"],
            ["orientation5.jpg", "red", "blue", "green", "white"],
            ["orientation6.jpg", "blue", "red", "white", "green"],
            ["orientation7.jpg", "white", "green", "blue", "red"],
            ["orientation8.jpg", "green", "white", "red", "blue"],
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
        $path = __DIR__ . "/../data/Momiji-WideRGB-yes.jpg";
        $this->sut()->thumbnail($path, 64);
        $path = vfsStream::url("root/cache/" . md5($path) . "_64.jpg");
        getimagesize($path, $info);
        $this->assertSame(str_replace("\n", "", $icc), base64_encode($info["APP2"]));
    }
}
