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

use Plib\Response;
use Plib\SystemChecker;
use Plib\View;

class PluginInfoCommand
{
    private string $pluginFolder;
    private SystemChecker $systemChecker;
    private View $view;

    public function __construct(
        string $pluginFolder,
        SystemChecker $systemChecker,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->systemChecker = $systemChecker;
        $this->view = $view;
    }

    public function __invoke(): Response
    {
        return Response::create($this->view->render("info", [
            "version" => Plugin::VERSION,
            "checks" => $this->checks(),
        ]))->withTitle("Fotorama " . $this->view->esc(Plugin::VERSION));
    }

    /** @return list<string> */
    private function checks(): array
    {
        return [
            $this->checkPhpVersion("7.4.0"),
            $this->checkExtension("dom", true),
            $this->checkExtension("gd", false),
            $this->checkExtension("exif", false),
            $this->checkXHVersion("1.7.0"),
            $this->checkPlibVersion("1.10"),
            $this->checkWritability($this->pluginFolder . "cache/"),
            $this->checkWritability($this->pluginFolder . "config/"),
            $this->checkWritability($this->pluginFolder . "css/"),
            $this->checkWritability($this->pluginFolder . "languages/"),
        ];
    }

    private function checkPhpVersion(string $version): string
    {
        $okay = $this->systemChecker->checkVersion(PHP_VERSION, $version);
        $severity = $okay ? "success" : "fail";
        return $this->view->message($severity, "syscheck_phpversion", $version, $this->state($okay));
    }

    private function checkExtension(string $extension, bool $mandatory): string
    {
        $okay = $this->systemChecker->checkExtension($extension);
        $severity = $okay ? "success" : ($mandatory ? "fail" : "warning");
        return $this->view->message($severity, "syscheck_extension", $extension, $this->state($okay));
    }

    private function checkXhVersion(string $version): string
    {
        $okay = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version");
        $severity = $okay ? "success" : "fail";
        return $this->view->message($severity, "syscheck_xhversion", $version, $this->state($okay));
    }

    private function checkPlibVersion(string $version): string
    {
        $okay = $this->systemChecker->checkPlugin("plib", $version);
        $severity = $okay ? "success" : "fail";
        return $this->view->message($severity, "syscheck_plibversion", $version, $this->state($okay));
    }

    private function checkWritability(string $filename): string
    {
        $okay = $this->systemChecker->checkWritability($filename);
        $severity = $okay ? "success" : "fail";
        return $this->view->message($severity, "syscheck_writable", $filename, $this->state($okay));
    }

    private function state(bool $okay): string
    {
        return $okay ? $this->view->plain("syscheck_yes") : $this->view->plain("syscheck_no");
    }
}
