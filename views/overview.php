<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $error
 * @var string $get_action
 * @var string $sel_gallery
 * @var list<object{name:string,id:string}> $galleries
 * @var string $action
 * @var string $token
 * @var string $name
 * @var string $path
 * @var list<string> $folders
 */
?>

<article class="fotorama_overview">
  <h1>Fotorama – <?=$this->text("menu_main")?></h1>
<?if ($error):?>
    <?=$this->raw($error)?>
<?endif?>
  <form action="<?=$this->esc($get_action)?>" method="get">
    <input type="hidden" name="selected" value="fotorama">
    <input type="hidden" name="admin" value="plugin_main">
    <ul>
<?foreach ($galleries as $gallery):?>
      <li>
        <input type="radio" name="fotorama_gallery" value="<?=$this->esc($gallery->name)?>" id="<?=$this->esc($gallery->id)?>" <?=$this->checked($gallery->name, $sel_gallery)?>/>
        <label for="<?=$this->esc($gallery->id)?>"><?=$this->esc($gallery->name)?>
      </li>
<?endforeach?>
    </ul>
    <p class="fotorama_controls">
      <button name="action" value="update"><?=$this->text("label_edit")?></button>
      <button name="action" value="check"><?=$this->text("label_check")?></button>
      <button name="action" value="delete"><?=$this->text("label_delete")?></button>
      <button name="action" value="clear_cache"><?=$this->text("label_clear_cache")?></button>
  </form>
  <form action="<?=$this->esc($action)?>" method="post">
    <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
    <fieldset>
      <legend><?=$this->text("label_create_gallery")?></legend>
      <p>
        <label for="fotorama_gallery">
          <span><?=$this->text("label_name")?></span>
          <span class="fotorama_help"><?=$this->text("help_name")?></span>
        </label>
        <input type="text" name="fotorama_gallery" id="fotorama_gallery" value="<?=$this->esc($name)?>" required pattern="[a-z0-9\-]+">
      </p>
      <p>
        <label for="fotorama_folder"><?=$this->text("label_folder")?></label>
        <select name="fotorama_folder" id="fotorama_folder">
<?foreach ($folders as $folder):?>
          <option <?=$this->selected($folder, $path)?>><?=$this->esc($folder)?></option>
<?endforeach?>
        </select>
      </p>
      <p>
        <button class="submit"><?=$this->text("label_create")?></button>
      </p>
    </fieldset>
  </form>
</article>
