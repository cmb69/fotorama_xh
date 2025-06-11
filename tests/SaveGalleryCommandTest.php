<?php

namespace Fotorama;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\View;

class SaveGalleryCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    /** @var CsrfProtector&Stub */
    private $csrfProtector;
    private View $view;

    protected function setUp(): void
    {
        global $plugin_cf;
        $plugin_cf["fotorama"]["xml_auto_validate"] = "";
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): SaveGalleryCommand
    {
        return new SaveGalleryCommand($this->galleryService, $this->csrfProtector, $this->view);
    }

    public function testRedirectsAfterSaving(): void
    {
        $this->galleryService->method("saveGalleryXML")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest();
        $response = $this->sut()->execute($request);
        $this->assertSame("http://example.com/", $response->location());
    }

    public function testSavingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest();
        $response = $this->sut()->execute($request);
        $this->assertSame(403, $response->status());
    }
}
