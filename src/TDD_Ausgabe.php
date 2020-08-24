<?php

// debugging
define ( 'DEBUG', false );
if ( DEBUG ) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}


$servername = "localhost";

define( 'VERSION', '2.0.2' );
define( 'DB_VER', 8 );



require "server/files.php";
require "server/setup.php";
require "server/login.php";
require "server/upgrade.php";

require "server/api.php";
require "server/pages.php";

// Document
require "files/doc.php";