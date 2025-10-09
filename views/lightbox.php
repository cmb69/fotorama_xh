<?php

use Fotorama\Dto\ImageDto;
use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $lightbox_script
 * @var string $stylesheet
 * @var string $rel
 * @var string $caption
 * @var array<string,mixed> $config
 * @var list<ImageDto> $images
 * @var bool $thumbnails
 */
?>

<link rel="stylesheet" type="text/css" href="<?=$this->esc($stylesheet)?>">
<figure class="fotorama_gallery">
<?if ($caption):?>
  <figcaption><?=$this->esc($caption)?></figcaption>
<?endif?>
  <div class="fotorama_lightbox">
<?foreach ($images as $image):?>
    <a rel="<?=$this->esc($rel)?>" href="<?=$this->esc($image->filename)?>" title="<?=$this->esc($image->caption)?>">
      <img src="<?=$this->esc($image->thumbnail)?>" srcset="<?=$this->esc($image->srcset)?>" loading="lazy" sizes="auto" width="<?=$this->esc($image->width)?>" height="<?=$this->esc($image->height)?>" title="<?=$this->esc($image->caption)?>" alt="<?=$this->esc($image->description)?>">
    </a>
<?endforeach?>
  </div>
</figure>
