<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $error
 * @var string $url
 * @var list<object{name:string,url:string}> $galleries
 * @var string $action
 * @var string $token
 * @var string $name
 * @var string $path
 * @var list<string> $folders
 */
?>

<h1>Fotorama – <?=$this->text("menu_main")?></h1>
<?if ($error):?>
  <?=$this->raw($error)?>
<?endif?>
<ul>
<?foreach ($galleries as $gallery):?>
  <li><a href="<?=$this->esc($gallery->url)?>"><?=$this->esc($gallery->name)?></a></li>
<?endforeach?>
</ul>
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
