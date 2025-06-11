<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $version
 */
?>

<article class="fotorama_pluginfo">
  <h1>Fotorama <?=$this->esc($version)?></h1>
</article>
