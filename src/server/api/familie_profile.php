<?php

function api_getfampicture($msg) {
  global $conn;

  if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
    header( 'Content-Type: image/png' );
    $loc = array_key_exists( 'image', $_GET ) ? STATIC_DIR . strval( $_GET['image'] ) : null;

    if ( is_null( $loc ) ) {
      http_response_code( 400 );
    } else if ( strpos( $_GET['image'], '/' ) !== false )  {
      http_response_code( 400 );
    } else if ( !file_exists( $loc ) ) {
      http_response_code( 404 );
      echo require('../../files/placeholder.png');
    } else {
      readfile( $loc );
    }
  } else {
    $loc = array_key_exists( 'key', $_GET ) ? strval( $_GET['key'] ) : null;
    if ( is_null( $loc ) || !in_array( $loc, array( 'ProfilePic', 'ProfilePic2' ) ) || !array_key_exists( 'image', $_FILES ) ) {
      $msg['status'] = 'failure';
      $msg['message'] = 'Invalid ID';
    } else {
      $filename = uniqid() . ".png";
      $filepath = STATIC_DIR . $filename;

      try {
        if ( !file_exists( STATIC_DIR ) ) mkdir( STATIC_DIR, 0755 );
        move_uploaded_file( $_FILES['image']['tmp_name'], $filepath );

        $sql3 = "INSERT INTO `logs` (`Type`, `Val`) VALUES (?, ?)";
        $logdata = array();
        $logdata[] = 'upload';
        $logdata[] = $filename;
        if ( !empty( $logdata ) ) {
          $stmt = $conn->prepare( $sql3 );
          $stmt->execute( $logdata );
        }

        $msg['status'] = 'success';
        $msg['data'] = $filename;
      } catch ( Exception $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
      }
    }

    $json = json_encode( $msg );
    if ( $json )
      echo $json;
    else
      echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
  }
}

?>
