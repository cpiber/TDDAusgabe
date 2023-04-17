<?php


session_start();

function login($out = true) {
  global $conn;
  global $servername;

  if ( isset( $_SESSION['user'] ) && isset( $_SESSION['pw'] ) ) {
    $username = $_SESSION['user'];
    $password = $_SESSION['pw'];

    $conn = connectdb($servername, $username, $password);
    if ( is_string($conn) ) {
      if ( $out ) connect_error($conn);
      return $conn;
    }
    return true;

  } else {
    if ( $out ) connect_error();
    return false;
  }
}


function connectdb($servername, $username, $password) {
  try {
    $c = new PDO( "mysql:host=$servername;dbname=tdd;charset=utf8", $username, $password );
    // set the PDO error mode to exception
    $c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    //echo "Connected successfully";
    return $c;
    
  } catch ( PDOException $e ) {
    $c = null;
    return $e->getMessage();
  }
}

function connect_error($error = false) {
  global $conn;
  if ( isset( $_GET['api'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    printf( '{"status":"failure", "type":"Connection failed", "loggedin": false, "message":"%s"}', $error ? $conn->getMessage() : "Not logged in." );
  } else {
    echo "<!DOCTYPE html><html><head>";
    echo "<title>Tischlein Deck Dich Login</title>";
    echo "<meta charset=\"UTF-8\">";
    echo "</head><body>";
    echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
    loginform( $error ? "<span style=\"color:red\">Login failed. $error</span>" : "" );
    echo "</body></html>";
  }
  exit;
}

function loginstyles() { ?>
  <style>
    .float-middle {
      max-width: 300px;
      border: 1px solid grey;
      background-color: lightgrey;
      padding: 20px 50px;
      margin: auto;
      margin-top: 40px;
    }
    form input {
      width: 100%;
      box-sizing: border-box;
    }
    @media (max-width: 360px) {
      .float-middle {
        padding: 20px 30px;
      }
    }
    @media (max-width: 320px) {
      .float-middle {
        padding: 20px 15px;
      }
    }
  </style>
<?php }

function loginform( $msg = "", $url = "" ) {
  if ( $url == "" ) {
    if ( isset( $_GET['url'] ) ) {
      $url = $_GET['url'];
    } else if ( !isset( $_GET['login' ] ) ) {
      $url = sprintf( "?%s", $_SERVER['QUERY_STRING'] );
    }
  }
  loginstyles(); ?>
  <div class="float-middle"><form action="<?php echo "?login" . ( $url == "" ? "" : "&url=".urlencode($url) ); ?>" method="POST">
    <?php echo $msg; ?>
    <h1>Login</h1><br />
    <input type="hidden" name="login" value=true />
    <input type="hidden" name="url" value="<?php echo $url ?>" />
    <input type="text" id="username" name="username" placeholder="Username" autocomplete="username" />
    <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" />
    <input type="submit" value="OK" />
    <p>&nbsp;</p>
    <p style="text-align: right; padding: 0 10px; margin: 2px 0;"><?php echo isset( $_GET['setup'] ) || isset( $_GET['url'] ) ? '<a href="?login">Zurück</a>' : ''; ?>  <!--<a href="?setup">Setup</a>--></p>
  </form></div>
<?php }


if ( isset( $_GET['login'] ) && isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
  $_SESSION['user'] = $_POST['username'];
  $_SESSION['pw'] = $_POST['password'];
  login();
  $url = isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : $_SERVER['SCRIPT_NAME'];
  header( "LOCATION: $url" );
  exit;

} else if ( isset( $_GET['api'] ) && $_GET['api'] == 'login' && isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
  header( "Content-Type: application/json; charset=UTF-8" );
  $_SESSION['user'] = $_POST['username'];
  $_SESSION['pw'] = $_POST['password'];

  if ( ( $err = login(false) ) !== true ) {
    echo json_encode( array(
      'status' => 'failure',
      'loggedin' => false,
      'message' => $err,
    ) );
  } else {
    echo json_encode( array(
      'status' => 'success',
      'loggedin' => true,
    ) );
  }
  exit;

} else if ( isset( $_GET['login'] ) ) {
  session_destroy();
  $_SESSION = array();
}


if ( isset( $_GET['setup'] ) ) return;

login();
session_write_close();

?>