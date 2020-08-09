<?php


function setup_error($sql, $error) {
  return sprintf( "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%%;padding:6px;\">%s: <strong>%s</strong><br></span></p>", $sql, $error );
}

$proc_resetFamNum = "CREATE PROCEDURE resetFamNum()
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
  ";
$proc_newNum = "CREATE FUNCTION newNum(
    q_ID INT,
    q_ort VARCHAR(255),
    q_gruppe INT
  )
  RETURNS int
  BEGIN
    DECLARE next INT DEFAULT 1;
    DECLARE f_ID INT DEFAULT q_ID;

    IF f_ID IS NULL THEN
      SET f_ID = 0;
    END IF;
    
    SELECT MIN(`left`.`Num`+1) INTO next FROM (
      SELECT 0 as `Num` UNION SELECT `Num` FROM `tdd`.`familien` AS `f` WHERE `f`.`ID` <> f_ID AND `f`.`Ort` = q_ort AND `f`.`Gruppe` = q_gruppe
    ) AS `left` LEFT JOIN (
      SELECT 0 as `Num` UNION SELECT `Num` FROM `tdd`.`familien` AS `f` WHERE `f`.`ID` <> f_ID AND `f`.`Ort` = q_ort AND `f`.`Gruppe` = q_gruppe
    ) AS `right` ON (`left`.`Num`+1 = `right`.`Num`) WHERE `right`.`Num` IS NULL;
    IF next IS NULL THEN
      SET next = 1;
    END IF;

    RETURN next;
  END;
  ";
$proc_splitStr = "CREATE FUNCTION splitStr (
    x VARCHAR(255),
    delim VARCHAR(12),
    pos INT
  )
  RETURNS VARCHAR(255)
  RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
         LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
         delim, '');
  "; // https://stackoverflow.com/a/14950556/
$trigger_familienNumInsert = "CREATE TRIGGER familienNumInsert BEFORE INSERT ON `familien`
  FOR EACH ROW SET NEW.`Num` = IF(NEW.`Num`=0 OR NEW.`Num` IS NULL,newNum(NULL, NEW.`Ort`, NEW.`Gruppe`),NEW.`Num`);
  ";
$trigger_familienNumUpdate = "CREATE TRIGGER familienNumUpdate BEFORE UPDATE ON `familien`
  FOR EACH ROW SET NEW.`Num` = IF(NEW.`Num`=0 OR NEW.`Num` IS NULL OR OLD.`Ort`<>NEW.`Ort` OR OLD.`Gruppe`<>NEW.`Gruppe`,newNum(NEW.`ID`, NEW.`Ort`, NEW.`Gruppe`),NEW.`Num`);
  ";
$view_logsalt = "CREATE VIEW logsalt AS SELECT `ID`, `DTime`, `Type`, `Val`, IF(`Type`='attendance',splitStr(`Val`, '/', 1),'') AS `P1`, IF(`Type`='attendance',splitStr(`Val`, '/', 2),'') AS `P2` FROM `logs`";


// Execute setup if needed
if ( isset( $_GET['setup'] ) ) {
  echo "<!DOCTYPE html><html><head><title>Tischlein Deck Dich Setup</title><meta charset=\"UTF-8\"><link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" /></head><body>";
  session_start();

  if ( isset( $_SESSION['user'] ) && isset( $_SESSION['pw'] ) ) {
    try {
      $conn = new PDO( "mysql:host=$servername", $_SESSION['user'], $_SESSION['pw'] );
      
      // set the PDO error mode to exception
      $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      
    } catch ( PDOException $e ) {
      loginform( '<span style=\"color:red\">Login failed. ' . $e->getMessage() . '</span>', $_SERVER['SCRIPT_NAME'] . '?setup' );
      exit;

    }
  } else {
    loginform( "<p>Admin-Login für Datenbank eingeben</p>", $_SERVER['SCRIPT_NAME'] . '?setup' );
    exit;

  }

  ?><style>
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
    .pointer {
      cursor: pointer;
    }
  </style>
  <div class="float-middle">
    <p style="text-align: right; padding: 0 10px; margin: 2px 0;"><a href="?login&url=<?php echo urlencode( $_SERVER['SCRIPT_NAME'] . '?setup' ); ?>">Log Out</a></p><?php

  if ( isset( $_GET['step'] ) ) {
    $step = $_GET['step'];
  } else {
    $step = 0;
  }

  switch ( $step ) {
    default:
      ?><h1>Setup</h1>
      <p style="color:red">Achtung: Bei diesem Prozess werden alle eventuell vorhandenen Daten gelöscht!</p>
      <br>
      <ul style="list-style-type:none;padding-left:0;">
        <li><a href="?setup&step=1">Step 1</a>: Datenbank</li>
        <li><a href="?setup&step=2">Step 2</a>: Nutzer</li>
        <li><a href="?setup&step=3">Step 3</a>: Tabellen</li>
        <li><a href="?setup&step=4">Step 4</a>: Prozeduren</li>
      </ul><?php
      break;
    
    case 1:
      echo "<h1>Step 1: Datenbank</h1><br>";
      try {
        $sql = "DROP DATABASE IF EXISTS tdd";
        $conn->exec( $sql );
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Löschen der Datenbank `tdd`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }
      try {
        $sql = "CREATE DATABASE tdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $conn->exec( $sql );
        
        echo "<p>Datenbank `tdd` erfolgreich (neu) angelegt.</p>";
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Datenbank `tdd`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }
      echo "<p>&nbsp;</p><p><a href=\"?setup&step=2\">Nächster Schritt</a></p>";
      break;
    
    case 2:
      echo "<h1>Step 2: Nutzer</h1><br>";
      if ( !isset( $_POST['u'] ) || !isset( $_POST['p'] ) ) {
        ?><p>Nutzername und Passwort für Normal-benutzer eingeben:<br><form action="?setup&step=2" method="POST"><input type="text" name="u" placeholder="Username"><br><input type="text" name="p" placeholder="Pasword"><br><input type="submit" value="Anlegen"></form></p><p>Dieser Login ist sowohl der Datenbanknutzer als auch Ihr Login für das Programm.</p><p>Es können mehrere Nutzer angelegt werden, allerdings nur in der SQL-Administrationsoverfläche gelöscht werden.</p><?php
      } else {
        $usern = $_POST['u'];
        $userp = $_POST['p'];

        try {
          $sql = "GRANT ALL ON `tdd`.* TO '$usern'@'localhost' IDENTIFIED BY '$userp'";
          $conn->exec( $sql );

          echo "<p>Nutzer $usern erfolgreich angelegt.</p>";
        } catch ( PDOException $e ) {
          echo "<p>Fehler beim Anlegen des Nutzers $usern.<br>";
          echo setup_error( $sql, $e->getMessage() );
        }
        try {
          $sql = "GRANT CREATE, INSERT ON *.* TO '$usern'@'localhost'";
          $conn->exec( $sql );

          echo "<p>Privilegien von $usern erfolgreich erweitert.</p>";
        } catch ( PDOException $e ) {
          echo "<p>Fehler beim Erweitern der Privilegien des Nutzers $usern.<br>";
          echo setup_error( $sql, $e->getMessage() );
        }
      }
      echo "<p>&nbsp;</p><p><a href=\"?setup&step=3\">Nächster Schritt</a></p>";
      break;

    case 3:
      echo "<h1>Step 3: Tabellen</h1><br>";
      try {
        $conn->exec( "USE tdd" );
        
        $sql = "CREATE TABLE familien(
          ID int NOT NULL AUTO_INCREMENT,
          Name varchar(255) NOT NULL,
          Erwachsene int NOT NULL DEFAULT '0',
          Kinder int NOT NULL DEFAULT '0',
          Ort int NOT NULL,
          Gruppe int NOT NULL,
          Schulden decimal(5,2) NOT NULL DEFAULT '0.00',
          Karte date,
          lAnwesenheit date,
          Notizen varchar(255),
          Num int DEFAULT '0',
          Adresse varchar(255),
          Telefonnummer varchar(255),
          PRIMARY KEY (ID)
        )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Familien` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Familien`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }
      
      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE orte(
          ID int NOT NULL AUTO_INCREMENT,
          Name varchar(255) NOT NULL,
          Gruppen int NOT NULL DEFAULT '0',
          PRIMARY KEY (ID)
        )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Orte` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Orte`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE einstellungen(
          ID int NOT NULL AUTO_INCREMENT,
          Name varchar(255) UNIQUE NOT NULL,
          Val longtext,
          PRIMARY KEY (ID)
        )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Einstellungen` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Einstellungen`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "INSERT INTO `einstellungen` ( Name, Val ) VALUES ( 'Preis', 'e + 0.5 * k' )";
        $conn->exec( $sql );
        echo "<p>`Preis` erfolgreich initialisiert.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Initialisieren von `Preis`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }

      try {
        $conn->exec( "USE tdd" );

        $designs = <<<'DESIGNS_NOWDOC____'
require "../files/designs.json";
DESIGNS_NOWDOC____;
        $sql = "INSERT INTO `einstellungen` ( Name, Val ) VALUES ( 'Kartendesigns', :designs )";
        $stmt = $conn->prepare( $sql );
        $stmt->execute( array(":designs" => $designs) );
        echo "<p>`Kartendesigns` erfolgreich initialisiert.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Initialisieren von `Kartendesigns`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }

      try {
        $conn->exec( "USE tdd" );

        $ver = DB_VER;
        $sql = "INSERT INTO `einstellungen` ( Name, Val ) VALUES ( 'Version', $ver )";
        $conn->exec( $sql );
        echo "<p>`Preis` erfolgreich initialisiert.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Initialisieren von `Preis`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE logs(
          ID int NOT NULL AUTO_INCREMENT,
          DTime DateTime NOT NULL,
          Type varchar(255) NOT NULL,
          Val varchar(255),
          PRIMARY KEY (ID)
        )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Logs` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Logs`.<br>";
        echo setup_error( $sql, $e->getMessage() );
      }
      echo "<p>&nbsp;</p><p><a href=\"?setup&step=4\">Nächster Schritt</a></p>";
      break;

    case 4:
      echo "<h1>Step 4: Misc</h1><br>";
      try {
        $conn->beginTransaction();
        $conn->exec( "USE tdd" );
        
        $conn->exec( "DROP PROCEDURE IF EXISTS resetFamNum;" );
        $conn->exec( $proc_resetFamNum );
        $conn->commit();
        echo "<p>Prozedur `resetFamNum` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        $conn->rollBack();
        echo "<p>Fehler beim Anlegen der Prozedur `resetFamNum`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      try {
        $conn->beginTransaction();
        $conn->exec( "USE tdd" );
        
        $conn->exec( "DROP FUNCTION IF EXISTS newNum;" );
        $conn->exec( $proc_newNum );
        $conn->commit();
        echo "<p>Prozedur `newNum` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        $conn->rollBack();
        echo "<p>Fehler beim Anlegen der Prozedur `newNum`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      try {
        $conn->beginTransaction();
        $conn->exec( "USE tdd" );
        
        $conn->exec( "DROP FUNCTION IF EXISTS splitStr;" );
        $conn->exec( $proc_splitStr );
        $conn->commit();
        echo "<p>Prozedur `splitStr` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        $conn->rollBack();
        echo "<p>Fehler beim Anlegen der Prozedur `splitStr`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      try {
        $conn->beginTransaction();
        $conn->exec( "USE tdd" );
        
        $conn->exec( "DROP TRIGGER IF EXISTS familienNumInsert;" );
        $conn->exec( $trigger_familienNumInsert );
        $conn->commit();
        echo "<p>Trigger `familienNumInsert` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        $conn->rollBack();
        echo "<p>Fehler beim Anlegen des Triggers `familienNumInsert`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      try {
        $conn->beginTransaction();
        $conn->exec( "USE tdd" );
        
        $conn->exec( "DROP TRIGGER IF EXISTS familienNumUpdate;" );
        $conn->exec( $trigger_familienNumUpdate );
        $conn->commit();
        echo "<p>Trigger `familienNumUpdate` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        $conn->rollBack();
        echo "<p>Fehler beim Anlegen des Triggers `familienNumUpdate`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      try {
        $conn->exec( "USE tdd" );

        $conn->exec( "DROP VIEW IF EXISTS logsalt;" );
        $conn->exec( $view_logsalt );
        echo "<p>View `Logsalt` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der View `Logsalt`.<br>";
        echo setup_error( "", $e->getMessage() );
      }
      echo "<p>&nbsp;</p><p><a href=\"?login\">Fertig</a></p>";
      break;
  }

  echo "</div></body></html>";
  $conn = null;
  exit();
}

?>