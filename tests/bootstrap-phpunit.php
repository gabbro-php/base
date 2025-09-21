<?php declare(strict_types=1);

require "../src/SimpleLoader.php";

use gabbro\SimpleLoader;

$loader = SimpleLoader::getInstance();
$loader->enableAutoload();
$loader->addPath(__DIR__, "gabbro\\test");

