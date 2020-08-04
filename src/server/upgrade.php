<?php


//Check version and upgrade database
$stmt = $conn->prepare( "SELECT Val FROM `einstellungen` WHERE `Name` = 'Version'" );
$stmt->execute();
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
      $conn->exec( "ALTER TABLE `familien` ADD Num int;" );
      $conn->exec( "ALTER TABLE `einstellungen` ADD UNIQUE(`Name`);" );

      $ver = 2;

    } catch ( PDOException $e ) {
      upgradeError( 2, $e );
    }
  }

  if ( $ver == 2 ) {
    // upgrade to version 3
    // add address and telephone column
    try {
      // update tables
      $conn->exec( "ALTER TABLE `familien` ADD Adresse varchar(255);" );
      $conn->exec( "ALTER TABLE `familien` ADD Telefonnummer varchar(255);" );

      $ver = 3;

    } catch ( PDOException $e ) {
      upgradeError( 3, $e );
    }
  }

  if ( $ver == 3 || $ver == 4 ) {
    // update procedures
    // originally added in version 2, then 3, now moved here and altered (and reverted) to new requirements
    try {
      // prodecure for resetting nums
      $conn->exec( "DROP PROCEDURE IF EXISTS resetFamNum;" );
      $conn->exec( "
        CREATE PROCEDURE resetFamNum()
        BEGIN
          DECLARE done INT DEFAULT FALSE;
          DECLARE f_id, f_gruppe INT;
          DECLARE f_ort VARCHAR(255);
          DECLARE f_num INT DEFAULT 1;
          DECLARE o_gruppe INT DEFAULT 0;
          DECLARE o_ort VARCHAR(255) DEFAULT '';

          DECLARE fam CURSOR FOR SELECT `ID`, `Ort`, `Gruppe` FROM `tdd`.`familien` ORDER BY `Ort`, `Gruppe`, `Name`, `ID`;
          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
          OPEN fam;

          loop1: LOOP
            FETCH fam INTO f_id, f_ort, f_gruppe;
            IF done THEN
              LEAVE loop1;
            END IF;

            IF f_ort <> o_ort OR f_gruppe <> o_gruppe THEN
              SET o_ort = f_ort;
              SET o_gruppe = f_gruppe;
              SET f_num = 1;
            END IF;

            UPDATE `tdd`.`familien` SET `Num` = f_num WHERE `ID` = f_id;
            SET f_num = f_num + 1;
          END LOOP;
          CLOSE fam;
        END;
        " );
      if ( !isset( $_GET['norenumber'] ) ) $conn->exec( "CALL resetFamNum();" );

      $ver = 5;

    } catch ( PDOException $e ) {
      upgradeError( 5, $e );
    }
  }

  if ( $ver == 5 ) {
    // update procedures
    try {
      // function for changing num to new group
      $conn->exec( "DROP FUNCTION IF EXISTS newNum;" );
      $conn->exec( "
        CREATE FUNCTION newNum(
          q_ort VARCHAR(255),
          q_gruppe INT
        )
        RETURNS int
        BEGIN
          DECLARE done INT DEFAULT FALSE;
          DECLARE next INT DEFAULT 1;
          DECLARE f_num INT;

          SELECT MIN(`f`.`Num`+1) INTO next FROM `tdd`.`familien` AS `f` LEFT JOIN `tdd`.`familien` AS `t` ON (`f`.`Num`+1 = `t`.`Num` AND `f`.`Ort` = `t`.`Ort` AND `f`.`Gruppe` = `t`.`Gruppe`) WHERE `t`.`Num` IS NULL AND `f`.`Ort` = q_ort AND `f`.`Gruppe` = q_gruppe;
          IF next IS NULL THEN
            SET next = 1;
          END IF;

          RETURN next;
        END;
        " );

      $ver = 6;

    } catch ( PDOException $e ) {
      upgradeError( 6, $e );
    }
  }

  // write new dbver
  try {
    $conn->exec( "INSERT INTO `einstellungen` (`Name`, `Val`) Values ('Version', $ver) ON DUPLICATE KEY UPDATE `Val` = $ver;" );
  } catch ( PDOException $e ) {
    echo $e->getMessage();
  }
}

function upgradeError( $ver, $e ) {
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