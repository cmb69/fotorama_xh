<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $script
 * @var string $error
 * @var string $name
 * @var string $action
 * @var string $token
 * @var string $base_url
 * @var object{caption:string,width:string,ratio:string,thumbs:bool,fullscreen:string,transition:string,images:string} $gallery
 */
?>

<script type="module" src="<?=$this->esc($script)?>"></script>
<article class="fotorama_editor">
  <h1>Fotorama – <?=$this->esc($name)?></h1>
<?if ($error):?>
    <?=$this->raw($error)?>
<?endif?>
  <form action="<?=$action?>" method="post">
    <div class="fotorama_gallery">
      <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
      <input type="hidden" name="fotorama_gallery" value="<?=$this->esc($name)?>">
      <p>
        <label for="fotorama_caption"><?=$this->text("label_caption")?></label>
        <input id="fotorama_caption" name="caption" value="<?=$this->esc($gallery->caption)?>">
      </p>
      <p>
        <label for="fotorama_width">
          <span><?=$this->text("label_width")?></span>
          <span class="fotorama_help"><?=$this->text("help_width")?></span>
        </label>
        <input id="fotorama_width" name="width" value="<?=$this->esc($gallery->width)?>">
      </p>
      <p>
        <label for="fotorama_ratio">
          <span><?=$this->text("label_ratio")?></span>
          <span class="fotorama_help"><?=$this->text("help_ratio")?></span>
        </label>
        <input id="fotorama_ratio" name="ratio" value="<?=$this->esc($gallery->ratio)?>">
      </p>
      <p>
        <input type="hidden" name="thumbs" value="">
        <input type="checkbox" id="fotorama_nav" name="thumbs" <?=$this->checked($gallery->thumbs)?>>
        <label for="fotorama_nav"><?=$this->text("label_nav")?></label>
      </p>
      <p>
        <label for="fotorama_fullscreen"><?=$this->text("label_fullscreen")?></label>
        <select id="fotorama_fullscreen" name="fullscreen">
          <option value="" <?=$this->selected("", $gallery->fullscreen)?>><?=$this->text("label_fullscreen_false")?></option>
          <option value="true" <?=$this->selected("true", $gallery->fullscreen)?>><?=$this->text("label_fullscreen_true")?></option>
          <option value="native" <?=$this->selected("native", $gallery->fullscreen)?>><?=$this->text("label_fullscreen_native")?></option>
        </select>
      </p>
      <p>
        <label for="fotorama_transition"><?=$this->text("label_transition")?></label>
        <select id="fotorama_transition" name="transition">
          <option value="slide" <?=$this->selected("slide", $gallery->transition)?>><?=$this->text("label_slide")?></option>
          <option value="crossfade" <?=$this->selected("crossfade", $gallery->transition)?>><?=$this->text("label_crossfade")?></option>
          <option value="dissolve" <?=$this->selected("dissolve", $gallery->transition)?>><?=$this->text("label_dissolve")?></option>
        </select>
      </p>
    </div>
    <input type="hidden" name="gallery_images" value="<?=$this->esc($gallery->images)?>">
    <fieldset class="fotorama_images">
      <legend><?=$this->text("label_images")?></legend>
      <ul data-base-url="<?=$this->esc($base_url)?>">
      </ul>
      <p class="fotorama_controls">
        <button class="fotorama_add_image" type="button"><?=$this->text("label_add")?></button>
      </p>
    </fieldset>
    <p class="fotorama_controls">
      <button><?=$this->text("label_save")?></button>
    </p>
  </form>
  <template class="fotorama_template">
    <li>
      <img class="fotorama_thumb" src="">
      <label class="fotorama_path">
        <span><?=$this->text("label_path")?></span>
        <input>
      </label>
      <label class="fotorama_caption">
        <span><?=$this->text("label_caption")?></span>
        <input>
      </label>
      <div>
        <button class="fotorama_move_image" type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="1em" height="1em" fill="currentColor"><title><?=$this->text("label_move_up")?></title><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M350 177.5c3.8-8.8 2-19-4.6-26l-136-144C204.9 2.7 198.6 0 192 0s-12.9 2.7-17.4 7.5l-136 144c-6.6 7-8.4 17.2-4.6 26s12.5 14.5 22 14.5l88 0 0 192c0 17.7-14.3 32-32 32l-80 0c-17.7 0-32 14.3-32 32l0 32c0 17.7 14.3 32 32 32l80 0c70.7 0 128-57.3 128-128l0-192 88 0c9.6 0 18.2-5.7 22-14.5z"/></svg></button>
        <button class="fotorama_delete_image" type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor"><title><?=$this->text("label_delete")?></title><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg></button>
      </div>
    </li>
  </template>
</article>
