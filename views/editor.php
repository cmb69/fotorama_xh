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
    <fieldset class="fotorama_gallery">
      <legend>Gallery</legend>
      <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
      <input type="hidden" name="fotorama_gallery" value="<?=$this->esc($name)?>">
      <p>
        <label for="fotorama_caption"><?=$this->text("label_caption")?></label>
        <input id="fotorama_caption" name="caption" value="<?=$this->esc($gallery->caption)?>">
      </p>
      <p>
        <label for="fotorama_width"><?=$this->text("label_width")?></label>
        <input id="fotorama_width" name="width" value="<?=$this->esc($gallery->width)?>">
      </p>
      <p>
        <label for="fotorama_ratio"><?=$this->text("label_ratio")?></label>
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
          <option <?=$this->selected("", $gallery->fullscreen)?>></option>
          <option <?=$this->selected("true", $gallery->fullscreen)?>>true</option>
          <option <?=$this->selected("native", $gallery->fullscreen)?>>native</option>
        </select>
      </p>
      <p>
        <label for="fotorama_transition"><?=$this->text("label_transition")?></label>
        <select id="fotorama_transition" name="transition">
          <option <?=$this->selected("slide", $gallery->transition)?>>slide</option>
          <option <?=$this->selected("crossfade", $gallery->transition)?>>crossfade</option>
          <option <?=$this->selected("dissolve", $gallery->transition)?>>dissolve</option>
        </select>
      </p>
    </fieldset>
    <input type="hidden" name="gallery_images" value="<?=$this->esc($gallery->images)?>">
    <fieldset>
      <legend>Images</legend>
      <ul>
      </ul>
      <p>
        <button class="fotorama_add" type="button">Add</button>
      </p>
    </fieldset>
    <button><?=$this->text("label_save")?></button>
  </form>
  <template class="fotorama_template">
    <li>
      <label class="fotorama_path">
        <span>Path</span>
        <input>
      </label>
      <label class="fotorama_caption">
        <span>Caption</span>
        <input>
      </label>
      <button class="fotorama_move" type="button">Move</button>
      <button class="fotorama_delete" type="button">Delete</button>
    </li>
  </template>
</article>
