<?php

function api_loglogin($msg) {
  global $conn;
  session_start();

  try {
    if ( !array_key_exists( 'pw', $_POST ) ) throw new InvalidArgumentException( 'Password not given' );
    $msg['message'] = 'Invalid password';
    if ( empty( $_POST['pw'] ) ) {
      $_SESSION['allow_logs'] = false;
      $msg['status'] = 'success';
    } else {
      if ( DEBUG ) {
        $salt = substr( base64_encode( openssl_random_pseudo_bytes( 17 ) ), 0, 22 );
        $salt = str_replace( "+", ".", $salt );
        $param = '$' . implode( '$', array(
          "2y", //select the most secure version of blowfish (>=PHP 5.3.7)
          str_pad( 11, 2, "0", STR_PAD_LEFT ), //add the cost in two digits
          $salt //add the salt
        ) );
        $msg['newsalted'] = crypt( $_POST['pw'], $param );
      }
      $stmt = $conn->prepare( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'LoginPass'" );
      $stmt->execute();
      $result = $stmt->fetchColumn();
      if ( $result === false || $result === null ) $found = false;
      else $found = ( $result == crypt( $_POST['pw'], $result ) );
      $_SESSION['allow_logs'] = $found;
      $msg['status'] = $found ? 'success' : 'failure';
    }

  } catch (PDOException $e) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
    $_SESSION['allow_logs'] = false;
  } catch (InvalidArgumentException $e) {
    $msg['status'] = 'failure';
    $msg['message'] = $e->getMessage();
    $_SESSION['allow_logs'] = false;
  }

  $json = json_encode( $msg );
  if ( $json )
    echo $json;
  else
    echo '{"status":"failure","message":"' . json_last_error_msg() . '"}';
}
