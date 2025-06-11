<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\View;

class GalleryEditorCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    /** @var CsrfProtector&Stub */
    private $csrfProtector;
    private View $view;

    protected function setUp(): void
    {
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("1234");
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryEditorCommand
    {
        return new GalleryEditorCommand($this->galleryService, $this->csrfProtector, $this->view);
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
