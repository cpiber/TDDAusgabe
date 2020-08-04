<?php

// debugging
define ( 'DEBUG', true );
if ( DEBUG ) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}


$servername = "localhost";

$admu = "root"; $admp = ""; //Admin login, keep blank while not using setup!

define( 'VERSION', 2 );
define( 'DB_VER', 7 );



require "server/files.php";
require "server/setup.php";
require "server/login.php";
require "server/upgrade.php";

require "server/api.php";
require "server/pages.php";

// Document
require "files/doc.php";