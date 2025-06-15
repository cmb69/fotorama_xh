<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use Fotorama\Model\ThumbnailService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore2 as DocumentStore;
use Plib\FakeRequest;
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
            "./plugins/fotorama/",
            "./",
            $this->store,
            $this->thumbnailService,
            $this->jquery,
            $this->view
        );
    }

    public function testRendersGallery(): void
    {
        $request = new FakeRequest();
        $response = $this->sut()($request, "test");
        Approvals::verifyHtml($response->output());
    }

    public function testIncludeJqueryOnce(): void
    {
        $this->jquery->expects($this->once())->method("include");
        $this->jquery->expects($this->once())->method("includePlugin")->with("fotorama");
        $sut = $this->sut();
        $request = new FakeRequest();
        $sut($request, "test");
        $sut($request, "test");
    }
}
