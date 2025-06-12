<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $error
 * @var string $name
 * @var string $action
 * @var string $token
 * @var string $xml
 */
?>

<h1>Fotorama – <?=$this->esc($name)?></h1>
<?if ($error):?>
  <?=$this->raw($error)?>
<?endif?>
<form action="<?=$action?>" method="post">
  <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
  <input type="hidden" name="admin" value="plugin_main">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="fotorama_gallery" value="<?=$this->esc($name)?>">
  <textarea rows="25" cols="80" class="fotorama_xml xh_file_edit" name="fotorama_text"><?=$this->esc($xml)?></textarea>
  <button><?=$this->text("label_save")?></button>
</form>
