<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\SystemChecker;
use Plib\View;

class PluginInfoCommandTest extends TestCase
{
    private SystemChecker $systemChecker;
    private View $view;

    public function setUp(): void
    {
        $this->systemChecker = new FakeSystemChecker();
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): PluginInfoCommand
    {
        return new PluginInfoCommand("./plugins/fotorama/", $this->systemChecker, $this->view);
    }

    public function testRendersPluginInfo(): void
    {
        $response = $this->sut()();
        $this->assertSame("Fotorama 1.0", $response->title());
        Approvals::verifyHtml($response->output());
    }
}
