<?php


//Check version and upgrade database
$stmt = $conn->query( "SELECT Val FROM `einstellungen` WHERE `Name` = 'Version'" );
$ver = $stmt->fetchColumn();
while (!is_int($ver)) {
  if ( $ver === false ) {
    $ver = 1; // default dbver to 1
  } else {
    $ver = intval($ver);
  }
}

if ( $ver > DB_VER ) {
  // don't mess with newer dbase versions, prompt to upgrade program
  ?><!DOCTYPE html><html>
  <head>
    <title>Tischlein Deck Dich - ERROR</title>
  </head>
  <body>
    <h1>FEHLER</h1>
    <p>Datenbank-Version (<?php echo $ver; ?>) ist zu neu - Aus Sicherheitsgründen wurde die Ausführung des Programms gestoppt.</p>
    <p>Die aktuell laufende Version des Programms erwartet eine ältere Version der Datenbank, eine geänderte Struktur kann unvohersehbare Folgen haben.</p>
  </body>
  </html>
  <?php
  exit;
}

//upgrade
if ( $ver < DB_VER ) {
  if ( $ver <= 1 ) {
    // upgrade to version 2
    // add fam num column and procedures
    try {
      // update tables
      $conn->beginTransaction();
      $conn->exec( "ALTER TABLE `familien` ADD Num int;" );
      $conn->exec( "ALTER TABLE `einstellungen` ADD UNIQUE(`Name`);" );
      $conn->commit();

      $ver = 2;

    } catch ( PDOException $e ) {
      upgrade_error( 2, $e );
    }
  }

  if ( $ver == 2 ) {
    // upgrade to version 3
    // add address and telephone column
    try {
      // update tables
      $conn->beginTransaction();
      $conn->exec( "ALTER TABLE `familien` ADD Adresse varchar(255);" );
      $conn->exec( "ALTER TABLE `familien` ADD Telefonnummer varchar(255);" );
      $conn->commit();

      $ver = 3;

    } catch ( PDOException $e ) {
      upgrade_error( 3, $e );
    }
  }

  if ( $ver == 3 || $ver == 4 ) {
    // update procedures
    // originally added in version 2, then 3, now moved here and altered (and reverted) to new requirements
    try {
      // prodecure for resetting nums
      $conn->beginTransaction();
      $conn->exec( "DROP PROCEDURE IF EXISTS resetFamNum;" );
      $conn->exec( $proc_resetFamNum );
      if ( !isset( $_GET['norenumber'] ) ) $conn->exec( "CALL resetFamNum();" );
      $conn->commit();

      $ver = 5;

    } catch ( PDOException $e ) {
      upgrade_error( 5, $e );
    }
  }

  if ( $ver == 5 ) {
    // update procedures
    try {
      // function for changing num to new group
      $conn->beginTransaction();
      $conn->exec( "DROP FUNCTION IF EXISTS newNum;" );
      $conn->exec( $proc_newNum );
      $conn->commit();

      $ver = 6;

    } catch ( PDOException $e ) {
      upgrade_error( 6, $e );
    }
  }

  if ( $ver == 6 ) {
    try {
      $conn->beginTransaction();

      // function for decoding url-encoded string
      $conn->exec( "DROP FUNCTION IF EXISTS url_decode;" );
      $conn->exec( "
        CREATE FUNCTION `url_decode`(str VARCHAR(255) CHARSET utf8) RETURNS VARCHAR(255) DETERMINISTIC
        BEGIN
          DECLARE X  INT;
          SET X = 128;
          WHILE X  < 192 DO
            SET str = REPLACE(str, CONCAT('%C5%', HEX(X)), UNHEX(CONCAT('C5', HEX(X))));
            SET str = REPLACE(str, CONCAT('%C4%', HEX(X)), UNHEX(CONCAT('C4', HEX(X))));
            SET str = REPLACE(str, CONCAT('%C3%', HEX(X)), UNHEX(CONCAT('C3', HEX(X))));
            SET  X = X + 1;
          END WHILE;
          SET X = 32;
          WHILE X  < 127 DO
            SET str = REPLACE(str, CONCAT('%', HEX(X)), UNHEX(HEX(X)));
            SET  X = X + 1;
          END WHILE;
          SET X = 168; -- C2
          WHILE X  < 192 DO
            SET str = REPLACE(str, CONCAT('%', HEX(X)), UNHEX(CONCAT('C2', HEX(X))));
            SET  X = X + 1;
          END WHILE;
          SET X = 192; -- C3
          WHILE X  < 256 DO
            SET str = REPLACE(str, CONCAT('%', HEX(X)), UNHEX(CONCAT('C3', HEX(X-64))));
            SET  X = X + 1;
          END WHILE;
          SET X = 256; -- C4
          WHILE X  < 320 DO
            SET str = REPLACE(str, CONCAT('%', HEX(X)), UNHEX(CONCAT('C4', HEX(X-128))));
            SET  X = X + 1;
          END WHILE;
          SET X = 320; -- C5
          WHILE X  < 384 DO
            SET str = REPLACE(str, CONCAT('%', HEX(X)), UNHEX(CONCAT('C5', HEX(X-192))));
            SET  X = X + 1;
          END WHILE;
          RETURN REPLACE(str, '+', ' ');
        END;
        " ); // https://stackoverflow.com/a/61549664/
      $conn->exec( "UPDATE `familien` SET `Name` = url_decode(`Name`), `Ort` = url_decode(`Ort`), `Notizen` = url_decode(`Notizen`), `Adresse` = url_decode(`Adresse`), `Telefonnummer` = url_decode(`Telefonnummer`)");
      $conn->exec( "UPDATE `orte` SET `Name` = url_decode(`Name`)");
      $conn->exec( "DROP FUNCTION IF EXISTS url_decode;" ); // one-time use

      $stmt = $conn->query( "SELECT `ID`, `Val` FROM `einstellungen`" );
      $stmt->setFetchMode( PDO::FETCH_ASSOC );

      foreach ( $stmt->fetchAll() as $r ) {
        $stmt2 = $conn->prepare( "UPDATE `einstellungen` SET `Val` = :Val WHERE `ID` = :ID" );
        $stmt2->execute(array(
          ":ID" => $r['ID'],
          ":Val" => rawurldecode($r['Val'])
        ));
      }

      $stmt = $conn->query( "SELECT `date_time`, `aff_table`, `action`, `message` FROM `logs`" );
      $stmt->setFetchMode( PDO::FETCH_ASSOC );
      $logs = $stmt->fetchAll();
      $conn->exec( "TRUNCATE TABLE `logs`" );

      $stmt2 = $conn->prepare( "INSERT INTO `logs` (`date_time`, `action`, `message`) VALUES (:dtime, :type, :val)" );
      foreach ( $logs as $r ) {
        if ( $r['aff_table'] != 'familien' || $r['action'] != 'UPDATE' ) continue;
        $msg = rawurldecode($r['message']);
        if ( substr( $msg, -1 ) !== "}" ) $msg .= "null}}"; // fix callback error
        $data = json_decode( $msg, true );
        if ( $data['geld'] == 0 ) continue;

        $stmt2->execute(array(
          ":dtime" => $r['date_time'],
          ":type" => 'money',
          ":val" => $data['geld']
        ));
        $stmt2->execute(array(
          ":dtime" => $r['date_time'],
          ":type" => 'attendance',
          ":val" => sprintf( "%s/%s", $data['post']['set']['Erwachsene'], $data['post']['set']['Kinder'] )
        ));
      }

      $conn->exec( "ALTER TABLE `logs` CHANGE `date_time` `DTime` datetime" );
      $conn->exec( "ALTER TABLE `logs` CHANGE `action` `Type` VARCHAR(255)" );
      $conn->exec( "ALTER TABLE `logs` CHANGE `message` `Val` VARCHAR(255)" );
      $conn->exec( "ALTER TABLE `logs` DROP `aff_table`" );

      $conn->exec( "DROP FUNCTION IF EXISTS splitStr;" );
      $conn->exec( $proc_splitStr );

      $conn->commit();

      $ver = 7;

    } catch ( PDOException $e ) {
      upgrade_error( 7, $e );
    }
  }

  // write new dbver
  try {
    $conn->exec( "INSERT INTO `einstellungen` (`Name`, `Val`) Values ('Version', $ver) ON DUPLICATE KEY UPDATE `Val` = $ver;" );
  } catch ( PDOException $e ) {
    echo $e->getMessage();
  }
}

function upgrade_error( $ver, $e ) {
  ?><!DOCTYPE html><html>
  <head>
    <title>Tischlein Deck Dich - ERROR</title>
  </head>
  <body>
    <h1>FEHLER</h1>
    <p>Datenbank konnte nicht auf Version <?php echo $ver; ?> aktualisiert werden.</p>
    <p><?php echo $e->getMessage(); ?></p>
  </body>
  </html>
  <?php
  exit;
}


?>