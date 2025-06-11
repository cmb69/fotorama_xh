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

class Controller
{
    public function dispatch(): void
    {
        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(true);
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    /**
     * @return bool
     */
    protected function isAdministrationRequested()
    {
        return XH_wantsPluginAdministration('fotorama');
    }

    protected function handleAdministration(): void
    {
        global $admin, $o;

        $o .= print_plugin_admin('on');
        switch ($admin) {
            case '':
                $o .= $this->render(new PluginInfoCommand());
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
                $this->createGallery();
                break;
            case 'edit':
                $o .= $this->render(new GalleryEditorCommand());
                break;
            case 'save':
                $this->saveGallery();
                break;
            default:
                $o .= $this->render(new GalleryListCommand());
        }
    }

    /**
     * @return string HTML.
     */
    protected function render(Command $command)
    {
        ob_start();
        $command->execute();
        return ob_get_clean();
    }

    protected function createGallery(): void
    {
        $command = new CreateGalleryCommand();
        $command->execute();
    }

    protected function saveGallery(): void
    {
        $command = new SaveGalleryCommand();
        $command->execute();
    }
}
