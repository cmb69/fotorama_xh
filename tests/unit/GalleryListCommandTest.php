<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection;

class GalleryListCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;

    protected function setUp(): void
    {
        global $plugin_tx, $_XH_csrfProtection;
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        $_XH_csrfProtection = $this->createStub(CSRFProtection::class);
        $this->galleryService = $this->createStub(GalleryService::class);
    }

    private function sut(): GalleryListCommand
    {
        return new GalleryListCommand($this->galleryService);
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
