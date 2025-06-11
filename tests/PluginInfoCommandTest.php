<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\View;

class PluginInfoCommandTest extends TestCase
{
    private View $view;

    public function setUp(): void
    {
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): PluginInfoCommand
    {
        return new PluginInfoCommand($this->view);
    }

    public function testRendersPluginInfo(): void
    {
        $response = $this->sut()();
        $this->assertSame("Fotorama 1.0beta2", $response->title());
        Approvals::verifyHtml($response->output());
    }
}
