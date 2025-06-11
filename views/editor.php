<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $name
 * @var string $action
 * @var string $token_input
 * @var string $xml
 */
?>

<h1>Fotorama – <?=$this->esc($name)?></h1>
<form action="<?=$action?>" method="post">
<?=$this->raw($token_input)?>
<input type="hidden" name="admin" value="plugin_main">
<input type="hidden" name="fotorama_gallery" value="<?=$this->esc($name)?>">
<textarea rows="25" cols="80" class="xh_file_edit" name="fotorama_text"><?=$this->esc($xml)?></textarea>
<button name="action" value="save"><?=$this->text("label_save")?></button>
</form>
