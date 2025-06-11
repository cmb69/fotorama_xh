<?php

/**
 * Copyright 2015-2021 Christoph M. Becker
 *
 * This file is part of Fotorama_XH.
 *
 * Fotorama_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fotorama_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *along with Fotorama_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

use Fotorama\Plugin;

/**
 * @var string $action
 * @var string $admin
 * @var string $o
 */

XH_registerStandardPluginMenuItems(true);
if (XH_wantsPluginAdministration("fotorama")) {
    $o .= print_plugin_admin("on");
    switch ($admin) {
        case "":
            ob_start();
            Plugin::pluginInfoCommand()->execute();
            $o .= ob_get_clean();
            break;
        case "plugin_main":
            switch ($action) {
                case "create":
                    Plugin::createGalleryCommand()->execute();
                    break;
                case "edit":
                    ob_start();
                    Plugin::galleryEditorCommand()->execute();
                    $o .= ob_get_clean();
                    break;
                case "save":
                    Plugin::saveGalleryCommand()->execute();
                    break;
                default:
                    ob_start();
                    Plugin::galleryListCommand()->execute();
                    $o .= ob_get_clean();
            }
            break;
        default:
            $o .= plugin_admin_common();
    }
}
