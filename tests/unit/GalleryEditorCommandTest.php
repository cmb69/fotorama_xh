<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection;

class GalleryEditorCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;

    protected function setUp(): void
    {
        global $plugin_tx, $_XH_csrfProtection;
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        $_XH_csrfProtection = $this->createStub(CSRFProtection::class);
        $_XH_csrfProtection->method("tokenInput")->willReturn('<input type="hidden" name="csrf_token" value="1234">');
        $this->galleryService = $this->createStub(GalleryService::class);
    }

    private function sut(): GalleryEditorCommand
    {
        return new GalleryEditorCommand($this->galleryService);
    }

    public function testRendersEditor(): void
    {
        $_GET = ["fotorama_gallery" => "test"];
        ob_start();
        $this->sut()->execute();
        $output = ob_get_clean();
        Approvals::verifyHtml($output);
    }
}
