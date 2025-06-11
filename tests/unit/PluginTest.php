<?php

namespace Fotorama;

use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    protected function setUp(): void
    {
        global $pth, $plugin_tx;
        $pth = ["folder" => ["plugins" => ""]];
        $plugin_tx = ["fotorama" => []];
    }

    public function testMakesGalleryView(): void
    {
        $this->assertInstanceOf(GalleryView::class, Plugin::galleryView());
    }

    public function testMakesGalleryListCommand(): void
    {
        $this->assertInstanceOf(GalleryListCommand::class, Plugin::galleryListCommand());
    }

    public function testMakesCreateGalleryCommand(): void
    {
        $this->assertInstanceOf(CreateGalleryCommand::class, Plugin::createGalleryCommand());
    }

    public function testMakesGalleryEditorCommand(): void
    {
        $this->assertInstanceOf(GalleryEditorCommand::class, Plugin::galleryEditorCommand());
    }

    public function testMakesSaveGalleryCommand(): void
    {
        $this->assertInstanceOf(SaveGalleryCommand::class, Plugin::saveGalleryCommand());
    }

    public function testMakesPluginInfoCommand(): void
    {
        $this->assertInstanceOf(PluginInfoCommand::class, Plugin::pluginInfoCommand());
    }
}
