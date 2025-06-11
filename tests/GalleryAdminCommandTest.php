<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
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
        global $plugin_cf;
        $plugin_cf['fotorama']['xml_auto_validate'] = "";
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
        $request = new FakeRequest();
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(true);
        $this->galleryService->method("saveGalleryXML")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?&action=edit&fotorama_gallery=gallery", $response->location());
    }

    public function testCreatingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest(["url" => "http://example.com/?&action=create"]);
        $response = $this->sut()($request);
        $this->assertSame(403, $response->status());
    }

    public function testReportsInvalidFolderNameWhenCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(false);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The folder &quot;&quot; does not exist!", $response->output());
    }

    public function testReportsInvalidGalleryNameWhenCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "not allowed",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Gallery name &quot;not allowed&quot; is invalid!", $response->output());
    }

    public function testReportsExistingGalleryWhenCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(true);
        $this->galleryService->method("hasGallery")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery &quot;&quot; does already exist!", $response->output());
    }

    public function testRendersEditor(): void
    {
        $_GET = ["fotorama_gallery" => "test"];
        $request = new FakeRequest(["url" => "http://example.com/?&action=edit"]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterSaving(): void
    {
        $this->galleryService->method("saveGalleryXML")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest(["url" => "http://example.com/?&action=save"]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/", $response->location());
    }

    public function testSavingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest(["url" => "http://example.com/?&action=save"]);
        $response = $this->sut()($request);
        $this->assertSame(403, $response->status());
    }

    public function testReportsFailureToSave(): void
    {
        $_GET = ["fotorama_gallery" => "test"];
        $this->galleryService->method("saveGalleryXML")->willReturn(false);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=save",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Can't save &quot;&quot;!", $response->output());
    }
}
