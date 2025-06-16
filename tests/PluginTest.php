<?php

namespace Fotorama;

use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    protected function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx;
        $pth = ["folder" => ["content" => "", "images" => "", "plugins" => ""]];
        $plugin_cf = ["fotorama" => []];
        $plugin_tx = ["fotorama" => []];
    }

    public function testMakesGalleryCommand(): void
    {
        $this->assertInstanceOf(GalleryCommand::class, Plugin::galleryCommand());
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
