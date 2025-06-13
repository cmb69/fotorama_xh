<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $error
 * @var string $action
 * @var string $gallery
 * @var string $token
 */
?>

<article class="fotorama_delete">
  <h1>Fotorama – <?=$this->text("label_delete")?></h1>
<?if ($error):?>
  <?=$this->raw($error)?>
<?endif?>
  <form action="<?=$this->esc($action)?>" method="post">
    <input type="hidden" name="fotorama_token" value="<?=$this->esc($token)?>">
    <p class="xh_warning"><?=$this->text("message_delete", $gallery)?></p>
    <p class="fotorama_controls">
      <button name="fotorama_do"><?=$this->text("label_delete")?></button>
    </p>
  </form>
</article>
