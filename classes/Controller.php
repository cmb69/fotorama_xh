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

use Plib\View;

class Controller
{
    public static function createGalleryCommand(): CreateGalleryCommand
    {
        global $pth, $plugin_tx;
        $view = new View($pth["folder"]["plugins"] . "fotorama/views/", $plugin_tx["fotorama"]);
        return new CreateGalleryCommand($view);
    }

    public static function saveGalleryCommand(): SaveGalleryCommand
    {
        global $pth, $plugin_tx;
        $view = new View($pth["folder"]["plugins"] . "fotorama/views/", $plugin_tx["fotorama"]);
        return new SaveGalleryCommand($view);
    }

    public function dispatch(): void
    {
        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(true);
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    protected function isAdministrationRequested(): bool
    {
        return XH_wantsPluginAdministration('fotorama');
    }

    protected function handleAdministration(): void
    {
        global $admin, $o;

        $o .= print_plugin_admin('on');
        switch ($admin) {
            case '':
                ob_start();
                (new PluginInfoCommand())->execute();
                $o .= ob_get_clean();
                break;
            case 'plugin_main':
                $this->handleMainAction();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    protected function handleMainAction(): void
    {
        global $action, $o;

        switch ($action) {
            case 'create':
                self::createGalleryCommand()->execute();
                break;
            case 'edit':
                ob_start();
                (new GalleryEditorCommand())->execute();
                $o .= ob_get_clean();
                break;
            case 'save':
                self::saveGalleryCommand()->execute();
                break;
            default:
                ob_start();
                (new GalleryListCommand())->execute();
                $o .= ob_get_clean();
        }
    }
}
