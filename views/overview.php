<?php

use Plib\View;

/**
 * @var View $this
 * @var string $url
 * @var list<string> $galleries
 * @var string $action
 * @var string $token
 * @var list<string> $folders
 */
?>

<h1>Fotorama – <?=$this->text("menu_main")?></h1>
<ul>
<?foreach ($galleries as $gallery):?>
  <li><a href="<?=$this->esc($url . $gallery)?>"><?=$this->esc($gallery)?></a></li>
<?endforeach?>
</ul>
<form action="<?=$this->esc($action)?>" method="post">
  <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
  <input type="hidden" name="admin" value="plugin_main">
  <fieldset>
    <legend><?=$this->text("label_create_gallery")?></legend>
    <p>
      <label>
        <span><?=$this->text("label_name")?></span>
        <input type="text" name="fotorama_gallery"></label>
    </p>
    <p>
      <label>
        <span><?=$this->text("label_folder")?></span>
        <select name="fotorama_folder">
<?foreach ($folders as $folder):?>
          <option><?=$this->esc($folder)?></option>
<?endforeach?>
        </select>
      </label>
    </p>
    <p>
      <button class="submit" name="action" value="create"><?=$this->text("label_create")?></button>
    </p>
  </fieldset>
</form>
