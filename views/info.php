<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $version
 */
?>

<h1>Fotorama <?=$this->esc($version)?></h1>
