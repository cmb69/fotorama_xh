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

class PluginInfoCommand
{
    public function execute(): void
    {
        global $pth, $plugin_tx;

        echo '<h1>Fotorama</h1>'
            . '<img src="' . $pth['folder']['plugins'] . 'fotorama/fotorama.png"'
                . ' class="fotorama_logo" alt="'
                . $plugin_tx['fotorama']['alt_logo'] . '">'
            . '<p>Version: ' . FOTORAMA_VERSION . '</p>'
            . '<p>Codeeditor_XH is powered by <a href="http://fotorama.io/">'
            . 'Fotorama</a>.</p>'
            . '<p>Copyright &copy; 2015-2021 <a href="http://3-magi.net">'
            . 'Christoph M. Becker</a></p>'
            . '<p class="fotorama_license">This program is free software:'
            . 'you can redistribute it and/or modify'
            . ' it under the terms of the GNU General Public License as published by'
            . ' the Free Software Foundation, either version 3 of the License, or'
            . ' (at your option) any later version.</p>'
            . '<p class="fotorama_license">This program is distributed in the hope'
            . ' that it will be useful,'
            . ' but <em>without any warranty</em>; without even the implied warranty'
            . ' of <em>merchantability</em> or <em>fitness for a particular purpose'
            . '</em>.  See the GNU General Public License for more details.</p>'
            . '<p class="fotorama_license">You should have received a copy of the'
            . ' GNU General Public License'
            . ' along with this program.  If not, see'
            . ' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/'
            . '</a>.</p>';
    }
}
