<?php

require_once "./vendor/autoload.php";

require_once "../../cmsimple/classes/CSRFProtection.php";
require_once "../../cmsimple/functions.php";

require_once "../plib/classes/CsrfProtector.php";
require_once "../plib/classes/Document2.php";
require_once "../plib/classes/DocumentStore2.php";
require_once "../plib/classes/Jquery.php";
require_once "../plib/classes/Request.php";
require_once "../plib/classes/Response.php";
require_once "../plib/classes/SystemChecker.php";
require_once "../plib/classes/Url.php";
require_once "../plib/classes/View.php";
require_once "../plib/classes/FakeRequest.php";
require_once "../plib/classes/FakeSystemChecker.php";

require_once "./classes/dto/GalleryDto.php";
require_once "./classes/model/Gallery.php";
require_once "./classes/model/Image.php";
require_once "./classes/model/ImageService.php";
require_once "./classes/model/ThumbnailService.php";
require_once "./classes/GalleryAdminCommand.php";
require_once "./classes/GalleryCommand.php";
require_once "./classes/Plugin.php";
require_once "./classes/PluginInfoCommand.php";

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.6";
