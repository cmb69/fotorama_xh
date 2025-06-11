<?php

namespace Fotorama;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class GalleryServiceTest extends TestCase
{
    private const FOO_XML = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE gallery SYSTEM
    "http://3-magi.net/userfiles/downloads/dtd/gallery.dtd">
<gallery/>
XML;

    private $sut;

    public function setUp(): void
    {
        global $pth;

        $this->root = vfsStream::setup();
        $pth = array('folder' => array(
            'content' => $this->root->url() . '/content/',
            'images' => $this->root->url() . '/images/'
        ));
        mkdir("{$pth['folder']['content']}fotorama", 0777, true);
        file_put_contents("{$pth['folder']['content']}fotorama/foo.xml", self::FOO_XML);
        touch("{$pth['folder']['content']}fotorama/bar.xml");
        mkdir("{$pth['folder']['images']}test", 0777, true);
        $img = imagecreate(100, 100);
        imagejpeg($img, "{$pth['folder']['images']}test/foo.jpg");
        imagejpeg($img, "{$pth['folder']['images']}test/bar.jpg");
        $this->sut = new GalleryService();
    }

    public function testAllGalleriesAreFound()
    {
        $this->assertEquals(array('bar', 'foo'), $this->sut->findAllGalleries());
    }

    public function testHasGallery()
    {
        $this->assertTrue($this->sut->hasGallery('foo'));
        $this->assertFalse($this->sut->hasGallery('baz'));
    }

    public function testGalleryIsSimpleXMLElement()
    {
        $this->assertInstanceOf('SimpleXMLElement', $this->sut->findGallery('foo'));
    }

    public function testFindsGalleryXml()
    {
        $this->assertEquals(self::FOO_XML, $this->sut->findGalleryXml('foo'));
    }

    public function testSavesGalleryXml()
    {
        global $pth;

        $this->sut->saveGalleryXML('bar', self::FOO_XML);
        $this->assertFileEquals(
            "{$pth['folder']['content']}fotorama/foo.xml",
            "{$pth['folder']['content']}fotorama/bar.xml"
        );
    }

    public function testFindsAllImageFolders()
    {
        $this->assertEquals(array('test'), $this->sut->findImageFolders());
    }

    public function testHasImageFolder()
    {
        $this->assertTrue($this->sut->hasImageFolder('test'));
        $this->assertFalse($this->sut->hasImageFolder('foo'));
    }

    public function testFindsAllImages()
    {
        $this->assertEquals(array('bar.jpg', 'foo.jpg'), $this->sut->findImagesIn('test'));
    }

    public function testImageFolderName()
    {
        $this->assertEquals('vfs://root/images/test', $this->sut->getImageFolderName('test'));
    }
}
