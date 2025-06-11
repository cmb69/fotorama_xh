<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\View;
use XH\CSRFProtection;

class GalleryListCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    private View $view;

    protected function setUp(): void
    {
        global $plugin_tx, $_XH_csrfProtection;
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        $_XH_csrfProtection = $this->createStub(CSRFProtection::class);
        $_XH_csrfProtection->method("tokenInput")->willReturn('<input type="hidden" name="csrf_token" value="1234">');
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->view = new View("./views/", $plugin_tx["fotorama"]);
    }

    private function sut(): GalleryListCommand
    {
        return new GalleryListCommand($this->galleryService, $this->view);
    }

    public function testRendersOverview(): void
    {
        $this->galleryService->method("findAllGalleries")->willReturn(["gallery1", "gallery2"]);
        $this->galleryService->method("findImageFolders")->willReturn(["folder1", "folder2"]);
        ob_start();
        $this->sut()->execute();
        $output = ob_get_clean();
        Approvals::verifyHtml($output);
    }
}
