<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $script
 * @var string $stylesheet
 * @var string $caption
 * @var array<string,mixed> $config
 * @var list<object{filename:string,caption:string,description:string,thumbnail:string}> $images
 * @var bool $thumbnails
 */
?>

<figure class="fotorama_gallery">
<?if ($caption):?>
  <figcaption><?=$this->esc($caption)?></figcaption>
<?endif?>
  <div class="fotorama_lightbox">
<?foreach ($images as $image):?>
    <a href="<?=$this->esc($image->filename)?>" title="<?=$this->esc($image->caption)?>">
      <img src="<?=$this->esc($image->thumbnail)?>" title="<?=$this->esc($image->caption)?>" alt="<?=$this->esc($image->description)?>">
    </a>
<?endforeach?>
  </div>
</figure>
