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
      <button name="action" value="edit"><?=$this->text("label_edit")?></button>
  </form>
  <form action="<?=$this->esc($action)?>" method="post">
    <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
    <fieldset>
      <legend><?=$this->text("label_create_gallery")?></legend>
      <p>
        <label>
          <span><?=$this->text("label_name")?></span>
          <input type="text" name="fotorama_gallery" value="<?=$this->esc($name)?>"></label>
      </p>
      <p>
        <label>
          <span><?=$this->text("label_folder")?></span>
          <select name="fotorama_folder">
<?foreach ($folders as $folder):?>
            <option <?=$this->selected($folder, $path)?>><?=$this->esc($folder)?></option>
<?endforeach?>
          </select>
        </label>
      </p>
      <p>
        <button class="submit"><?=$this->text("label_create")?></button>
      </p>
    </fieldset>
  </form>
</article>
