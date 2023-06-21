<?php

global $conn;
global $proc_resetFamNum;
global $proc_newNum;
global $proc_newGruppe;
global $proc_splitStr;
global $trigger_familienInsert;
global $trigger_familienUpdate;
global $trigger_orteUpdate;
global $view_logsalt;

//Check version and upgrade database
$stmt = $conn->query( "SELECT Val FROM `einstellungen` WHERE `Name` = 'Version'" );
$ver = $stmt->fetchColumn();
if ( $ver === false ) {
  $ver = 1; // default dbver to 1
} else {
  $ver = intval($ver);
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
    <p>Die aktuell laufende Version des Programms erwartet eine ältere Version der Datenbank, eine geänderte Struktur kann unvohergesehene Folgen haben.</p>
  </body>
  </html>
  <?php
  exit;
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
}

//upgrade
if ( $ver < DB_VER ) {
  $error = false;

  if ( $ver <= 1 ) {
    // upgrade to version 2
    // add fam num column
    try {
      // update tables
      $conn->beginTransaction();
      $conn->exec( "ALTER TABLE `familien` ADD Num int;" );
      $conn->exec( "ALTER TABLE `einstellungen` ADD UNIQUE(`Name`);" );
      $conn->commit();

      $ver = 2;

    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 2, $e );
      $error = true;
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
      $conn->rollBack();
      upgrade_error( 3, $e );
      $error = true;
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
      $conn->rollBack();
      upgrade_error( 5, $e );
      $error = true;
    }
  }

  if ( $ver == 5 || $ver == 6 ) {
    try {
      $conn->exec( "ALTER DATABASE tdd CHARACTER SET utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "SET autocommit = 0" );

      $conn->beginTransaction();

      $conn->exec( "ALTER TABLE `familien` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "ALTER TABLE `orte` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "ALTER TABLE `einstellungen` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "ALTER TABLE `logs` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci" );

      // function for decoding url-encoded string
      $conn->exec( "DROP FUNCTION IF EXISTS url_decode;" );
      $conn->exec( "CREATE FUNCTION `url_decode`(str VARCHAR(255) CHARSET utf8) RETURNS VARCHAR(255) DETERMINISTIC
        BEGIN
          DECLARE X  INT;
          SET X = 128;
          WHILE X  < 192 DO
            SET str = REPLACE(str, CONCAT('%C5%', HEX(X)), UNHEX(CONCAT('C5', HEX(X))));
            SET str = REPLACE(str, CONCAT('%C4%', HEX(X)), UNHEX(CONCAT('C4', HEX(X))));
            SET str = REPLACE(str, CONCAT('%C3%', HEX(X)), UNHEX(CONCAT('C3', HEX(X))));
            SET  X = X + 1;
          END WHILE;
          SET str = REPLACE(str, '%09', '\t');
          SET str = REPLACE(str, '%0A', '\n');
          SET str = REPLACE(str, '%0D', '\r');
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

      $conn->exec( "UPDATE `familien` SET `Name` = url_decode(`Name`), `Ort` = COALESCE((SELECT `ID` FROM `orte` WHERE `orte`.`Name`=`Ort`), 0), `Notizen` = url_decode(`Notizen`), `Adresse` = url_decode(`Adresse`), `Telefonnummer` = url_decode(`Telefonnummer`)" );
      $conn->exec( "UPDATE `orte` SET `Name` = url_decode(`Name`)");
      $conn->exec( "DROP FUNCTION IF EXISTS url_decode;" ); // one-time use

      $stmt = $conn->query( "SELECT `ID`, `Val` FROM `einstellungen`" );
      $stmt->setFetchMode( PDO::FETCH_ASSOC );

      foreach ( $stmt->fetchAll() as $r ) {
        $stmt2 = $conn->prepare( "UPDATE `einstellungen` SET `Val` = :Val WHERE `ID` = :ID" );
        $stmt2->execute(array(
          ":ID" => $r['ID'],
          ":Val" => str_replace('$Ort','$Ortname',rawurldecode(str_replace('%FC','ü',str_replace('%u20AC','€',$r['Val']))))
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

      $conn->exec( "ALTER TABLE `familien` CHANGE `Name` `Name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Erwachsene` `Erwachsene` INT NOT NULL DEFAULT '0'" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Kinder` `Kinder` INT NOT NULL DEFAULT '0'" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Ort` `Ort` INT NOT NULL" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Gruppe` `Gruppe` INT DEFAULT '0'" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Schulden` `Schulden` DECIMAL(5,2) NOT NULL DEFAULT '0.00'" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Notizen` `Notizen` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "ALTER TABLE `familien` CHANGE `Num` `Num` INT DEFAULT '0'" );
      
      $conn->exec( "ALTER TABLE `orte` CHANGE `Name` `Name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL" );
      $conn->exec( "ALTER TABLE `orte` CHANGE `Gruppen` `Gruppen` INT NOT NULL DEFAULT '0'" );
      
      $conn->exec( "ALTER TABLE `einstellungen` CHANGE `Name` `Name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL" );
      $conn->exec( "ALTER TABLE `einstellungen` CHANGE `Val` `Val` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci" );

      $conn->exec( "ALTER TABLE `logs` CHANGE `date_time` `DTime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP" );
      $conn->exec( "ALTER TABLE `logs` CHANGE `action` `Type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL" );
      $conn->exec( "ALTER TABLE `logs` CHANGE `message` `Val` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci" );
      $conn->exec( "ALTER TABLE `logs` DROP `aff_table`" );

      $conn->exec( "DROP FUNCTION IF EXISTS splitStr;" );
      $conn->exec( $proc_splitStr );

      $conn->exec( "DROP FUNCTION IF EXISTS newNum;" );
      $conn->exec( $proc_newNum );

      $conn->exec( "DROP FUNCTION IF EXISTS newGruppe;" );
      $conn->exec( $proc_newGruppe );

      $conn->exec( "DROP TRIGGER IF EXISTS familienInsert;" );
      $conn->exec( $trigger_familienInsert );

      $conn->exec( "DROP TRIGGER IF EXISTS familienUpdate;" );
      $conn->exec( $trigger_familienUpdate );

      $conn->exec( "DROP VIEW IF EXISTS logsalt;" );
      $conn->exec( $view_logsalt );

      $conn->commit();
      $conn->exec( "SET autocommit = 1" );

      $ver = 8;

    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 8, $e );
      $error = true;
    }
  }

  if ( $ver == 7 ) {
    try {
      $conn->beginTransaction();
      $conn->exec( "UPDATE `familien` SET `Adresse` = REPLACE(REPLACE(REPLACE(`Adresse`, '%0A', '\n'), '%0D', '\r'), '%09', '\t')" );
      $conn->exec( "UPDATE `einstellungen` SET `Val` = REPLACE(REPLACE(REPLACE(`Val`, '%0A', '\n'), '%0D', '\r'), '%09', '\t')" );
      $conn->commit();

      $ver = 8;

    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 8, $e );
      $error = true;
    }
  }


  if ( $ver == 8 ) {
    try {
      $conn->beginTransaction();

      $stmt = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'Kartendesigns'" );
      $designs = $stmt->fetchColumn();
      $designs = json_decode( $designs, true );
      $designs[] = array(
        "name" => "Visitenkarte 2 - Vollständig",
		    "format" => "54x86",
		    "elements" => array(
          array( "html" => "<p style=\"font-weight:500; text-align: right; width: 150px; font-size: 1.8em; line-height: 1; margin: 0\">\$Name</p>", "position" => array( 2.1, 41.1 ) ),
          array( "html" => "<p style=\"font-weight:bold; text-align: right; width: 150px; font-size: 3em; margin: 0\">\$Erwachsene/\$Kinder</p>", "position" => array( 23.9, 41.1 ) ),
          array( "html" => "<p style=\"text-align: right; width: 100px; font-size: 1.5em; margin: 0\">\$Num</p>", "position" => array( 41,30.9 ) ),
          array( "html" => "\$img", "position" => array( 41,58.4 ) ),
          array( "html" => "<img src=\"?file=placeholder\" style=\"object-fit: cover; width: 100%; height: 100%;\" />", "position" => array( 0,0 ), "size" => array( 40, 54 ) ),
        ),
      );
      $stmt = $conn->prepare( "UPDATE `einstellungen` SET `Val` = :designs WHERE `Name` = 'Kartendesigns'" );
      $stmt->execute(array(":designs" => json_encode( $designs, JSON_PRETTY_PRINT )));

      $conn->commit();
      $ver = 9;
    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 9, $e );
      $error = true;
    }
  }

  if ( $ver == 9 ) {
    try {
      $conn->beginTransaction();

      $conn->exec( "ALTER TABLE `familien` ADD COLUMN `ProfilePic` varchar(255) NOT NULL DEFAULT '', ADD COLUMN `ProfilePic2` varchar(255) NOT NULL DEFAULT ''" );
      $stmt = $conn->query( "SELECT `Val` FROM `einstellungen` WHERE `Name` = 'Kartendesigns'" );
      $designs = $stmt->fetchColumn();
      $designs = json_decode( $designs, true );
      array_pop($designs);
      $designs[] = array(
        "name" => "Visitenkarte 2 - Vollständig",
		    "format" => "54x86",
		    "elements" => array(
          array( "html" => "<p style=\"font-weight:500; text-align: right; width: 150px; font-size: 1.8em; line-height: 1; margin: 0\">\$Name</p>", "position" => array( 2.1, 41.1 ) ),
          array( "html" => "<p style=\"font-weight:bold; text-align: right; width: 150px; font-size: 3em; margin: 0\">\$Erwachsene/\$Kinder</p>", "position" => array( 23.9, 41.1 ) ),
          array( "html" => "<p style=\"text-align: right; width: 100px; font-size: 1.5em; margin: 0\">\$Num</p>", "position" => array( 41,30.9 ) ),
          array( "html" => "\$img", "position" => array( 41,58.4 ) ),
          array( "html" => "<img src=\"\$ProfilBildSrc1\" style=\"object-fit: cover; width: 100%; height: 100%;\" />", "position" => array( 0,0 ), "size" => array( 40, 54 ) ),
        ),
      );
      $stmt = $conn->prepare( "UPDATE `einstellungen` SET `Val` = :designs WHERE `Name` = 'Kartendesigns'" );
      $stmt->execute(array(":designs" => json_encode( $designs, JSON_PRETTY_PRINT )));
      
      $conn->commit();
      $ver = 10;

    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 10, $e );
      $error = true;
    }
  }

  if ( $ver == 10 ) {
    try {
      $conn->exec( "SET autocommit = 0" );
      $conn->beginTransaction();

      $conn->exec( "DROP TRIGGER IF EXISTS familienUpdate;" );
      $conn->exec( "DROP TRIGGER IF EXISTS orteUpdate;" );

      $conn->exec( "ALTER TABLE `familien` ADD COLUMN `last_update` timestamp NOT NULL, ADD COLUMN `deleted` bit NOT NULL" );
      $conn->exec( "ALTER TABLE `orte` ADD COLUMN `last_update` timestamp NOT NULL, ADD COLUMN `deleted` bit NOT NULL" );
      $conn->exec( "INSERT INTO `einstellungen` (`Name`, `Val`) VALUES ('last_sync', '0')" );
      $conn->exec( "UPDATE `familien` SET `last_update` = 0" );
      $conn->exec( "UPDATE `orte` SET `last_update` = 0" );

      $conn->exec( $trigger_familienUpdate );
      $conn->exec( $trigger_orteUpdate );
      
      $conn->commit();
      $ver = 11;

    } catch ( PDOException $e ) {
      $conn->rollBack();
      upgrade_error( 11, $e );
      $error = true;
    }
  }
  
  if ($error) exit;

  // write new dbver
  try {
    $conn->exec( "INSERT INTO `einstellungen` (`Name`, `Val`) Values ('Version', $ver) ON DUPLICATE KEY UPDATE `Val` = $ver;" );
  } catch ( PDOException $e ) {
    echo $e->getMessage();
    exit;
  }
}

?>