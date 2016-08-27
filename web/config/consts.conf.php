<?php

/* DEFAULT */
$rootPath = str_replace("config", "", dirname(__FILE__));
$rootPath = implode("/", array_slice(explode("/",$rootPath), 0, -1));
define("_PROJECT_ROOT_PATH_", $rootPath);
define("_PROJECT_CONFIG_PATH_", dirname(__FILE__));

define("_PROJECT_NAME_", 'Photobox');

/* TIMEZONE */
define("_DEFAULT_TIMEZONE_", 'Europe/Berlin');