<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $attributes
 * @var list<object{filename:string,caption:string,thumbnail:string}> $images
 * @var bool $thumbnails
 */
?>

<div class="fotorama" <?=$this->raw($attributes)?>>
<?foreach ($images as $image):?>
<?if ($thumbnails):?>
  <a href="<?=$this->esc($image->filename)?>" data-caption="<?=$this->esc($image->caption)?>">
<?endif?>
    <img src="<?=$this->esc($image->thumbnail)?>" data-caption="<?=$this->esc($image->caption)?>" alt="<?=$this->esc($image->caption)?>">
<?if ($thumbnails):?>
  </a>
<?endif?>
<?endforeach?>
</div>
