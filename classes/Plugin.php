<?php

/*
Copyright 2015-2021 Christoph M. Becker

This file is part of Fotorama_XH.

Fotorama_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fotorama_XH is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Fotorama;

use Plib\Jquery;
use Plib\View;

class Plugin
{
    public const VERSION = "1.0beta2";

    public static function galleryView(): GalleryView
    {
        global $pth;
        return new GalleryView(
            $pth["folder"]["plugins"] . "fotorama/",
            $pth["folder"]["images"],
            new GalleryService(),
            new Jquery($pth["folder"]["plugins"] . "jquery/"),
            self::view()
        );
    }

    public static function galleryListCommand(): GalleryListCommand
    {
        return new GalleryListCommand();
    }

    public static function createGalleryCommand(): CreateGalleryCommand
    {
        return new CreateGalleryCommand(
            self::view()
        );
    }

    public static function galleryEditorCommand(): GalleryEditorCommand
    {
        return new GalleryEditorCommand();
    }

    public static function saveGalleryCommand(): SaveGalleryCommand
    {
        return new SaveGalleryCommand(
            self::view()
        );
    }

    public static function pluginInfoCommand(): PluginInfoCommand
    {
        return new PluginInfoCommand();
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;
        return new View($pth["folder"]["plugins"] . "fotorama/views/", $plugin_tx["fotorama"]);
    }
}
