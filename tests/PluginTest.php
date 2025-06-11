<?php

namespace Fotorama;

use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    protected function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx;
        $pth = ["folder" => ["images" => "", "plugins" => ""]];
        $plugin_cf = ["fotorama" => []];
        $plugin_tx = ["fotorama" => []];
    }

    public function testMakesGalleryView(): void
    {
        $this->assertInstanceOf(GalleryView::class, Plugin::galleryView());
    }

    public function testMakesGalleryAdminCommand(): void
    {
        $this->assertInstanceOf(GalleryAdminCommand::class, Plugin::galleryAdminCommand());
    }

    public function testMakesPluginInfoCommand(): void
    {
        $this->assertInstanceOf(PluginInfoCommand::class, Plugin::pluginInfoCommand());
    }
}
