<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\Jquery;
use Plib\View;
use SimpleXMLElement;

class GalleryViewTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    /** @var ThumbnailService&Stub */
    private $thumbnailService;
    /** @var Jquery&MockObject */
    private $jquery;
    private View $view;

    protected function setUp(): void
    {
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->thumbnailService = $this->createStub(ThumbnailService::class);
        $this->jquery = $this->createMock(Jquery::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryView
    {
        return new GalleryView(
            "./",
            "./",
            $this->galleryService,
            $this->thumbnailService,
            $this->jquery,
            $this->view
        );
    }

    public function testRendersGallery(): void
    {
        $sxe = new SimpleXMLElement(__DIR__ . "/data/test.xml", 0, true);
        $this->galleryService->method("hasGallery")->willReturn(true);
        $this->galleryService->method("findGallery")->willReturn($sxe);
        $output = $this->sut()->render("test");
        Approvals::verifyHtml($output);
    }
}
