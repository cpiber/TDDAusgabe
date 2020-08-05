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
            DECLARE end INT;
            DECLARE start INT;
            SET start = LOCATE('%', str);
            WHILE start > 0 DO
                SET end = start;
                WHILE SUBSTRING(str, end, 1) = '%' AND UPPER(SUBSTRING(str, end + 1, 1)) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F') AND UPPER(SUBSTRING(str, end + 2, 1)) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F') DO
                    SET end = end + 3;
                END WHILE;
                IF start <> end THEN
                    SET str = INSERT(str, start, end - start, UNHEX(REPLACE(SUBSTRING(str, start, end - start), '%', '')));
                END IF;
                SET start = LOCATE('%', str, start + 1);
            END WHILE;
            RETURN REPLACE(str, '+', ' ');
        END;
        " );
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