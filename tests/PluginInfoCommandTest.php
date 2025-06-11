<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class PluginInfoCommandTest extends TestCase
{
    private function sut(): PluginInfoCommand
    {
        return new PluginInfoCommand();
    }

    public function testRendersPluginInfo(): void
    {
        $response = $this->sut()();
        Approvals::verifyHtml($response->output());
    }
}
