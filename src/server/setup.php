<?php


// Execute setup if needed
if ( isset( $_GET['setup'] ) ) {
  echo "<!DOCTYPE html><html><head><title>Tischlein Deck Dich Setup</title><link href=\"?file=favicon\" rel=\"icon\" type=\"image/x-icon\" /></head><body>";
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
    case 1:
      echo "<h1>Step 1: Datenbank</h1><br>";
      try {
        $sql = "DROP DATABASE IF EXISTS tdd;
        CREATE DATABASE tdd COLLATE utf8_unicode_ci";
        // use exec() because no results are returned
        $conn->exec( $sql );
        
        echo "<p>Datenbank `tdd` erfolgreich (neu) angelegt.</p>";
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Datenbank `tdd`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }
      echo "<p>&nbsp;</p><p><a href=\"?setup&step=2\">Nächster Schritt</a></p>";
      break;
    
    case 2:
      echo "<h1>Step 2: Nutzer</h1><br>";
      try {
        if ( !isset( $_POST['u'] ) || !isset( $_POST['p'] ) ) {
          ?><p>Nutzername und Passwort für Normal-benutzer eingeben:<br><form action="?setup&step=2" method="POST"><input type="text" name="u" placeholder="Username"><br><input type="text" name="p" placeholder="Pasword"><br><input type="submit" value="Anlegen"></form></p><p>Dieser Login ist sowohl der Datenbanknutzer als auch Ihr Login für das Programm.</p><p>Es können mehrere Nutzer angelegt werden, allerdings nur in der SQL-Administrationsoverfläche gelöscht werden.</p><?php
        } else {
          $usern = $_POST['u'];
          $userp = $_POST['p'];
          $sql = "GRANT ALL ON `tdd`.* TO '$usern'@'localhost' IDENTIFIED BY '$userp';
          GRANT CREATE, INSERT ON *.* TO '$usern'@'localhost'";
          $conn->exec( $sql );

          echo "<p>Nutzer $usern erfolgreich angelegt.</p>";
        }
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen des Nutzers $usern.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }
      echo "<p>&nbsp;</p><p><a href=\"?setup&step=3\">Nächster Schritt</a></p>";
      break;

    case 3:
      echo "<h1>Step 3: Tabellen</h1><br>";
      try {
        $conn->exec( "USE tdd" );
        
        $sql = "CREATE TABLE familien( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Erwachsene int, Kinder int, Ort varchar(255), Gruppe int, Schulden decimal(3,2), Karte date, lAnwesenheit date, Notizen varchar(255), PRIMARY KEY (ID) )";
        $conn->exec( $sql );
        echo "Tabelle `Familien` erfolgreich angelegt.";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Familien`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }
      
      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE orte( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Gruppen int, PRIMARY KEY (ID) )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Orte` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Orte`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE einstellungen( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Val longtext, PRIMARY KEY (ID) )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Einstellungen` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Einstellungen`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "INSERT INTO `einstellungen` ( Name, Val ) VALUES ( 'Preis', 'e + 0.5 * k' )";
        $conn->exec( $sql );
        echo "<p>`Preis` erfolgreich initialisiert.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Initialisieren von `Preis`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "INSERT INTO `einstellungen` ( Name, Val ) VALUES ( 'Kartendesigns', '%5B%0A%09%7B%0A%09%09%22name%22%3A%20%22Barcode%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%24img%22%20%7D%0A%09%09%5D%0A%09%7D%2C%0A%09%7B%0A%09%09%22name%22%3A%20%22Formular%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%3Ch1%3ETischlein%20Deck%20Dich%3C%2Fh1%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%20style%3D%5C%22font-style%3Aitalic%3Bfont-size%3A16px%5C%22%3EZulassung%20zur%20Ausgabe%3C%2Fp%3E%3Cbr%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%24img%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EName%3C%2Fspan%3E%3A%20%24Name%20%28ID%3A%20%24ID%29%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EOrt%3C%2Fspan%3E%3A%20%24Ort%2C%20%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EGruppe%3C%2Fspan%3E%3A%20%24Gruppe%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EErwachsene%2FKinder%3C%2Fspan%3E%3A%20%24Erwachsene%2F%24Kinder%20%28%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3E%24Preis%u20AC%3C%2Fspan%3E%29%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EG%FCltig%20bis%3C%2Fspan%3E%3A%20%24Karte%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3ENotizen%3A%3C%2Fspan%3E%3Cbr%3E%24Notizen%3C%2Fp%3E%22%20%7D%0A%09%09%5D%0A%09%7D%2C%0A%09%7B%0A%09%09%22name%22%3A%20%22Visitenkarte%201%20-%20Barcode%20Mitte%22%2C%0A%09%09%22format%22%3A%20%2254x86%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%24img%22%2C%20%22position%22%3A%20%5B31.2%2C6%5D%20%7D%0A%09%09%5D%0A%09%7D%0A%5D' )";
        $conn->exec( $sql );
        echo "<p>`Kartendesigns` erfolgreich initialisiert.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Initialisieren von `Kartendesigns`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }

      try {
        $conn->exec( "USE tdd" );

        $sql = "CREATE TABLE logs( ID int NOT NULL AUTO_INCREMENT, date_time DateTime, aff_table varchar(255), action varchar(255), message LONGTEXT, PRIMARY KEY (ID) )";
        $conn->exec( $sql );
        echo "<p>Tabelle `Logs` erfolgreich angelegt.</p>";
        
      } catch ( PDOException $e ) {
        echo "<p>Fehler beim Anlegen der Tabelle `Logs`.<br>";
        echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
      }
      echo "<p>&nbsp;</p><p><a href=\"?login\">Fertig</a></p>";
      break;
    default:
      ?><h1>Setup</h1>
      <p style="color:red">Achtung: Bei diesem Prozess werden alle eventuell vorhandenen Daten gelöscht!</p>
      <br>
      <ul style="list-style-type:none;padding-left:0;">
        <li><a href="?setup&step=1">Step 1</a>: Datenbank</li>
        <li><a href="?setup&step=2">Step 2</a>: Nutzer</li>
        <li><a href="?setup&step=3">Step 3</a>: Tabellen</li>
      </ul><?php
  }

  echo "</div></body></html>";
  $conn = null;
  exit();
}

?>