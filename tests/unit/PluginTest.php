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

    public function testMakesCreateGalleryCommand(): void
    {
        $this->assertInstanceOf(CreateGalleryCommand::class, Plugin::createGalleryCommand());
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
