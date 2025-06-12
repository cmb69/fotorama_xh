<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use Fotorama\Model\Gallery;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class GalleryAdminCommandTest extends TestCase
{
    /** @var GalleryService&Stub */
    private $galleryService;
    private DocumentStore $store;
    /** @var CsrfProtector&Stub */
    private $csrfProtector;
    private View $view;

    protected function setUp(): void
    {
        vfsStream::setup("root");
        $this->galleryService = $this->createStub(GalleryService::class);
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("1234");
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryAdminCommand
    {
        return new GalleryAdminCommand($this->galleryService, $this->store, $this->csrfProtector, $this->view);
    }

    public function testRendersOverview(): void
    {
        Gallery::create("gallery1", $this->store);
        Gallery::create("gallery2", $this->store);
        $this->store->commit();
        $this->galleryService->method("findImageFolders")->willReturn(["folder1", "folder2"]);
        $request = new FakeRequest();
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – Galleries", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterCreating(): void
    {
        $this->galleryService->method("hasImageFolder")->willReturn(true);
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
        Gallery::create("gallery", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery &quot;gallery&quot; does already exist!", $response->output());
    }

    public function testReportsFailureToSaveWhenCreating(): void
    {
        vfsStream::setQuota(0);
        $this->galleryService->method("hasImageFolder")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Can't save &quot;gallery&quot;!", $response->output());
    }

    public function testRendersEditor(): void
    {
        $request = new FakeRequest(["url" => "http://example.com/?&action=edit&fotorama_gallery=test"]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – test", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterSaving(): void
    {
        Gallery::create("test", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=save",
            "post" => [
                "fotorama_gallery" => "test",
                "fotorama_text" => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><gallery path=""/>',
            ],
        ]);
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

    public function testReportsNonExistingGallery(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=save&fotorama_gallery=test",
            "post" => [
                "fotorama_gallery" => "test",
                "fotorama_text" => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><gallery/>',
            ]
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery &quot;test&quot; does not exist!", $response->output());
    }

    public function testReportsInvalidXML(): void
    {
        Gallery::create("test", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=save&fotorama_gallery=test",
            "post" => [
                "fotorama_gallery" => "test",
                "fotorama_text" => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><gallery/>',
            ]
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Invalid XML!", $response->output());
    }

    public function testReportsFailureToSave(): void
    {
        Gallery::create("test", $this->store);
        $this->store->commit();
        vfsStream::setQuota(0);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=save&fotorama_gallery=test",
            "post" => [
                "fotorama_gallery" => "test",
                "fotorama_text" => '<?xml version="1.0" encoding="UTF-8" standalone="no"?><gallery path=""/>',
            ]
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Can't save &quot;test&quot;!", $response->output());
    }
}
