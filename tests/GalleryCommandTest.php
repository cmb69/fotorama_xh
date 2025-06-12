<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore2 as DocumentStore;
use Plib\Jquery;
use Plib\View;

class GalleryViewTest extends TestCase
{
    private DocumentStore $store;
    /** @var ThumbnailService&Stub */
    private $thumbnailService;
    /** @var Jquery&MockObject */
    private $jquery;
    private View $view;

    protected function setUp(): void
    {
        $this->store = new DocumentStore(__DIR__ . "/" . "data/");
        $this->thumbnailService = $this->createStub(ThumbnailService::class);
        $this->jquery = $this->createMock(Jquery::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryCommand
    {
        return new GalleryCommand(
            "./",
            "./",
            $this->store,
            $this->thumbnailService,
            $this->jquery,
            $this->view
        );
    }

    public function testRendersGallery(): void
    {
        $output = $this->sut()->render("test");
        Approvals::verifyHtml($output);
    }
}
