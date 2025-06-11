<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class PluginInfoCommandTest extends TestCase
{
    protected function setUp(): void
    {
        global $pth, $plugin_tx;
        $pth = ["folder" => ["plugins" => "./plugins/"]];
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
    }

    private function sut(): PluginInfoCommand
    {
        return new PluginInfoCommand();
    }

    public function testRendersPluginInfo(): void
    {
        $response = $this->sut()->execute();
        Approvals::verifyHtml($response->output());
    }
}
