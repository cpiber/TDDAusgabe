<?php


session_start();

if ( isset( $_GET['login'] ) && isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
  $_SESSION['user'] = $_POST['username'];
  $_SESSION['pw'] = $_POST['password'];
  $url = ( isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : $_SERVER['SCRIPT_NAME'] );
  header( "LOCATION: " . $url );
  exit;

} else if ( isset( $_GET['login'] ) ) {
  session_destroy();
  $_SESSION = array();
}

if ( isset( $_SESSION['user'] ) && isset( $_SESSION['pw'] ) ) {
  $username = $_SESSION['user'];
  $password = $_SESSION['pw'];

  $conn = connectdb($servername, $username, $password);
  if ( get_class($conn) != "PDO" ) {
    if ( isset( $_GET['post'] ) ) {
      header("Content-Type: application/json; charset=UTF-8");
      echo '{"status":"failure", "type":"Connection failed", "message":"' . $conn->getMessage() . '"}';
      exit;
    } else {
      echo "<html><body>";
      loginform( "<span style=\"color:red\">Login failed.</span>", ( isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : '' ) );
      echo "</body></html>";
      exit;
    }
  }

} else {
  echo "<html><body>";
  echo "<link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" />";
  loginform( '', ( isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : '' ) );
  echo "</body></html>";
  exit;
}


function connectdb($servername, $username, $password) {
  try {
    $c = new PDO( "mysql:host=$servername;dbname=tdd", $username, $password );
    // set the PDO error mode to exception
    $c->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    //echo "Connected successfully";
    return $c;
    
  } catch ( PDOException $e ) {
    $c = null;
    return $e;
  }
}

function loginform( $msg = "", $url = "" ) { ?>
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
    }
  </style>
  <div class="float-middle"><form action="<?php echo "?login" . ( $url == "" ? "" : "&url=".urlencode($url) ); ?>" method="POST">
    <?php echo $msg; ?>
    <h1>Login</h1><br>
    <input type="hidden" name="login" value=true>
    <input type="text" id="username" name="username" placeholder="Username"><br>
    <input type="password" id="password" name="password" placeholder="Password"><br>
    <input type="submit" value="OK">
    <p>&nbsp;</p>
    <p style="text-align: right; padding: 0 10px; margin: 2px 0;"><a href="?login">Zurück</a>  <!--<a href="?setup">Setup</a>--></p>
  </form></div>
<?php }


?>