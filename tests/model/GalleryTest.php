<?php

namespace Fotorama\Model;

use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
{
    private const GALLERY = <<<'EOS'
        <?xml version="1.0" encoding="UTF-8"?>
        <gallery path="somewhere" width="400px" ratio="16/9" nav="thumbs" fullscreen="native" transition="crossfade">
          <pic path="image.jpg" caption="An Image"/>
        </gallery>
        EOS . "\n";

    public function testSerializesUnserialization(): void
    {
        $gallery = Gallery::fromString(self::GALLERY, "");
        $this->assertEquals(self::GALLERY, $gallery->toString());
    }
}
