<?php

namespace Fotorama;

use ApprovalTests\Approvals;
use Fotorama\Model\Gallery;
use Fotorama\Model\ImageFinder;
use Fotorama\Model\ThumbnailService;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\DocumentStore2 as DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class GalleryAdminCommandTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;
    /** @var ImageFinder&Stub */
    private $imageFinder;
    /** @var ThumbnailService&MockObject */
    private $thumbnailService;
    private DocumentStore $store;
    /** @var CsrfProtector&Stub */
    private $csrfProtector;
    private View $view;

    protected function setUp(): void
    {
        vfsStream::setup("root");
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["fotorama"];
        $this->imageFinder = $this->createStub(ImageFinder::class);
        $this->thumbnailService = $this->createMock(ThumbnailService::class);
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("1234");
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["fotorama"]);
    }

    private function sut(): GalleryAdminCommand
    {
        return new GalleryAdminCommand(
            "./plugins/fotorama/",
            $this->conf,
            $this->imageFinder,
            $this->thumbnailService,
            $this->store,
            $this->csrfProtector,
            $this->view
        );
    }

    public function testRendersOverview(): void
    {
        Gallery::create("gallery1", "gallery1", $this->store);
        Gallery::create("gallery2", "gallery2", $this->store);
        $this->store->commit();
        $this->imageFinder->method("folders")->willReturn(["folder1", "folder2"]);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=plugin_tx&normal",
        ]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – Galleries", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterCreating(): void
    {
        $this->imageFinder->method("isFolder")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?&action=update&fotorama_gallery=gallery", $response->location());
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
        $this->imageFinder->method("isFolder")->willReturn(false);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The folder “folder” does not exist!", $response->output());
    }

    public function testReportsInvalidGalleryNameWhenCreating(): void
    {
        $this->imageFinder->method("isFolder")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "not allowed",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Gallery name “not allowed” is invalid!", $response->output());
    }

    public function testReportsExistingGalleryWhenCreating(): void
    {
        $this->imageFinder->method("isFolder")->willReturn(true);
        Gallery::create("gallery", "gallery", $this->store);
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
        $this->assertStringContainsString("The gallery “gallery” does already exist!", $response->output());
    }

    public function testReportsFailureToSaveWhenCreating(): void
    {
        vfsStream::setQuota(0);
        $this->imageFinder->method("isFolder")->willReturn(true);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=create",
            "post" => [
                "fotorama_gallery" => "gallery",
                "fotorama_folder" => "folder",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot save the gallery “gallery”!", $response->output());
    }

    public function testRendersCheckResult(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $request = new FakeRequest(["url" => "http://example.com/?&action=check&fotorama_gallery=test"]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – Check", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testReportsNonExistingGalleryWhenChecking(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=check&fotorama_gallery=test",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot load the gallery “test”!", $response->output());
    }

    public function testReportsNonWellFormedXML(): void
    {
        file_put_contents(vfsStream::url("root/test.xml"), '<?xml version="1.0" encoding="UTF-8"?><gallery>');
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=check&fotorama_gallery=test",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery “test” is not well-formed!", $response->output());
    }

    public function testReportsValidationErrors(): void
    {
        file_put_contents(vfsStream::url("root/test.xml"), '<?xml version="1.0" encoding="UTF-8"?><gallery/>');
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=check&fotorama_gallery=test",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery “test” is invalid!", $response->output());
    }

    public function testRendersEditor(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $request = new FakeRequest(["url" => "http://example.com/?&action=update&fotorama_gallery=test"]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – test", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testRedirectsAfterSaving(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $this->imageFinder->method("filename")->willReturn("./userfiles/images/");
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update&fotorama_gallery=test",
            "post" => [
                "fotorama_do" => "",
                "checksum" => Gallery::read("test", $this->store)->checksum(),
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?&fotorama_gallery=test", $response->location());
    }

    public function testSavingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertSame(403, $response->status());
    }

    public function testReportsNonExistingGallery(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update&fotorama_gallery=test",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot load the gallery “test”!", $response->output());
    }

    public function testReportsConflictWhenSaving(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update&fotorama_gallery=test",
            "post" => [
                "fotorama_do" => "",
                "gallery_images" => "nope",
                "checksum" => "",
            ]
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The gallery has been modified in the meantime!", $response->output());
    }

    public function testReportsInvalidXML(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update&fotorama_gallery=test",
            "post" => [
                "fotorama_do" => "",
                "gallery_images" => "nope",
                "checksum" => Gallery::read("test", $this->store)->checksum(),
            ]
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Invalid gallery!", $response->output());
    }

    public function testReportsFailureToSave(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        vfsStream::setQuota(0);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&action=update&fotorama_gallery=test",
            "post" => [
                "fotorama_do" => "",
                "checksum" => Gallery::read("test", $this->store)->checksum(),
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot save the gallery “test”!", $response->output());
    }

    public function testRendersDeleteConfirmation(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $request = new FakeRequest(["url" => "http://example.com/?&action=delete&fotorama_gallery=test"]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – Delete", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testReportsNonExistingGalleryWhenDeleting(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=delete&fotorama_gallery=test",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot load the gallery “test”!", $response->output());
    }

    public function testDeletesGallery(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=delete&fotorama_gallery=test",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertEmpty($this->store->find('/test\..xml/'));
        $this->assertSame("http://example.com/?&fotorama&admin=plugin_main", $response->location());
    }

    public function testDeletingIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=delete&fotorama_gallery=test",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertSame(403, $response->status());
    }

    public function testReportsFailureToDelete(): void
    {
        Gallery::create("test", "test", $this->store);
        $this->store->commit();
        chmod(vfsStream::url("root"), 0000);
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=delete&fotorama_gallery=test",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot delete the “test” gallery!", $response->output());
    }

    public function testRendersClearsCacheConfirmation(): void
    {
        $request = new FakeRequest(["url" => "http://example.com/?&action=clear_cache&fotorama_gallery=test"]);
        $response = $this->sut()($request);
        $this->assertSame("Fotorama – Clear Cache", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testClearsCache(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $this->thumbnailService->expects($this->once())->method("clearCache")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=clear_cache",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertEmpty($this->store->find('/test\..xml/'));
        $this->assertSame("http://example.com/?&fotorama&admin=plugin_main", $response->location());
    }

    public function testClearingCacheIsCsrfProtected(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=clear_cache",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertSame(403, $response->status());
    }

    public function testReportsFailureToClearCache(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $this->thumbnailService->expects($this->once())->method("clearCache")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&fotorama&admin=plugin_main&action=clear_cache",
            "post" => ["fotorama_do" => ""],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("Cannot clear the thumbnail cache!", $response->output());
    }
}
