<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\View;

class GalleryAdminCommandTest extends TestCase
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

    private function sut(): GalleryAdminCommand
    {
        return new GalleryAdminCommand($this->galleryService, $this->csrfProtector, $this->view);
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
