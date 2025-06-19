<?php

use Fotorama\Dto\ImageDto;
use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $script
 * @var string $stylesheet
 * @var string $caption
 * @var array<string,mixed> $config
 * @var list<ImageDto> $images
 * @var bool $thumbnails
 */
?>

<script type="module" src="<?=$this->esc($script)?>"></script>
<link rel="stylesheet" type="text/css" href="<?=$this->esc($stylesheet)?>">
<figure class="fotorama_gallery" data-config='<?=$this->json($config)?>'>
<?if ($caption):?>
  <figcaption><?=$this->esc($caption)?></figcaption>
<?endif?>
  <div class="fotorama" data-auto="false">
<?foreach ($images as $image):?>
<?if ($thumbnails):?>
    <a href="<?=$this->esc($image->filename)?>" data-caption="<?=$this->esc($image->caption)?>">
<?endif?>
      <img src="<?=$this->esc($image->thumbnail)?>" data-caption="<?=$this->esc($image->caption)?>" alt="<?=$this->esc($image->description)?>">
<?if ($thumbnails):?>
    </a>
<?endif?>
<?endforeach?>
  </div>
</figure>
