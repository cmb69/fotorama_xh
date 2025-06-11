<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\View;
use XH\CSRFProtection;

class GalleryEditorCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    private View $view;

    protected function setUp(): void
    {
        global $_XH_csrfProtection;
        $_XH_csrfProtection = $this->createStub(CSRFProtection::class);
        $_XH_csrfProtection->method("tokenInput")->willReturn('<input type="hidden" name="csrf_token" value="1234">');
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryEditorCommand
    {
        return new GalleryEditorCommand($this->galleryService, $this->view);
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
