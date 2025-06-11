<?php

namespace Fotorama;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\View;

class CreateGalleryCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    /** @var CsrfProtector&Stub */
    private $csrfProtector;
    private $view;

    protected function setUp(): void
    {
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): CreateGalleryCommand
    {
        return new CreateGalleryCommand($this->galleryService, $this->csrfProtector, $this->view);
    }

    public function testRedirectsAfterCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(true);
        $this->galleryService->method("saveGalleryXML")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()->execute($request);
        $this->assertSame("http://example.com/?&action=edit&fotorama_gallery=gallery", $response->location());
    }

    public function testCreatingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest();
        $response = $this->sut()->execute($request);
        $this->assertSame(403, $response->status());
    }
}
