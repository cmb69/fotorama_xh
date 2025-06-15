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

use Fotorama\Model\ThumbnailService;
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Jquery;
use Plib\SystemChecker;
use Plib\View;

class Plugin
{
    public const VERSION = "1.0beta2";

    private static ?GalleryCommand $galleryView = null;

    public static function galleryCommand(): GalleryCommand
    {
        global $pth;
        if (self::$galleryView === null) {
            self::$galleryView = new GalleryCommand(
                $pth["folder"]["plugins"] . "fotorama/",
                $pth["folder"]["images"],
                self::store(),
                new ThumbnailService(),
                new Jquery($pth["folder"]["plugins"] . "jquery/"),
                self::view()
            );
        }
        return self::$galleryView;
    }

    public static function galleryAdminCommand(): GalleryAdminCommand
    {
        global $pth;
        return new GalleryAdminCommand(
            $pth["folder"]["plugins"] . "fotorama/",
            new GalleryService(),
            self::store(),
            new CsrfProtector(),
            self::view()
        );
    }

    public static function pluginInfoCommand(): PluginInfoCommand
    {
        global $pth;
        return new PluginInfoCommand(
            $pth["folder"]["plugins"] . "fotorama/",
            new SystemChecker(),
            self::view()
        );
    }

    private static function store(): DocumentStore
    {
        global $pth;
        return new DocumentStore($pth["folder"]["content"] . "fotorama/");
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;
        return new View($pth["folder"]["plugins"] . "fotorama/views/", $plugin_tx["fotorama"]);
    }
}
