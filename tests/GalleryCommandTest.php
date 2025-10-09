<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use Fotorama\Model\ImageFinder;
use Fotorama\Model\ThumbnailService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore2 as DocumentStore;
use Plib\FakeRequest;
use Plib\JavaScript;
use Plib\Jquery;
use Plib\View;

class GalleryCommandTest extends TestCase
{
    private array $conf;
    private DocumentStore $store;
    /** @var ImageFinder&Stub */
    private $imageFinder;
    /** @var ThumbnailService&Stub */
    private $thumbnailService;
    /** @var Jquery&MockObject */
    private $jquery;
    /** @var JavaScript&MockObject */
    private $javaScript;
    private View $view;

    protected function setUp(): void
    {
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["fotorama"];
        $this->store = new DocumentStore(__DIR__ . "/" . "data/");
        $this->imageFinder = $this->createStub(ImageFinder::class);
        $this->imageFinder->method("filename")->willReturnArgument(0);
        $this->thumbnailService = $this->createStub(ThumbnailService::class);
        $this->jquery = $this->createMock(Jquery::class);
        $this->javaScript = $this->createMock(JavaScript::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryCommand
    {
        return new GalleryCommand(
            "./plugins/fotorama/",
            $this->conf,
            $this->store,
            $this->imageFinder,
            $this->thumbnailService,
            $this->jquery,
            $this->javaScript,
            $this->view
        );
    }

    public function testRendersLightboxGallery(): void
    {
        $this->conf["gallery_frontend"] = "lightbox";
        $request = new FakeRequest();
        $response = $this->sut()($request, "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersFotoramaGallery(): void
    {
        $this->conf["gallery_frontend"] = "fotorama";
        $this->javaScript->expects($this->once())->method("include")->with("./plugins/fotorama/js/fotorama");
        $request = new FakeRequest();
        $response = $this->sut()($request, "test");
        Approvals::verifyHtml($response->output());
    }
}
