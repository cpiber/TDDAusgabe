<?php

$servername = "localhost";

$admu = "root"; $admp = ""; //Admin login, keep blank while not using setup!

define( 'VERSION', 1.3 );
define( 'DB_VER', 6 );



// Execute setup if needed
if ( isset( $_GET['setup'] ) ) {
    echo "<!DOCTYPE html><html><head><title>Tischlein Deck Dich Setup</title></head><body>";
    session_start();

    if ( isset( $_SESSION['user'] ) && isset( $_SESSION['pw'] ) ) {
        try {
            $conn = new PDO( "mysql:host=$servername", $_SESSION['user'], $_SESSION['pw'] );
            
            // set the PDO error mode to exception
            $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            
        } catch ( PDOException $e ) {
            loginform( '<span style=\"color:red\">Login failed.</span>', $_SERVER['SCRIPT_NAME'] . '?setup' );
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
                    ?><p>Nutzername und Passwort für Normal-benutzer eingeben:<br><form action="?setup&step=2" method="POST"><input type="text" name="u" placeholder="Username"><br><input type="text" name="p" placeholder="Pasword"><br><input type="submit" value="OK"></form></p><p>Dieser Login ist sowohl der Datenbanknutzer als auch Ihr Login für das Programm.</p><p>Es können mehrere Nutzer angelegt werden, allerdings nur in der SQL-Administrationsoverfläche gelöscht werden.</p><?php
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
                
                $sql = "CREATE TABLE Familien( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Erwachsene int, Kinder int, Ort varchar(255), Gruppe int, Schulden decimal(3,2), Karte date, lAnwesenheit date, Notizen varchar(255), PRIMARY KEY (ID) )";
                $conn->exec( $sql );
                echo "Tabelle `Familien` erfolgreich angelegt.";
                
            } catch ( PDOException $e ) {
                echo "<p>Fehler beim Anlegen der Tabelle `Familien`.<br>";
                echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
            }
            
            try {
                $conn->exec( "USE tdd" );

                $sql = "CREATE TABLE Orte( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Gruppen int, PRIMARY KEY (ID) )";
                $conn->exec( $sql );
                echo "<p>Tabelle `Orte` erfolgreich angelegt.</p>";
                
            } catch ( PDOException $e ) {
                echo "<p>Fehler beim Anlegen der Tabelle `Orte`.<br>";
                echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
            }

            try {
                $conn->exec( "USE tdd" );

                $sql = "CREATE TABLE Einstellungen( ID int NOT NULL AUTO_INCREMENT, Name varchar(255), Val longtext, PRIMARY KEY (ID) )";
                $conn->exec( $sql );
                echo "<p>Tabelle `Einstellungen` erfolgreich angelegt.</p>";
                
            } catch ( PDOException $e ) {
                echo "<p>Fehler beim Anlegen der Tabelle `Einstellungen`.<br>";
                echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
            }

            try {
                $conn->exec( "USE tdd" );

                $sql = "INSERT INTO `Einstellungen` ( Name, Val ) VALUES ( 'Preis', 'e + 0.5 * k' )";
                $conn->exec( $sql );
                echo "<p>`Preis` erfolgreich initialisiert.</p>";
                
            } catch ( PDOException $e ) {
                echo "<p>Fehler beim Initialisieren von `Preis`.<br>";
                echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
            }

            try {
                $conn->exec( "USE tdd" );

                $sql = "INSERT INTO `Einstellungen` ( Name, Val ) VALUES ( 'Kartendesigns', '%5B%0A%09%7B%0A%09%09%22name%22%3A%20%22Barcode%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%24img%22%20%7D%0A%09%09%5D%0A%09%7D%2C%0A%09%7B%0A%09%09%22name%22%3A%20%22Formular%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%3Ch1%3ETischlein%20Deck%20Dich%3C%2Fh1%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%20style%3D%5C%22font-style%3Aitalic%3Bfont-size%3A16px%5C%22%3EZulassung%20zur%20Ausgabe%3C%2Fp%3E%3Cbr%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%24img%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EName%3C%2Fspan%3E%3A%20%24Name%20%28ID%3A%20%24ID%29%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EOrt%3C%2Fspan%3E%3A%20%24Ort%2C%20%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EGruppe%3C%2Fspan%3E%3A%20%24Gruppe%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EErwachsene%2FKinder%3C%2Fspan%3E%3A%20%24Erwachsene%2F%24Kinder%20%28%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3E%24Preis%u20AC%3C%2Fspan%3E%29%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3EG%FCltig%20bis%3C%2Fspan%3E%3A%20%24Karte%3C%2Fp%3E%22%20%7D%2C%0A%09%09%09%7B%20%22html%22%3A%20%22%3Cp%3E%3Cspan%20style%3D%5C%22font-weight%3Abold%5C%22%3ENotizen%3A%3C%2Fspan%3E%3Cbr%3E%24Notizen%3C%2Fp%3E%22%20%7D%0A%09%09%5D%0A%09%7D%2C%0A%09%7B%0A%09%09%22name%22%3A%20%22Visitenkarte%201%20-%20Barcode%20Mitte%22%2C%0A%09%09%22format%22%3A%20%2254x86%22%2C%0A%09%09%22elements%22%3A%20%5B%0A%09%09%09%7B%20%22html%22%3A%20%22%24img%22%2C%20%22position%22%3A%20%5B31.2%2C6%5D%20%7D%0A%09%09%5D%0A%09%7D%0A%5D' )";
                $conn->exec( $sql );
                echo "<p>`Kartendesigns` erfolgreich initialisiert.</p>";
                
            } catch ( PDOException $e ) {
                echo "<p>Fehler beim Initialisieren von `Kartendesigns`.<br>";
                echo "<a class=\"pointer\" onclick=\"this.nextSibling.style.display='block'\">Mehr</a><span style=\"display:none;font-size:90%;padding:6px;\">" . $sql . ": <strong>" . $e->getMessage() . "</strong><br></span></p>";
            }

            try {
                $conn->exec( "USE tdd" );

                $sql = "CREATE TABLE Logs( ID int NOT NULL AUTO_INCREMENT, date_time DateTime, aff_table varchar(255), action varchar(255), message LONGTEXT, PRIMARY KEY (ID) )";
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




//Return various JSON-Strings
if ( isset( $_GET['get'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $table = ( isset( $_POST['table'] ) ? rawurlencode($_POST['table']) : '' );
        
        $sql = "SELECT * FROM `$table`";
        $sql .= post_where();
        $sql .= post_orderby();
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ( $stmt->fetchAll() as $r) {
            $msg['query'][] = $r;
            $msg['query']['length'] = ++$count;
        }
        if ( !isset( $msg['query']['length'] ) ) { $msg['query']['length'] = 0; }
        $msg['status'] = 'success';
        $msg['sql'] = $sql;

        $r = $conn->query( "SELECT COUNT(*) FROM `$table`".post_where() )->fetchColumn();
        $msg['rows'] = $r;
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

if ( isset( $_GET['getOrte'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $table = "`Orte`";
        
        $sql = "SELECT * FROM $table";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $sql2 = "SELECT `Ort`, `Gruppe`, COUNT(*) FROM `Familien` GROUP BY `Ort`, `Gruppe`";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        
        $result2 = $stmt2->setFetchMode(PDO::FETCH_ASSOC);
        $f = $stmt2->fetchAll();
        
        $count = 0;
        foreach ( $stmt->fetchAll() as $r) {
            //Families in each group
            for ( $i = 1; $i <= $r['Gruppen']; $i++ ) {
                $r['Personen'][$i] = 0;
                foreach( $f as $c ) {
                    if ( $c['Ort'] == $r['Name'] && $c['Gruppe'] == $i ) { $r['Personen'][$i] = (int)$c['COUNT(*)']; }
                }
            }
            $msg['query'][] = $r;
            $msg['query']['length'] = ++$count;
        }
        if ( !isset( $msg['query']['length'] ) ) { $msg['query']['length'] = 0; }
        $msg['status'] = 'success';
        $msg['sql'] = $sql;
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

if ( isset( $_GET['getSearch'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $return = "";
        if ( isset( $_POST['meta'] ) ) {
            $return .= ' WHERE';
            $con = "";

            if ( !isset( $_POST['meta'][0] ) ) {
                $meta_a = array();
                foreach ( $_POST['meta'] as $key => $value ) {
                    $meta_a[0][$key] = $value;
                }
            } else {
                $meta_a = $_POST['meta'];
            }

            foreach ( $meta_a as $meta ) {
                $k = array( "`ID`", "`Name`", "`Ort`", "`Num`" );

                $n = ( isset( $meta['connect'] ) ? $meta['connect'] : "" );
                $a = array( "", "NOT" );
                $n = ( in_array( strtoupper($n), $a ) ? $n : "" );
                if ( $n != "" ) { $n = " " . $n; }

                $c = ( isset( $meta['compare'] ) ? $meta['compare'] : "LIKE" );
                $a = array( "=", "<>", "!=", ">", "<", ">=", "<=", "LIKE", "BETWEEN", "IN" );
                $c = ( in_array( strtoupper($c), $a ) ? $c : "LIKE" );

                $v = ( isset( $meta['value'] ) ? rawurlencode($meta['value']) : 1 );
                $v = strtr( $v, array( "%25" => "%") );
                if ( is_numeric($v) ) { $k = array( "`ID`", "`Num`" ); $v = ltrim( $v, '0' ); }

                $return .= "$con$n (";
                $c2 = "";
                foreach ( $k as $k ) {
                    $return .= "$c2 $k $c \"$v\"";
                    $c2 = " OR";
                }
                $return .= " )";
                $con = " AND";
            }
        }

        $sql = "SELECT * FROM `familien`$return";
        $sql .= " ORDER BY `Ort`, `Gruppe`, `Num`, `ID`";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ( $stmt->fetchAll() as $r ) {
            $msg['query'][] = $r;
            $msg['query']['length'] = ++$count;
        }
        if ( !isset( $msg['query']['length'] ) ) { $msg['query']['length'] = 0; }
        $msg['status'] = 'success';
        $msg['sql'] = $sql;

        $r = $conn->query( "SELECT COUNT(*) FROM `familien`$return" )->fetchColumn();
        $msg['rows'] = $r;
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

if ( isset( $_GET['update'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $table = ( isset( $_POST['table'] ) ? rawurlencode($_POST['table']) : '' );
        $tbl = strtr( $table, array("`"=>"") );
        
        $sql = "UPDATE `$table`";
        if ( isset( $_POST['set'] ) ) {
            $sql .= ' SET';
            $con = "";
            foreach ( $_POST['set'] as $key => $value ) {
                if ( preg_match( "/\w+\(.*\)/", $value ) ) {
                    // function
                    $sql .= "$con `".rawurlencode($key)."` = $value";
                } else {
                    $v = rawurlencode($value);
                    $v = strtr( $v, array( "%25" => "%") );
                    $sql .= "$con `".rawurlencode($key)."` = \"$v\"";
                }
                $con = ',';
            }
        }
        $sql .= post_where();
        $sql .= post_orderby();
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $msg['rows'] = $stmt->rowCount();
        
        $msg['status'] = 'success';
        $msg['sql'] = $sql;

        date_default_timezone_set( "UTC" );
        $date = new DateTime();
        $d = $date->format( "Y-m-d H:i:s" );
        $m = array( "geld" => ( isset( $_POST['preis'] ) ? $_POST['preis'] : "0" ), "post" => $_POST );
        $conn->exec( "INSERT INTO `Logs` ( date_time, aff_table, action, message ) VALUES ( '$d', '$tbl', 'UPDATE', '" . addslashes(json_encode($m)) . "' )" );
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

if ( isset( $_GET['insert'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $table = ( isset( $_POST['table'] ) ? rawurlencode($_POST['table']) : '' );
        $tbl = strtr( $table, array("`"=>"") );
        
        $sql = "INSERT INTO `$table` (";
        if ( isset( $_POST['set'] ) ) {
            $con = "";
            foreach ( $_POST['set'] as $key => $value ) {
                $sql .= "$con `".rawurlencode($key)."`";
                $con = ',';
            }
            $sql .= ' ) VALUES (';
            $con = "";
            foreach ( $_POST['set'] as $key => $value ) {
                if ( preg_match( "/\w+\(.*\)/", $value ) ) {
                    // function
                    $sql .= "$con $value";
                } else {
                    $v = rawurlencode($value);
                    $v = strtr( $v, array( "%25" => "%") );
                    $sql .= "$con \"$v\"";
                }
                $con = ',';
            }
            $sql .= ' )';
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $last_id = $conn->lastInsertId();
        
        $msg['status'] = 'success';
        $msg['ID'] = $last_id;
        $msg['sql'] = $sql;

        date_default_timezone_set( "UTC" );
        $date = new DateTime();
        $d = $date->format( "Y-m-d H:i:s" );
        $m = array( "post" => $_POST );
        $conn->exec( "INSERT INTO `Logs` ( date_time, aff_table, action, message ) VALUES ( '$d', '$tbl', 'INSERT', '" . addslashes(json_encode($m)) . "' )" );
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

if ( isset( $_GET['delete'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );
    
    try {
        $table = ( isset( $_POST['table'] ) ? rawurlencode($_POST['table']) : '' );
        $tbl = strtr( $table, array("`"=>"") );
        
        $sql = "DELETE FROM `$table`";
        $sql .= post_where();
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $msg['rows'] = $stmt->rowCount();
        
        $msg['status'] = 'success';
        $msg['sql'] = $sql;

        date_default_timezone_set( "UTC" );
        $date = new DateTime();
        $d = $date->format( "Y-m-d H:i:s" );
        $m = array( "post" => $_POST );
        $conn->exec( "INSERT INTO `Logs` ( date_time, aff_table, action, message ) VALUES ( '$d', '$tbl', 'DELETE', '" . addslashes(json_encode($m)) . "' )" );
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }
    
    $msg['post'] = $_POST;
    
    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';
    
    $conn = null;
    exit;
}

function post_where() {
    $return = "";
    if ( isset( $_POST['meta'] ) ) {
        $return .= ' WHERE';
        $con = "";
        if ( !isset( $_POST['meta'][0] ) ) {
            $meta_a = array();
            foreach ( $_POST['meta'] as $key => $value ) {
                $meta_a[0][$key] = $value;
            }
        } else {
            $meta_a = $_POST['meta'];
        }
        foreach ( $meta_a as $meta ) {
            $n = ( isset( $meta['connect'] ) ? $meta['connect'] : "" );
            $a = array( "", "NOT" );
            $n = ( in_array( strtoupper($n), $a ) ? $n : "" );
            if ( $n != "" ) { $n = " " . $n; }
            $k = ( isset( $meta['key'] ) ? rawurlencode($meta['key']) : 1 );
            $c = ( isset( $meta['compare'] ) ? $meta['compare'] : "=" );
            $a = array( "=", "<>", "!=", ">", "<", ">=", "<=", "LIKE", "BETWEEN", "IN" );
            $c = ( in_array( strtoupper($c), $a ) ? $c : "=" );
            $v = ( isset( $meta['value'] ) ? rawurlencode($meta['value']) : 1 );
            $v = strtr( $v, array( "%25" => "%") );
            $return .= "$con$n ( `$k` $c \"$v\" )";
            $con = ( isset( $_POST['meta_connection'] ) ? " " . rawurlencode($_POST['meta_connection']) : " AND" );
        }
    }
    return $return;
}

function post_orderby() {
    $return = "";
    if ( isset( $_POST['order_by'] ) ) {
        $o = rawurlencode($_POST['order_by']);
        $a = array( "%2C", "%20" );
        $r = array( ",", " " );
        $o = str_replace( $a, $r, $o );

        $return .= " ORDER BY " . $o;
    }
    if ( isset( $_POST['limit'] ) ) {
        $return .= " LIMIT " . rawurlencode($_POST['limit']);
        if ( isset( $_POST['offset'] ) ) {
            $return .= " OFFSET " . rawurlencode($_POST['offset']);
        }
    }
    return $return;
}


// Backup Database
if ( isset( $_GET['backup_db'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );

    date_default_timezone_set( "UTC" );
    $date = new DateTime();
    $d = $date->format( "Ymd-Hi" );
    $db = "tdd_backup_$d";
    $msg['db'] = $db;

    try {
        $sql = "CREATE DATABASE `$db` COLLATE utf8_unicode_ci";
        $conn->exec( $sql );

        $msg['status'] = 'success';
        $msg['sql1'] = $sql;
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['step'] = 1;
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }

    try {
        $conn->exec( "USE `tdd`" );

        $sql = "SHOW TABLES";
        $stmt = $conn->prepare( $sql );
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $tables = $stmt->fetchAll();

        $msg['status'] = 'success';
        $msg['sql2'] = $sql;

    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['step'] = 2;
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }

    try {
        $conn->exec( "USE `$db`" );

        $i = 0;
        $sql = array();
        foreach ( $tables as $t ) {
            $t = $t['Tables_in_tdd'];
            $sql[$i] = "CREATE TABLE `$t` SELECT * FROM `tdd`.`$t`";
            $conn->exec( $sql[$i++] );
        }

        $msg['status'] = 'success';
        $msg['sql3'] = $sql;

    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['step'] = 3;
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }

    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';

    $conn = null;
    exit;
}

// Reset numbers
if ( isset( $_GET['reset_fam'] ) ) {
    header("Content-Type: application/json; charset=UTF-8");
    $msg = array( "status" => "pending" );
    $msg['callback'] = ( isset( $_POST['callback'] ) ? $_POST['callback'] : '' );

    try {
        $sql = "CALL resetFamNum()";
        $conn->exec( $sql );

        $msg['status'] = 'success';
        $msg['sql'] = $sql;
        
    } catch ( PDOException $e ) {
        $msg['status'] = 'failure';
        $msg['step'] = 1;
        $msg['message'] = $e->getMessage();
        $msg['sql'] = $sql;
    }

    $json = json_encode( $msg );
    if ( $json )
        echo $json;
    else
        echo '{"status":"failure","message":"'.json_last_error_msg().'"}';

    $conn = null;
    exit;
}



// Print-Version
if ( isset( $_GET['print'] ) ) {
    echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
    echo "<style>table { width: 100%; } table th { text-align: left; } tbody > tr > * { border: 1px solid grey; padding: 5px; } h3 { margin-top: 1em; margin-bottom: 0; } span.check { width: 14px; height: 14px; border: 1px solid black; display: block; float: right; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; }</style>\n";
    echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"" . logo() . "\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
    $o = ( isset( $_GET['ort'] ) ? $_GET['ort'] : "Alle" );
    echo "<div class=\"body\"><form action=\"?print\" method=\"GET\">\n<input type=\"hidden\" name=\"print\" value=true>\n<select id=\"ort\" name=\"ort\">\n<option value=\"Alle\">Alle</option>\n";

    try {
        $sql = "SELECT * FROM `orte`";

        $stmt = $conn->prepare( $sql );
        $stmt->execute();

        $orte = array();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        foreach ( $stmt->fetchAll() as $r) {
            $orte[$r['Name']] = $r;
            $s = ( $r['Name'] == $o ? " selected" : "" );
            echo "<option value=\"" . $r['Name'] . "\"$s>" . iconv( "iso8859-9", "utf-8", rawurldecode($r['Name'])) . "</option>\n";
        }
        $break = false;

    } catch ( PDOException $e ) {
        echo "<i>Fehler bei abrufen der Orte</i><br>" . $e->getMessage();
        $break = true;

    }
    echo "</select>\n";
    if ( $break ) exit;
    echo "<select id=\"gruppe\" name=\"gruppe\">\n<option value=\"Alle\">Alle</option>\n";
    if ( $o != "Alle" ) {
        $g = ( isset( $_GET['gruppe'] ) ? $_GET['gruppe'] : "Alle" );
        $ort = $orte[$o];
        for ( $i = 1; $i <= $ort['Gruppen']; $i++ ) {
            $s = ( $i == $g ? " selected" : "" );
            echo "<option value=\"$i\"$s>Gruppe $i</option>";
        }

    } else {
        $g = "Alle";
    }
    echo "</select>\n";
    echo "<input type=\"submit\" value=\"OK\">\n</form>";

    // Get settings
    try {
        $sql = "SELECT * FROM `Einstellungen`";

        $stmt = $conn->prepare( $sql );
        $stmt->execute();

        $orte = array();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        foreach ( $stmt->fetchAll() as $r) {
            $einst[$r['Name']] = rawurldecode( $r['Val'] );
        }

    } catch ( PDOException $e ) {
        echo "<i>Fehler bei abrufen der Einstellungen</i><br>" . $e->getMessage();
        exit;

    }

    // Get Familien in selected Ort
    try {
        if ( $o == "Alle" ) { $o = "%"; }
        if ( $g == "Alle" ) { $g = "%"; }
        $sql = "SELECT * FROM `familien` WHERE `Ort` LIKE :ort AND `Gruppe` LIKE :gruppe ORDER BY Ort, Gruppe, Num, ID";
        
        $stmt = $conn->prepare( $sql );
        $stmt->execute( array( ':ort' => $o, ':gruppe' => $g ) );

        $ort = ""; $gruppe = -1;
        $result = $stmt->setFetchMode( PDO::FETCH_ASSOC );
        foreach ( $stmt->fetchAll() as $r ) {
            if ( $ort != $r['Ort'] ) {
                if ( $ort != "" ) {
                    echo "</tbody></table>\n";
                }
                $ort = $r['Ort']; $gruppe = -1;
                echo "<h1>".iconv( "iso8859-9", "utf-8", rawurldecode($ort) )."</h1>\n<table><tbody>\n";
            }
            if ( $gruppe != $r['Gruppe'] ) {
                $gruppe = $r['Gruppe'];
                echo "<tr><td colspan=\"8\"><h3>Gruppe $gruppe</h3></td></tr>\n<tr><th>Nummer</th><th>Name</th><th>Erw. / Kinder</th><th>Preis</th><th>Schulden</th><th>Karte</th><th>Notizen</th><th>Adresse</th></tr>\n";
            }

            $num = $r['Num'];
            $name = iconv( "iso8859-9", "utf-8", rawurldecode($r['Name']) );
            $anw = "<span class=\"check\"></span>";
            $leute = $r['Erwachsene'] . " / " . $r['Kinder'];
            $preis = $einst['Preis'];
            $preis = strtr( $preis, array( 'e' => (int)$r['Erwachsene'], 'k' => (int)$r['Kinder'] ) );
            ob_start();
            try {
                $preis = eval( "return (int)(" . preg_replace( '/[^0-9\+\-\*\/\(\)\.><=]/', '', $preis ) . ");" );
                //keine Zahl -> Fehler
                if ( $preis === false ) {
                    ob_clean();
                    $preis = "<i>Fehler in der Preis-Formel</i>";
                } else {
                    $preis = number_format( $preis, 2 ) . "€";
                }
            } catch ( ParseError $e ) {
                $preis = "<i>Fehler in der Preis-Formel</i>";
            }
            $schuld = $r['Schulden'] . "€";
            date_default_timezone_set( "UTC" );
            $karte = strtotime( $r['Karte'] );
            $karte = date( "d. m. Y", $karte );
            $karte = ( $r['Karte'] == "" || $r['Karte'] == "0000-00-00" ? "" : $karte );
            $notiz = iconv( "iso8859-9", "utf-8", rawurldecode($r['Notizen']) );
            $addr = iconv( "iso8859-9", "utf-8", rawurldecode($r['Adresse']) );
            echo "<tr><td>$num</td><td><span>$name</span>$anw</td><td>$leute</td><td>$preis</td><td>$schuld</td><td>$karte</td><td>$notiz</td><td>$addr</td></tr>\n";

        }

    } catch ( PDOException $e ) {
        echo "<i>Fehler beim Abrufen der Familien</i><br>" . $e->getMessage();

    }

    echo "</div></body>\n</html>";

    exit;
}

// Load csv backup
if ( isset( $_GET['load_backup'] ) ) {
    if ( !isset( $_REQUEST['table'] ) || $_REQUEST['table'] == 'familien' ) {
        $table = 'familien';
        $available_cols = array(
            'ID' => true,
            'Name' => true,
            'Erwachsene' => true,
            'Kinder' => true,
            'Personen' => false,
            'Ort' => true,
            'Gruppe' => true,
            'Schulden' => true,
            'Karte' => true,
            'lAnwesenheit' => true,
            'Notizen' => true,
            'Num' => true,
            'Adresse' => true,
            'Straße' => false,
            'Telefonnummer' => true,
        );
    } elseif ( $_REQUEST['table'] == 'orte' ) {
        $available_cols = array(
            'ID' => true,
            'Name' => true,
            'Gruppen' => true,
        );
    } elseif ( $_REQUEST['table'] == 'einstellungen' ) {
        $available_cols = array(
            'ID' => true,
            'Name' => true,
            'Val' => true,
        );
    } else {
        $available_cols = array();
    }
    if ( !isset( $table ) ) $table = $_REQUEST['table'];
    $msg = array();
    if ( isset( $_POST['load_backup'] ) ) {
        try {
            $toEncode = tablesCoding( $table );
            $cols = isset( $_POST['cols'] ) ? $_POST['cols'] : array();
            $selected_cols = array();
            foreach ( $available_cols as $name => $_ ) {
                if ( !array_key_exists( $name, $cols ) ) {
                    $selected_cols[$name] = false;
                } else {
                    $selected_cols[$name] = true;
                }
            }

            if ( !empty( $_FILES["file"]["tmp_name"] ) ) {
                ini_set( 'auto_detect_line_endings', TRUE );
                $file = $_FILES["file"]["tmp_name"];
                $file = fopen( $file, "r" );
                if ( empty( $_POST['delimiter'] ) ) {
                    $delimiter = ",";
                } else {
                    $delimiter = $_POST['delimiter'];
                }
                $header = fgetcsv( $file, 0, $delimiter );
                if ( $header === null || $header === false ) {
                    $msg[] = array(
                        "Datei invalid!",
                        "error",
                    );
                } else {
                    $used_cols = array();
                    $names = array();
                    $header[0] = preg_replace( '/[\x00-\x1F\x7F-\xFF]/', '', $header[0] );
                    // find columns
                    foreach ( $selected_cols as $name => $enabled ) {
                        if ( $enabled ) {
                            if ( ( $col = array_search( $name, $header ) ) !== false ) {
                                $used_cols[$name] = $col;
                                if ( $name != 'Personen' && $name != 'Straße' ) $names[] = $name;
                                if ( $name == 'Personen' && !in_array( 'Erwachsene', $names ) ) $names[] = 'Erwachsene';
                                if ( $name == 'Personen' && !in_array( 'Kinder', $names ) ) $names[] = 'Kinder';
                                if ( $name == 'Straße' && !in_array( 'Adresse', $names ) ) $names[] = 'Adresse';
                            } else {
                                $msg[] = array(
                                    "Spalte " . $name . " nicht verfügbar",
                                    "warn",
                                );
                            }
                        }
                    }
                    $keys = array_map( function( $val ) { return ":" . $val; }, $names );
                    $insertSql = "INSERT INTO " . $table . " (" . implode( ", ", $names ) . ") VALUES (" . implode( ", ", $keys ) . ");";
                    $updateSql = "UPDATE " . $table . " SET ";
                    $first = true;
                    for ( $i = 0; $i < sizeof($keys); $i++ ) {
                        if ( $names[$i] == 'ID' ) continue;
                        $updateSql .= ( !$first ? ", " : "" ) . $names[$i] . " = " . $keys[$i];
                        $first = false;
                    }
                    $updateSql .= " WHERE ID = :ID;";

                    if ( isset( $used_cols['ID'] ) || isset( $used_cols['Name'] ) ) {
                        // parse content
                        $objs = array();
                        while ( ( $data = fgetcsv( $file ) ) !== false ) {
                            $cur = sizeof($objs);
                            $objs[] = array();
                            foreach ( $used_cols as $name => $col ) {
                                if ( sizeof($data) > $col ) {
                                    if ( $name == "Straße" ) $name = "Adresse";
                                    $objs[$cur][$name] = isset( $objs[$cur][$name] ) ? $objs[$cur][$name] + $data[$col] : $data[$col];
                                    if ( $name == "Name" && empty( $objs[$cur][$name] ) ) {
                                        $msg[] = array(
                                            "Zeile " . strval( $cur + 2 ) . ": Name ungültig",
                                            "warn",
                                        );
                                    } elseif ( $name == "ID" && !is_numeric( $objs[$cur][$name] ) ) {
                                        $msg[] = array(
                                            "Zeile " . strval( $cur + 2 ) . ": ID ungültig",
                                            "warn",
                                        );
                                    }
                                } else {
                                    $msg[] = array(
                                        "Zeile " . strval( $cur + 2 ) . " enthält Spalte " . $name . " nicht.",
                                        "warn",
                                    );
                                }
                            }
                        }
                        foreach ( $objs as $i => $obj ) {
                            if ( ( isset( $obj['Name'] ) && empty( $obj['Name'] ) ) || ( isset( $obj['ID'] ) && !is_numeric($obj['ID']) ) ) {
                                $msg[] = array(
                                    "Überspringe Zeile " . strval( $i + 2 ),
                                    "warn",
                                );
                                continue;
                            }
                            foreach ( $obj as $key => $value ) {
                                $obj[$key] = iconv( "utf-8", "iso8859-9//TRANSLIT//IGNORE", $obj[$key] );
                            }
                            foreach ( $toEncode as $name ) {
                                if ( isset( $obj[$name] ) ) {
                                    $obj[$name] = rawurlencode( $obj[$name] );
                                }
                            }
                            if ( isset( $obj['Personen'] ) ) {
                                $matches = array();
                                if ( preg_match("/(\d+)(?:\/(\d+))?/", $obj['Personen'], $matches ) ) {
                                    $obj['Erwachsene'] = $matches[1];
                                    $obj['Kinder'] = isset( $matches[2] ) ? $matches[2] : 0;
                                } else {
                                    $msg[] = array(
                                        "Personen in Zeile " . strval( $i + 2 ) . " ungültig. Auf 0/0 gesetzt.",
                                        "warn",
                                    );
                                    $obj['Erwachsene'] = 0;
                                    $obj['Kinder'] = 0;
                                }
                            }
                            $id = isset( $obj['ID'] ) ? $obj['ID'] : 0;
                            $res = null;
                            if ( isset( $obj['ID'] ) ) {
                                $stmt = $conn->prepare( "SELECT ID FROM " . $table . " WHERE `ID` = :ID LIMIT 1;");
                                $stmt->execute( array( ":ID" => $obj['ID'] ) );
                            } elseif ( isset( $obj['Name'] ) ) {
                                $stmt = $conn->prepare( "SELECT ID FROM " . $table . " WHERE `Name` = :Name LIMIT 1;");
                                $stmt->execute( array( ":Name" => $obj['Name'] ) );
                            }
                            $res = $stmt->fetch( PDO::FETCH_ASSOC );
                            if ( $res && isset( $res['ID'] ) ) $id = $res['ID'];

                            $set = array();
                            if ( $id ) $set[":ID"] = $id;
                            foreach ( $obj as $key => $value ) {
                                if ( $key == "ID" || $key == "Personen" ) continue;
                                $set[":".$key] = $value;
                            }
                            
                            $sql = $id == 0 ? $insertSql : $updateSql;
                            $stmt = $conn->prepare( $sql );
                            $stmt->execute( $set );
                        }
                        $msg[] = array(
                            "Erfolgreich importiert.",
                            "ok",
                        );
                    } else {
                        $msg[] = array(
                            "ID oder Name müssen gegeben sein!",
                            "error",
                        );
                    }
                }
                fclose( $file );
                ini_set( 'auto_detect_line_endings', FALSE );
            } else {
                $msg[] = array(
                    "Keine Datei gegeben!",
                    "error",
                );
            }
        } catch ( Exception $e ) {
            $msg[] = array(
                "Fehler: " . $e->getMessage(),
                "error",
            );
        }
    } else {
        $selected_cols = $available_cols;
    }

    echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
    echo "<style>table { max-width: 100%; } table th { font-weight: bold; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; } .msg { margin: 0 10px; padding: 4px 6px; border: 1px solid gray; border-radius: 2px; } .msg.msg-ok { background-color: lightgreen; } .msg.msg-error { background-color: red; }</style>\n";
    echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"" . logo() . "\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
    foreach ( $msg as $m ) {
        echo "<p class=\"msg msg-" . $m[1] . "\">" . $m[0] . "</p>";
    }
    if ( !empty( $msg ) ) echo "<p></p>";
    echo "<div class=\"body\">";
    echo "<form action=\"?load_backup\" method=\"GET\"><input type=\"hidden\" name=\"load_backup\" value=\"true\" /><select name=\"table\">";
    $tables = array(
        'familien' => 'Familien',
        'orte' => 'Orte',
        'einstellungen' => 'Einstellungen',
        'logs' => 'Logs',
    );
    foreach ( $tables as $name => $display ) {
        echo "<option value=\"" . $name . "\" " . ( $name == $table ? "selected" : "" ) . ">" . $display . "</option>";
    }
    echo "</select><input type=\"submit\" value=\"Wählen\" /></form>";
    echo "<form action=\"?load_backup\" method=\"POST\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"load_backup\" value=\"true\" /><input type=\"hidden\" name=\"table\" value=\"" . $table . "\" />\n<table><tbody>\n<tr><th>Spalte</th><th>Laden</th>\n";
    foreach ( $selected_cols as $name => $enabled ) {
        echo "<tr><td><label for=\"" . $name . "\">" . $name . "</label></td><td><input type=\"checkbox\" id=\"" . $name . "\" name=\"cols[" . $name . "]\" " . ( $enabled ? "checked" : "" ) . " value=\"on\" /></td></tr>\n";
    }
    echo "<tr><td><i>Alle</i></td><td><input type=\"checkbox\" id=\"toggle_all\" checked /></td></tr>\n";
    echo "</tbody></table>\n";
    echo "<p><label title=\"Zeichen zwischen Spalten. Für Excel-Export ;\">CSV-Trennzeichen: <input type=\"text\" name=\"delimiter\" placeholder=\",\" size=\"1\" /></label><br /><label>Datei: <input type=\"file\" name=\"file\" /></label></p>\n";
    echo "<p><input type=\"submit\" value=\"Backup laden\" /></p>\n";
    echo "</form>\n";
    echo "<script>window.onload = function(){var boxes = document.getElementsByTagName('input'); document.getElementById('toggle_all').addEventListener('click', function() {for (var i = 0; i < boxes.length; i++) {if (boxes[i].type == 'checkbox') {boxes[i].checked = this.checked;}}});};</script>";
    echo "</div></body>\n</html>";

    exit;
}

// Create csv backup
if ( isset( $_GET['create_backup'] ) ) {
    $table = isset( $_REQUEST['table'] ) ? $_REQUEST['table'] : "familien";
    $msg = array();
    $tables = array(
        'familien' => 'Familien',
        'orte' => 'Orte',
        'einstellungen' => 'Einstellungen',
    );

    if ( isset( $_POST['create_backup'] ) ) {
        try {
            if ( !isset( $tables[$table] ) ) throw new Exception( "Tabelle ungültig!", 1 );
            
            $stmt = $conn->prepare( "SELECT * FROM " . $table );
            $stmt->execute();
            $fname = $tables[$table];

            $toDecode = tablesCoding( $table );

            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename=' . $fname . '.csv' );
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Connection: Keep-Alive' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Pragma: public' );
            $res = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $out = fopen( 'php://output', 'w' );
            foreach ( $res as $i => $obj ) {
                $line = array();
                $header = array();
                foreach ( $obj as $key => $value ) {
                    if ( $i == 0 ) $header[] = $key;
                    if ( in_array( $key, $toDecode ) ) $value = rawurldecode( $value );
                    $value = iconv( "iso8859-9", "utf-8", $value );
                    $line[] = $value;
                }
                if ( $i == 0 ) fputcsv( $out, $header );
                fputcsv( $out, $line );
            }
            fclose( $out );
            exit;
        } catch ( Exception $e ) {
            $msg[] = array(
                "Fehler: " . $e->getMessage(),
                "error",
            );
        }
    }

    echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";
    echo "<style>table { max-width: 100%; } table th { font-weight: bold; } html, body { margin: 0; } p { font-size: 15px; } .header { font-size: 32px; color: #ffffff; height: 100px; width: 100%; background-color: #f85e3d; margin: 8px 0; display: table; } .header > div { display: table-cell; vertical-align: middle; padding: 15px 25px; } div.body { margin: 4px; } .msg { margin: 0 10px; padding: 4px 6px; border: 1px solid gray; border-radius: 2px; } .msg.msg-ok { background-color: lightgreen; } .msg.msg-error { background-color: red; }</style>\n";
    echo "</head>\n<body>\n<div id=\"header\" class=\"header\"><div><span><a href=\"\"><img src=\"" . logo() . "\" style=\"max-height:120px;max-width:100%\" /></a></span></div></div>\n&nbsp;\n";
    foreach ( $msg as $m ) {
        echo "<p class=\"msg msg-" . $m[1] . "\">" . $m[0] . "</p>";
    }
    if ( !empty( $msg ) ) echo "<p></p>";
    echo "<div class=\"body\">";
    echo "<form action=\"?create_backup\" method=\"POST\"><input type=\"hidden\" name=\"create_backup\" value=\"true\" /><select name=\"table\">";
    foreach ( $tables as $name => $display ) {
        echo "<option value=\"" . $name . "\" " . ( $name == $table ? "selected" : "" ) . ">" . $display . "</option>";
    }
    echo "</select><br /><input type=\"submit\" value=\"Herunterladen\" /></form>";
    echo "</div></body>\n</html>";

    exit;
}

function tablesCoding( $table ) {
    if ( $table == 'familien' ) {
        return array(
            'Name',
            'Ort',
            'Notizen',
            'Adresse',
        );
    } elseif ( $table == 'orte' ) {
        return array(
            'Name',
        );
    } elseif ( $table == 'einstellungen' ) {
        return array(
            'Name',
            'Val',
        );
    } else {
        return array();
    }
}

// Karte
if ( isset( $_GET['karte'] ) ) {
    echo "<html>\n<head>\n<title>Tischlein Deck Dich</title>\n";

    $d = isset( $_POST['designs'] ) ? $_POST['designs'] : '%5B%5D';
    $f = isset( $_POST['familie'] ) ? $_POST['familie'] : '%7B%7D';
    $designs = iconv( "iso8859-9", "utf-8", rawurldecode( $d ) ); $designs = preg_replace( "/[\n\r]/", "", $designs );
    $familie = iconv( "iso8859-9", "utf-8", rawurldecode( $f ) ); $familie = preg_replace( "/[\n\r]/", "", $familie );
    ?><style>html, body { margin: 0 } #testCanvas { border: 1px solid black }</style>
    <script type="text/javascript">
        var designs = JSON.parse( "<?php echo addslashes($designs); ?>" );
        var familie = JSON.parse( "<?php echo addslashes($familie); ?>" );
        for ( var i in familie ) {
            familie[i] = unescape( familie[i] );
        }

        var formats = {
            A3: [ 297, 420 ],
            A4: [ 210, 297 ],
            A5: [ 148, 210 ]
        };
        var pxConst = 3.7795275591;

        window.onload = function() {
            var d = document.getElementById( 'design-select' );

            for ( var i = 0; i < designs.length; i++ ) {
                var e = document.createElement( 'option' );
                e.value = i;
                var t = document.createTextNode( designs[i].name );
                e.appendChild( t );
                d.appendChild( e );
            }

            d.addEventListener( 'change', changeDesign );
            changeDesign();

            var b = document.getElementById( 'drucken' );
            b.addEventListener( 'click', function() {
                var c = document.getElementById( 'testCanvas' ),
                    m = document.getElementById( 'menu' );
                c.style.border = "none";
                m.style.display = "none";
                window.print();
                c.style.border = "";
                m.style.display = "";
            } );
        };

        function changeDesign() {
            var d = document.getElementById( 'design-select' ),
                c = document.getElementById( 'testCanvas' );
            if ( d.value === "" ) return;
            design = designs[d.value];

            c.innerHTML = "";

            //Set canvas size
            var f = formats[design.format];
            var format = design.format;
            if ( typeof(f) !== "undefined" ) {
                c.style.height = f[0] * pxConst;
                c.style.width = f[1] * pxConst;
                c.style.border = "";
            } else if ( typeof(format) !== "undefined" && (format.match(/x/g) || []).length == 1 ) {
                f = design.format.split( "x" );
                c.style.height = f[0] * pxConst;
                c.style.width = f[1] * pxConst;
                c.style.border = "";
            } else if ( (typeof(format) !== "undefined" && (format === "none" || format === "")) || typeof(format) === "undefined" ) {
                c.style.height = "";
                c.style.width = "";
                c.style.border = "none";
            } else {
                console.debug( "Invalid format!" );
                return;
            }

            //Add specified elements
            if ( typeof(design.elements) !== "undefined" && design.elements.constructor.name === "Array" ) {
                for ( var i = 0; i < design.elements.length; i ++ ) {
                    var e = design.elements[i];
                    var h = "<div style=\"";

                    if ( typeof(e.css) !== "undefined" && typeof(e.position) === "undefined" ) {
                        h += ";" + e.css + "\"";
                    }
                    if ( typeof(e.css) !== "undefined" && typeof(e.position) !== "undefined" ) {
                        if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
                            h += ";" + e.css + ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
                        }
                    }
                    if ( typeof(e.css) === "undefined" && typeof(e.position) !== "undefined" ) {
                        if ( e.position.constructor.name === "Array" && e.position.length == 2 ) {
                            h += ";position:absolute;top:" + (e.position[0] * pxConst) + "px;left:" + (e.position[1] * pxConst) + "px";
                        }
                    }
                    h += "\" >";
                    if ( typeof(e.html) !== "undefined" ) {
                        html = unescape(e.html);
                        for ( var prop in familie ) {
                            var rg = new RegExp( "\(\?:\^\\$\|\(\?:\(\[\^\\\\\]\)\\$\)\)" + prop, "g" );
                            html = html.replace( rg, "$1" + familie[prop] );
                        }
                        html = html.replace( /\n/g, '<br>' );
                        html = html.replace( /\\/g, '' );
                        h += html;
                    }
                    h += "</div>";

                    c.innerHTML += h;
                }
            }
        }

        function escapeRegExp( s ) {
            return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        }
    </script>
    <?php
    echo "</head>\n<body>\n";

    echo "<div id=\"testCanvas\" style=\"position:relative\"></div>\n";
    echo "<div id=\"menu\">\n<select id=\"design-select\">\n";
    //echo "<option value=\"\" selected disabled hidden></option>";
    echo "</select>\n";
    echo "<button id=\"drucken\" value=\"Drucken\">Drucken</button>\n</div>\n";
    echo "</body>\n</html>";
    exit;
}




//Main JavaScript file
function js() { ?><script type="text/javascript">
    window.onload = function() {
        var tabHs = document.getElementById( 'tab-head' ).firstElementChild.children;
        //Make tabs clickable and select one
        for ( var i = 0; i < tabHs.length; i++ ) {
            var t = tabHs[i];
            if ( a = tabHs[i].getElementsByTagName( 'a' )[0] ) {
                jQuery( a.getAttribute( 'href' ) ).css( 'display', 'none' );
                if ( ( window.location.hash == "" && i == 0 ) || a.getAttribute( 'href' ) === window.location.hash ) {
                    currentTab = a;
                    changeTab(a);
                }
                a.addEventListener( 'click' , function() { changeTab(this) } );
            }
        }
        
        tabH();

        keyboard_timeout = true;
        keyboard_timeout_ = undefined;
        fam_timeout = undefined;
        
        jQuery( '#ort-select' ).change( ortChange );
        jQuery( '#gruppe-select' ).change( gruppeChange );
        jQuery( '#verw-ort' ).change( ortChangeV );
        
        tdd_orte = { length:0 };
        tdd_ort = {};
        tdd_familien = { query:{length:0} };
        tdd_fam_curr = { query:{length:0} };
        tdd_fam_neu = { query:{length:0} };
        tdd_unsaved_queue = [];
        tdd_unsaved_queue.callback = function( data ) {
            var pc = data.post.set;
            var nd = { ID:data.post.meta.value };
            //jQuery.each( pc, function(i,e) { s = e; nd[i] = s.replaceAll( "'", "" ); } );
            jQuery.each( pc, function(i,e) { nd[i] = e; } );
            console.debug( nd );
            
            var index = -1;
            jQuery.each( tdd_unsaved_queue, function(i,e) { if ( JSON.stringify(e.newdata) == JSON.stringify(nd) ) { index = i; } } );
            
            var f = tdd_unsaved_queue[index];
            if ( data.status == "success" ) {
                if (JSON.stringify( tdd_fam_curr[f.index] ) == JSON.stringify( f.data ) ) {
                    tdd_fam_curr[f.index] = clone( f.newdata );
                    if ( typeof(selected_fam) != "undefined" && f.index == selected_fam.index ) {
                        var new_fam = new familie( tdd_fam_curr[f.index], f.index );
                        selected_fam = new_fam;
                        console.debug( selected_fam, 'saved' );
                        displayFam();
                    } else {
                        console.debug( data, 'saved' );
                    }
                } else {
                    console.debug( data, 'saved' );
                }
                if ( typeof( fam_for_v ) != "undefined" ) {
                    var nd = {ID:data.post.id};
                    jQuery.each( data.post.set, function( i, e ) {
                        nd[i] = e;
                    } );
                    fam_for_v = {data:nd};
                    jumpToV();
                }
            } else {
                f.error = clone( data );
                tdd_save_error.push( f );
                alert("<p>Fehler beim Speichern:</p><p><b>"+data.post.set.Name+"</b></p><p>Mehr in der Konsole</p>","Achtung!");
                console.debug( 'Fehler:', tdd_save_error );
            }
            tdd_unsaved_queue.splice( index, 1 );
            var e = document.getElementById( 'familie-search' );
            e.focus();
            e.select();
            e.setSelectionRange(0, e.value.length);
        };
        tdd_save_error = [];
        
        getOrte();
        getSettings();
        displayLogs();
        
        fam = 0;
        jQuery( '.fam-count, #fam-anw' ).each( function(i,e){
            e.addEventListener( 'click', function(){
                var el = document.getElementById( 'familie-count' );
                el.innerText = fam;
            } );
        } );
        jQuery( '#familie-count' ).on( 'click', function(){
            if ( this.firstChild.tagName != "INPUT" ) {
                var e = document.createElement( 'input' );
                e.type = "number";
                e.value = fam;
                e.style.width = "55px";
                this.replaceChild( e, this.firstChild );
                e.focus();
                e.select();
            }
        } )
        .on( 'keyup', function() {
            var code = event.keyCode ? event.keyCode : event.which;
            if ( code == 13 ) {
                var v = this.firstChild.value;
                fam = ( v == "" ? 0 : v );
                this.innerHTML = fam;
            }
        } )
        .on( 'focusout', function() {
            var v = this.firstChild.value;
            fam = ( v == "" ? 0 : v );
            this.innerHTML = fam;
        } );
        
        jQuery( '#fam-reload' ).on( 'click', function(){
            if ( typeof(tdd_orte) !== "undefined" ) { gruppeChange(); }
        } );
        jQuery( '#fam-anw' ).on( 'click', function(){
            if ( selected_fam.retry ) {
                this.checked = false;
                selected_fam.retry = false;
                highlightElement( jQuery( '#fam-szh' ) );
            }
        } );
        jQuery( '#verw-bneu' ).on( 'click', verwFamNeu );
        jQuery( '#verw-del' ).on( 'click', function(){
            jQuery( '#verw-save, #verw-del' ).prop( 'disabled', true );
            remove( {table:'familien',meta:{key:'ID',value:verw_fam.data.ID}}, delFamV );
        } );
        jQuery( '#fam-schuld' ).on( 'keyup', schuldfieldChange ).on( 'change', schuldfieldChange );
        jQuery( '#fam-gv' ).on( 'click', function(){
            jQuery( '#fam-schuld' ).prop( 'disabled', true );
            if ( +selected_fam.data.Schulden + selected_fam.preis >= selected_fam.preis*3 ) {
                jQuery( '#fam-szh' ).html( "Darf nächstes Mal nur noch nach Begleichen der Schulden hinein." );
            }
        } );
        jQuery( '#fam-sb' ).on( 'click', function(){
            jQuery( '#fam-schuld' ).prop( 'disabled', true );
            if ( selected_fam.schuld ) {
                jQuery( '#fam-anw' ).prop( 'disabled', false );
                jQuery( '#fam-szh' ).html( "" );
            }
        } );

        jQuery( '#log-go' ).on( 'click', getEinnahmen );

        jQuery( '#sett-save' ).on( 'click', function(){saveSettings()} );
        jQuery( '#settings input' ).each( function(i,e){
            jQuery(e).on( 'keypress', function(event){ var code = event.keyCode ? event.keyCode : event.which; if (code == 13) { saveSettings(this); } } );
        } );

        //Fancy alert
        var modal = document.getElementById('modal');
        var span = modal.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
            document.body.style.overflow = "";
        };
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                document.body.style.overflow = "";
            }
        };

        //Help headings
        var hs = jQuery( '#tab6 h2, #tab6 h3, #tab6 h4, #tab6 h5, #tab6 h6' );
        var ul = document.createElement( 'ul' );
        ul.style.marginTop = '0';
        ul.style.marginBottom = '2.5em';
        hs.each( function(i,e) {
            var m = 0, f = '0.85em';
            switch ( e.tagName ) {
                case "H2":
                    m = "10px";
                    break;
                case "H3":
                    m = "25px";
                    f = "0.85em";
                    break;
                case "H4":
                    m = "32px";
                    f = "0.725em";
                    break;
                case "H5":
                    m = "36px";
                    f = "0.675em";
                    break;
                case "H6":
                    m = "39px";
                    f = "0.65em";
                    break;
            }
            var li = document.createElement( 'li' ),
                a = document.createElement( 'a' );

            a.href = '#';
            a.classList.add( 'link' );
            a.innerHTML = e.innerHTML;
            a.style.fontSize = f;
            a.toEl = jQuery(e);

            a.onclick = function(){return false};
            a.addEventListener( 'click', function() {
                scrollEl = this.toEl;
                jQuery( "body" ).animate( {
                    scrollTop: this.toEl.offset().top 
                }, 600, "swing", function() {
                    //Anim done, flash heading
                    scrollEl.fadeTo( 100, 0.2 ).fadeTo( 200, 1.0 ),
                    scrollEl = undefined
                } );
            } );

            li.appendChild( a );
            li.style.marginLeft = m, li.style.marginRight = m;
            ul.appendChild( li );
        } );
        jQuery( ul ).insertAfter( jQuery( '#tab6 h1' ).first() );
    };
    
    window.onresize = function() {
        changeTab( currentTab );
        tabH();
    };
    
    window.onkeydown = function( evt ) {
        evt = evt || window.event;
        
        var charCode = evt.keyCode || evt.charCode || evt.which;
        var charStr = String.fromCharCode(charCode);
        
        if ( currentTab.getAttribute( 'href' ) == "#tab2" && evt.altKey == true && evt.key !== "Alt" ) { switch (charStr) {
            case "N":
                document.getElementById( 'ort-select' ).focus();
                break;
            case "M":
                document.getElementById( 'gruppe-select' ).focus();
                break;
            case "J":
                document.getElementById( 'fam-karte' ).focus();
                break;
            case "K":
                document.getElementById( 'fam-schuld' ).focus();
                break;
            case "L":
                document.getElementById( 'fam-notiz' ).focus();
                break;
            case "U":
                document.getElementById( 'fam-anw' ).click();
                break;
            case "I":
                document.getElementById( 'fam-gv' ).click();
                break;
            case "O":
                document.getElementById( 'fam-sb' ).click();
                break;
            case "¼": //Komma
                var e = document.getElementById( 'familie-search' );
                e.focus();
                e.select();
                e.setSelectionRange(0, e.value.length);
                break;
            case "¾": //Punkt
                document.getElementById( 'fam-reload' ).click();
                break;
            case "&": //Pfeil auf
                document.getElementById( 'familie-list' ).focus();
                var i = tdd_fam_curr.length - 1;
                if ( typeof(selected_fam) != "undefined" ) { i = selected_fam.index - 1; }
                var e = jQuery( '#familie-list li[value="'+i+'"]').get(0);
                if ( typeof(e) != "undefined" ) {
                    selectFam.call( e );
                    jQuery( '#familie-list' ).scrollTo( e );
                }
                break;
            case "(": //Pfeil ab
                document.getElementById( 'familie-list' ).focus();
                var i = 0;
                if ( typeof(selected_fam) != "undefined" ) { i = selected_fam.index + 1; }
                var e = jQuery( '#familie-list li[value="'+i+'"]').get(0);
                if ( typeof(e) != "undefined" ) {
                    selectFam.call( e );
                    jQuery( '#familie-list' ).scrollTo( e );
                }
                break;
            default:
                //console.debug( evt, charCode, charStr );
                break;
        } }

        keyboard_timeout = false;
        if ( typeof(keyboard_timeout_) != "undefined" ) { clearTimeout( keyboard_timeout_ ); }
        keyboard_timeout_ = setTimeout( function(){ keyboard_timeout = true; }, 1500 );
    
    }
    
    function changeTab(e) {
        var h = e.getAttribute( 'href' );
        var c = currentTab;
        
        if ( c.getAttribute( 'href' ) == '#tab2' && typeof(selected_fam) !== "undefined" ) {
            selected_fam.save();
        }
        if ( c.getAttribute( 'href' ) == '#tab5' ) { getOrte(); }
        location.href = h;
        
        jQuery( c.getAttribute( 'href' ) ).css( 'display', 'none' );
        c.classList.remove( 'selected' );
        jQuery( h ).css( 'display', 'block' );
        e.classList.add( 'selected' );
        currentTab = e;

        if ( h == '#tab2' ) {
            if ( typeof(tdd_orte) !== "undefined" ) { gruppeChange(); }
        }
        if ( h == '#tab3' ) { searchV(searchFamV); }
        if ( h == '#tab4' ) { var p = jQuery( '#log-pagination' ).val(); getLogs(p); }
        if ( h == '#tab5' ) { getOrte(); }
        
        selected_fam = undefined;
        verw_fam = undefined;
        
        var f = getFamForm();
        resetForm( f );
        f = getVerwForm();
        resetForm( f );
        jQuery( '#verw-save, #verw-del, #verw-neu' ).prop( 'disabled', true );
    }
    
    
    //Serverrequests
    function getOrte() {
        jQuery.post( '?post&getOrte', {callback:'orte'}, postC );
    }
    
    function orte( data ) {
        if ( data.status == "success" ) {
            var oa = data.query;
            tdd_orte = oa;

            for ( var i = 0; i < oa.length; i++ ) {
                tdd_ort[oa[i].Name] = i;
            }
            
            var o = document.getElementById( 'ort-select' );
            while ( o.lastChild ) {
                o.removeChild( o.lastChild );
            }
            var g = document.getElementById( 'gruppe-select' );
            while ( g.lastChild ) {
                g.removeChild( g.lastChild );
            }
            
            for ( var i = 0; i < oa.length; i++ ) {
                var e = document.createElement( 'option' );
                e.value = i;
                var t = document.createTextNode( unescape(oa[i].Name) );
                e.appendChild( t );
                o.appendChild( e );
            }
            
            ortChange();
            displayOrte();
            //getFamilien();
        } else {
            console.debug( 'Orte failed: ', data );
        }
    }
    
    function getFamilien() {
        get( {table:"Familien"}, fam );
    }
    
    function fam( data ) {
        if ( data.status == "success" ) {
            var fa = data.query;
            tdd_familien = fa;
        } else { console.debug( data ); }
    }

    function getSettings() {
        get( {table:"Einstellungen"}, sett );
    }

    function sett( data ) {
        if ( data.status == "success" ) {
            var q = data.query;
            var s = {query:q};
            jQuery.each( q, function(i,e){
                s[e.Name] = unescape(e.Val);
            });
            tdd_settings = s;

            displaySettings();
        } else { console.debug( data ); }
    }

    function getSearch( string, callback ) {
        var meta = [], byid = false;
        string = string.replace( /^(?: )+|(?: )+$/g, '' ).replace( /(?: ){2,}/g, ' ' );
        var a = string.split( / (?=(?:[^'|"]*(?:'|")[^'|"]*(?:'|"))*[^'|"]*$)/g );
        // regex by https://stackoverflow.com/a/3147901
        
        if ( Number.isInteger(+string) && string != "" ) {
            meta.push({value:string});
            byid = true;
        } else {
            for ( var i = 0; i < a.length; i++ ) {
                var str = a[i], c = "LIKE", con = "";
                if ( str.slice(0,1) === "!" ) {
                    str = str.slice(1);
                    con = "NOT";
                }
                if ( str.slice(0,1) === "=" ) {
                    str = str.slice(1);
                    c = "=";
                }
                str = escape( str.replace( /^(?:'|")|(?:'|")$/g, '' ) );
                if ( c === "LIKE" ) {
                    str = "%" + str + "%";
                }
                meta.push({compare:c,value:str,connect:con});
            }
        }
        
        post( '?post&getSearch', {meta:meta,byid:byid}, callback );
    }
    
    //Handling requests to server
    function get( postparam = {}, callback = "" ) {
        post( '?post&get', postparam, callback );
    }
    
    function update( postparam = {}, callback = "" ) {
        post( '?post&update', postparam, callback );
    }
    
    function insert( postparam = {}, callback = "" ) {
        post( '?post&insert', postparam, callback );
    }
    
    function remove( postparam = {}, callback = "" ) {
        post( '?post&delete', postparam, callback );
    }
    
    function post( url, postparam = {}, callback = "" ) {
        if ( typeof( callback ) == "string" || typeof( callback ) == "object" ) {
            postparam.callback = callback;
        } else if ( typeof( callback ) == "function" ) {
            postparam.callback = callback.name;
        } else {
            postparam.callback = "";
        }
        jQuery.post( url, postparam, postC );
    }
    
    function postC( data ) {
        if ( typeof( data.callback ) == 'string' ) {
            var fn = window[data.callback];
        } else if ( typeof( data.callback == 'object' ) ) {
            var fn = window;
            jQuery.each( data.callback, function(i,e) { fn = fn[e]; } );
        }
        if ( typeof fn === 'function' ) {
            fn( data );
        } else {
            console.debug( data.callback, 'is no function\n', data );
        }
    }


    //Minimum-height for tabs
    function tabH() {
        var b = document.getElementById( 'tab-body' ),
            w = window.outerWidth,
            h = document.getElementById( 'tab-head' ).offsetHeight;
        if ( w >= 1160 ) {
            b.style.minHeight = h + 'px';
        } else {
            b.style.minHeight = '';
        }
    }
    
    
    //Make familien-list + selecting functions
    function famList( first = false ) {
        var l = document.getElementById( 'familie-list' );
        var lis = l.children;
        for ( var i = 0; i < lis.length; i++ ) {
            lis[i].removeEventListener( 'click', selectFam );
            if ( lis[i].getAttribute( 'value' ) !== null ) {
                lis[i].addEventListener( 'click', selectFam );
                // if ( first ) {
                //     //Only one: set directly as present and prepare for next search
                //     lis[i].click();
                //     document.getElementById( 'fam-anw' ).click();
                // }
            }
        }
        
        var e = document.getElementById( 'familie-search' );
        e.focus();
        e.select();
        e.setSelectionRange(0, e.value.length);

    }
    
    function selectFam() {
        if ( typeof(selected_fam) !== "undefined" ) {
            var cf = selected_fam.index;
            var ce = jQuery( 'ul#familie-list li[value='+cf+']' )[0];
            if ( typeof(ce) !== "undefined" ) { ce.classList.remove( 'selected' ); }
            selected_fam.save();
        }
        this.classList.add( 'selected' );
        var new_fam = new familie( tdd_fam_curr[this.value], this.value );
        selected_fam = new_fam;
        displayFam();
    }
    
    function displayFam() {
        var f = getFamForm(),
            d = selected_fam.data;
        
        f[0].html( unescape(d.Ort) );
        f[1].html( d.Gruppe );
        if ( d.lAnwesenheit != "0000-00-00" && d.lAnwesenheit != "" ) {
            var date = new Date( d.lAnwesenheit );
            var date = date.toLocaleDateString();
        } else {
            var date = "";
        }
        f[2].html( date );
        var karte = ( d.Karte != "0000-00-00" ? d.Karte : "" );
        f[3].val( karte );
        f[4].html( d.Erwachsene );
        f[5].html( d.Kinder );
        var pr = +preis(+d.Erwachsene, +d.Kinder);
        selected_fam.preis = pr;
        f[6].html( pr.toFixed(2) + "€&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&rarr; " + (pr + +d.Schulden).toFixed(2) );
        f[7].val( (+d.Schulden).toFixed(2) );
        f[8].val( unescape(d.Notizen) );
        var lt = (date == new Date().toLocaleDateString());
        f[9].prop( "checked", lt );
        f[12].html( d.Num );
        f[13].html( "(" + unescape(d.Name) + ", " + d.ID + ")</span>" );
        f[14].html( unescape(d.Adresse).replace(/\n/g, '<br />') );
        f[15].html( unescape(d.Telefonnummer) );

        jQuery( f ).each( function(i,e){ e.prop( 'disabled', false ); } );
        f[9].prop( "disabled", lt );

        jQuery( '#barcode' ).JsBarcode( num_pad( d.ID, 6 ), { height:28, width:1, textMargin:0, fontSize:11, background:0, marginLeft:15, marginRight:15, margin:0, displayValue:true } );

        //Karte abgelaufen?
        var date = new Date( karte );
        var t = new Date();
        var diff = ( t.getTime() - date.getTime() );
        var days = Math.ceil( diff / (1000 * 3600 * 24) );
        if ( days > 0 ) {
            jQuery( '#fam-ab' ).html( "Karte abgelaufen!" );
        } else if ( karte == "" ) {
            jQuery( '#fam-ab' ).html( "Ablaufdatum eingeben!" );
        } else {
            jQuery( '#fam-ab' ).html( "" );
        }

        //Schulden zu hoch?
        var date = new Date( d.lAnwesenheit );
        var t = new Date();
        var diff = ( t.getTime() - date.getTime() );
        var days = Math.ceil( diff / (1000 * 3600 * 24) );
        if ( +d.Schulden >= pr*3 && days != 1 ) {
            f[9].prop( 'disabled', true );
            selected_fam.schuld = true;
            jQuery( '#fam-szh' ).html( "Schulden zu hoch! Muss erst Schulden begleichen!" );
        } else if ( +d.Schulden >= pr*3 && days == 1 ) {
            selected_fam.schuld = false;
            jQuery( '#fam-szh' ).html( "Darf nächstes Mal nur noch nach Begleichen der Schulden hinein." );
        } else {
            selected_fam.schuld = false;
            jQuery( '#fam-szh' ).html( "" );
        }

        //Bereits abgeholt diese Woche?
        if ( days <= t.getDay() ) {
            var t = jQuery( '#fam-szh' ).html();
            if ( t != "" ) t += '<br>';
            jQuery( '#fam-szh' ).html( t + "Hat diese Woche bereits abgeholt!" );
            selected_fam.retry = true;
        }

        if ( typeof(fam_timeout) != "undefined" ) { clearTimeout( fam_timeout ); }
        var timeout = setTimeout( saveTimeout, 20000 );
        fam_timeout = timeout;
    }

    function insertFam( data, list = "" ) {
        if ( data.status == "success" ) {
            var f = document.getElementById( 'familie-list' ),
                q = data.query;

            while ( f.lastChild ) {
                f.lastChild.removeEventListener( 'click', selectFam );
                f.removeChild( f.lastChild );
            }
            tdd_fam_curr = data.query;
            if ( list !== "" ) { q = list.query; tdd_fam_curr = q; }
            
            for ( var i = 0; i < q.length; i++ ) {
                var e = document.createElement( 'li' );
                e.value = i;
                var name = unescape(q[i].Name);
                if ( name.trim() == "" ) { name = " - "; }
                if ( q[i].Num ) name = q[i].Num + "/ " + name;
                var t = document.createTextNode( name );
                e.appendChild( t );
                f.appendChild( e );
            }
            
            famList();
            
        } else { console.debug( data ); }
    }

    
    //Verwaltung-tab
    function verwList() {
        var l = document.getElementById( 'verwaltung-list' );
        var lis = l.children;
        for ( var i = 0; i < lis.length; i++ ) {
            lis[i].removeEventListener( 'click', selectFamV );
            if ( lis[i].getAttribute( 'value' ) !== null ) {
                lis[i].addEventListener( 'click', selectFamV );
            }
        }
    }
    
    function selectFamV() {
        if ( typeof(verw_index) != "undefined" ) {
            var cf = verw_index;
            var ce = jQuery( 'ul#verwaltung-list li[value='+cf+']' )[0];
            if ( typeof(ce) !== "undefined" ) { ce.classList.remove( 'selected' ); }
        }
        if ( typeof(verw_fam) == "undefined" || (typeof(verw_fam) != "undefined" && verw_fam.saved) ) {
            if ( this !== window ) {
                this.classList.add( 'selected' );
                verw_fam = new familie( tdd_fam_curr[this.value], this.value );
                verw_index = this.value;
            }
            displayFamV( verw_fam );
            var bs = jQuery( '#verw-save' );
            bs.off( 'click' );
            bs.on( 'click', function(){ verw_fam.save( 'verwaltung' ); } );
        }
    }
    
    function displayFamV(e) {
        var f = getVerwForm(),
            d = e.data;

        if ( typeof(d) == "undefined" ) { return; }
        jQuery( '#verw-save, #verw-del' ).prop( 'disabled', false )
            .css( 'display', 'inline-block' );
        jQuery( '#verw-neu' ).css( 'display', 'none' );

        var ort = -1;
        jQuery.each( tdd_orte, function(i,e) { if ( e.Name == d.Ort ) { ort = i; } } );
        e.ort = ort;
        
        var o = f[0].get(0);
        
        var g = o;
        while ( g.lastChild ) {
            g.lastChild.removeEventListener( 'click', selectFamV );
            g.removeChild( g.lastChild );
        }
        
        var oa = tdd_orte;
        for ( var i = 0; i < oa.length; i++ ) {
            var el = document.createElement( 'option' );
            el.value = i;
            var t = document.createTextNode( unescape(oa[i].Name) );
            el.appendChild( t );
            o.appendChild( el );
        }
        
        ortChangeV();
        
        f[0].val( e.ort );
        if ( d.Gruppe != 0 ) { f[1].val( d.Gruppe ) };
        if ( d.lAnwesenheit != "0000-00-00" ) {
            var date = d.lAnwesenheit;
        } else { var date = ""; }
        f[2].val( date );
        f[3].val( d.Karte );
        f[4].val( d.Erwachsene );
        f[5].val( d.Kinder );
        f[6].html( d.ID );
        f[7].val( d.Schulden );
        f[8].val( unescape(d.Notizen) );
        f[9].val( unescape(d.Name) );
        f[10].val( d.Num );
        f[11].val( unescape(d.Adresse) );
        f[12].val( unescape(d.Telefonnummer) );
        jQuery( '#barcode' ).JsBarcode( num_pad( d.ID, 6 ), { height:28, width:1, textMargin:0, fontSize:11, background:0, marginLeft:15, marginRight:15, margin:0, displayValue:true } );

        jQuery( f ).each( function(i,e){ e.prop( 'disabled', false ); } );
    }
    
    function savedVerw( data ) {
        if ( data.status == "success" ) {
            searchV( searchFamV );

            verw_fam = new familie( verw_neu.newdata, verw_neu.index );
            verw_fam.saved = true;
            console.debug( verw_fam, 'saved' );

            tdd_fam_curr[verw_fam.index] = clone( verw_fam.data );

            displayFamV( verw_fam );
            verw_neu = undefined;
            
            jQuery( '#verw-save, #verw-del' ).prop( 'disabled', false );
        } else { console.debug( data ); saveErrorV( data ); }
    }
    
    function savedVerwN( data ) {
        if ( data.status == "success" ) {
            console.debug( data );
            if ( data.ID ) {
                searchV( searchFamV );

                verw_fam = new familie( {ID:data.ID}, -1 );
                delete verw_neu.newdata.ID;
                jQuery.each( verw_neu.newdata, function(i,e) { verw_fam.data[i] = e; } );
                verw_fam.saved = true;
                console.debug( verw_fam, 'saved' );

                try {
                    tdd_orte[tdd_ort[verw_fam.data.Ort]].Personen[verw_fam.data.Gruppe]++;
                } catch (e) {}
                displayFamV( verw_fam );
                verw_neu = undefined;
                
                var i = tdd_fam_neu.query.length;
                tdd_fam_neu.query[i] = verw_fam.data;
                tdd_fam_neu.query.length = ++i;
                
                jQuery( '#verw-save, #verw-del, #verw-neu' ).prop( 'disabled', false )
                    .css( 'display', 'inline-block' );
                jQuery( '#verw-neu' ).css( 'display', '' );
            } else {
                alert( "<p>Keine ID bekommen...<br>Bitte neu probieren.</p>", "Fehler" );
            }
        } else { saveErrorV( data ); }
    }
    
    function saveErrorV( data ) {
        if ( data.status == "success" ) {
            console.debug( "Something went wrong.......\n", data );
        } else { console.debug( data ); alert("<p>Fehler beim Speichern:</p><p><b>"+data.post.set.Name+"</b></p><p>Mehr in der Konsole</p>","Achtung!"); }
    }
    
    function delFamV( data ) {
        if ( data.status == "success" ) {
            if ( data.rows == 1 ) {
                var f = getVerwForm();
                resetForm( f );
                jQuery( '#verw-barcode' ).attr( 'src', '' );
                searchV( searchFamV );
            } else { console.debug( data ); }
        } else { console.debug( data ); }
    }
    
    function famInV() {
        if ( selected_fam != "undefined" ) {
            fam_for_v = selected_fam;
            var tabHs = document.getElementById( 'tab-head' ).firstElementChild.children;
            a = tabHs[2].getElementsByTagName( 'a' )[0];
            changeTab( a );
        }
    }
    function jumpToV() {
        verw_fam = new familie( fam_for_v.data, -1 );
        selectFamV();
        fam_for_v = undefined;
    }

    function verwFamNeu(){
        var f = getVerwForm();
        resetForm( f );
        jQuery( f ).each( function(i,e){ e.prop( 'disabled', false ); } );
        
        var bn = jQuery( '#verw-neu' );
        jQuery( '#verw-save, #verw-del' ).css( 'display', 'none' );
        bn.css( 'display', 'inline-block' );
        bn.prop( 'disabled', false );
        bn.off( 'click' );
        bn.on( 'click', function(){ verw_fam.save( 'verwaltung-neu' ); } );
        
        var ort = -1;
        
        var o = f[0].get(0);
        
        var g = o;
        while ( g.lastChild ) {
            g.lastChild.removeEventListener( 'click', selectFamV );
            g.removeChild( g.lastChild );
        }
        
        var oa = tdd_orte;
        for ( var i = 0; i < oa.length; i++ ) {
            var el = document.createElement( 'option' );
            el.value = i;
            var t = document.createTextNode( unescape(oa[i].Name) );
            el.appendChild( t );
            o.appendChild( el );
        }
        
        ortChangeV();
        
        if ( typeof(verw_index) !== "undefined" ) {
            var cf = verw_index;
            var ce = jQuery( 'ul#verwaltung-list li[value='+cf+']' )[0];
            if ( typeof(ce) !== "undefined" ) {ce.classList.remove( 'selected' ); }
        }
        verw_fam = new familie( {}, -1 );
        verw_index = undefined;
    }


    //Logstab
    function displayLogs() {
        var lf = jQuery( '#log-from' ), lt = jQuery( '#log-to' );

        var d = new Date();
        d.setUTCDate(1); d.setUTCHours(0); d.setUTCMinutes(0); d.setUTCSeconds(0);
        lf.val( d.toISOString().replace( /\.[0-9]{3}Z/, "") );

        var d = new Date();
        d.setUTCMonth(d.getUTCMonth()+1); d.setUTCDate(0); d.setUTCHours(23); d.setUTCMinutes(59); d.setUTCSeconds(59);
        lt.val( d.toISOString().replace( /\.[0-9]{3}Z/, "") );

        getEinnahmen();
        getLogs();
    }

    function getEinnahmen() {
        var lf = jQuery( '#log-from' ), lt = jQuery( '#log-to' );
        var d1 = lf.val().replaceAll(':','.');
        var d2 = lt.val().replaceAll(':','.');
        get( {table:'logs',meta:[{key:'date_time',value:d1,compare:'>='},{key:'date_time',value:d2,compare:'<='},{key:'aff_table',value:'familien'},{key:'action',value:'UPDATE'}]}, einnahmenC );
    }
    function einnahmenC ( data ) {
        var ein = jQuery( '#einnahmen' ),
            kinder = jQuery( '#log_kinder' ),
            erw = jQuery( '#log_erw' );

        if ( data.status == "success" ) {
            var g = 0, k = 0, e = 0;

            for ( var i = 0; i < data.query.length; i++ ) {
                var j = JSON.parse(data.query[i].message);
                if ( typeof(j.geld) != "undefined" && j.geld != "NaN" ) {
                    g += +j.geld;
                }
                if ( j.post && typeof(j.post.anw) != "undefined" && j.post.anw == "true" ) {
                    // only count if properly set to "anwesend"
                    if ( j.post && j.post.set ) {
                        if ( j.post.set.Kinder && +j.post.set.Kinder != NaN ) {
                            k += +j.post.set.Kinder;
                        }
                        if ( j.post.set.Erwachsene && +j.post.set.Erwachsene != NaN ) {
                            e += +j.post.set.Erwachsene;
                        }
                    }
                }
            }

            ein.html( g.toFixed(2) + "€" );
            kinder.html( k );
            erw.html( e );

        } else { ein.html( "<i>Fehler</i>" ); console.debug( data ); }
    }

    function getLogs( page = 0, search = [] ) {
        if ( page != null && typeof(page) == "object" && page.target ) {
            //page is change event
            page = page.target.value;
        }
        var lgs = document.getElementById( 'complete-log' );
        if ( (!Array.isArray(search) || search.length == 0) && typeof(lgs.search) !== "undefined" ) {
            search = lgs.search;
        }
        get( {table:'logs',meta:search,limit:20,offset:page*20,order_by:'date_time',page:page,meta_connection:"OR"}, logs );
    }
    function searchLogs( element ) {
        var meta = [],
            string = escape(element[0].value),
            h = element.headings,
            lgs = document.getElementById( 'complete-log' );

        string = string.replace( /^(?:%20)+|(?:%20)+$/g, '' ).replace( /(?:%20){2,}/g, '%20' );
        var a = string.split( '%20' );
        
        for ( var i = 0; i < a.length; i++ ) {
            var str = a[i];
            str = "%" + str + "%";
            for ( var j = 0; j < h.length; j++ ) {
                meta.push({key:h[j],value:str,compare:"LIKE"});
            }
        }

        lgs.search = meta;
        getLogs( 0, meta );
    }
    function logs( data ) {
        var p = jQuery( '#log-pagination' ), lgs = jQuery( '#complete-log' ),
            page = data.post.page, pages = Math.ceil( data.rows/20 );

        if ( p.children().length != pages || data.status != "success" ) {
            var o = p.get(0);
            while ( o.lastChild ) {
                o.removeChild( o.lastChild );
            }
            for ( var i = 0; i < pages; i++ ) {
                var e = document.createElement( 'option' );
                e.value = i;
                var t = document.createTextNode( 'Seite ' + (i+1) );
                e.appendChild( t );
                p.append( e );
            }
            o.removeEventListener( 'click', getLogs );
            o.addEventListener( 'change', getLogs );
            p.val( page );
        }
        if ( p.val() != page ) { p.val( page ) };

        lgs.html( '' );
        if ( data.status == "success" ) {
            if ( data.query.length > 0 ) {
                var form = document.createElement( 'form' );
                form.action = "#";
                form.onsubmit = function(){searchLogs(this);return false};
                form.innerHTML = "<input type=\"text\" placeholder=\"Suchen\"><input type=\"submit\" value=\"Suchen\">";
                form.headings = [];
                var table = document.createElement( 'table' );
                table.classList.add( 'logs' );
                var tbody = document.createElement( 'tbody' );
                table.appendChild( tbody );
                var tr = document.createElement( 'tr' );
                jQuery.each( data.query[0], function(e) {
                    var th = document.createElement( 'th' );
                    var t = document.createTextNode( e );
                    form.headings.push( e );
                    th.appendChild( t );
                    tr.appendChild( th );
                } );
                tbody.appendChild( tr );
                for ( var i = 0; i < data.query.length; i++ ) {
                    var tr = document.createElement( 'tr' );
                    jQuery.each( data.query[i], function(e,n) {
                        var td = document.createElement( 'td' );
                        var t = document.createTextNode( n );
                        td.appendChild( t );
                        tr.appendChild( td );
                    } );
                    tbody.appendChild( tr );
                }
                lgs.append( form );
                lgs.append( table );
            }
        }
    }


    //Settingstab
    function displaySettings() {
        var f = getSettForm();
        var s = tdd_settings;
        for ( var i = 0; i < f.length; i++ ) {
            var n = f[i].data( "name" );
            if ( typeof(n) !== "undefined" ) {
                if ( typeof( f[i].html ) == "function" ) { f[i].html( unescape(s[n]) ); }
                if ( typeof( f[i].val ) == "function" ) { f[i].val( unescape(s[n]) ); }
            }
        }
    }

    function displayOrte() {
        var o = document.getElementById( 'orte' );
        //o.style.height = '';

        while ( o.lastChild ) {
            o.lastChild.removeEventListener( 'click', selectOrt );
            o.removeChild( o.lastChild );
        }

        var os = tdd_orte;
        for ( var i = 0; i < os.length; i++ ) {
            var el = document.createElement( 'li' );
            el.value = i;
            var t = document.createTextNode( unescape(os[i].Name) );
            el.appendChild( t );

            var sp = document.createElement( 'span' );
            sp.classList.add( 'list-add' );
            var it = document.createTextNode( 'ID:' + os[i].ID );
            sp.appendChild( it );
            el.appendChild( sp );

            var div = document.createElement( 'div' );
            div.classList.add( 'expand' );
            var inp = document.createElement( 'input' );
            inp.type = 'number';
            inp.value = os[i].Gruppen;
            inp.placeholder = "Gruppen";
            div.appendChild( inp );
            div.appendChild( document.createElement( 'br' ) );

            var b = document.createElement( 'button' );
            b.addEventListener( 'click', ortSave );
            var t = document.createTextNode( 'Speichern' );
            b.appendChild( t );
            div.appendChild( b );

            var b = document.createElement( 'a' );
            b.addEventListener( 'click', ortDel );
            b.classList.add( 'link-delete' );
            b.classList.add( 'ml15px' );
            b.href = '#';
            var t = document.createTextNode( 'Löschen' );
            b.appendChild( t );
            div.appendChild( b );

            el.appendChild( div );
            el.name = unescape(os[i].Name);
            o.appendChild( el );
        }

        var el = document.createElement( 'li' );
        el.value = -1;
        el.style.textAlign = 'center';
        var t = document.createTextNode( '+' );
        el.appendChild( t );
        o.appendChild( el );

        //o.style.height = jQuery(o).outerHeight();

        var lis = o.children;
        for ( var i = 0; i < lis.length; i++ ) {
            lis[i].removeEventListener( 'click', selectOrt );
            if ( lis[i].getAttribute( 'value' ) !== null ) {
                lis[i].addEventListener( 'click', selectOrt );
            }
        }
    }

    function saveSettings( element = null ) {
        if ( element == null ) {
            var f = getSettForm();
        } else {
            var f = [jQuery(element)];
        }
        for ( var i = 0; i < f.length; i++ ) {
            var n = f[i].data( "name" );
            var v = "";
            if ( typeof(n) !== "undefined" ) {
                if ( typeof( f[i].html ) == "function" ) { v = escape(f[i].html()); }
                if ( typeof( f[i].val ) == "function" ) { v = escape(f[i].val()); }
            }
            update( {table:"Einstellungen",meta:{key:"Name",value:n},set:{Val:v}}, savedSett );
        }
    }

    function savedSett( data ) {
        if ( data.status == "success" ) {
            console.debug( data, 'saved' );
            getSettings();
        } else { console.debug( data ); }
    }
    
    function selectOrt ( event ) {
        if ( event.target == this && event.target.value == -1 ) {
            insert( { table:'Orte', set:{ID:'NULL',Name:""} }, ortInsert );
            return;
        }
        if ( event.target == this || event.target.tagName == "DIV" ) {
            if ( !this.classList.contains( 'expanded' ) ) {
                var t = this.firstChild;
                var inp = document.createElement( 'input' );
                inp.value = this.name;
                inp.placeholder = "Name";
                this.replaceChild( inp, t );
            } else {
                var i = this.firstChild;
                var t = document.createTextNode( this.name );
                this.replaceChild( t, i );
            }
            this.classList.toggle( 'expanded' );
        }
    }

    function ortInsert ( data ) {
        if ( data.status == "success" ) {
            getOrte();
            displayOrte();
            changeTab( currentTab );
        } else { console.debug( data ); }
    }

    function ortSave() {
        var li = this.parentNode.parentNode;
        var i = li.value;
        var id = tdd_orte[i].ID;

        var set = {};
        var ins = li.getElementsByTagName( 'input' );
        set['Name'] = escape(ins[0].value);
        set['Gruppen'] = ins[1].value;

        console.debug( set, 'saving' );
        update( {table:"Orte",meta:{key:"ID",value:id},set:set,val:i}, savedOrt );
    }

    function savedOrt ( data ) {
        if ( data.status == "success" ) {
            console.debug( data, 'saved' );
            var li = jQuery( 'ul#orte li[value='+data.post.val+']' );
            li.prop( 'name', unescape(data.post.set.Name) );
            li.children()[2].children[0].value = +data.post.set.Gruppen;
            li.click();
        } else { console.debug( data ); }
    }

    function ortDel ( event ) {
        var li = this.parentNode.parentNode;
        var i = li.value;
        var id = tdd_orte[i].ID;

        remove( {table:"Orte",meta:{key:"ID",value:id},val:i}, removedOrt );
        event.preventDefault();
    }

    function removedOrt ( data ) {
        if ( data.status == "success" ) {
            var i = data.post.val;
            delete tdd_orte[i];

            var li = jQuery( '#orte li[value="'+i+'"]' );
            li = li.get(0);
            li.parentElement.removeChild( li );
        } else { console.debug( data ); }
    }

    function delFamDate( date = -1, column = 'lAnwesenheit' ) {
        if ( date == -1 ) {
            date = new Date();
            //8 weeks
            date.setTime( date.getTime() - 8*7*24*1000*3600 );
        }
        if ( typeof(date) == "number" ) {
            var n = date;
            date = new Date();
            //n days
            date.setTime( date.getTime() - n*24*1000*3600 );
        }
        if ( typeof(date) == "object" ) {
            date = formatDate( date );
        }
        if ( typeof(date) == "string" ) {
            remove( {table:'Familien',meta:[{key:column,value:date,compare:"<="},{key:column,value:"0000-00-00",compare:"<>"}]}, famBulkDel );
        } else {
            console.debug( date, "is no string" );
        }
    }

    function famBulkDel( data ) {
        if ( data.status == "success" ) {
            alert( data.rows + " Einträge gelöscht.", "Fertig" )
        } else { console.debug( data ); }
    }


    //Eventlisteners dropdown-menus
    function ortChange() {
        var g = document.getElementById( 'gruppe-select' );
        while ( g.lastChild ) {
            g.removeChild( g.lastChild );
        }
        
        var os = document.getElementById( 'ort-select' ).selectedOptions[0];
        if ( typeof( os ) != "undefined" ) {
            var o = tdd_orte[os.value];
            ort = o.Name;
            
            for ( var i = 1; i <= +o.Gruppen; i++) {
                var e = document.createElement( 'option' );
                e.value = i;
                var t = document.createTextNode( 'Gruppe ' + i );
                e.appendChild( t );
                g.appendChild( e );
            }
            var e = document.createElement( 'option' );
            e.value = "0";
            var t = document.createTextNode( 'Neu' );
            e.appendChild( t );
            g.appendChild( e );
            
            gruppeChange();
        }
    }
    
    function gruppeChange() {
        if ( typeof(selected_fam) !== "undefined" ) {
            selected_fam.save();
        }
        var o = typeof( document.getElementById( 'ort-select' ).selectedOptions[0] ),
            g = typeof( document.getElementById( 'gruppe-select' ).selectedOptions[0] );
        
        var f = document.getElementById( 'familie-list' );
        while ( f.lastChild ) {
            f.lastChild.removeEventListener( 'click', selectFam );
            f.removeChild( f.lastChild );
        }
        
        if ( currentTab.getAttribute('href') == '#tab2' ) {
            if ( o !== "undefined" && g !== "undefined" ) { 
                gruppe = document.getElementById( 'gruppe-select' ).selectedOptions[0].value;
                if ( gruppe == "0" ) {
                    insertFam( {status:"success"}, tdd_fam_neu );
                    return;
                }
                
                get( {table:"familien", meta:[{key:"Ort",value:ort},{key:"Gruppe",value:gruppe}], order_by:"Num, ID"}, insertFam );
            } else {
                search(searchFamA);
            }
        }
    }

    function ortChangeV() {
        var f = getVerwForm();
        
        var o = f[1].get(0),
            ort = f[0].val();
        
        while ( o.lastChild ) {
            o.removeChild( o.lastChild );
        }
        
        var oa = tdd_orte[ort] && tdd_orte[ort].Gruppen || 0;
        var g = 1, p = Infinity;
        for ( var i = 1; i <= oa; i++ ) {
            var el = document.createElement( 'option' );
            el.value = i;
            var t = document.createTextNode( 'Gruppe ' + i );
            el.appendChild( t );
            o.appendChild( el );
            if ( tdd_orte[ort].Personen[i] < p ) {
                g = i;
                p = tdd_orte[ort].Personen[i];
            }
        }
        o.value = g;
    }
    
    
    //Search for familien and verwaltung tabs
    function search(c) {
        if ( typeof(selected_fam) !== "undefined" ) {
            selected_fam.save();
        }
        
        jQuery( '#ort-select' ).prop( "value", -1 );
        jQuery( '#gruppe-select' ).prop( "value", -1 );
        
        var g = document.getElementById( 'gruppe-select' );
        while ( g.lastChild ) {
            g.removeChild( g.lastChild );
        }
        
        var f = document.getElementById( 'familie-list' );
        while ( f.lastChild ) {
            f.lastChild.removeEventListener( 'click', selectFam );
            f.removeChild( f.lastChild );
        }

        var f = getFamForm();
        jQuery( '#barcode' ).attr( 'src', '' );
        resetForm( f );
        selected_fam = undefined;
        
        var s = document.getElementById('familie-search').value;
        getSearch( s, c );
    }
    
    function searchV(c) {
        var f = document.getElementById( 'verwaltung-list' );
        while ( f.lastChild ) {
            f.lastChild.removeEventListener( 'click', selectFamV );
            f.removeChild( f.lastChild );
        }

        var f = getVerwForm();
        jQuery( '#barcode' ).attr( 'src', '' );
        resetForm( f );
        verw_fam = undefined;
        
        var s = document.getElementById('verwaltung-search').value;
        getSearch( s, c );
    }
    
    //Search-callbacks
    function searchFamA( data ) {
        var f = document.getElementById( 'familie-list' );
        while ( f.lastChild ) {
            f.lastChild.removeEventListener( 'click', selectFam );
            f.removeChild( f.lastChild );
        }
        searchFam( data, f );
        famList( (data.post.byid == "true") );
    }
    
    function searchFamV( data ) {
        var f = document.getElementById( 'verwaltung-list' );
        while ( f.lastChild ) {
            f.lastChild.removeEventListener( 'click', selectFam );
            f.removeChild( f.lastChild );
        }
        searchFam( data, f );
        verwList();

        if ( typeof(verw_fam) !== "undefined" ) {
            jQuery( '#verwaltung-list li[value="' + verw_fam.index + '"]' ).addClass( 'selected' );
        }
    }
    
    function searchFam( data, f ) {
        if ( data.status == "success" ) {
            var q = data.query,
                co = "",
                cg = 0;
            
            tdd_fam_curr = q;
            
            for ( var i = 0; i < q.length; i++ ) {
                if ( co !== q[i].Ort || cg !== q[i].Gruppe ) {
                    var e = document.createElement( 'li' );
                    e.classList.add( 'title' );
                    var t = document.createTextNode( unescape(q[i].Ort) + ", Gruppe " + q[i].Gruppe );
                    e.appendChild( t );
                    f.appendChild( e );
                    
                    co = q[i].Ort;
                    cg = q[i].Gruppe;
                }
                var e = document.createElement( 'li' );
                e.value = i;
                var name = unescape(q[i].Name);
                if ( name.trim() == "" ) { name = " - "; }
                if ( q[i].Num ) name = q[i].Num + "/ " + name;
                var t = document.createTextNode( name );
                e.appendChild( t );
                f.appendChild( e );
            }
            
        } else { console.debug( data ); }
    }


    function postFamKarte( familie ) {
        if ( typeof(familie) !== "undefined" && familie !== {} ) {
            var e = escape( tdd_settings["Kartendesigns"] );
            var fe = '<input type="hidden" name="designs" value="'+e+'" />';
            var d = familie.data;
            d.Preis = preis( +d.Erwachsene, +d.Kinder ).toFixed(2);
            var s = document.getElementById( 'barcode' ).src;
            d.isrc = s;
            d.img = "<img src=\"" + s + "\" />";
            e = escape( JSON.stringify( d ) );
            fe += '<input type="hidden" name="familie" value="'+e+'" />';
            var frmName = "frm" + new Date().getTime();
            var url = "?karte";
            var form = '<form name="'+frmName+'" method="post" target="karte" action="'+url+'">'+fe+'</form>';

            var wrapper = document.createElement("div");
            wrapper.innerHTML = form;
            document.body.appendChild( wrapper );
            document.forms[frmName].submit();
            wrapper.parentNode.removeChild( wrapper );
        }

    }
    

    //Forms
    function getFamForm() {
        var f = [];
        f.push( jQuery( '#fam-ort' ) );
        f.push( jQuery( '#fam-gruppe' ) );
        f.push( jQuery( '#fam-lan' ) );
        f.push( jQuery( '#fam-karte' ) );
        f.push( jQuery( '#fam-erw' ) );
        f.push( jQuery( '#fam-kinder' ) );
        f.push( jQuery( '#fam-preis' ) );
        f.push( jQuery( '#fam-schuld' ) );
        f.push( jQuery( '#fam-notiz' ) );
        f.push( jQuery( '#fam-anw' ) );
        f.push( jQuery( '#fam-gv' ) );
        f.push( jQuery( '#fam-sb' ) );
        f.push( jQuery( '#fam-num' ) );
        f.push( jQuery( '#fam-info' ) );
        f.push( jQuery( '#fam-adresse' ) );
        f.push( jQuery( '#fam-tel' ) );
        return f;
    }
    function getVerwForm() {
        var f = [];
        f.push( jQuery( '#verw-ort' ) );
        f.push( jQuery( '#verw-gruppe' ) );
        f.push( jQuery( '#verw-lan' ) );
        f.push( jQuery( '#verw-karte' ) );
        f.push( jQuery( '#verw-erw' ) );
        f.push( jQuery( '#verw-kinder' ) );
        f.push( jQuery( '#verw-id' ) );
        f.push( jQuery( '#verw-schuld' ) );
        f.push( jQuery( '#verw-notiz' ) );
        f.push( jQuery( '#verw-name' ) );
        f.push( jQuery( '#verw-num' ) );
        f.push( jQuery( '#verw-adresse' ) );
        f.push( jQuery( '#verw-tel' ) );
        return f;
    }

    function getSettForm() {
        var f = [];
        f.push( jQuery( '#preisf' ) );
        f.push( jQuery( '#kartend' ) );
        return f;
    }
    
    function resetForm ( f ) {
        for ( var i = 0; i < f.length; i++ ) {
            if ( typeof( f[i].html ) == "function" ) { f[i].html( '' ); }
            if ( typeof( f[i].val ) == "function" ) { f[i].val( '' ); }
            if ( typeof( f[i].prop ) == "function" ) { f[i].prop( 'checked', false ).prop( 'disabled', true ); }
        }
    }

    function backupComplete ( data ) {
        if ( data.status == "success" ) {
            var t = "<p><i>Alles problemlos!</i></p><p>Datenbank " + data.db + " enthält alle Daten.</p>";
            alert( t, "Fertig", "Backup" );
        } else {
            var t = "<p><i style='color:red'>Fehler sind aufgetreten!</i></p><br><p>" + JSON.stringify(data) + "</p>";
            alert( t, "FEHLER", "Backup" );
            console.debug( data );
        }
    }

    function resetComplete ( data ) {
        if ( data.status == "success" ) {
            var t = "<p><i>Alles problemlos!</i></p><p>Alle Familien wurden neu durchnummeriert.</p>";
            alert( t, "Fertig", "Reset Nummern" );
        } else {
            var t = "<p><i style='color:red'>Fehler sind aufgetreten!</i></p><br><p>" + JSON.stringify(data) + "</p>";
            alert( t, "FEHLER", "Reset Nummern" );
            console.debug( data );
        }
    }


    function saveTimeout() {
        if ( typeof( selected_fam ) != "undefined" && keyboard_timeout ) {
            selected_fam.save();
            fam_timeout = undefined;
        } else if ( typeof( selected_fam ) != "undefined" && !keyboard_timeout ) {
            fam_timeout = setTimeout( saveTimeout, 500 );
        }
    }


    function schuldfieldChange() {
        jQuery( '#fam-gv, #fam-sb' ).prop( 'disabled', true );
        if ( this.value >= selected_fam.preis*3 && !selected_fam.schuld ) {
            jQuery( '#fam-szh' ).html( "Darf nächstes Mal nur noch nach Begleichen der Schulden hinein." );
        } else {
            jQuery( '#fam-szh' ).html( "" );
        }
        if ( selected_fam.schuld ) {
            if ( this.value == 0 ) {
                jQuery( '#fam-anw' ).prop( 'disabled', false );
            } else {
                jQuery( '#fam-szh' ).html( "Schulden zu hoch! Muss erst Schulden begleichen!" );
            }
        }
    }


    //Calculate price
    function preis( erwachsene = 0, kinder = 0 ) {
        var s = tdd_settings.Preis;
        if ( typeof(s) == "undefined" ) { return -1; }
        s = s.replaceAll( 'e', +erwachsene );
        s = s.replaceAll( 'k', +kinder );
        s = s.replace( /[^0-9\+\-\*\/\(\)\.><=]/g, '' );
        try {
            return eval( s );
        } catch (e) {
            console.debug( tdd_settings.Preis + ' invalide Preis-Formel (' + e + ')' );
            alert( "<p>Fehler in der Preis-Formel!<br>" + e + "</p>", "Fehler" );
        }
    }
    
    
    //Familiy-construct
    function familie ( data, index ) {
        this.data = data;
        this.saved = true;
        this.index = index;
    }
    familie.prototype.save = function ( tab = 'familie' ) {
        if ( this.saved ) {
            tab = tab.split('-');
            opt = tab.splice(1).join('-');
            tab = tab[0];
            
            var f;
            if ( tab == 'familie' ) { f = getFamForm(); }
            if ( tab == 'verwaltung' ) { f = getVerwForm(); }
            var d = this.data || {},
                nd = {},
                preist = false;
            
            //Find new familiy data
            nd.ID = d.ID;
            if ( tab == 'familie' ) {
                nd.Name = d.Name;
                nd.Erwachsene = d.Erwachsene;
                nd.Kinder = d.Kinder;
                nd.Adresse = d.Adresse;
                nd.Telefonnummer = d.Telefonnummer;
            } else if ( tab == 'verwaltung' ) {
                nd.Name = escape(f[9].val());
                nd.Erwachsene = f[4].val();
                nd.Kinder = f[5].val();
                nd.Adresse = escape(f[11].val());
                nd.Telefonnummer = escape(f[12].val());
            }
            
            if ( tab == 'familie' ) {
                nd.Ort = d.Ort;
                nd.Gruppe = d.Gruppe;
                nd.Num = d.Num;
            } else if ( tab == 'verwaltung' ) {
                try {
                    nd.Ort = tdd_orte[f[0].val()].Name;
                } catch (e) {}
                nd.Gruppe = f[1].val();
                nd.Num = f[10].val();

                // if not manually changed but moved to different group/location
                // then let mysql update num
                if (
                    nd.Num == "" || nd.Num == "0" ||
                    ( nd.Num == d.Num && ( nd.Ort != d.Ort || nd.Gruppe != d.Gruppe ) ) )
                {
                    nd.Num = "newNum('"+nd.Ort+"',"+nd.Gruppe+")";
                }
            }
            
            var s = +f[7].val();
            if ( tab == 'familie' ) {
                // schulden beglichen
                if ( f[11].prop( "checked" ) ) {
                    s = 0;
                    f[11].prop( "checked", false );
                }
                // geld vergessen
                if ( f[10].prop( "checked" ) ) {
                    s += +this.preis;
                    f[10].prop( "checked", false );
                }
            }
            nd.Schulden = s.toFixed(2);
            var pr = -(+nd.Schulden - +d.Schulden);
            
            nd.Karte = f[3].val();
            
            // anwesend
            if ( tab == 'familie' && !f[9].prop( "checked" ) ) {
                var date = d.lAnwesenheit;
            } else if ( tab == 'familie' && f[9].prop( "checked" ) ) {
                var date = formatDate( new Date() );
                if ( date != d.lAnwesenheit ) { preist = true; }
            } else if ( tab == 'verwaltung' ) {
                var date = f[2].val();
            }
            nd.lAnwesenheit = date;
            
            nd.Notizen = escape(f[8].val());
            if ( opt == 'neu' ) { nd.Notizen = ""; }
            
            //Save if new/updated
            if ( !(JSON.stringify(nd) == JSON.stringify(d)) && opt !== 'neu' ) {
                if ( typeof(d.ID) == "undefined" ) { alert( "<p>Konnte nicht speichern, ID wurde nicht gefunden!</p><p>Möglicherweise hilft es, eine andere Person zu speichern, ansonsten bitte neu laden (Familien-Anzahl nicht vergessen).</p>", "Fehler" ); console.debug( 'Error saving', this, '\nCould not find ID' ); return; }
                if ( tab == 'familie' ) {
                    clearTimeout( fam_timeout );

                    this.newdata = clone(nd);
                    this.saved = false;
                    console.debug( this, 'saving' );
                    var i = tdd_unsaved_queue.push(this) - 1;
                    
                    delete nd.ID;
                    var pr0 = +this.preis;
                    if ( preist ) { pr += pr0; }
                    
                    td = {table:"familien",id:d.ID,meta:{key:"ID",value:d.ID},set:nd,preis:pr,anw:preist};
                    update( td, ["tdd_unsaved_queue","callback"] );
                    jQuery( f ).each( function(i,e){ e.prop( 'disabled', true ); } );

                } else if ( tab == 'verwaltung' ) {
                    this.newdata = clone(nd);
                    this.saved = false;
                    console.debug( this, 'saving' );

                    delete nd.ID;

                    jQuery( '#verw-save, #verw-del' ).prop( 'disabled', true );
                    update( {table:"familien",meta:{key:"ID",value:d.ID},set:nd,preis:pr,anw:false}, savedVerw );
                    jQuery( f ).each( function(i,e){ e.prop( 'disabled', true ); } );

                    verw_neu = this;

                }
            } else if ( tab == 'verwaltung' && opt == 'neu' ) {
                this.newdata = clone(nd);
                this.saved = false;
                console.debug( this, 'saving' );

                delete nd.ID;

                jQuery( '#verw-neu' ).prop( 'disabled', true );
                insert( {table:"familien",set:nd,preis:pr,anw:false}, savedVerwN );
                jQuery( f ).each( function(i,e){ e.prop( 'disabled', true ); } );

                verw_neu = this;

            } else {
                console.debug( this, 'already saved' );
                if ( typeof( fam_for_v ) != "undefined" ) { setTimeout( jumpToV, 1 ); }

            }
        } else { console.debug( this, 'already saving' ); }
        
    };


    //Hilfe-Modal text
    preist =
        "<p>Der Preis kann über eine mathematische Formel angegeben werden.<br>" +
        "Er wird für jede Familie jedes Mal neu berechnet.</p>" +
        "<p>Um die Familienmitglieder hinein zu beziehen, kann <span class=\"code\">e</span> für die Zahl der Erwachsenen und <span class=\"code\">k</span> für die Zahl der Kinder verwendet werden.</p><br>" +
        "<p>Kommas sind mit <span class=\"code\">.</span> darzustellen!</p><br>" +
        "<p>Erlaubt sind alle Grundrechenarten (<span class=\"code\">+</span>, <span class=\"code\">-</span>, <span class=\"code\">*</span>, <span class=\"code\">/</span>), Klammern werden beachtet.</p>" +
        "<p>Ebenfalls möglich sind Vergleiche wie \"größer als\" (<span class=\"code\">&gt;</span>) oder \"kleiner als\" (<span class=\"code\">&lt;</span>), auch <span class=\"code\">==</span> (\"entspricht\"), <span class=\"code\">&lt;=</span>, <span class=\"code\">&gt;=</span>.<br>" +
        "Richtig wird als <span class=\"code\">1</span> gewertet, falsch als <span class=\"code\">0</span>.</p>" +
        "<p>Zum Beispiel: <span class=\"code\">e + k * 0.5</span> oder <span class=\"code\">(e > 0) * 2 + (k > 0)</span>." +
        "<p>Ersteres berechnet 1€ pro Erwachsener und 50cent pro Kind. Zweiteres berechnet 2€ pauschal für alle Erwachsenen und 1€ für alle Kinder (jeweils sofern vorhanden).</p>";

    kartent =
        "<p>Dieses Feld erlaubt das erstellen und bearbeiten von Kartendesigns für \"Karte drucken\".</p>" +
        "<p>Designs werden im <span class=\"code\"><a href=\"https://www.w3schools.com/js/js_json.asp\">JSON</a></span>-Format gespeichert.</p><br>" +
        "<p>Alle Designs finden sich in einem <i>Array</i>. Das heißt, das Feld beginnt mit <span class=\"code\">[</span> und endet mit <span class=\"code\">]</span>.<br>" +
        "Alle Elemente zwischen <span class=\"code\">[ ]</span> gehören zu einer Liste, alle Elemente einer Liste sind mit <span class=\"code\">,</span> getrennt.</p>" +
        "<p>In diesem Fall muss jedes dieser Elemente ein <i>Objekt</i> sein. Objekte gruppieren Unterelemente mit dem \"Key-Value\"-Prinzip. Jedes Element (\"Property\") hat einen Namen, anders als bei der Liste (numerisch zugeordnet).<br>" +
        "Objekte werden mit <span class=\"code\">{ }</span> angeschrieben. Unterelemente werden ebenfalls mit <span class=\"code\">,</span> getrennt.</p>" +
        "<p>Die Namen der Eigenschaften werden in Anführungszeichen (<span class=\"code\">\"</span> angeschrieben, dann mit Semicolon (<span class=\"code\">:</span>) vom Inhalt getrennt.<br>" +
        "Inhalt kann ein Array, ein Objekt, ein String (in Anführungszeichen) oder eine Zahl sein.</p>" +
        "<p>Ein Design-Objekt muss die Eigenschaft <span class=\"code\">name</span> haben. Die Eigenschaften <span class=\"code\">format</span> und <span class=\"code\">elements</span> sind optional. Achtung auf Groß-Kleinschreibung!</p>" +
        "<ul><li><span class=\"code\">name</span>: String (Text) zur Identifizierung</li>" +
        "<li><span class=\"code\">format</span> (opt): String, spezifiziert Kartengröße. Möglich: HxB (Höhe mal Breite in mm), Papierformate (A3, A4, ...), leer</li>" +
        "<li><span class=\"code\">elements</span> (opt): Array mit allen Elementen, die auf der Seite erwünscht sind. Elemente sind im Objekt-Format anzugeben (alle optional):" +
        "<ul><li><span class=\"code\">position</span>: String (HxB in mm), positioniert linke obere Ecke</li>" +
        "<li><span class=\"code\">html</span>: String, HTML-Code (oder reiner Text) für das Element</li>" +
        "<li><span class=\"code\">css</span>: String, CSS-Eigenschaften für style-Property</li></ul></li></ul>" +
        "<br><p>Alle Elemente könnten im <span class=\"code\">html</span> auf alle Eigenschaften der gewählten Familie zugreifen. Dazu einfach $ + Eigenschaft:" +
        "<ul><li><span class=\"code\">$ID</span>: Identifikationszahl, selbe wie Barcode</li>" +
        "<li><span class=\"code\">$Name</span></li>" +
        "<li><span class=\"code\">$Preis</span></li>" +
        "<li><span class=\"code\">$Erwachsene</span></li>" +
        "<li><span class=\"code\">$Kinder</span></li>" +
        "<li><span class=\"code\">$Ort</span></li>" +
        "<li><span class=\"code\">$Gruppe</span></li>" +
        "<li><span class=\"code\">$Schulden</span></li>" +
        "<li><span class=\"code\">$Karte</span>: Ablaufdatum</li>" +
        "<li><span class=\"code\">$lAnwesenheit</span>: Datum der letzen Anwesenheit</li>" +
        "<li><span class=\"code\">$Notizen</span></li>" +
        "<li><span class=\"code\">$img</span>: Barcode Bildelement</li>" +
        "<li><span class=\"code\">$isrc</span>: Barcode data:image/png</li></ul><br><br>" +
        "<p>Kommentare beginnen mit <span class=\"code\">//</span> und sind hier in grün dargestellt. Strings sind hier in rot.</p>" +
        "<p class=\"code\">[ <span style=\"color:green\">//Beginn der Design-Liste</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;{ <span style=\"color:green\">//Beginn Design-Objekt</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;\"<span style=\"color:red\">name</span>\": \"<span style=\"color:red\">Design1</span>\", <span style=\"color:green\">//Name festlegen (Komma!)</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;\"<span style=\"color:red\">elements</span>\": [ <span style=\"color:green\">//Beginn der Element-Liste</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ \"<span style=\"color:red\">html</span>\": \"<span style=\"color:red\">&lt;p&gt;Zeile 1&lt;p&gt;</span>\" }, <span style=\"color:green\">//Ein Element mit HTML-Inhalt (Komma!)</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ <span style=\"color:green\">//Beginn Element 2</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\"<span style=\"color:red\">html</span>\": \"<span style=\"color:red\">&lt;p&gt;Zeile 1&lt;p&gt;</span>\", <span style=\"color:green\">//HTML-Eigenschaft (Komma!)</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\"<span style=\"color:red\">css</span>\": \"<span style=\"color:red\">color:red</span>\" <span style=\"color:green\">//CSS-Eigenschaft</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;} <span style=\"color:green\">//Ende Element 2</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;&nbsp;&nbsp;] <span style=\"color:green\">//Ende Element-Liste</span></p>" +
        "<p class=\"code\">&nbsp;&nbsp;} <span style=\"color:green\">//Ende Design-Objekt</span></p>" +
        "<p class=\"code\">] <span style=\"color:green\">//Ende Design-Liste</span></p><p></span></p>";

    
    

    function clone(obj) {
        if (null == obj || "object" != typeof obj) return obj;
        var copy = obj.constructor();
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
        }
        return copy;
    }
    
    String.prototype.replaceAll = function(search, replacement) {
        var target = this;
        return target.replace(new RegExp(search, 'g'), replacement);
    };

    function num_pad(num, size) {
        var s = num+"";
        while (s.length < size) s = "0" + s;
        return s;
    }

    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    function highlightElement( el ) {
        el.addClass( 'highlight' );
        setTimeout( function() {
            el.removeClass( 'highlight' );
        }, 400 );
    }

    function alert ( text, title = "Meldung:", footer = "" ) {
        var m = modal;
        var h = m.getElementsByClassName( 'modal-head' )[0];
        var f = m.getElementsByClassName( 'modal-foot' )[0];
        var b = m.getElementsByClassName( 'modal-body' )[0];

        b.innerHTML = text;
        h.innerHTML = title;
        f.innerHTML = footer;

        modal.style.display = "block";
        document.body.style.overflow = "hidden"
    }
    
</script><?php }

function jquery() { ?><script type="text/javascript">
    /*! jQuery v3.3.1 | (c) JS Foundation and other contributors | jquery.org/license */
    !function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(e,t){"use strict";var n=[],r=e.document,i=Object.getPrototypeOf,o=n.slice,a=n.concat,s=n.push,u=n.indexOf,l={},c=l.toString,f=l.hasOwnProperty,p=f.toString,d=p.call(Object),h={},g=function e(t){return"function"==typeof t&&"number"!=typeof t.nodeType},y=function e(t){return null!=t&&t===t.window},v={type:!0,src:!0,noModule:!0};function m(e,t,n){var i,o=(t=t||r).createElement("script");if(o.text=e,n)for(i in v)n[i]&&(o[i]=n[i]);t.head.appendChild(o).parentNode.removeChild(o)}function x(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?l[c.call(e)]||"object":typeof e}var b="3.3.1",w=function(e,t){return new w.fn.init(e,t)},T=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;w.fn=w.prototype={jquery:"3.3.1",constructor:w,length:0,toArray:function(){return o.call(this)},get:function(e){return null==e?o.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=w.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return w.each(this,e)},map:function(e){return this.pushStack(w.map(this,function(t,n){return e.call(t,n,t)}))},slice:function(){return this.pushStack(o.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(n>=0&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:s,sort:n.sort,splice:n.splice},w.extend=w.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||g(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)n=a[t],a!==(r=e[t])&&(l&&r&&(w.isPlainObject(r)||(i=Array.isArray(r)))?(i?(i=!1,o=n&&Array.isArray(n)?n:[]):o=n&&w.isPlainObject(n)?n:{},a[t]=w.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},w.extend({expando:"jQuery"+("3.3.1"+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==c.call(e))&&(!(t=i(e))||"function"==typeof(n=f.call(t,"constructor")&&t.constructor)&&p.call(n)===d)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e){m(e)},each:function(e,t){var n,r=0;if(C(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},trim:function(e){return null==e?"":(e+"").replace(T,"")},makeArray:function(e,t){var n=t||[];return null!=e&&(C(Object(e))?w.merge(n,"string"==typeof e?[e]:e):s.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:u.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r,i=[],o=0,a=e.length,s=!n;o<a;o++)(r=!t(e[o],o))!==s&&i.push(e[o]);return i},map:function(e,t,n){var r,i,o=0,s=[];if(C(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&s.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&s.push(i);return a.apply([],s)},guid:1,support:h}),"function"==typeof Symbol&&(w.fn[Symbol.iterator]=n[Symbol.iterator]),w.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){l["[object "+t+"]"]=t.toLowerCase()});function C(e){var t=!!e&&"length"in e&&e.length,n=x(e);return!g(e)&&!y(e)&&("array"===n||0===t||"number"==typeof t&&t>0&&t-1 in e)}var E=function(e){var t,n,r,i,o,a,s,u,l,c,f,p,d,h,g,y,v,m,x,b="sizzle"+1*new Date,w=e.document,T=0,C=0,E=ae(),k=ae(),S=ae(),D=function(e,t){return e===t&&(f=!0),0},N={}.hasOwnProperty,A=[],j=A.pop,q=A.push,L=A.push,H=A.slice,O=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},P="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",R="(?:\\\\.|[\\w-]|[^\0-\\xa0])+",I="\\["+M+"*("+R+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+R+"))|)"+M+"*\\]",W=":("+R+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+I+")*)|.*)\\)|)",$=new RegExp(M+"+","g"),B=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),F=new RegExp("^"+M+"*,"+M+"*"),_=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),z=new RegExp("="+M+"*([^\\]'\"]*?)"+M+"*\\]","g"),X=new RegExp(W),U=new RegExp("^"+R+"$"),V={ID:new RegExp("^#("+R+")"),CLASS:new RegExp("^\\.("+R+")"),TAG:new RegExp("^("+R+"|[*])"),ATTR:new RegExp("^"+I),PSEUDO:new RegExp("^"+W),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+P+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},G=/^(?:input|select|textarea|button)$/i,Y=/^h\d$/i,Q=/^[^{]+\{\s*\[native \w/,J=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,K=/[+~]/,Z=new RegExp("\\\\([\\da-f]{1,6}"+M+"?|("+M+")|.)","ig"),ee=function(e,t,n){var r="0x"+t-65536;return r!==r||n?t:r<0?String.fromCharCode(r+65536):String.fromCharCode(r>>10|55296,1023&r|56320)},te=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ne=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},re=function(){p()},ie=me(function(e){return!0===e.disabled&&("form"in e||"label"in e)},{dir:"parentNode",next:"legend"});try{L.apply(A=H.call(w.childNodes),w.childNodes),A[w.childNodes.length].nodeType}catch(e){L={apply:A.length?function(e,t){q.apply(e,H.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function oe(e,t,r,i){var o,s,l,c,f,h,v,m=t&&t.ownerDocument,T=t?t.nodeType:9;if(r=r||[],"string"!=typeof e||!e||1!==T&&9!==T&&11!==T)return r;if(!i&&((t?t.ownerDocument||t:w)!==d&&p(t),t=t||d,g)){if(11!==T&&(f=J.exec(e)))if(o=f[1]){if(9===T){if(!(l=t.getElementById(o)))return r;if(l.id===o)return r.push(l),r}else if(m&&(l=m.getElementById(o))&&x(t,l)&&l.id===o)return r.push(l),r}else{if(f[2])return L.apply(r,t.getElementsByTagName(e)),r;if((o=f[3])&&n.getElementsByClassName&&t.getElementsByClassName)return L.apply(r,t.getElementsByClassName(o)),r}if(n.qsa&&!S[e+" "]&&(!y||!y.test(e))){if(1!==T)m=t,v=e;else if("object"!==t.nodeName.toLowerCase()){(c=t.getAttribute("id"))?c=c.replace(te,ne):t.setAttribute("id",c=b),s=(h=a(e)).length;while(s--)h[s]="#"+c+" "+ve(h[s]);v=h.join(","),m=K.test(e)&&ge(t.parentNode)||t}if(v)try{return L.apply(r,m.querySelectorAll(v)),r}catch(e){}finally{c===b&&t.removeAttribute("id")}}}return u(e.replace(B,"$1"),t,r,i)}function ae(){var e=[];function t(n,i){return e.push(n+" ")>r.cacheLength&&delete t[e.shift()],t[n+" "]=i}return t}function se(e){return e[b]=!0,e}function ue(e){var t=d.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function le(e,t){var n=e.split("|"),i=n.length;while(i--)r.attrHandle[n[i]]=t}function ce(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function fe(e){return function(t){return"input"===t.nodeName.toLowerCase()&&t.type===e}}function pe(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function de(e){return function(t){return"form"in t?t.parentNode&&!1===t.disabled?"label"in t?"label"in t.parentNode?t.parentNode.disabled===e:t.disabled===e:t.isDisabled===e||t.isDisabled!==!e&&ie(t)===e:t.disabled===e:"label"in t&&t.disabled===e}}function he(e){return se(function(t){return t=+t,se(function(n,r){var i,o=e([],n.length,t),a=o.length;while(a--)n[i=o[a]]&&(n[i]=!(r[i]=n[i]))})})}function ge(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}n=oe.support={},o=oe.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return!!t&&"HTML"!==t.nodeName},p=oe.setDocument=function(e){var t,i,a=e?e.ownerDocument||e:w;return a!==d&&9===a.nodeType&&a.documentElement?(d=a,h=d.documentElement,g=!o(d),w!==d&&(i=d.defaultView)&&i.top!==i&&(i.addEventListener?i.addEventListener("unload",re,!1):i.attachEvent&&i.attachEvent("onunload",re)),n.attributes=ue(function(e){return e.className="i",!e.getAttribute("className")}),n.getElementsByTagName=ue(function(e){return e.appendChild(d.createComment("")),!e.getElementsByTagName("*").length}),n.getElementsByClassName=Q.test(d.getElementsByClassName),n.getById=ue(function(e){return h.appendChild(e).id=b,!d.getElementsByName||!d.getElementsByName(b).length}),n.getById?(r.filter.ID=function(e){var t=e.replace(Z,ee);return function(e){return e.getAttribute("id")===t}},r.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&g){var n=t.getElementById(e);return n?[n]:[]}}):(r.filter.ID=function(e){var t=e.replace(Z,ee);return function(e){var n="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return n&&n.value===t}},r.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&g){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),r.find.TAG=n.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):n.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},r.find.CLASS=n.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&g)return t.getElementsByClassName(e)},v=[],y=[],(n.qsa=Q.test(d.querySelectorAll))&&(ue(function(e){h.appendChild(e).innerHTML="<a id='"+b+"'></a><select id='"+b+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&y.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||y.push("\\["+M+"*(?:value|"+P+")"),e.querySelectorAll("[id~="+b+"-]").length||y.push("~="),e.querySelectorAll(":checked").length||y.push(":checked"),e.querySelectorAll("a#"+b+"+*").length||y.push(".#.+[+~]")}),ue(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=d.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&y.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&y.push(":enabled",":disabled"),h.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&y.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),y.push(",.*:")})),(n.matchesSelector=Q.test(m=h.matches||h.webkitMatchesSelector||h.mozMatchesSelector||h.oMatchesSelector||h.msMatchesSelector))&&ue(function(e){n.disconnectedMatch=m.call(e,"*"),m.call(e,"[s!='']:x"),v.push("!=",W)}),y=y.length&&new RegExp(y.join("|")),v=v.length&&new RegExp(v.join("|")),t=Q.test(h.compareDocumentPosition),x=t||Q.test(h.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},D=t?function(e,t){if(e===t)return f=!0,0;var r=!e.compareDocumentPosition-!t.compareDocumentPosition;return r||(1&(r=(e.ownerDocument||e)===(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!n.sortDetached&&t.compareDocumentPosition(e)===r?e===d||e.ownerDocument===w&&x(w,e)?-1:t===d||t.ownerDocument===w&&x(w,t)?1:c?O(c,e)-O(c,t):0:4&r?-1:1)}:function(e,t){if(e===t)return f=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e===d?-1:t===d?1:i?-1:o?1:c?O(c,e)-O(c,t):0;if(i===o)return ce(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?ce(a[r],s[r]):a[r]===w?-1:s[r]===w?1:0},d):d},oe.matches=function(e,t){return oe(e,null,null,t)},oe.matchesSelector=function(e,t){if((e.ownerDocument||e)!==d&&p(e),t=t.replace(z,"='$1']"),n.matchesSelector&&g&&!S[t+" "]&&(!v||!v.test(t))&&(!y||!y.test(t)))try{var r=m.call(e,t);if(r||n.disconnectedMatch||e.document&&11!==e.document.nodeType)return r}catch(e){}return oe(t,d,null,[e]).length>0},oe.contains=function(e,t){return(e.ownerDocument||e)!==d&&p(e),x(e,t)},oe.attr=function(e,t){(e.ownerDocument||e)!==d&&p(e);var i=r.attrHandle[t.toLowerCase()],o=i&&N.call(r.attrHandle,t.toLowerCase())?i(e,t,!g):void 0;return void 0!==o?o:n.attributes||!g?e.getAttribute(t):(o=e.getAttributeNode(t))&&o.specified?o.value:null},oe.escape=function(e){return(e+"").replace(te,ne)},oe.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},oe.uniqueSort=function(e){var t,r=[],i=0,o=0;if(f=!n.detectDuplicates,c=!n.sortStable&&e.slice(0),e.sort(D),f){while(t=e[o++])t===e[o]&&(i=r.push(o));while(i--)e.splice(r[i],1)}return c=null,e},i=oe.getText=function(e){var t,n="",r=0,o=e.nodeType;if(o){if(1===o||9===o||11===o){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=i(e)}else if(3===o||4===o)return e.nodeValue}else while(t=e[r++])n+=i(t);return n},(r=oe.selectors={cacheLength:50,createPseudo:se,match:V,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(Z,ee),e[3]=(e[3]||e[4]||e[5]||"").replace(Z,ee),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||oe.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&oe.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return V.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=a(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(Z,ee).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=E[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&E(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(e,t,n){return function(r){var i=oe.attr(r,e);return null==i?"!="===t:!t||(i+="","="===t?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.slice(-n.length)===n:"~="===t?(" "+i.replace($," ")+" ").indexOf(n)>-1:"|="===t&&(i===n||i.slice(0,n.length+1)===n+"-"))}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,f,p,d,h,g=o!==a?"nextSibling":"previousSibling",y=t.parentNode,v=s&&t.nodeName.toLowerCase(),m=!u&&!s,x=!1;if(y){if(o){while(g){p=t;while(p=p[g])if(s?p.nodeName.toLowerCase()===v:1===p.nodeType)return!1;h=g="only"===e&&!h&&"nextSibling"}return!0}if(h=[a?y.firstChild:y.lastChild],a&&m){x=(d=(l=(c=(f=(p=y)[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]||[])[0]===T&&l[1])&&l[2],p=d&&y.childNodes[d];while(p=++d&&p&&p[g]||(x=d=0)||h.pop())if(1===p.nodeType&&++x&&p===t){c[e]=[T,d,x];break}}else if(m&&(x=d=(l=(c=(f=(p=t)[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]||[])[0]===T&&l[1]),!1===x)while(p=++d&&p&&p[g]||(x=d=0)||h.pop())if((s?p.nodeName.toLowerCase()===v:1===p.nodeType)&&++x&&(m&&((c=(f=p[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]=[T,x]),p===t))break;return(x-=i)===r||x%r==0&&x/r>=0}}},PSEUDO:function(e,t){var n,i=r.pseudos[e]||r.setFilters[e.toLowerCase()]||oe.error("unsupported pseudo: "+e);return i[b]?i(t):i.length>1?(n=[e,e,"",t],r.setFilters.hasOwnProperty(e.toLowerCase())?se(function(e,n){var r,o=i(e,t),a=o.length;while(a--)e[r=O(e,o[a])]=!(n[r]=o[a])}):function(e){return i(e,0,n)}):i}},pseudos:{not:se(function(e){var t=[],n=[],r=s(e.replace(B,"$1"));return r[b]?se(function(e,t,n,i){var o,a=r(e,null,i,[]),s=e.length;while(s--)(o=a[s])&&(e[s]=!(t[s]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),t[0]=null,!n.pop()}}),has:se(function(e){return function(t){return oe(e,t).length>0}}),contains:se(function(e){return e=e.replace(Z,ee),function(t){return(t.textContent||t.innerText||i(t)).indexOf(e)>-1}}),lang:se(function(e){return U.test(e||"")||oe.error("unsupported lang: "+e),e=e.replace(Z,ee).toLowerCase(),function(t){var n;do{if(n=g?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return(n=n.toLowerCase())===e||0===n.indexOf(e+"-")}while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===h},focus:function(e){return e===d.activeElement&&(!d.hasFocus||d.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:de(!1),disabled:de(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!r.pseudos.empty(e)},header:function(e){return Y.test(e.nodeName)},input:function(e){return G.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:he(function(){return[0]}),last:he(function(e,t){return[t-1]}),eq:he(function(e,t,n){return[n<0?n+t:n]}),even:he(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:he(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:he(function(e,t,n){for(var r=n<0?n+t:n;--r>=0;)e.push(r);return e}),gt:he(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=r.pseudos.eq;for(t in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})r.pseudos[t]=fe(t);for(t in{submit:!0,reset:!0})r.pseudos[t]=pe(t);function ye(){}ye.prototype=r.filters=r.pseudos,r.setFilters=new ye,a=oe.tokenize=function(e,t){var n,i,o,a,s,u,l,c=k[e+" "];if(c)return t?0:c.slice(0);s=e,u=[],l=r.preFilter;while(s){n&&!(i=F.exec(s))||(i&&(s=s.slice(i[0].length)||s),u.push(o=[])),n=!1,(i=_.exec(s))&&(n=i.shift(),o.push({value:n,type:i[0].replace(B," ")}),s=s.slice(n.length));for(a in r.filter)!(i=V[a].exec(s))||l[a]&&!(i=l[a](i))||(n=i.shift(),o.push({value:n,type:a,matches:i}),s=s.slice(n.length));if(!n)break}return t?s.length:s?oe.error(e):k(e,u).slice(0)};function ve(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function me(e,t,n){var r=t.dir,i=t.next,o=i||r,a=n&&"parentNode"===o,s=C++;return t.first?function(t,n,i){while(t=t[r])if(1===t.nodeType||a)return e(t,n,i);return!1}:function(t,n,u){var l,c,f,p=[T,s];if(u){while(t=t[r])if((1===t.nodeType||a)&&e(t,n,u))return!0}else while(t=t[r])if(1===t.nodeType||a)if(f=t[b]||(t[b]={}),c=f[t.uniqueID]||(f[t.uniqueID]={}),i&&i===t.nodeName.toLowerCase())t=t[r]||t;else{if((l=c[o])&&l[0]===T&&l[1]===s)return p[2]=l[2];if(c[o]=p,p[2]=e(t,n,u))return!0}return!1}}function xe(e){return e.length>1?function(t,n,r){var i=e.length;while(i--)if(!e[i](t,n,r))return!1;return!0}:e[0]}function be(e,t,n){for(var r=0,i=t.length;r<i;r++)oe(e,t[r],n);return n}function we(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Te(e,t,n,r,i,o){return r&&!r[b]&&(r=Te(r)),i&&!i[b]&&(i=Te(i,o)),se(function(o,a,s,u){var l,c,f,p=[],d=[],h=a.length,g=o||be(t||"*",s.nodeType?[s]:s,[]),y=!e||!o&&t?g:we(g,p,e,s,u),v=n?i||(o?e:h||r)?[]:a:y;if(n&&n(y,v,s,u),r){l=we(v,d),r(l,[],s,u),c=l.length;while(c--)(f=l[c])&&(v[d[c]]=!(y[d[c]]=f))}if(o){if(i||e){if(i){l=[],c=v.length;while(c--)(f=v[c])&&l.push(y[c]=f);i(null,v=[],l,u)}c=v.length;while(c--)(f=v[c])&&(l=i?O(o,f):p[c])>-1&&(o[l]=!(a[l]=f))}}else v=we(v===a?v.splice(h,v.length):v),i?i(null,a,v,u):L.apply(a,v)})}function Ce(e){for(var t,n,i,o=e.length,a=r.relative[e[0].type],s=a||r.relative[" "],u=a?1:0,c=me(function(e){return e===t},s,!0),f=me(function(e){return O(t,e)>-1},s,!0),p=[function(e,n,r){var i=!a&&(r||n!==l)||((t=n).nodeType?c(e,n,r):f(e,n,r));return t=null,i}];u<o;u++)if(n=r.relative[e[u].type])p=[me(xe(p),n)];else{if((n=r.filter[e[u].type].apply(null,e[u].matches))[b]){for(i=++u;i<o;i++)if(r.relative[e[i].type])break;return Te(u>1&&xe(p),u>1&&ve(e.slice(0,u-1).concat({value:" "===e[u-2].type?"*":""})).replace(B,"$1"),n,u<i&&Ce(e.slice(u,i)),i<o&&Ce(e=e.slice(i)),i<o&&ve(e))}p.push(n)}return xe(p)}function Ee(e,t){var n=t.length>0,i=e.length>0,o=function(o,a,s,u,c){var f,h,y,v=0,m="0",x=o&&[],b=[],w=l,C=o||i&&r.find.TAG("*",c),E=T+=null==w?1:Math.random()||.1,k=C.length;for(c&&(l=a===d||a||c);m!==k&&null!=(f=C[m]);m++){if(i&&f){h=0,a||f.ownerDocument===d||(p(f),s=!g);while(y=e[h++])if(y(f,a||d,s)){u.push(f);break}c&&(T=E)}n&&((f=!y&&f)&&v--,o&&x.push(f))}if(v+=m,n&&m!==v){h=0;while(y=t[h++])y(x,b,a,s);if(o){if(v>0)while(m--)x[m]||b[m]||(b[m]=j.call(u));b=we(b)}L.apply(u,b),c&&!o&&b.length>0&&v+t.length>1&&oe.uniqueSort(u)}return c&&(T=E,l=w),x};return n?se(o):o}return s=oe.compile=function(e,t){var n,r=[],i=[],o=S[e+" "];if(!o){t||(t=a(e)),n=t.length;while(n--)(o=Ce(t[n]))[b]?r.push(o):i.push(o);(o=S(e,Ee(i,r))).selector=e}return o},u=oe.select=function(e,t,n,i){var o,u,l,c,f,p="function"==typeof e&&e,d=!i&&a(e=p.selector||e);if(n=n||[],1===d.length){if((u=d[0]=d[0].slice(0)).length>2&&"ID"===(l=u[0]).type&&9===t.nodeType&&g&&r.relative[u[1].type]){if(!(t=(r.find.ID(l.matches[0].replace(Z,ee),t)||[])[0]))return n;p&&(t=t.parentNode),e=e.slice(u.shift().value.length)}o=V.needsContext.test(e)?0:u.length;while(o--){if(l=u[o],r.relative[c=l.type])break;if((f=r.find[c])&&(i=f(l.matches[0].replace(Z,ee),K.test(u[0].type)&&ge(t.parentNode)||t))){if(u.splice(o,1),!(e=i.length&&ve(u)))return L.apply(n,i),n;break}}}return(p||s(e,d))(i,t,!g,n,!t||K.test(e)&&ge(t.parentNode)||t),n},n.sortStable=b.split("").sort(D).join("")===b,n.detectDuplicates=!!f,p(),n.sortDetached=ue(function(e){return 1&e.compareDocumentPosition(d.createElement("fieldset"))}),ue(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||le("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),n.attributes&&ue(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||le("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ue(function(e){return null==e.getAttribute("disabled")})||le(P,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),oe}(e);w.find=E,w.expr=E.selectors,w.expr[":"]=w.expr.pseudos,w.uniqueSort=w.unique=E.uniqueSort,w.text=E.getText,w.isXMLDoc=E.isXML,w.contains=E.contains,w.escapeSelector=E.escape;var k=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&w(e).is(n))break;r.push(e)}return r},S=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},D=w.expr.match.needsContext;function N(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var A=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function j(e,t,n){return g(t)?w.grep(e,function(e,r){return!!t.call(e,r,e)!==n}):t.nodeType?w.grep(e,function(e){return e===t!==n}):"string"!=typeof t?w.grep(e,function(e){return u.call(t,e)>-1!==n}):w.filter(t,e,n)}w.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?w.find.matchesSelector(r,e)?[r]:[]:w.find.matches(e,w.grep(t,function(e){return 1===e.nodeType}))},w.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(w(e).filter(function(){for(t=0;t<r;t++)if(w.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)w.find(e,i[t],n);return r>1?w.uniqueSort(n):n},filter:function(e){return this.pushStack(j(this,e||[],!1))},not:function(e){return this.pushStack(j(this,e||[],!0))},is:function(e){return!!j(this,"string"==typeof e&&D.test(e)?w(e):e||[],!1).length}});var q,L=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(w.fn.init=function(e,t,n){var i,o;if(!e)return this;if(n=n||q,"string"==typeof e){if(!(i="<"===e[0]&&">"===e[e.length-1]&&e.length>=3?[null,e,null]:L.exec(e))||!i[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(i[1]){if(t=t instanceof w?t[0]:t,w.merge(this,w.parseHTML(i[1],t&&t.nodeType?t.ownerDocument||t:r,!0)),A.test(i[1])&&w.isPlainObject(t))for(i in t)g(this[i])?this[i](t[i]):this.attr(i,t[i]);return this}return(o=r.getElementById(i[2]))&&(this[0]=o,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):g(e)?void 0!==n.ready?n.ready(e):e(w):w.makeArray(e,this)}).prototype=w.fn,q=w(r);var H=/^(?:parents|prev(?:Until|All))/,O={children:!0,contents:!0,next:!0,prev:!0};w.fn.extend({has:function(e){var t=w(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(w.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&w(e);if(!D.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?a.index(n)>-1:1===n.nodeType&&w.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(o.length>1?w.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?u.call(w(e),this[0]):u.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(w.uniqueSort(w.merge(this.get(),w(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}});function P(e,t){while((e=e[t])&&1!==e.nodeType);return e}w.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return k(e,"parentNode")},parentsUntil:function(e,t,n){return k(e,"parentNode",n)},next:function(e){return P(e,"nextSibling")},prev:function(e){return P(e,"previousSibling")},nextAll:function(e){return k(e,"nextSibling")},prevAll:function(e){return k(e,"previousSibling")},nextUntil:function(e,t,n){return k(e,"nextSibling",n)},prevUntil:function(e,t,n){return k(e,"previousSibling",n)},siblings:function(e){return S((e.parentNode||{}).firstChild,e)},children:function(e){return S(e.firstChild)},contents:function(e){return N(e,"iframe")?e.contentDocument:(N(e,"template")&&(e=e.content||e),w.merge([],e.childNodes))}},function(e,t){w.fn[e]=function(n,r){var i=w.map(this,t,n);return"Until"!==e.slice(-5)&&(r=n),r&&"string"==typeof r&&(i=w.filter(r,i)),this.length>1&&(O[e]||w.uniqueSort(i),H.test(e)&&i.reverse()),this.pushStack(i)}});var M=/[^\x20\t\r\n\f]+/g;function R(e){var t={};return w.each(e.match(M)||[],function(e,n){t[n]=!0}),t}w.Callbacks=function(e){e="string"==typeof e?R(e):w.extend({},e);var t,n,r,i,o=[],a=[],s=-1,u=function(){for(i=i||e.once,r=t=!0;a.length;s=-1){n=a.shift();while(++s<o.length)!1===o[s].apply(n[0],n[1])&&e.stopOnFalse&&(s=o.length,n=!1)}e.memory||(n=!1),t=!1,i&&(o=n?[]:"")},l={add:function(){return o&&(n&&!t&&(s=o.length-1,a.push(n)),function t(n){w.each(n,function(n,r){g(r)?e.unique&&l.has(r)||o.push(r):r&&r.length&&"string"!==x(r)&&t(r)})}(arguments),n&&!t&&u()),this},remove:function(){return w.each(arguments,function(e,t){var n;while((n=w.inArray(t,o,n))>-1)o.splice(n,1),n<=s&&s--}),this},has:function(e){return e?w.inArray(e,o)>-1:o.length>0},empty:function(){return o&&(o=[]),this},disable:function(){return i=a=[],o=n="",this},disabled:function(){return!o},lock:function(){return i=a=[],n||t||(o=n=""),this},locked:function(){return!!i},fireWith:function(e,n){return i||(n=[e,(n=n||[]).slice?n.slice():n],a.push(n),t||u()),this},fire:function(){return l.fireWith(this,arguments),this},fired:function(){return!!r}};return l};function I(e){return e}function W(e){throw e}function $(e,t,n,r){var i;try{e&&g(i=e.promise)?i.call(e).done(t).fail(n):e&&g(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}w.extend({Deferred:function(t){var n=[["notify","progress",w.Callbacks("memory"),w.Callbacks("memory"),2],["resolve","done",w.Callbacks("once memory"),w.Callbacks("once memory"),0,"resolved"],["reject","fail",w.Callbacks("once memory"),w.Callbacks("once memory"),1,"rejected"]],r="pending",i={state:function(){return r},always:function(){return o.done(arguments).fail(arguments),this},"catch":function(e){return i.then(null,e)},pipe:function(){var e=arguments;return w.Deferred(function(t){w.each(n,function(n,r){var i=g(e[r[4]])&&e[r[4]];o[r[1]](function(){var e=i&&i.apply(this,arguments);e&&g(e.promise)?e.promise().progress(t.notify).done(t.resolve).fail(t.reject):t[r[0]+"With"](this,i?[e]:arguments)})}),e=null}).promise()},then:function(t,r,i){var o=0;function a(t,n,r,i){return function(){var s=this,u=arguments,l=function(){var e,l;if(!(t<o)){if((e=r.apply(s,u))===n.promise())throw new TypeError("Thenable self-resolution");l=e&&("object"==typeof e||"function"==typeof e)&&e.then,g(l)?i?l.call(e,a(o,n,I,i),a(o,n,W,i)):(o++,l.call(e,a(o,n,I,i),a(o,n,W,i),a(o,n,I,n.notifyWith))):(r!==I&&(s=void 0,u=[e]),(i||n.resolveWith)(s,u))}},c=i?l:function(){try{l()}catch(e){w.Deferred.exceptionHook&&w.Deferred.exceptionHook(e,c.stackTrace),t+1>=o&&(r!==W&&(s=void 0,u=[e]),n.rejectWith(s,u))}};t?c():(w.Deferred.getStackHook&&(c.stackTrace=w.Deferred.getStackHook()),e.setTimeout(c))}}return w.Deferred(function(e){n[0][3].add(a(0,e,g(i)?i:I,e.notifyWith)),n[1][3].add(a(0,e,g(t)?t:I)),n[2][3].add(a(0,e,g(r)?r:W))}).promise()},promise:function(e){return null!=e?w.extend(e,i):i}},o={};return w.each(n,function(e,t){var a=t[2],s=t[5];i[t[1]]=a.add,s&&a.add(function(){r=s},n[3-e][2].disable,n[3-e][3].disable,n[0][2].lock,n[0][3].lock),a.add(t[3].fire),o[t[0]]=function(){return o[t[0]+"With"](this===o?void 0:this,arguments),this},o[t[0]+"With"]=a.fireWith}),i.promise(o),t&&t.call(o,o),o},when:function(e){var t=arguments.length,n=t,r=Array(n),i=o.call(arguments),a=w.Deferred(),s=function(e){return function(n){r[e]=this,i[e]=arguments.length>1?o.call(arguments):n,--t||a.resolveWith(r,i)}};if(t<=1&&($(e,a.done(s(n)).resolve,a.reject,!t),"pending"===a.state()||g(i[n]&&i[n].then)))return a.then();while(n--)$(i[n],s(n),a.reject);return a.promise()}});var B=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;w.Deferred.exceptionHook=function(t,n){e.console&&e.console.warn&&t&&B.test(t.name)&&e.console.warn("jQuery.Deferred exception: "+t.message,t.stack,n)},w.readyException=function(t){e.setTimeout(function(){throw t})};var F=w.Deferred();w.fn.ready=function(e){return F.then(e)["catch"](function(e){w.readyException(e)}),this},w.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--w.readyWait:w.isReady)||(w.isReady=!0,!0!==e&&--w.readyWait>0||F.resolveWith(r,[w]))}}),w.ready.then=F.then;function _(){r.removeEventListener("DOMContentLoaded",_),e.removeEventListener("load",_),w.ready()}"complete"===r.readyState||"loading"!==r.readyState&&!r.documentElement.doScroll?e.setTimeout(w.ready):(r.addEventListener("DOMContentLoaded",_),e.addEventListener("load",_));var z=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===x(n)){i=!0;for(s in n)z(e,t,s,n[s],!0,o,a)}else if(void 0!==r&&(i=!0,g(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(w(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},X=/^-ms-/,U=/-([a-z])/g;function V(e,t){return t.toUpperCase()}function G(e){return e.replace(X,"ms-").replace(U,V)}var Y=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function Q(){this.expando=w.expando+Q.uid++}Q.uid=1,Q.prototype={cache:function(e){var t=e[this.expando];return t||(t={},Y(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[G(t)]=n;else for(r in t)i[G(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][G(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(G):(t=G(t))in r?[t]:t.match(M)||[]).length;while(n--)delete r[t[n]]}(void 0===t||w.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!w.isEmptyObject(t)}};var J=new Q,K=new Q,Z=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,ee=/[A-Z]/g;function te(e){return"true"===e||"false"!==e&&("null"===e?null:e===+e+""?+e:Z.test(e)?JSON.parse(e):e)}function ne(e,t,n){var r;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(ee,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n=te(n)}catch(e){}K.set(e,t,n)}else n=void 0;return n}w.extend({hasData:function(e){return K.hasData(e)||J.hasData(e)},data:function(e,t,n){return K.access(e,t,n)},removeData:function(e,t){K.remove(e,t)},_data:function(e,t,n){return J.access(e,t,n)},_removeData:function(e,t){J.remove(e,t)}}),w.fn.extend({data:function(e,t){var n,r,i,o=this[0],a=o&&o.attributes;if(void 0===e){if(this.length&&(i=K.get(o),1===o.nodeType&&!J.get(o,"hasDataAttrs"))){n=a.length;while(n--)a[n]&&0===(r=a[n].name).indexOf("data-")&&(r=G(r.slice(5)),ne(o,r,i[r]));J.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof e?this.each(function(){K.set(this,e)}):z(this,function(t){var n;if(o&&void 0===t){if(void 0!==(n=K.get(o,e)))return n;if(void 0!==(n=ne(o,e)))return n}else this.each(function(){K.set(this,e,t)})},null,t,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){K.remove(this,e)})}}),w.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=J.get(e,t),n&&(!r||Array.isArray(n)?r=J.access(e,t,w.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=w.queue(e,t),r=n.length,i=n.shift(),o=w._queueHooks(e,t),a=function(){w.dequeue(e,t)};"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,a,o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return J.get(e,n)||J.access(e,n,{empty:w.Callbacks("once memory").add(function(){J.remove(e,[t+"queue",n])})})}}),w.fn.extend({queue:function(e,t){var n=2;return"string"!=typeof e&&(t=e,e="fx",n--),arguments.length<n?w.queue(this[0],e):void 0===t?this:this.each(function(){var n=w.queue(this,e,t);w._queueHooks(this,e),"fx"===e&&"inprogress"!==n[0]&&w.dequeue(this,e)})},dequeue:function(e){return this.each(function(){w.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=w.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=J.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var re=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,ie=new RegExp("^(?:([+-])=|)("+re+")([a-z%]*)$","i"),oe=["Top","Right","Bottom","Left"],ae=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&w.contains(e.ownerDocument,e)&&"none"===w.css(e,"display")},se=function(e,t,n,r){var i,o,a={};for(o in t)a[o]=e.style[o],e.style[o]=t[o];i=n.apply(e,r||[]);for(o in t)e.style[o]=a[o];return i};function ue(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return w.css(e,t,"")},u=s(),l=n&&n[3]||(w.cssNumber[t]?"":"px"),c=(w.cssNumber[t]||"px"!==l&&+u)&&ie.exec(w.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)w.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,w.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var le={};function ce(e){var t,n=e.ownerDocument,r=e.nodeName,i=le[r];return i||(t=n.body.appendChild(n.createElement(r)),i=w.css(t,"display"),t.parentNode.removeChild(t),"none"===i&&(i="block"),le[r]=i,i)}function fe(e,t){for(var n,r,i=[],o=0,a=e.length;o<a;o++)(r=e[o]).style&&(n=r.style.display,t?("none"===n&&(i[o]=J.get(r,"display")||null,i[o]||(r.style.display="")),""===r.style.display&&ae(r)&&(i[o]=ce(r))):"none"!==n&&(i[o]="none",J.set(r,"display",n)));for(o=0;o<a;o++)null!=i[o]&&(e[o].style.display=i[o]);return e}w.fn.extend({show:function(){return fe(this,!0)},hide:function(){return fe(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ae(this)?w(this).show():w(this).hide()})}});var pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]+)/i,he=/^$|^module$|\/(?:java|ecma)script/i,ge={option:[1,"<select multiple='multiple'>","</select>"],thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};ge.optgroup=ge.option,ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td;function ye(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&N(e,t)?w.merge([e],n):n}function ve(e,t){for(var n=0,r=e.length;n<r;n++)J.set(e[n],"globalEval",!t||J.get(t[n],"globalEval"))}var me=/<|&#?\w+;/;function xe(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===x(o))w.merge(p,o.nodeType?[o]:o);else if(me.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+w.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;w.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&w.inArray(o,r)>-1)i&&i.push(o);else if(l=w.contains(o.ownerDocument,o),a=ye(f.appendChild(o),"script"),l&&ve(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}!function(){var e=r.createDocumentFragment().appendChild(r.createElement("div")),t=r.createElement("input");t.setAttribute("type","radio"),t.setAttribute("checked","checked"),t.setAttribute("name","t"),e.appendChild(t),h.checkClone=e.cloneNode(!0).cloneNode(!0).lastChild.checked,e.innerHTML="<textarea>x</textarea>",h.noCloneChecked=!!e.cloneNode(!0).lastChild.defaultValue}();var be=r.documentElement,we=/^key/,Te=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,Ce=/^([^.]*)(?:\.(.+)|)/;function Ee(){return!0}function ke(){return!1}function Se(){try{return r.activeElement}catch(e){}}function De(e,t,n,r,i,o){var a,s;if("object"==typeof t){"string"!=typeof n&&(r=r||n,n=void 0);for(s in t)De(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=ke;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return w().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=w.guid++)),e.each(function(){w.event.add(this,t,i,r,n)})}w.event={global:{},add:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=J.get(e);if(y){n.handler&&(n=(o=n).handler,i=o.selector),i&&w.find.matchesSelector(be,i),n.guid||(n.guid=w.guid++),(u=y.events)||(u=y.events={}),(a=y.handle)||(a=y.handle=function(t){return"undefined"!=typeof w&&w.event.triggered!==t.type?w.event.dispatch.apply(e,arguments):void 0}),l=(t=(t||"").match(M)||[""]).length;while(l--)d=g=(s=Ce.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=w.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=w.event.special[d]||{},c=w.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&w.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(e,r,h,a)||e.addEventListener&&e.addEventListener(d,a)),f.add&&(f.add.call(e,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),w.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=J.hasData(e)&&J.get(e);if(y&&(u=y.events)){l=(t=(t||"").match(M)||[""]).length;while(l--)if(s=Ce.exec(t[l])||[],d=g=s[1],h=(s[2]||"").split(".").sort(),d){f=w.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,y.handle)||w.removeEvent(e,d,y.handle),delete u[d])}else for(d in u)w.event.remove(e,d+t[l],n,r,!0);w.isEmptyObject(u)&&J.remove(e,"handle events")}},dispatch:function(e){var t=w.event.fix(e),n,r,i,o,a,s,u=new Array(arguments.length),l=(J.get(this,"events")||{})[t.type]||[],c=w.event.special[t.type]||{};for(u[0]=t,n=1;n<arguments.length;n++)u[n]=arguments[n];if(t.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,t)){s=w.event.handlers.call(this,t,l),n=0;while((o=s[n++])&&!t.isPropagationStopped()){t.currentTarget=o.elem,r=0;while((a=o.handlers[r++])&&!t.isImmediatePropagationStopped())t.rnamespace&&!t.rnamespace.test(a.namespace)||(t.handleObj=a,t.data=a.data,void 0!==(i=((w.event.special[a.origType]||{}).handle||a.handler).apply(o.elem,u))&&!1===(t.result=i)&&(t.preventDefault(),t.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,t),t.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&e.button>=1))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?w(i,this).index(l)>-1:w.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(e,t){Object.defineProperty(w.Event.prototype,e,{enumerable:!0,configurable:!0,get:g(t)?function(){if(this.originalEvent)return t(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[e]},set:function(t){Object.defineProperty(this,e,{enumerable:!0,configurable:!0,writable:!0,value:t})}})},fix:function(e){return e[w.expando]?e:new w.Event(e)},special:{load:{noBubble:!0},focus:{trigger:function(){if(this!==Se()&&this.focus)return this.focus(),!1},delegateType:"focusin"},blur:{trigger:function(){if(this===Se()&&this.blur)return this.blur(),!1},delegateType:"focusout"},click:{trigger:function(){if("checkbox"===this.type&&this.click&&N(this,"input"))return this.click(),!1},_default:function(e){return N(e.target,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},w.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},w.Event=function(e,t){if(!(this instanceof w.Event))return new w.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?Ee:ke,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&w.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[w.expando]=!0},w.Event.prototype={constructor:w.Event,isDefaultPrevented:ke,isPropagationStopped:ke,isImmediatePropagationStopped:ke,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Ee,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Ee,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Ee,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},w.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:function(e){var t=e.button;return null==e.which&&we.test(e.type)?null!=e.charCode?e.charCode:e.keyCode:!e.which&&void 0!==t&&Te.test(e.type)?1&t?1:2&t?3:4&t?2:0:e.which}},w.event.addProp),w.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,t){w.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,o=e.handleObj;return i&&(i===r||w.contains(r,i))||(e.type=o.origType,n=o.handler.apply(this,arguments),e.type=t),n}}}),w.fn.extend({on:function(e,t,n,r){return De(this,e,t,n,r)},one:function(e,t,n,r){return De(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,w(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=ke),this.each(function(){w.event.remove(this,e,n,t)})}});var Ne=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,Ae=/<script|<style|<link/i,je=/checked\s*(?:[^=]|=\s*.checked.)/i,qe=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function Le(e,t){return N(e,"table")&&N(11!==t.nodeType?t:t.firstChild,"tr")?w(e).children("tbody")[0]||e:e}function He(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function Oe(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Pe(e,t){var n,r,i,o,a,s,u,l;if(1===t.nodeType){if(J.hasData(e)&&(o=J.access(e),a=J.set(t,o),l=o.events)){delete a.handle,a.events={};for(i in l)for(n=0,r=l[i].length;n<r;n++)w.event.add(t,i,l[i][n])}K.hasData(e)&&(s=K.access(e),u=w.extend({},s),K.set(t,u))}}function Me(e,t){var n=t.nodeName.toLowerCase();"input"===n&&pe.test(e.type)?t.checked=e.checked:"input"!==n&&"textarea"!==n||(t.defaultValue=e.defaultValue)}function Re(e,t,n,r){t=a.apply([],t);var i,o,s,u,l,c,f=0,p=e.length,d=p-1,y=t[0],v=g(y);if(v||p>1&&"string"==typeof y&&!h.checkClone&&je.test(y))return e.each(function(i){var o=e.eq(i);v&&(t[0]=y.call(this,i,o.html())),Re(o,t,n,r)});if(p&&(i=xe(t,e[0].ownerDocument,!1,e,r),o=i.firstChild,1===i.childNodes.length&&(i=o),o||r)){for(u=(s=w.map(ye(i,"script"),He)).length;f<p;f++)l=i,f!==d&&(l=w.clone(l,!0,!0),u&&w.merge(s,ye(l,"script"))),n.call(e[f],l,f);if(u)for(c=s[s.length-1].ownerDocument,w.map(s,Oe),f=0;f<u;f++)l=s[f],he.test(l.type||"")&&!J.access(l,"globalEval")&&w.contains(c,l)&&(l.src&&"module"!==(l.type||"").toLowerCase()?w._evalUrl&&w._evalUrl(l.src):m(l.textContent.replace(qe,""),c,l))}return e}function Ie(e,t,n){for(var r,i=t?w.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||w.cleanData(ye(r)),r.parentNode&&(n&&w.contains(r.ownerDocument,r)&&ve(ye(r,"script")),r.parentNode.removeChild(r));return e}w.extend({htmlPrefilter:function(e){return e.replace(Ne,"<$1></$2>")},clone:function(e,t,n){var r,i,o,a,s=e.cloneNode(!0),u=w.contains(e.ownerDocument,e);if(!(h.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||w.isXMLDoc(e)))for(a=ye(s),r=0,i=(o=ye(e)).length;r<i;r++)Me(o[r],a[r]);if(t)if(n)for(o=o||ye(e),a=a||ye(s),r=0,i=o.length;r<i;r++)Pe(o[r],a[r]);else Pe(e,s);return(a=ye(s,"script")).length>0&&ve(a,!u&&ye(e,"script")),s},cleanData:function(e){for(var t,n,r,i=w.event.special,o=0;void 0!==(n=e[o]);o++)if(Y(n)){if(t=n[J.expando]){if(t.events)for(r in t.events)i[r]?w.event.remove(n,r):w.removeEvent(n,r,t.handle);n[J.expando]=void 0}n[K.expando]&&(n[K.expando]=void 0)}}}),w.fn.extend({detach:function(e){return Ie(this,e,!0)},remove:function(e){return Ie(this,e)},text:function(e){return z(this,function(e){return void 0===e?w.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return Re(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||Le(this,e).appendChild(e)})},prepend:function(){return Re(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=Le(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return Re(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return Re(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(w.cleanData(ye(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return w.clone(this,e,t)})},html:function(e){return z(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!Ae.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=w.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(w.cleanData(ye(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var e=[];return Re(this,arguments,function(t){var n=this.parentNode;w.inArray(this,e)<0&&(w.cleanData(ye(this)),n&&n.replaceChild(t,this))},e)}}),w.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){w.fn[e]=function(e){for(var n,r=[],i=w(e),o=i.length-1,a=0;a<=o;a++)n=a===o?this:this.clone(!0),w(i[a])[t](n),s.apply(r,n.get());return this.pushStack(r)}});var We=new RegExp("^("+re+")(?!px)[a-z%]+$","i"),$e=function(t){var n=t.ownerDocument.defaultView;return n&&n.opener||(n=e),n.getComputedStyle(t)},Be=new RegExp(oe.join("|"),"i");!function(){function t(){if(c){l.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",c.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",be.appendChild(l).appendChild(c);var t=e.getComputedStyle(c);i="1%"!==t.top,u=12===n(t.marginLeft),c.style.right="60%",s=36===n(t.right),o=36===n(t.width),c.style.position="absolute",a=36===c.offsetWidth||"absolute",be.removeChild(l),c=null}}function n(e){return Math.round(parseFloat(e))}var i,o,a,s,u,l=r.createElement("div"),c=r.createElement("div");c.style&&(c.style.backgroundClip="content-box",c.cloneNode(!0).style.backgroundClip="",h.clearCloneStyle="content-box"===c.style.backgroundClip,w.extend(h,{boxSizingReliable:function(){return t(),o},pixelBoxStyles:function(){return t(),s},pixelPosition:function(){return t(),i},reliableMarginLeft:function(){return t(),u},scrollboxSize:function(){return t(),a}}))}();function Fe(e,t,n){var r,i,o,a,s=e.style;return(n=n||$e(e))&&(""!==(a=n.getPropertyValue(t)||n[t])||w.contains(e.ownerDocument,e)||(a=w.style(e,t)),!h.pixelBoxStyles()&&We.test(a)&&Be.test(t)&&(r=s.width,i=s.minWidth,o=s.maxWidth,s.minWidth=s.maxWidth=s.width=a,a=n.width,s.width=r,s.minWidth=i,s.maxWidth=o)),void 0!==a?a+"":a}function _e(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}var ze=/^(none|table(?!-c[ea]).+)/,Xe=/^--/,Ue={position:"absolute",visibility:"hidden",display:"block"},Ve={letterSpacing:"0",fontWeight:"400"},Ge=["Webkit","Moz","ms"],Ye=r.createElement("div").style;function Qe(e){if(e in Ye)return e;var t=e[0].toUpperCase()+e.slice(1),n=Ge.length;while(n--)if((e=Ge[n]+t)in Ye)return e}function Je(e){var t=w.cssProps[e];return t||(t=w.cssProps[e]=Qe(e)||e),t}function Ke(e,t,n){var r=ie.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function Ze(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=w.css(e,n+oe[a],!0,i)),r?("content"===n&&(u-=w.css(e,"padding"+oe[a],!0,i)),"margin"!==n&&(u-=w.css(e,"border"+oe[a]+"Width",!0,i))):(u+=w.css(e,"padding"+oe[a],!0,i),"padding"!==n?u+=w.css(e,"border"+oe[a]+"Width",!0,i):s+=w.css(e,"border"+oe[a]+"Width",!0,i));return!r&&o>=0&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))),u}function et(e,t,n){var r=$e(e),i=Fe(e,t,r),o="border-box"===w.css(e,"boxSizing",!1,r),a=o;if(We.test(i)){if(!n)return i;i="auto"}return a=a&&(h.boxSizingReliable()||i===e.style[t]),("auto"===i||!parseFloat(i)&&"inline"===w.css(e,"display",!1,r))&&(i=e["offset"+t[0].toUpperCase()+t.slice(1)],a=!0),(i=parseFloat(i)||0)+Ze(e,t,n||(o?"border":"content"),a,r,i)+"px"}w.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Fe(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=G(t),u=Xe.test(t),l=e.style;if(u||(t=Je(s)),a=w.cssHooks[t]||w.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"==(o=typeof n)&&(i=ie.exec(n))&&i[1]&&(n=ue(e,t,i),o="number"),null!=n&&n===n&&("number"===o&&(n+=i&&i[3]||(w.cssNumber[s]?"":"px")),h.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=G(t);return Xe.test(t)||(t=Je(s)),(a=w.cssHooks[t]||w.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=Fe(e,t,r)),"normal"===i&&t in Ve&&(i=Ve[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),w.each(["height","width"],function(e,t){w.cssHooks[t]={get:function(e,n,r){if(n)return!ze.test(w.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?et(e,t,r):se(e,Ue,function(){return et(e,t,r)})},set:function(e,n,r){var i,o=$e(e),a="border-box"===w.css(e,"boxSizing",!1,o),s=r&&Ze(e,t,r,a,o);return a&&h.scrollboxSize()===o.position&&(s-=Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-parseFloat(o[t])-Ze(e,t,"border",!1,o)-.5)),s&&(i=ie.exec(n))&&"px"!==(i[3]||"px")&&(e.style[t]=n,n=w.css(e,t)),Ke(e,n,s)}}}),w.cssHooks.marginLeft=_e(h.reliableMarginLeft,function(e,t){if(t)return(parseFloat(Fe(e,"marginLeft"))||e.getBoundingClientRect().left-se(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),w.each({margin:"",padding:"",border:"Width"},function(e,t){w.cssHooks[e+t]={expand:function(n){for(var r=0,i={},o="string"==typeof n?n.split(" "):[n];r<4;r++)i[e+oe[r]+t]=o[r]||o[r-2]||o[0];return i}},"margin"!==e&&(w.cssHooks[e+t].set=Ke)}),w.fn.extend({css:function(e,t){return z(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=$e(e),i=t.length;a<i;a++)o[t[a]]=w.css(e,t[a],!1,r);return o}return void 0!==n?w.style(e,t,n):w.css(e,t)},e,t,arguments.length>1)}});function tt(e,t,n,r,i){return new tt.prototype.init(e,t,n,r,i)}w.Tween=tt,tt.prototype={constructor:tt,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||w.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(w.cssNumber[n]?"":"px")},cur:function(){var e=tt.propHooks[this.prop];return e&&e.get?e.get(this):tt.propHooks._default.get(this)},run:function(e){var t,n=tt.propHooks[this.prop];return this.options.duration?this.pos=t=w.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):tt.propHooks._default.set(this),this}},tt.prototype.init.prototype=tt.prototype,tt.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=w.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){w.fx.step[e.prop]?w.fx.step[e.prop](e):1!==e.elem.nodeType||null==e.elem.style[w.cssProps[e.prop]]&&!w.cssHooks[e.prop]?e.elem[e.prop]=e.now:w.style(e.elem,e.prop,e.now+e.unit)}}},tt.propHooks.scrollTop=tt.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},w.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},w.fx=tt.prototype.init,w.fx.step={};var nt,rt,it=/^(?:toggle|show|hide)$/,ot=/queueHooks$/;function at(){rt&&(!1===r.hidden&&e.requestAnimationFrame?e.requestAnimationFrame(at):e.setTimeout(at,w.fx.interval),w.fx.tick())}function st(){return e.setTimeout(function(){nt=void 0}),nt=Date.now()}function ut(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=oe[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function lt(e,t,n){for(var r,i=(pt.tweeners[t]||[]).concat(pt.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function ct(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ae(e),y=J.get(e,"fxshow");n.queue||(null==(a=w._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,w.queue(e,"fx").length||a.empty.fire()})}));for(r in t)if(i=t[r],it.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!y||void 0===y[r])continue;g=!0}d[r]=y&&y[r]||w.style(e,r)}if((u=!w.isEmptyObject(t))||!w.isEmptyObject(d)){f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=y&&y.display)&&(l=J.get(e,"display")),"none"===(c=w.css(e,"display"))&&(l?c=l:(fe([e],!0),l=e.style.display||l,c=w.css(e,"display"),fe([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===w.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1;for(r in d)u||(y?"hidden"in y&&(g=y.hidden):y=J.access(e,"fxshow",{display:l}),o&&(y.hidden=!g),g&&fe([e],!0),p.done(function(){g||fe([e]),J.remove(e,"fxshow");for(r in d)w.style(e,r,d[r])})),u=lt(g?y[r]:0,r,p),r in y||(y[r]=u.start,g&&(u.end=u.start,u.start=0))}}function ft(e,t){var n,r,i,o,a;for(n in e)if(r=G(n),i=t[r],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=w.cssHooks[r])&&"expand"in a){o=a.expand(o),delete e[r];for(n in o)n in e||(e[n]=o[n],t[n]=i)}else t[r]=i}function pt(e,t,n){var r,i,o=0,a=pt.prefilters.length,s=w.Deferred().always(function(){delete u.elem}),u=function(){if(i)return!1;for(var t=nt||st(),n=Math.max(0,l.startTime+l.duration-t),r=1-(n/l.duration||0),o=0,a=l.tweens.length;o<a;o++)l.tweens[o].run(r);return s.notifyWith(e,[l,r,n]),r<1&&a?n:(a||s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l]),!1)},l=s.promise({elem:e,props:w.extend({},t),opts:w.extend(!0,{specialEasing:{},easing:w.easing._default},n),originalProperties:t,originalOptions:n,startTime:nt||st(),duration:n.duration,tweens:[],createTween:function(t,n){var r=w.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;n<r;n++)l.tweens[n].run(1);return t?(s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l,t])):s.rejectWith(e,[l,t]),this}}),c=l.props;for(ft(c,l.opts.specialEasing);o<a;o++)if(r=pt.prefilters[o].call(l,e,c,l.opts))return g(r.stop)&&(w._queueHooks(l.elem,l.opts.queue).stop=r.stop.bind(r)),r;return w.map(c,lt,l),g(l.opts.start)&&l.opts.start.call(e,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),w.fx.timer(w.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l}w.Animation=w.extend(pt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return ue(n.elem,e,ie.exec(t),n),n}]},tweener:function(e,t){g(e)?(t=e,e=["*"]):e=e.match(M);for(var n,r=0,i=e.length;r<i;r++)n=e[r],pt.tweeners[n]=pt.tweeners[n]||[],pt.tweeners[n].unshift(t)},prefilters:[ct],prefilter:function(e,t){t?pt.prefilters.unshift(e):pt.prefilters.push(e)}}),w.speed=function(e,t,n){var r=e&&"object"==typeof e?w.extend({},e):{complete:n||!n&&t||g(e)&&e,duration:e,easing:n&&t||t&&!g(t)&&t};return w.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in w.fx.speeds?r.duration=w.fx.speeds[r.duration]:r.duration=w.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){g(r.old)&&r.old.call(this),r.queue&&w.dequeue(this,r.queue)},r},w.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ae).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=w.isEmptyObject(e),o=w.speed(t,n,r),a=function(){var t=pt(this,w.extend({},e),o);(i||J.get(this,"finish"))&&t.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(e,t,n){var r=function(e){var t=e.stop;delete e.stop,t(n)};return"string"!=typeof e&&(n=t,t=e,e=void 0),t&&!1!==e&&this.queue(e||"fx",[]),this.each(function(){var t=!0,i=null!=e&&e+"queueHooks",o=w.timers,a=J.get(this);if(i)a[i]&&a[i].stop&&r(a[i]);else for(i in a)a[i]&&a[i].stop&&ot.test(i)&&r(a[i]);for(i=o.length;i--;)o[i].elem!==this||null!=e&&o[i].queue!==e||(o[i].anim.stop(n),t=!1,o.splice(i,1));!t&&n||w.dequeue(this,e)})},finish:function(e){return!1!==e&&(e=e||"fx"),this.each(function(){var t,n=J.get(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=w.timers,a=r?r.length:0;for(n.finish=!0,w.queue(this,e,[]),i&&i.stop&&i.stop.call(this,!0),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;t<a;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}}),w.each(["toggle","show","hide"],function(e,t){var n=w.fn[t];w.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(ut(t,!0),e,r,i)}}),w.each({slideDown:ut("show"),slideUp:ut("hide"),slideToggle:ut("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){w.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),w.timers=[],w.fx.tick=function(){var e,t=0,n=w.timers;for(nt=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||w.fx.stop(),nt=void 0},w.fx.timer=function(e){w.timers.push(e),w.fx.start()},w.fx.interval=13,w.fx.start=function(){rt||(rt=!0,at())},w.fx.stop=function(){rt=null},w.fx.speeds={slow:600,fast:200,_default:400},w.fn.delay=function(t,n){return t=w.fx?w.fx.speeds[t]||t:t,n=n||"fx",this.queue(n,function(n,r){var i=e.setTimeout(n,t);r.stop=function(){e.clearTimeout(i)}})},function(){var e=r.createElement("input"),t=r.createElement("select").appendChild(r.createElement("option"));e.type="checkbox",h.checkOn=""!==e.value,h.optSelected=t.selected,(e=r.createElement("input")).value="t",e.type="radio",h.radioValue="t"===e.value}();var dt,ht=w.expr.attrHandle;w.fn.extend({attr:function(e,t){return z(this,w.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){w.removeAttr(this,e)})}}),w.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?w.prop(e,t,n):(1===o&&w.isXMLDoc(e)||(i=w.attrHooks[t.toLowerCase()]||(w.expr.match.bool.test(t)?dt:void 0)),void 0!==n?null===n?void w.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=w.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!h.radioValue&&"radio"===t&&N(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(M);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),dt={set:function(e,t,n){return!1===t?w.removeAttr(e,n):e.setAttribute(n,n),n}},w.each(w.expr.match.bool.source.match(/\w+/g),function(e,t){var n=ht[t]||w.find.attr;ht[t]=function(e,t,r){var i,o,a=t.toLowerCase();return r||(o=ht[a],ht[a]=i,i=null!=n(e,t,r)?a:null,ht[a]=o),i}});var gt=/^(?:input|select|textarea|button)$/i,yt=/^(?:a|area)$/i;w.fn.extend({prop:function(e,t){return z(this,w.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each(function(){delete this[w.propFix[e]||e]})}}),w.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&w.isXMLDoc(e)||(t=w.propFix[t]||t,i=w.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=w.find.attr(e,"tabindex");return t?parseInt(t,10):gt.test(e.nodeName)||yt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),h.optSelected||(w.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),w.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){w.propFix[this.toLowerCase()]=this});function vt(e){return(e.match(M)||[]).join(" ")}function mt(e){return e.getAttribute&&e.getAttribute("class")||""}function xt(e){return Array.isArray(e)?e:"string"==typeof e?e.match(M)||[]:[]}w.fn.extend({addClass:function(e){var t,n,r,i,o,a,s,u=0;if(g(e))return this.each(function(t){w(this).addClass(e.call(this,t,mt(this)))});if((t=xt(e)).length)while(n=this[u++])if(i=mt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=t[a++])r.indexOf(" "+o+" ")<0&&(r+=o+" ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},removeClass:function(e){var t,n,r,i,o,a,s,u=0;if(g(e))return this.each(function(t){w(this).removeClass(e.call(this,t,mt(this)))});if(!arguments.length)return this.attr("class","");if((t=xt(e)).length)while(n=this[u++])if(i=mt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=t[a++])while(r.indexOf(" "+o+" ")>-1)r=r.replace(" "+o+" "," ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},toggleClass:function(e,t){var n=typeof e,r="string"===n||Array.isArray(e);return"boolean"==typeof t&&r?t?this.addClass(e):this.removeClass(e):g(e)?this.each(function(n){w(this).toggleClass(e.call(this,n,mt(this),t),t)}):this.each(function(){var t,i,o,a;if(r){i=0,o=w(this),a=xt(e);while(t=a[i++])o.hasClass(t)?o.removeClass(t):o.addClass(t)}else void 0!==e&&"boolean"!==n||((t=mt(this))&&J.set(this,"__className__",t),this.setAttribute&&this.setAttribute("class",t||!1===e?"":J.get(this,"__className__")||""))})},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&(" "+vt(mt(n))+" ").indexOf(t)>-1)return!0;return!1}});var bt=/\r/g;w.fn.extend({val:function(e){var t,n,r,i=this[0];{if(arguments.length)return r=g(e),this.each(function(n){var i;1===this.nodeType&&(null==(i=r?e.call(this,n,w(this).val()):e)?i="":"number"==typeof i?i+="":Array.isArray(i)&&(i=w.map(i,function(e){return null==e?"":e+""})),(t=w.valHooks[this.type]||w.valHooks[this.nodeName.toLowerCase()])&&"set"in t&&void 0!==t.set(this,i,"value")||(this.value=i))});if(i)return(t=w.valHooks[i.type]||w.valHooks[i.nodeName.toLowerCase()])&&"get"in t&&void 0!==(n=t.get(i,"value"))?n:"string"==typeof(n=i.value)?n.replace(bt,""):null==n?"":n}}}),w.extend({valHooks:{option:{get:function(e){var t=w.find.attr(e,"value");return null!=t?t:vt(w.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!N(n.parentNode,"optgroup"))){if(t=w(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=w.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=w.inArray(w.valHooks.option.get(r),o)>-1)&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),w.each(["radio","checkbox"],function(){w.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=w.inArray(w(e).val(),t)>-1}},h.checkOn||(w.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),h.focusin="onfocusin"in e;var wt=/^(?:focusinfocus|focusoutblur)$/,Tt=function(e){e.stopPropagation()};w.extend(w.event,{trigger:function(t,n,i,o){var a,s,u,l,c,p,d,h,v=[i||r],m=f.call(t,"type")?t.type:t,x=f.call(t,"namespace")?t.namespace.split("."):[];if(s=h=u=i=i||r,3!==i.nodeType&&8!==i.nodeType&&!wt.test(m+w.event.triggered)&&(m.indexOf(".")>-1&&(m=(x=m.split(".")).shift(),x.sort()),c=m.indexOf(":")<0&&"on"+m,t=t[w.expando]?t:new w.Event(m,"object"==typeof t&&t),t.isTrigger=o?2:3,t.namespace=x.join("."),t.rnamespace=t.namespace?new RegExp("(^|\\.)"+x.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,t.result=void 0,t.target||(t.target=i),n=null==n?[t]:w.makeArray(n,[t]),d=w.event.special[m]||{},o||!d.trigger||!1!==d.trigger.apply(i,n))){if(!o&&!d.noBubble&&!y(i)){for(l=d.delegateType||m,wt.test(l+m)||(s=s.parentNode);s;s=s.parentNode)v.push(s),u=s;u===(i.ownerDocument||r)&&v.push(u.defaultView||u.parentWindow||e)}a=0;while((s=v[a++])&&!t.isPropagationStopped())h=s,t.type=a>1?l:d.bindType||m,(p=(J.get(s,"events")||{})[t.type]&&J.get(s,"handle"))&&p.apply(s,n),(p=c&&s[c])&&p.apply&&Y(s)&&(t.result=p.apply(s,n),!1===t.result&&t.preventDefault());return t.type=m,o||t.isDefaultPrevented()||d._default&&!1!==d._default.apply(v.pop(),n)||!Y(i)||c&&g(i[m])&&!y(i)&&((u=i[c])&&(i[c]=null),w.event.triggered=m,t.isPropagationStopped()&&h.addEventListener(m,Tt),i[m](),t.isPropagationStopped()&&h.removeEventListener(m,Tt),w.event.triggered=void 0,u&&(i[c]=u)),t.result}},simulate:function(e,t,n){var r=w.extend(new w.Event,n,{type:e,isSimulated:!0});w.event.trigger(r,null,t)}}),w.fn.extend({trigger:function(e,t){return this.each(function(){w.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return w.event.trigger(e,t,n,!0)}}),h.focusin||w.each({focus:"focusin",blur:"focusout"},function(e,t){var n=function(e){w.event.simulate(t,e.target,w.event.fix(e))};w.event.special[t]={setup:function(){var r=this.ownerDocument||this,i=J.access(r,t);i||r.addEventListener(e,n,!0),J.access(r,t,(i||0)+1)},teardown:function(){var r=this.ownerDocument||this,i=J.access(r,t)-1;i?J.access(r,t,i):(r.removeEventListener(e,n,!0),J.remove(r,t))}}});var Ct=e.location,Et=Date.now(),kt=/\?/;w.parseXML=function(t){var n;if(!t||"string"!=typeof t)return null;try{n=(new e.DOMParser).parseFromString(t,"text/xml")}catch(e){n=void 0}return n&&!n.getElementsByTagName("parsererror").length||w.error("Invalid XML: "+t),n};var St=/\[\]$/,Dt=/\r?\n/g,Nt=/^(?:submit|button|image|reset|file)$/i,At=/^(?:input|select|textarea|keygen)/i;function jt(e,t,n,r){var i;if(Array.isArray(t))w.each(t,function(t,i){n||St.test(e)?r(e,i):jt(e+"["+("object"==typeof i&&null!=i?t:"")+"]",i,n,r)});else if(n||"object"!==x(t))r(e,t);else for(i in t)jt(e+"["+i+"]",t[i],n,r)}w.param=function(e,t){var n,r=[],i=function(e,t){var n=g(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(Array.isArray(e)||e.jquery&&!w.isPlainObject(e))w.each(e,function(){i(this.name,this.value)});else for(n in e)jt(n,e[n],t,i);return r.join("&")},w.fn.extend({serialize:function(){return w.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=w.prop(this,"elements");return e?w.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!w(this).is(":disabled")&&At.test(this.nodeName)&&!Nt.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=w(this).val();return null==n?null:Array.isArray(n)?w.map(n,function(e){return{name:t.name,value:e.replace(Dt,"\r\n")}}):{name:t.name,value:n.replace(Dt,"\r\n")}}).get()}});var qt=/%20/g,Lt=/#.*$/,Ht=/([?&])_=[^&]*/,Ot=/^(.*?):[ \t]*([^\r\n]*)$/gm,Pt=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Mt=/^(?:GET|HEAD)$/,Rt=/^\/\//,It={},Wt={},$t="*/".concat("*"),Bt=r.createElement("a");Bt.href=Ct.href;function Ft(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match(M)||[];if(g(n))while(r=o[i++])"+"===r[0]?(r=r.slice(1)||"*",(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function _t(e,t,n,r){var i={},o=e===Wt;function a(s){var u;return i[s]=!0,w.each(e[s]||[],function(e,s){var l=s(t,n,r);return"string"!=typeof l||o||i[l]?o?!(u=l):void 0:(t.dataTypes.unshift(l),a(l),!1)}),u}return a(t.dataTypes[0])||!i["*"]&&a("*")}function zt(e,t){var n,r,i=w.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&w.extend(!0,e,r),e}function Xt(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}function Ut(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}w.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Ct.href,type:"GET",isLocal:Pt.test(Ct.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":$t,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":w.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?zt(zt(e,w.ajaxSettings),t):zt(w.ajaxSettings,e)},ajaxPrefilter:Ft(It),ajaxTransport:Ft(Wt),ajax:function(t,n){"object"==typeof t&&(n=t,t=void 0),n=n||{};var i,o,a,s,u,l,c,f,p,d,h=w.ajaxSetup({},n),g=h.context||h,y=h.context&&(g.nodeType||g.jquery)?w(g):w.event,v=w.Deferred(),m=w.Callbacks("once memory"),x=h.statusCode||{},b={},T={},C="canceled",E={readyState:0,getResponseHeader:function(e){var t;if(c){if(!s){s={};while(t=Ot.exec(a))s[t[1].toLowerCase()]=t[2]}t=s[e.toLowerCase()]}return null==t?null:t},getAllResponseHeaders:function(){return c?a:null},setRequestHeader:function(e,t){return null==c&&(e=T[e.toLowerCase()]=T[e.toLowerCase()]||e,b[e]=t),this},overrideMimeType:function(e){return null==c&&(h.mimeType=e),this},statusCode:function(e){var t;if(e)if(c)E.always(e[E.status]);else for(t in e)x[t]=[x[t],e[t]];return this},abort:function(e){var t=e||C;return i&&i.abort(t),k(0,t),this}};if(v.promise(E),h.url=((t||h.url||Ct.href)+"").replace(Rt,Ct.protocol+"//"),h.type=n.method||n.type||h.method||h.type,h.dataTypes=(h.dataType||"*").toLowerCase().match(M)||[""],null==h.crossDomain){l=r.createElement("a");try{l.href=h.url,l.href=l.href,h.crossDomain=Bt.protocol+"//"+Bt.host!=l.protocol+"//"+l.host}catch(e){h.crossDomain=!0}}if(h.data&&h.processData&&"string"!=typeof h.data&&(h.data=w.param(h.data,h.traditional)),_t(It,h,n,E),c)return E;(f=w.event&&h.global)&&0==w.active++&&w.event.trigger("ajaxStart"),h.type=h.type.toUpperCase(),h.hasContent=!Mt.test(h.type),o=h.url.replace(Lt,""),h.hasContent?h.data&&h.processData&&0===(h.contentType||"").indexOf("application/x-www-form-urlencoded")&&(h.data=h.data.replace(qt,"+")):(d=h.url.slice(o.length),h.data&&(h.processData||"string"==typeof h.data)&&(o+=(kt.test(o)?"&":"?")+h.data,delete h.data),!1===h.cache&&(o=o.replace(Ht,"$1"),d=(kt.test(o)?"&":"?")+"_="+Et+++d),h.url=o+d),h.ifModified&&(w.lastModified[o]&&E.setRequestHeader("If-Modified-Since",w.lastModified[o]),w.etag[o]&&E.setRequestHeader("If-None-Match",w.etag[o])),(h.data&&h.hasContent&&!1!==h.contentType||n.contentType)&&E.setRequestHeader("Content-Type",h.contentType),E.setRequestHeader("Accept",h.dataTypes[0]&&h.accepts[h.dataTypes[0]]?h.accepts[h.dataTypes[0]]+("*"!==h.dataTypes[0]?", "+$t+"; q=0.01":""):h.accepts["*"]);for(p in h.headers)E.setRequestHeader(p,h.headers[p]);if(h.beforeSend&&(!1===h.beforeSend.call(g,E,h)||c))return E.abort();if(C="abort",m.add(h.complete),E.done(h.success),E.fail(h.error),i=_t(Wt,h,n,E)){if(E.readyState=1,f&&y.trigger("ajaxSend",[E,h]),c)return E;h.async&&h.timeout>0&&(u=e.setTimeout(function(){E.abort("timeout")},h.timeout));try{c=!1,i.send(b,k)}catch(e){if(c)throw e;k(-1,e)}}else k(-1,"No Transport");function k(t,n,r,s){var l,p,d,b,T,C=n;c||(c=!0,u&&e.clearTimeout(u),i=void 0,a=s||"",E.readyState=t>0?4:0,l=t>=200&&t<300||304===t,r&&(b=Xt(h,E,r)),b=Ut(h,b,E,l),l?(h.ifModified&&((T=E.getResponseHeader("Last-Modified"))&&(w.lastModified[o]=T),(T=E.getResponseHeader("etag"))&&(w.etag[o]=T)),204===t||"HEAD"===h.type?C="nocontent":304===t?C="notmodified":(C=b.state,p=b.data,l=!(d=b.error))):(d=C,!t&&C||(C="error",t<0&&(t=0))),E.status=t,E.statusText=(n||C)+"",l?v.resolveWith(g,[p,C,E]):v.rejectWith(g,[E,C,d]),E.statusCode(x),x=void 0,f&&y.trigger(l?"ajaxSuccess":"ajaxError",[E,h,l?p:d]),m.fireWith(g,[E,C]),f&&(y.trigger("ajaxComplete",[E,h]),--w.active||w.event.trigger("ajaxStop")))}return E},getJSON:function(e,t,n){return w.get(e,t,n,"json")},getScript:function(e,t){return w.get(e,void 0,t,"script")}}),w.each(["get","post"],function(e,t){w[t]=function(e,n,r,i){return g(n)&&(i=i||r,r=n,n=void 0),w.ajax(w.extend({url:e,type:t,dataType:i,data:n,success:r},w.isPlainObject(e)&&e))}}),w._evalUrl=function(e){return w.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,"throws":!0})},w.fn.extend({wrapAll:function(e){var t;return this[0]&&(g(e)&&(e=e.call(this[0])),t=w(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(e){return g(e)?this.each(function(t){w(this).wrapInner(e.call(this,t))}):this.each(function(){var t=w(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=g(e);return this.each(function(n){w(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(e){return this.parent(e).not("body").each(function(){w(this).replaceWith(this.childNodes)}),this}}),w.expr.pseudos.hidden=function(e){return!w.expr.pseudos.visible(e)},w.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},w.ajaxSettings.xhr=function(){try{return new e.XMLHttpRequest}catch(e){}};var Vt={0:200,1223:204},Gt=w.ajaxSettings.xhr();h.cors=!!Gt&&"withCredentials"in Gt,h.ajax=Gt=!!Gt,w.ajaxTransport(function(t){var n,r;if(h.cors||Gt&&!t.crossDomain)return{send:function(i,o){var a,s=t.xhr();if(s.open(t.type,t.url,t.async,t.username,t.password),t.xhrFields)for(a in t.xhrFields)s[a]=t.xhrFields[a];t.mimeType&&s.overrideMimeType&&s.overrideMimeType(t.mimeType),t.crossDomain||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest");for(a in i)s.setRequestHeader(a,i[a]);n=function(e){return function(){n&&(n=r=s.onload=s.onerror=s.onabort=s.ontimeout=s.onreadystatechange=null,"abort"===e?s.abort():"error"===e?"number"!=typeof s.status?o(0,"error"):o(s.status,s.statusText):o(Vt[s.status]||s.status,s.statusText,"text"!==(s.responseType||"text")||"string"!=typeof s.responseText?{binary:s.response}:{text:s.responseText},s.getAllResponseHeaders()))}},s.onload=n(),r=s.onerror=s.ontimeout=n("error"),void 0!==s.onabort?s.onabort=r:s.onreadystatechange=function(){4===s.readyState&&e.setTimeout(function(){n&&r()})},n=n("abort");try{s.send(t.hasContent&&t.data||null)}catch(e){if(n)throw e}},abort:function(){n&&n()}}}),w.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),w.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return w.globalEval(e),e}}}),w.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),w.ajaxTransport("script",function(e){if(e.crossDomain){var t,n;return{send:function(i,o){t=w("<script>").prop({charset:e.scriptCharset,src:e.url}).on("load error",n=function(e){t.remove(),n=null,e&&o("error"===e.type?404:200,e.type)}),r.head.appendChild(t[0])},abort:function(){n&&n()}}}});var Yt=[],Qt=/(=)\?(?=&|$)|\?\?/;w.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Yt.pop()||w.expando+"_"+Et++;return this[e]=!0,e}}),w.ajaxPrefilter("json jsonp",function(t,n,r){var i,o,a,s=!1!==t.jsonp&&(Qt.test(t.url)?"url":"string"==typeof t.data&&0===(t.contentType||"").indexOf("application/x-www-form-urlencoded")&&Qt.test(t.data)&&"data");if(s||"jsonp"===t.dataTypes[0])return i=t.jsonpCallback=g(t.jsonpCallback)?t.jsonpCallback():t.jsonpCallback,s?t[s]=t[s].replace(Qt,"$1"+i):!1!==t.jsonp&&(t.url+=(kt.test(t.url)?"&":"?")+t.jsonp+"="+i),t.converters["script json"]=function(){return a||w.error(i+" was not called"),a[0]},t.dataTypes[0]="json",o=e[i],e[i]=function(){a=arguments},r.always(function(){void 0===o?w(e).removeProp(i):e[i]=o,t[i]&&(t.jsonpCallback=n.jsonpCallback,Yt.push(i)),a&&g(o)&&o(a[0]),a=o=void 0}),"script"}),h.createHTMLDocument=function(){var e=r.implementation.createHTMLDocument("").body;return e.innerHTML="<form></form><form></form>",2===e.childNodes.length}(),w.parseHTML=function(e,t,n){if("string"!=typeof e)return[];"boolean"==typeof t&&(n=t,t=!1);var i,o,a;return t||(h.createHTMLDocument?((i=(t=r.implementation.createHTMLDocument("")).createElement("base")).href=r.location.href,t.head.appendChild(i)):t=r),o=A.exec(e),a=!n&&[],o?[t.createElement(o[1])]:(o=xe([e],t,a),a&&a.length&&w(a).remove(),w.merge([],o.childNodes))},w.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return s>-1&&(r=vt(e.slice(s)),e=e.slice(0,s)),g(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),a.length>0&&w.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?w("<div>").append(w.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},w.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){w.fn[t]=function(e){return this.on(t,e)}}),w.expr.pseudos.animated=function(e){return w.grep(w.timers,function(t){return e===t.elem}).length},w.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l,c=w.css(e,"position"),f=w(e),p={};"static"===c&&(e.style.position="relative"),s=f.offset(),o=w.css(e,"top"),u=w.css(e,"left"),(l=("absolute"===c||"fixed"===c)&&(o+u).indexOf("auto")>-1)?(a=(r=f.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),g(t)&&(t=t.call(e,n,w.extend({},s))),null!=t.top&&(p.top=t.top-s.top+a),null!=t.left&&(p.left=t.left-s.left+i),"using"in t?t.using.call(e,p):f.css(p)}},w.fn.extend({offset:function(e){if(arguments.length)return void 0===e?this:this.each(function(t){w.offset.setOffset(this,e,t)});var t,n,r=this[0];if(r)return r.getClientRects().length?(t=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:t.top+n.pageYOffset,left:t.left+n.pageXOffset}):{top:0,left:0}},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===w.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===w.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=w(e).offset()).top+=w.css(e,"borderTopWidth",!0),i.left+=w.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-w.css(r,"marginTop",!0),left:t.left-i.left-w.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===w.css(e,"position"))e=e.offsetParent;return e||be})}}),w.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(e,t){var n="pageYOffset"===t;w.fn[e]=function(r){return z(this,function(e,r,i){var o;if(y(e)?o=e:9===e.nodeType&&(o=e.defaultView),void 0===i)return o?o[t]:e[r];o?o.scrollTo(n?o.pageXOffset:i,n?i:o.pageYOffset):e[r]=i},e,r,arguments.length)}}),w.each(["top","left"],function(e,t){w.cssHooks[t]=_e(h.pixelPosition,function(e,n){if(n)return n=Fe(e,t),We.test(n)?w(e).position()[t]+"px":n})}),w.each({Height:"height",Width:"width"},function(e,t){w.each({padding:"inner"+e,content:t,"":"outer"+e},function(n,r){w.fn[r]=function(i,o){var a=arguments.length&&(n||"boolean"!=typeof i),s=n||(!0===i||!0===o?"margin":"border");return z(this,function(t,n,i){var o;return y(t)?0===r.indexOf("outer")?t["inner"+e]:t.document.documentElement["client"+e]:9===t.nodeType?(o=t.documentElement,Math.max(t.body["scroll"+e],o["scroll"+e],t.body["offset"+e],o["offset"+e],o["client"+e])):void 0===i?w.css(t,n,s):w.style(t,n,i,s)},t,a?i:void 0,a)}})}),w.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,t){w.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}}),w.fn.extend({hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),w.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)}}),w.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),g(e))return r=o.call(arguments,2),i=function(){return e.apply(t||this,r.concat(o.call(arguments)))},i.guid=e.guid=e.guid||w.guid++,i},w.holdReady=function(e){e?w.readyWait++:w.ready(!0)},w.isArray=Array.isArray,w.parseJSON=JSON.parse,w.nodeName=N,w.isFunction=g,w.isWindow=y,w.camelCase=G,w.type=x,w.now=Date.now,w.isNumeric=function(e){var t=w.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},"function"==typeof define&&define.amd&&define("jquery",[],function(){return w});var Jt=e.jQuery,Kt=e.$;return w.noConflict=function(t){return e.$===w&&(e.$=Kt),t&&e.jQuery===w&&(e.jQuery=Jt),w},t||(e.jQuery=e.$=w),w});
    ;(function(f){"use strict";"function"===typeof define&&define.amd?define(["jquery"],f):"undefined"!==typeof module&&module.exports?module.exports=f(require("jquery")):f(jQuery)})(function($){"use strict";function n(a){return!a.nodeName||-1!==$.inArray(a.nodeName.toLowerCase(),["iframe","#document","html","body"])}function h(a){return $.isFunction(a)||$.isPlainObject(a)?a:{top:a,left:a}}var p=$.scrollTo=function(a,d,b){return $(window).scrollTo(a,d,b)};p.defaults={axis:"xy",duration:0,limit:!0};$.fn.scrollTo=function(a,d,b){"object"=== typeof d&&(b=d,d=0);"function"===typeof b&&(b={onAfter:b});"max"===a&&(a=9E9);b=$.extend({},p.defaults,b);d=d||b.duration;var u=b.queue&&1<b.axis.length;u&&(d/=2);b.offset=h(b.offset);b.over=h(b.over);return this.each(function(){function k(a){var k=$.extend({},b,{queue:!0,duration:d,complete:a&&function(){a.call(q,e,b)}});r.animate(f,k)}if(null!==a){var l=n(this),q=l?this.contentWindow||window:this,r=$(q),e=a,f={},t;switch(typeof e){case "number":case "string":if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(e)){e= h(e);break}e=l?$(e):$(e,q);case "object":if(e.length===0)return;if(e.is||e.style)t=(e=$(e)).offset()}var v=$.isFunction(b.offset)&&b.offset(q,e)||b.offset;$.each(b.axis.split(""),function(a,c){var d="x"===c?"Left":"Top",m=d.toLowerCase(),g="scroll"+d,h=r[g](),n=p.max(q,c);t?(f[g]=t[m]+(l?0:h-r.offset()[m]),b.margin&&(f[g]-=parseInt(e.css("margin"+d),10)||0,f[g]-=parseInt(e.css("border"+d+"Width"),10)||0),f[g]+=v[m]||0,b.over[m]&&(f[g]+=e["x"===c?"width":"height"]()*b.over[m])):(d=e[m],f[g]=d.slice&& "%"===d.slice(-1)?parseFloat(d)/100*n:d);b.limit&&/^\d+$/.test(f[g])&&(f[g]=0>=f[g]?0:Math.min(f[g],n));!a&&1<b.axis.length&&(h===f[g]?f={}:u&&(k(b.onAfterFirst),f={}))});k(b.onAfter)}})};p.max=function(a,d){var b="x"===d?"Width":"Height",h="scroll"+b;if(!n(a))return a[h]-$(a)[b.toLowerCase()]();var b="client"+b,k=a.ownerDocument||a.document,l=k.documentElement,k=k.body;return Math.max(l[h],k[h])-Math.min(l[b],k[b])};$.Tween.propHooks.scrollLeft=$.Tween.propHooks.scrollTop={get:function(a){return $(a.elem)[a.prop]()}, set:function(a){var d=this.get(a);if(a.options.interrupt&&a._last&&a._last!==d)return $(a.elem).stop();var b=Math.round(a.now);d!==b&&($(a.elem)[a.prop](b),a._last=this.get(a))}};return p});

</script><?php }

function jsbarcode() { ?><script type="text/javascript">
    /*! JsBarcode v3.11.0 | (c) Johan Lindell | MIT license */
    !function(t){function e(r){if(n[r])return n[r].exports;var i=n[r]={i:r,l:!1,exports:{}};return t[r].call(i.exports,i,i.exports,e),i.l=!0,i.exports}var n={};e.m=t,e.c=n,e.i=function(t){return t},e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="",e(e.s=11)}([function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=Object.assign||function(t){for(var e=1;e<arguments.length;e++){var n=arguments[e];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(t[r]=n[r])}return t};e.default=function(t,e){return r({},t,e)}},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function i(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function o(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}Object.defineProperty(e,"__esModule",{value:!0});var a=function(t){function e(t,n){r(this,e);var o=i(this,(e.__proto__||Object.getPrototypeOf(e)).call(this));return o.name="InvalidInputException",o.symbology=t,o.input=n,o.message='"'+o.input+'" is not a valid input for '+o.symbology,o}return o(e,t),e}(Error),u=function(t){function e(){r(this,e);var t=i(this,(e.__proto__||Object.getPrototypeOf(e)).call(this));return t.name="InvalidElementException",t.message="Not supported type to render on",t}return o(e,t),e}(Error),s=function(t){function e(){r(this,e);var t=i(this,(e.__proto__||Object.getPrototypeOf(e)).call(this));return t.name="NoElementException",t.message="No element to render on.",t}return o(e,t),e}(Error);e.InvalidInputException=a,e.InvalidElementException=u,e.NoElementException=s},function(t,e,n){"use strict";function r(t){var e=["width","height","textMargin","fontSize","margin","marginTop","marginBottom","marginLeft","marginRight"];for(var n in e)e.hasOwnProperty(n)&&(n=e[n],"string"==typeof t[n]&&(t[n]=parseInt(t[n],10)));return"string"==typeof t.displayValue&&(t.displayValue="false"!=t.displayValue),t}Object.defineProperty(e,"__esModule",{value:!0}),e.default=r},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r={width:2,height:100,format:"auto",displayValue:!0,fontOptions:"",font:"monospace",text:void 0,textAlign:"center",textPosition:"bottom",textMargin:2,fontSize:20,background:"#ffffff",lineColor:"#000000",margin:10,marginTop:void 0,marginBottom:void 0,marginLeft:void 0,marginRight:void 0,valid:function(){}};e.default=r},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function i(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function o(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}Object.defineProperty(e,"__esModule",{value:!0});var a=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),u=n(14),s=n(12),f=function(t){return t&&t.__esModule?t:{default:t}}(s),c=function(t){function e(){return r(this,e),i(this,(e.__proto__||Object.getPrototypeOf(e)).apply(this,arguments))}return o(e,t),a(e,[{key:"valid",value:function(){return-1!==this.data.search(/^([0-9]{2})+$/)}},{key:"encode",value:function(){var t=this,e=this.data.match(/.{2}/g).map(function(e){return t.encodePair(e)}).join("");return{data:u.START_BIN+e+u.END_BIN,text:this.text}}},{key:"encodePair",value:function(t){var e=u.BINARIES[t[1]];return u.BINARIES[t[0]].split("").map(function(t,n){return("1"===t?"111":"1")+("1"===e[n]?"000":"0")}).join("")}}]),e}(f.default);e.default=c},function(t,e,n){"use strict";function r(t,e){return e.height+(e.displayValue&&t.text.length>0?e.fontSize+e.textMargin:0)+e.marginTop+e.marginBottom}function i(t,e,n){if(n.displayValue&&e<t){if("center"==n.textAlign)return Math.floor((t-e)/2);if("left"==n.textAlign)return 0;if("right"==n.textAlign)return Math.floor(t-e)}return 0}function o(t,e,n){for(var o=0;o<t.length;o++){var a,u=t[o],f=(0,c.default)(e,u.options);a=f.displayValue?s(u.text,f,n):0;var l=u.data.length*f.width;u.width=Math.ceil(Math.max(a,l)),u.height=r(u,f),u.barcodePadding=i(a,l,f)}}function a(t){for(var e=0,n=0;n<t.length;n++)e+=t[n].width;return e}function u(t){for(var e=0,n=0;n<t.length;n++)t[n].height>e&&(e=t[n].height);return e}function s(t,e,n){var r;if(n)r=n;else{if("undefined"==typeof document)return 0;r=document.createElement("canvas").getContext("2d")}return r.font=e.fontOptions+" "+e.fontSize+"px "+e.font,r.measureText(t).width}Object.defineProperty(e,"__esModule",{value:!0}),e.getTotalWidthOfEncodings=e.calculateEncodingAttributes=e.getBarcodePadding=e.getEncodingHeight=e.getMaximumHeightOfEncodings=void 0;var f=n(0),c=function(t){return t&&t.__esModule?t:{default:t}}(f);e.getMaximumHeightOfEncodings=u,e.getEncodingHeight=r,e.getBarcodePadding=i,e.calculateEncodingAttributes=o,e.getTotalWidthOfEncodings=a},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n(15);e.default={ITF:r.ITF,ITF14:r.ITF14}},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var i=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),o=function(){function t(e){r(this,t),this.api=e}return i(t,[{key:"handleCatch",value:function(t){if("InvalidInputException"!==t.name)throw t;if(this.api._options.valid===this.api._defaults.valid)throw t.message;this.api._options.valid(!1),this.api.render=function(){}}},{key:"wrapBarcodeCall",value:function(t){try{var e=t.apply(void 0,arguments);return this.api._options.valid(!0),e}catch(t){return this.handleCatch(t),this.api}}}]),t}();e.default=o},function(t,e,n){"use strict";function r(t){return t.marginTop=t.marginTop||t.margin,t.marginBottom=t.marginBottom||t.margin,t.marginRight=t.marginRight||t.margin,t.marginLeft=t.marginLeft||t.margin,t}Object.defineProperty(e,"__esModule",{value:!0}),e.default=r},function(t,e,n){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}function i(t){if("string"==typeof t)return o(t);if(Array.isArray(t)){for(var e=[],n=0;n<t.length;n++)e.push(i(t[n]));return e}if("undefined"!=typeof HTMLCanvasElement&&t instanceof HTMLImageElement)return a(t);if(t&&"svg"===t.nodeName||"undefined"!=typeof SVGElement&&t instanceof SVGElement)return{element:t,options:(0,f.default)(t),renderer:l.default.SVGRenderer};if("undefined"!=typeof HTMLCanvasElement&&t instanceof HTMLCanvasElement)return{element:t,options:(0,f.default)(t),renderer:l.default.CanvasRenderer};if(t&&t.getContext)return{element:t,renderer:l.default.CanvasRenderer};if(t&&"object"===(void 0===t?"undefined":u(t))&&!t.nodeName)return{element:t,renderer:l.default.ObjectRenderer};throw new d.InvalidElementException}function o(t){var e=document.querySelectorAll(t);if(0!==e.length){for(var n=[],r=0;r<e.length;r++)n.push(i(e[r]));return n}}function a(t){var e=document.createElement("canvas");return{element:e,options:(0,f.default)(t),renderer:l.default.CanvasRenderer,afterRender:function(){t.setAttribute("src",e.toDataURL())}}}Object.defineProperty(e,"__esModule",{value:!0});var u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},s=n(16),f=r(s),c=n(18),l=r(c),d=n(1);e.default=i},function(t,e,n){"use strict";function r(t){function e(t){if(Array.isArray(t))for(var r=0;r<t.length;r++)e(t[r]);else t.text=t.text||"",t.data=t.data||"",n.push(t)}var n=[];return e(t),n}Object.defineProperty(e,"__esModule",{value:!0}),e.default=r},function(t,e,n){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}function i(t,e,n){t=""+t;var r=new e(t,n);if(!r.valid())throw new w.InvalidInputException(r.constructor.name,t);var i=r.encode();i=(0,d.default)(i);for(var o=0;o<i.length;o++)i[o].options=(0,c.default)(n,i[o].options);return i}function o(){return s.default.CODE128?"CODE128":Object.keys(s.default)[0]}function a(t,e,n){e=(0,d.default)(e);for(var r=0;r<e.length;r++)e[r].options=(0,c.default)(n,e[r].options),(0,h.default)(e[r].options);(0,h.default)(n),new(0,t.renderer)(t.element,e,n).render(),t.afterRender&&t.afterRender()}var u=n(6),s=r(u),f=n(0),c=r(f),l=n(10),d=r(l),p=n(8),h=r(p),g=n(9),v=r(g),y=n(2),b=r(y),m=n(7),_=r(m),w=n(1),x=n(3),O=r(x),P=function(){},E=function(t,e,n){var r=new P;if(void 0===t)throw Error("No element to render on was provided.");return r._renderProperties=(0,v.default)(t),r._encodings=[],r._options=O.default,r._errorHandler=new _.default(r),void 0!==e&&(n=n||{},n.format||(n.format=o()),r.options(n)[n.format](e,n).render()),r};E.getModule=function(t){return s.default[t]};for(var j in s.default)s.default.hasOwnProperty(j)&&function(t,e){P.prototype[e]=P.prototype[e.toUpperCase()]=P.prototype[e.toLowerCase()]=function(n,r){var o=this;return o._errorHandler.wrapBarcodeCall(function(){r.text=void 0===r.text?void 0:""+r.text;var a=(0,c.default)(o._options,r);a=(0,b.default)(a);var u=t[e],s=i(n,u,a);return o._encodings.push(s),o})}}(s.default,j);P.prototype.options=function(t){return this._options=(0,c.default)(this._options,t),this},P.prototype.blank=function(t){var e=new Array(t+1).join("0");return this._encodings.push({data:e}),this},P.prototype.init=function(){if(this._renderProperties){Array.isArray(this._renderProperties)||(this._renderProperties=[this._renderProperties]);var t;for(var e in this._renderProperties){t=this._renderProperties[e];var n=(0,c.default)(this._options,t.options);"auto"==n.format&&(n.format=o()),this._errorHandler.wrapBarcodeCall(function(){var e=n.value,r=s.default[n.format.toUpperCase()],o=i(e,r,n);a(t,o,n)})}}},P.prototype.render=function(){if(!this._renderProperties)throw new w.NoElementException;if(Array.isArray(this._renderProperties))for(var t=0;t<this._renderProperties.length;t++)a(this._renderProperties[t],this._encodings,this._options);else a(this._renderProperties,this._encodings,this._options);return this},P.prototype._defaults=O.default,"undefined"!=typeof window&&(window.JsBarcode=E),"undefined"!=typeof jQuery&&(jQuery.fn.JsBarcode=function(t,e){var n=[];return jQuery(this).each(function(){n.push(this)}),E(n,t,e)}),t.exports=E},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var i=function t(e,n){r(this,t),this.data=e,this.text=n.text||e,this.options=n};e.default=i},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function i(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function o(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}Object.defineProperty(e,"__esModule",{value:!0});var a=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),u=n(4),s=function(t){return t&&t.__esModule?t:{default:t}}(u),f=function(t){var e=t.substr(0,13).split("").map(function(t){return parseInt(t,10)}).reduce(function(t,e,n){return t+e*(3-n%2*2)},0);return 10*Math.ceil(e/10)-e},c=function(t){function e(t,n){return r(this,e),-1!==t.search(/^[0-9]{13}$/)&&(t+=f(t)),i(this,(e.__proto__||Object.getPrototypeOf(e)).call(this,t,n))}return o(e,t),a(e,[{key:"valid",value:function(){return-1!==this.data.search(/^[0-9]{14}$/)&&+this.data[13]===f(this.data)}}]),e}(s.default);e.default=c},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});e.START_BIN="1010",e.END_BIN="11101",e.BINARIES=["00110","10001","01001","11000","00101","10100","01100","00011","10010","01010"]},function(t,e,n){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0}),e.ITF14=e.ITF=void 0;var i=n(4),o=r(i),a=n(13),u=r(a);e.ITF=o.default,e.ITF14=u.default},function(t,e,n){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}function i(t){var e={};for(var n in s.default)s.default.hasOwnProperty(n)&&(t.hasAttribute("jsbarcode-"+n.toLowerCase())&&(e[n]=t.getAttribute("jsbarcode-"+n.toLowerCase())),t.hasAttribute("data-"+n.toLowerCase())&&(e[n]=t.getAttribute("data-"+n.toLowerCase())));return e.value=t.getAttribute("jsbarcode-value")||t.getAttribute("data-value"),e=(0,a.default)(e)}Object.defineProperty(e,"__esModule",{value:!0});var o=n(2),a=r(o),u=n(3),s=r(u);e.default=i},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var i=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),o=n(0),a=function(t){return t&&t.__esModule?t:{default:t}}(o),u=n(5),s=function(){function t(e,n,i){r(this,t),this.canvas=e,this.encodings=n,this.options=i}return i(t,[{key:"render",value:function(){if(!this.canvas.getContext)throw new Error("The browser does not support canvas.");this.prepareCanvas();for(var t=0;t<this.encodings.length;t++){var e=(0,a.default)(this.options,this.encodings[t].options);this.drawCanvasBarcode(e,this.encodings[t]),this.drawCanvasText(e,this.encodings[t]),this.moveCanvasDrawing(this.encodings[t])}this.restoreCanvas()}},{key:"prepareCanvas",value:function(){var t=this.canvas.getContext("2d");t.save(),(0,u.calculateEncodingAttributes)(this.encodings,this.options,t);var e=(0,u.getTotalWidthOfEncodings)(this.encodings),n=(0,u.getMaximumHeightOfEncodings)(this.encodings);this.canvas.width=e+this.options.marginLeft+this.options.marginRight,this.canvas.height=n,t.clearRect(0,0,this.canvas.width,this.canvas.height),this.options.background&&(t.fillStyle=this.options.background,t.fillRect(0,0,this.canvas.width,this.canvas.height)),t.translate(this.options.marginLeft,0)}},{key:"drawCanvasBarcode",value:function(t,e){var n,r=this.canvas.getContext("2d"),i=e.data;n="top"==t.textPosition?t.marginTop+t.fontSize+t.textMargin:t.marginTop,r.fillStyle=t.lineColor;for(var o=0;o<i.length;o++){var a=o*t.width+e.barcodePadding;"1"===i[o]?r.fillRect(a,n,t.width,t.height):i[o]&&r.fillRect(a,n,t.width,t.height*i[o])}}},{key:"drawCanvasText",value:function(t,e){var n=this.canvas.getContext("2d"),r=t.fontOptions+" "+t.fontSize+"px "+t.font;if(t.displayValue){var i,o;o="top"==t.textPosition?t.marginTop+t.fontSize-t.textMargin:t.height+t.textMargin+t.marginTop+t.fontSize,n.font=r,"left"==t.textAlign||e.barcodePadding>0?(i=0,n.textAlign="left"):"right"==t.textAlign?(i=e.width-1,n.textAlign="right"):(i=e.width/2,n.textAlign="center"),n.fillText(e.text,i,o)}}},{key:"moveCanvasDrawing",value:function(t){this.canvas.getContext("2d").translate(t.width,0)}},{key:"restoreCanvas",value:function(){this.canvas.getContext("2d").restore()}}]),t}();e.default=s},function(t,e,n){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var i=n(17),o=r(i),a=n(20),u=r(a),s=n(19),f=r(s);e.default={CanvasRenderer:o.default,SVGRenderer:u.default,ObjectRenderer:f.default}},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var i=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),o=function(){function t(e,n,i){r(this,t),this.object=e,this.encodings=n,this.options=i}return i(t,[{key:"render",value:function(){this.object.encodings=this.encodings}}]),t}();e.default=o},function(t,e,n){"use strict";function r(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var i=function(){function t(t,e){for(var n=0;n<e.length;n++){var r=e[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,r.key,r)}}return function(e,n,r){return n&&t(e.prototype,n),r&&t(e,r),e}}(),o=n(0),a=function(t){return t&&t.__esModule?t:{default:t}}(o),u=n(5),s="http://www.w3.org/2000/svg",f=function(){function t(e,n,i){r(this,t),this.svg=e,this.encodings=n,this.options=i,this.document=i.xmlDocument||document}return i(t,[{key:"render",value:function(){var t=this.options.marginLeft;this.prepareSVG();for(var e=0;e<this.encodings.length;e++){var n=this.encodings[e],r=(0,a.default)(this.options,n.options),i=this.createGroup(t,r.marginTop,this.svg);this.setGroupOptions(i,r),this.drawSvgBarcode(i,r,n),this.drawSVGText(i,r,n),t+=n.width}}},{key:"prepareSVG",value:function(){for(;this.svg.firstChild;)this.svg.removeChild(this.svg.firstChild);(0,u.calculateEncodingAttributes)(this.encodings,this.options);var t=(0,u.getTotalWidthOfEncodings)(this.encodings),e=(0,u.getMaximumHeightOfEncodings)(this.encodings),n=t+this.options.marginLeft+this.options.marginRight;this.setSvgAttributes(n,e),this.options.background&&this.drawRect(0,0,n,e,this.svg).setAttribute("style","fill:"+this.options.background+";")}},{key:"drawSvgBarcode",value:function(t,e,n){var r,i=n.data;r="top"==e.textPosition?e.fontSize+e.textMargin:0;for(var o=0,a=0,u=0;u<i.length;u++)a=u*e.width+n.barcodePadding,"1"===i[u]?o++:o>0&&(this.drawRect(a-e.width*o,r,e.width*o,e.height,t),o=0);o>0&&this.drawRect(a-e.width*(o-1),r,e.width*o,e.height,t)}},{key:"drawSVGText",value:function(t,e,n){var r=this.document.createElementNS(s,"text");if(e.displayValue){var i,o;r.setAttribute("style","font:"+e.fontOptions+" "+e.fontSize+"px "+e.font),o="top"==e.textPosition?e.fontSize-e.textMargin:e.height+e.textMargin+e.fontSize,"left"==e.textAlign||n.barcodePadding>0?(i=0,r.setAttribute("text-anchor","start")):"right"==e.textAlign?(i=n.width-1,r.setAttribute("text-anchor","end")):(i=n.width/2,r.setAttribute("text-anchor","middle")),r.setAttribute("x",i),r.setAttribute("y",o),r.appendChild(this.document.createTextNode(n.text)),t.appendChild(r)}}},{key:"setSvgAttributes",value:function(t,e){var n=this.svg;n.setAttribute("width",t+"px"),n.setAttribute("height",e+"px"),n.setAttribute("x","0px"),n.setAttribute("y","0px"),n.setAttribute("viewBox","0 0 "+t+" "+e),n.setAttribute("xmlns",s),n.setAttribute("version","1.1"),n.setAttribute("style","transform: translate(0,0)")}},{key:"createGroup",value:function(t,e,n){var r=this.document.createElementNS(s,"g");return r.setAttribute("transform","translate("+t+", "+e+")"),n.appendChild(r),r}},{key:"setGroupOptions",value:function(t,e){t.setAttribute("style","fill:"+e.lineColor+";")}},{key:"drawRect",value:function(t,e,n,r,i){var o=this.document.createElementNS(s,"rect");return o.setAttribute("x",t),o.setAttribute("y",e),o.setAttribute("width",n),o.setAttribute("height",r),i.appendChild(o),o}}]),t}();e.default=f}]);
</script><?php }

function css() { ?><style type="text/css">
    html {
        background-color: #eb9e9e;
    }
    html, body {
        margin: 0;
    }
    h1,h2,h3 {
        letter-spacing: 0.1em;
    }
    h2 {
        margin-bottom: 0.6em;
    }
    h3 {
        margin-bottom: 0.3em;
        margin-top: 1em;
    }
    h4 {
        margin-bottom: 0;
        margin-top: 0.5em;
    }
    h5 {
        margin-bottom: 0;
        margin-top: 0.3em;
    }
    h6 {
        margin-bottom: 0;
        margin-top: 0.2em;
    }
    p {
        font-size: 15px;
    }
    #tab6 p {
        margin: 0.5em 0;
    }
    a.link {
        color: #bababa;
        font-style: italic;
    }
    select:focus {
        border: 0.5px solid #0c3536;
    }

    .header {
        font-size: 32px;
        color: #ffffff;
        
        height: 100px;
        width: 100%;
        background-color: #f85e3d;
        
        margin: 8px 0;
        
        display: table;
    }
    .header > div {
        display: table-cell;
        vertical-align: middle;
        
        padding: 15px 25px;
    }
    
    #tabs {
        padding: 8px;
    }
    
    ul.tab {
        list-style-type: none;
        margin: 0 6px;
        padding: 0;
    }
    
    ul.tab li {
        display: inline-block;
        
        font-size: 18px;
        margin: 12px 0;
    }
    ul.tab li a {
        padding: 12px 15px;
        background-color: #b1b1b1;
        color: white;
        text-decoration: none;
        
        border-style: solid solid hidden solid;
        border-color: #828282;
        border-width: 2px;
        border-top-right-radius: 10px;
        border-top-left-radius: 10px;
    }
    ul.tab li a.selected {
        background-color: #b70c0c;
        border-bottom: 2px solid #b70c0c;
    }
    ul.tab li a:not(.selected):hover {
        background-color: #898989;
    }
    
    #tab-body {
        border: 2px solid grey;
        
        margin: 0;
        padding: 8px;
        padding-bottom: 16px;
        margin-top: -1px;
        
        overflow: auto;
        
        background-color: #922121;
        color: #f3f3f3;
    }
    #tab-body > div {
        animation: opac 0.5s;
        -webkit-animation: opac 0.5s;
        -moz-animation: opac 0.5s;
        -ms-animation: opac 0.5s;
        -o-animation: opac 0.5s;
        
        display: none;
        overflow: auto;
    }
    #tab-body > div > *:first-child {
        margin-top: 0;
        padding-top: 8px;
    }
    
    .select-list {
        border: 1px solid #282828;
        background-color: #d0d0d0;
        color: initial;
        box-sizing: border-box;
    }
    .select-list ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        height: 630px;
        overflow: auto;
    }
    .select-list ul li {
        padding: 2px 12px;
        font-size: 14px;
    }
    .select-list ul li.selected {
        background-color: #a6a2a2;
    }
    .select-list ul li:not(.selected):not(.title):hover {
        background-color: #f0f0f0;
    }
    .select-list ul li:not(.title):hover {
        cursor: pointer;
    }
    .select-list ul li.title {
        font-style: italic;
        margin-top: 10px;
    }
    div .select-list.cols2:nth-child(odd) {
        margin-right: 13px;
    }
    div .select-list .expand {
        display: none;
    }
    div .select-list .expanded .expand {
        display: block;
    }
    .list-add {
        font-size: 65%;
        font-style: italic;
        margin-left: 15px;
    }
    
    .familie-data {
        font-size: 16px;
    }
    
    #tab1 {
        font-size: 14px;
    }
    
    #verw-neu {
        display: none;
    }
    
    ul#orte {
        height: auto;
        max-height: 450px;
    }

    a.link-delete {
        text-decoration: none;
        color: red;
    }
    a.link-delete:hover {
        text-decoration: underline;
    }

    span#familie-count:hover {
        cursor: text;
    }
    
    
    
    .w100pm400px {
        width: 100%;
        max-width: 400px;
    }
    
    .cw100p > * {
        width: 100%;
    }

    .ml15px {
        margin-left: 15px;
    }


    p.heading {
        font-size: 115%;
        letter-spacing: 0.7px;
        margin-bottom: 0;
    }

    a.button {
        border: 1px solid #a0a0a0;
        border-radius: 3px;

        background-color: #dbdbdb;
        padding: 6px;

        color: black;
        text-decoration: none;
    }
    a.button:hover {
        background-color: #c5c5c5;
    }

    button:not(.o):hover {
        border-color: #989898;
    }
    button:not(.o):focus {
        border-style: inset;
    }
    button:not(.o) {
        border-radius: 2px;
        border-style: solid;
        cursor: pointer;
    }

    table.logs {
        color: #b9b9b9;
        text-align: left;
    }
    table.logs th, table.logs td {
        padding-left: 5px;
        padding-right: 5px;
    }

    .msg-box:not(:empty) {
        border: 2px solid grey;
        background-color: #ff1010;
        padding: 2px 4px;
    }

    span.help {
         font-size: 70%;
         margin: 0;
    }
    span.help:hover {
        cursor: pointer;
    }

    span.code, p.code {
        font-family: monospace, monospace;
        background-color: #9b9b9b;
        padding: 0 0.15em;
    }
    p.code {
        padding: inherit;
        padding-left: 18px; padding-right: 18px;
        margin-bottom: 0;
    }
    p.code + p.code {
        margin-top: 0;
    }

    #tabs span.code, #tabs p.code {
        background-color: #a47272;
    }

    div.cols2, div.cols3 {
        overflow: auto;
    }

    div.cols2 *, div.cols3 * {
        box-sizing: border-box;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 10;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0,0,0);
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        position: relative;
        background-color: #fefefe;
        margin: 100px auto;
        padding: 0;
        border: 1px solid #888;
        width: 80%;
        max-width: 900px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
        -webkit-animation-name: animatetop;
        -webkit-animation-duration: 0.4s;
        animation-name: animatetop;
        animation-duration: 0.4s
    }

    .highlight {
        animation: highlight 0.4s;
    }

    /* Add Animation */
    @-webkit-keyframes animatetop {
        from {top:-300px; opacity:0} 
        to {top:0; opacity:1}
    }
    @keyframes animatetop {
        from {top:-300px; opacity:0}
        to {top:0; opacity:1}
    }

    .close {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover, .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-header {
        padding: 2px 16px;
        background-color: #ff2626;
        color: white;
    }
    .modal-body { padding: 2px 16px; }
    .modal-footer {
        padding: 2px 16px;
        background-color: #ff2626;
        color: white;
    }
    
    
    @media only screen and (max-width: 800px) {
        div.cols2, div.cols3 {
            width: 100%;
            float: none;
        }
        div.cols2:not(:first-child), div.cols3:not(:first-child) {
            margin-top: 1em;
        }
        div div.cols2:nth-child(odd), div div.cols3:nth-child(odd) {
            margin-right: 0;
        }
        
        .select-list ul {
            height: 250px;
        }
    }
    
    @media only screen and (min-width: 800px) {
        div.cols2, div.cols3 {
            width: 45%;
            width: calc( 50% - 7.5px );
            float: left;
        }
        div.cols2:not(:first-child):not(:nth-child(2)), div.cols3:not(:first-child):not(:nth-child(2)) {
            margin-top: 1em;
        }
        div div.cols2:nth-child(odd), div div.cols3:nth-child(odd) {
            margin-right: 15px;
        }
        div.cols2 > *:first-child, div.cols3 > *:first-child {
            margin-top: 0 !important;
        }
    }
    
    @media only screen and (min-width: 960px) {
        div.cols3 {
            width: 30%; /* Support for older browser */
            width: calc( 100% / 3 - 10px );
        }
        div.cols3:not(:first-child):not(:nth-child(2)):not(:nth-child(3)) {
            margin-top: 1em;
        }
        div div.cols3:not(:first-child):nth-child(3) {
            margin-top: unset;
        }
        div div.cols3:nth-child(odd) {
            margin-right: unset;
        }
        div div.cols3:not(:nth-child(3n+0)) {
            margin-right: 15px;
        }
    }
    
    @media only screen and (min-width: 1160px) {
        #tabs {
            overflow: auto;
        }
        #tab-head {
            float: left;
            max-width: 30%;
        }
        ul.tab {
            margin: 0;
        }
        ul.tab li {
            margin: 0;
            width: 100%;
        }
        ul.tab li a {
            display: block;
            
            border-style: solid hidden solid solid;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            border-top-right-radius: 0;
        }
        ul.tab li a.selected {
            border-right: 2px solid #b70c0c;
            border-bottom: 2px solid #828282;
        }
    }
    
    @keyframes opac {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    @keyframes highlight {
        0% {
            color: #ffffff;
            background-color: #ff1010
        }
        60% {
            color: #ff2222;
            background-color: #aaaaaa;
        }
        100% {
            color: #ffffff;
            background-color: #ff1010;
        }
    }
</style><?php }


function favicon() {
    return
        "data:image/x-icon;base64,AAABAAEAr68AAAEAIAAU7wEAFgAAACgAAACvAAAAXgEAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP//+wD+//YA/f/1APr/9AD1/fMA9/74APT7+QDq8/LM5e7v/+34+Gbf6+n/4O7q/9vm4//O2dT/z9nT/9Td1//W3dj/3+Xg/+Dm4f/S2tT/4+vl/9vj3f/q8ezM8/r2APH39AD4/foA/P/9APz//QD9/v0A+/38AP3+/QD//v4A//37AP78+QD+/fsA+vbzAP79/AD//v0A//v8AP/8/gD//v8A//7/AP7+/wD+/v8A/f7/AP3//wD9/v8A/f7/AP7+/wD//v8A////AP///wD///wA///8AP///AD///0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v4A/vz8APf3+wD6+vsA9/r7ANba5/+pr8j/jZez/4SPsP9zgaP/b36h/3eHqv94iKv/cICj/3aEp/96iKv/eIWp/3+Kr/99h67/ZnCY/3N+pf99iKz/hpKx/3+MpP99i5z/laWv/6SyuP/M29r/tMK//8rWz//c5t7/7/XuRPz++QD9/vgA/P/4AP38+QD///wA///8AP/8+QD//foA///7AP//+wD+/vkA+Pr0APv/+wD9//4A/f//AP3//wD+/v8A//7/AP/+/wD//v8A///+AP///gD///wA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP79/AD7+/4A+vz/APb6/wDL0+X/ipSz/2FulP9cbZn/TGGR/0xjlf9Sap3/Umug/0tkmv9PZpz/UGed/05kmv9VaKD/VGaf/0pclf9PYpr/VGWe/1hqof9VZpv/VGWX/19xn/9od6H/fYuw/3B/of+Ajan/dYGX/5agsP+/xdL/xMrS/9Xd3//d4uP/9fv6APr9/gD7/fwA+/37APv++wD9//oA/P74APj89AD7//kA/f/8AP7//gD+//8A//7/AP/+/wD//v8A//7/AP/+/wD///8A///+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///QD///oA///8APv+/wD1+/8A1d/w/5GevP9cbJP/V2uZ/1FpnP9NZ57/UGuj/01ppP9JZaH/TGWh/0xkn/9KYZ3/UGSg/09jn/9OY57/T2Wf/0xin/9OY6H/Umam/1Jkpv9QYqP/U2Sl/1Bgn/9WZqP/VGWe/0ZVif9caZn/doOt/3mEq/+Jlbf/hpKv/7C71P+0vM//0dfl/+ju9//z+f0A+P79APn/+wD7//kA/P/6APv9+AD+/vwA////AP/+/wD//f8A//7/AP/+/wD+/v8A/f//AP3//gD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///0A///7AP79+gD7/f4A9/v/AOTq+/+fqsr/YG+Z/1Rnmf9RZ57/S2Ke/0tjof9KYaD/TGKh/09jov9PYZ//T1+d/1Jfnv9OXJv/TV6b/01enP9NX53/TF+d/01gnv9MYJ7/SV6c/01iov9MYqL/T2ak/01kov9SZ6X/U2mm/1Flof9QYp//U2Sh/0VWkv9ldKr/ZXCc/4qTtf+pscz/uMDR/8nQ2f/h6Ov/5+vq//z+/AD7/PgA/v78AP///wD//v8A//3/AP/+/wD//v8A/P//APv//gD7//0A/f/+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/9AP7/+wD7+/kA+/z+APj7/wDt8f9mrbXS/2dzn/9TY5n/UWWg/05iof9LYKD/SV2d/01fnv9RYJ//Ul+c/1Jemv9TXpv/UVuZ/1Fdnf9PXJ3/UF+g/05fn/9LXZz/Sl2a/0hdmv9HXZr/SGOf/0Nemf9JZaH/S2ek/0plo/9KZKP/TmWl/1Bop/9LYqP/U2il/1Rlnf9XZJf/V2OO/1hjh/9veZb/nKW4/6etuf/f4ur/7/D0IvT19wD6+/wA/v7+AP7+/gD//v4A//7+APv//gD8//0A/P/9AP3//gD+//8A////AP/+/wD///8A////AP///wD///8A////AP///wD///8A/v/+AP7//gD+//4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//QD7//kA+v/7APv8/gD7+v8A9vj/AL7D1v90fab/U2Cc/0xfov9LYaP/RmCf/0lenf9KXpv/TV+b/05em/9MXZv/Slyc/0lcnP9KWpz/S1ud/0pcn/9KXJ//SVyf/0ldn/9IXJ7/RVye/0Vcnv9JX6D/SV+h/0lgoP9LYqH/TGKi/01hof9MYaD/S2Ce/0hfnf9FYJ7/Smej/0tkoP9MY53/T2Ka/0hXi/9MV4P/g4yw/6620P/Bx9b/3+Xr//L39wD3+vcA/v/8AP7//AD9/f0A//7+AP/+/gD9/v4A/f/+AP3+/wD+/f4A//7/AP/9/gD//v8A////AP7+/gD+//4A/f/9APz//AD7//wA+//9AP///wD///8A//7/AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9//0A/f/5APv//AD+/f8A/vv/APr7/wDL0OD/g4uz/1Rhnv9PYaX/TWOl/0hiof9KX5//S1+d/01enf9NXZz/S1yb/0lbm/9HWpr/SFmZ/0pbm/9JW53/Slyf/0lcn/9IXJ7/SFye/0Vcnv9HXZ//SF+e/0denf9HXp3/SF+e/0pgn/9MYKD/S1+f/0pfnf9KX53/S1+b/05inv9MXpz/TV+c/1Vno/9WZqH/TmCV/1dnmf9SYoz/S1l8/4GPqv+tucv/zNXe//b9/wD7//8A/f/8AP/++gD///sA+vv4APv8+wD+//4A//7+AP/+/gD9+PoA//3/AP/+/wD6+fsA/Pz8AP7//wD8//8A+///APv//wD9//8A/f//AP7+/wD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/f/9AP3/+wD6//sA/v3+AP37/wD6+/8A2N3t/5Kbwf9RXpn/UGGk/09kqP9MYqT/TGCi/0xfoP9OXp7/TV2d/0pbm/9JWpr/R1qa/0hZmf9JWpr/SVuc/0lcnP9IW5v/R1qa/0Zamv9GWpv/R1ub/0ZdnP9FXJv/RVyb/0ZdnP9IXpz/SV6c/0lenP9KX53/S1+d/0xamf9RXZ3/UF2e/01dnf9MX6D/TF+i/0hgov9MY6T/Rl2c/0phm/9Wa5//XXGc/2Fwkv97iJ//0Nrk//H4+AD9/voA/v72AP7++QD+//wA/v/+AP/+/gD//f0A//3+AP/7/AD//f4A/v3/AP/+/wD//v8A/v7/AP3//wD9//8A/f7/AP3+/wD+/v8A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//gD9//wA+f36AP78/gD8+v8A+vr/AOPo9v+dp8v/T12U/09gov9OY6f/TGGk/0tgov9MX6H/Tl6f/01dnf9LXJz/SVub/0hbm/9IWJv/SVqc/0lbnP9IW5v/R1qa/0VYmP9EV5f/Q1iW/0RZl/9GW5n/RVuZ/0VamP9FW5n/R1ya/0hdm/9IXZv/SV6c/0xgnv9NXJ3/TVud/0xanP9JWpz/Rlud/0RcoP9DX6P/RWCn/0Ziqv9IYar/Rl2i/05hof9WZ57/XGqV/3F9nP+EkKL/k5ul/+Ho6P/5/v4A+v39APz9/QD///8A//7+AP/+/QD//v0A//v7AP/9/AD//v4A//7/AP/+/wD+/v8A/f//AP3//wD9//8A/f//AP3//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A/f/9APn7+wD+/P4A/vv/APv8/wDs8f2Iq7TV/1djmP9RY6L/T2Om/0tgpP9LX6L/S16h/01doP9NXJ3/Slub/0pbm/9IW5r/R1mc/0hanf9IWp3/SFqc/0ZZmv9EV5f/Q1aW/0NXlf9EWJb/RlqY/0VamP9EWZf/RFmX/0Zamf9HW5r/R1yb/0henf9JX5//Sl6h/0danf9GWZz/R1ud/0ddnf9FXZ7/Q1yf/0BYnv9DWqP/R1yn/0pdp/9NXab/UWCk/1Nin/9QYJP/XW2X/1dmiP+Gla3/u8bW/9fd5//p7fLu+/3/AP39/wD///4A///9AP/++wD//vsA//79AP///gD///8A////AP3//wD9//4A/f/9AP3//QD9//0A/v/+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP7//gD7/PsA/vz+AP/8/wD8/f8A8vf/ALnC3v9lcaT/U2Wj/09jpP9LYaT/S1+i/0tdof9MXJ//TFud/0lamv9JW5r/R1ua/0dZnP9HWZ3/SFqd/0dZnP9HWZr/RliY/0ZYl/9GWJf/RlmX/0ZZl/9FWZf/RFiW/0VYmP9GWpr/R1uc/0dbnP9FXZ7/RV2f/0VcoP9CWp7/RFue/0ddnv9HXZz/R1yc/0dcnP9GV5r/SFmd/0lYn/9PXqX/T12m/01bpP9PX6X/TF2d/1Nmov9NYZr/UmeX/3KApf+Ik67/qrLE/93i7f/4/P8A+/3/AP7//gD///wA/v75AP7++wD///0A///9AP///gD+//4A/v/9AP7//AD+//wA/v/8AP///QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD+//0A/f78AP/8/wD//f8A/f7/APX7/wDF0OX/dYOy/1JloP9PY6P/S2Gj/0teov9LXaD/TFyf/0tbnP9JW5r/SFua/0dbm/9HWp3/R1md/0dZnf9HWZz/R1mb/0hZmv9IWZr/SFmY/0hZmP9GWJf/RViW/0RXl/9FWJj/R1qb/0dbnf9HW53/RFue/0Rcn/9EW57/Q1ue/0Vbn/9GW57/Rluc/0lbnf9LXZ7/TFqc/0tZmv9KWJv/T1yf/09eov9NXaL/TFyh/0lan/9LXaH/T2Ko/01hof9NXpX/UV6M/2Rwlf+SnLj/z9bl/+br9f/5/f4A+vz7APv8+QD+/vsA///9AP///QD///4A///+AP///QD///0A///8AP///QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A/v79AP///gD//f8A//3/AP3+/wD3/f8Az9rr/4STv/9OYpv/TWGh/0pgof9KXaH/Slyg/0xcn/9MW53/Slya/0ldmv9JXZz/SFqe/0dYnv9GWJ3/Rlic/0dYnP9JWZz/SFmb/0hZmf9HWJj/RleX/0ZXlv9FV5f/RliZ/0danP9HW53/Rlqd/0NZnf9FWZ3/R1qc/0lbnP9HWZz/Rlec/0hYnv9LWaD/S1mg/0tZnv9GVZj/S1qb/0tcmv9MXZr/TF6b/0lbmv9IWpv/SVqd/0paof9NXaP/TV2i/01dnP9FVIv/TVmH/4SOsf+ttcv/z9Tb/+ru8sz4+/wA/v/+AP///gD///8A//7/AP/+/wD//v8A//7/AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/+AP39/AD///4A//7/AP79/gD8/v8A9/7/ANfi8v+SoMr/TF+V/0xgnP9JYJ3/SV2f/0lcn/9LXJ//TFyd/0pdm/9KXZv/Sl6d/0hanf9HWJ7/Rled/0ZXm/9GV5v/SFeb/0hYmv9HWJn/RleY/0ZXmP9FVpf/RVeZ/0ZYnP9HWp7/SFuf/0danv9EWJ3/RVid/0lYmv9MWZv/S1ic/0pXnv9KV6D/R1Sf/0NRnP9GV6D/SFqf/0lcnf9JXpv/SFyX/0hclf9KXpf/SVuW/09enf9KVpn/S1id/0tao/9KW6T/TF2g/1Ffmf9UYY//XmiK/2p1iP/DytT/6O/y//f7/AD9//8A/v7/AP/9/wD//f8A//3/AP/9/wD+/v0A/v79AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP///gD7/vwA+/79APf8/wDk6vz/rbTd/1Rgkf9PY5P/S2KX/0hgnP9FXp//RV6e/0henP9JXZz/Slqd/0pYm/9IWZn/R1iY/0dYmP9HWJj/RleY/0ZWmf9HV5r/Rlic/0ZYnP9HWJ7/RVac/0VXnf9GWJ7/SFqg/0lbof9IWqD/R1mf/0dZn/9IWJ3/SVid/0dXnv9GVp//RVaf/0RVn/9EVZ//Qlef/0NZnv9FW5//RV2e/0Zcm/9GWpr/RluZ/0damv9IW5v/SVmc/0lamv9NYJz/SV2Z/0lcnP9OYab/TF+h/1Zqof9WbpT/YXiK/6O5v//b6+3/8/z+APn7/wD++v8A//r/AP/9/wD//v0A+/71APz+9gD+//oA///9AP///wD///8A////AP///wD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD///4A/f/9APv//gD4/f8A7PL/iL7F6P9ocqL/VmiZ/01jmv9HXZ3/RF2f/0hfn/9LX53/S12e/0tan/9MWZ7/S1ue/0pbm/9JWpr/SFmZ/0dYmf9HV5r/R1ea/0dWm/9HVpv/Rled/0VWnP9EVZv/Rled/0hZn/9JWqD/SFmf/0hanv9HWZ3/SVqd/0panf9JWZ7/R1if/0dYn/9HV6D/SFih/0haov9IWqD/R1qf/0dbnf9HW5z/R1qa/0hbm/9IW5v/SFub/0lZnP9JWZz/Slqe/05eof9SZKX/UWWj/0ldmf9QZKD/Wm6l/1Fjkf9YZ4v/k5+2/87Y5P/t8/hm+/7/AP3+/wD++v8A/fr+AP79/QD+//wA/v78AP7+/gD///8A////AP39/QD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP79/wD//v8A////AP3//wD8//8A+f7/APH3/wDM1O3/eoSy/1hpnf9NYZz/R1yd/0Rcn/9IX5//S1+d/0pdnf9LWZ7/TFme/0tbnv9KW5z/SVmb/0hYmv9HV5r/R1ea/0dXmv9GVZr/RVSZ/0dWm/9FVZr/RVWa/0dWm/9IWJ3/SFqe/0dZnf9HWZz/R1mc/0hZmv9IWZr/SFib/0hXnP9IV57/R1eg/0hYof9JWqD/SVqg/0hZnv9HWZ3/R1mc/0dZnP9IWpz/Slqd/0panf9KWZ7/SVie/0tYoP9KWJ//Slqd/0xenP9NYZ7/S16h/0xdo/9SYKb/V2Oj/2FrnP9rdZX/i5am/8/X2//6//0A/f7+APz8/QD+/P4A//z+AP/9/gD//v8A////AP///wD+/v4A/f39AP7+/gD///8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP///wD///8A+/79APj9/QD0+v8A2eDw/4uWwP9WZZ3/Tl+f/0lcn/9HXKD/SF6e/0penP9KXJ3/Slme/0tYnf9KWZ3/SFib/0dXmv9GVpn/R1aa/0dXm/9HV5v/R1ab/0ZVmv9HVpv/RlWa/0ZVmf9HV5r/SFic/0hZnP9IWJv/SFma/0hZmv9HWJj/R1iX/0dXmf9HV5r/R1ec/0hXnv9JWJ//SVmf/0lZn/9JWJ3/SFid/0hYnP9IWJv/SFic/0lZnf9KWZ7/Slig/0pYoP9OW6P/Tlyj/0tan/9KW57/TF+i/0hbn/9GWZ//UGCq/1Fgqf9VYaP/WGGX/2Fskf+Dj6D/tb7C/+zy7oj7/foA/vv+AP76/wD/+/8A//7/AP///wD///8A////AP7+/gD///8A////AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v3/AP/+/wD//v8A////APz9/AD5/f0A9/z/AOHo9f+fqdD/U12Y/09cof9MXaL/SF2g/0ddnP9JXpr/Sl2c/0tanv9LWZ3/SVmc/0hYm/9HV5r/R1ea/0hXm/9IWJz/SFic/0hXnP9HVpv/SFec/0dWm/9HVpr/R1ea/0hYm/9IWJv/R1ia/0dYmP9HWJj/SFmY/0dYl/9HV5j/R1ea/0hXm/9IV53/SVie/0lYnv9JWJ7/Slme/0lYnf9JWJz/SFib/0hXm/9JWJ3/SVid/0pYof9KWKD/Slif/0tan/9LWqD/SVmg/0han/9IXJ//S1+h/0xgpf9JW6H/Slqg/05cnf9VYZb/X2uR/3J/k/+ep6//6O7w//r8/gD9/P8A/v3/AP7+/gD9/f0A/f39AP///wD///8A////AP///wD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP38/wD//v8A//7/AP///wD9/vwA+/78APj+/wDo8Pr/srzf/1FZlv9QWqL/TV2i/0ldn/9HXZr/SF6Y/0pemv9MXJ7/S1md/0panP9IWZv/R1ma/0dZmv9IWZv/SVmc/0lZnP9JWJz/SFic/0hYnP9IV5z/R1eb/0hYm/9IWJv/SFib/0dXmv9HV5j/R1iY/0dZmP9HWZj/R1iZ/0dYm/9IWJz/SFed/0lYnv9JWJ7/Slmf/0pZn/9KWZ7/SVic/0hYm/9HV5r/R1ec/0hYnf9JWKD/SVig/0hXn/9JWZ//S1yg/0lbn/9FWJn/SFuc/0penv9GW5z/R1ud/0dbnf9KXJ3/UGCc/1Zjl/9YZI7/XmiH/6ixw//d5Ov/9Pr6APz//gD+/v4A/f39AP39/QD///8A////AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/f4A//7/AP/+/gD///4A/f77APv++wD4//0A7fb9ZsPN6/9YX5v/U1yk/0xcof9IXJz/R12Y/0helv9JXpf/S1yc/0panP9KW5v/SFua/0hbmv9IW5r/SFqb/0hZm/9IWZv/SFmc/0lZnP9JWJ3/SFec/0hXnP9IWJv/SFib/0hXm/9HVpr/RlaZ/0ZXmf9GWJn/RlmZ/0ZZm/9HWZz/R1id/0dXnv9IV57/SVie/0pZn/9LWp//Slme/0hYnP9HWJv/Rleb/0VXm/9GWJz/Rlef/0ZXn/9IWaD/SVug/0pdn/9IXJv/RFiV/0dZmv9IW5v/RFeZ/0henv9LYJ7/S2Gd/01hnf9PYJz/VGOc/1Rdkf9lb5b/kp2y/9Ha4v/0+vwA/P7/AP///wD+/v4A////AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD//v4A///9AP7/+gD8//oA+P/7APD7/QDR3PP/Z26q/1hhqv9MW6H/SFyb/0lfmP9JX5b/SF2V/0tamf9KW5v/Slua/0ldm/9JXpv/SV6c/0hcm/9HWpr/R1ma/0hanf9JW57/SFid/0hXnP9IV5z/SFec/0hXnP9IV5z/RlWa/0ZVmv9FV5r/RFaZ/0RYmf9GWZv/R1md/0dYnv9GVp//Rlaf/0lYnv9LWqD/Slme/0lYnf9IWJz/Rlib/0ZYm/9GWJz/Rlic/0RXnP9DVpv/Q1aa/0NXmf9EWJn/RVeY/0VXmP9KWZ3/Tl6h/0tdn/9IW5z/RlqY/0Zdmf9KYZ7/TmKg/09hn/9VZJ3/WGSX/0FMdP+PmLT/2+Dv//X3+wD+/v4A////AP///wD9/f0A/v7+AP///wD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/wD9/f0A/v7+AP///AD///sA/f/7APn/+wD0/fwA3uj2/3qAuv9fZ7D/T12j/0tenf9MYZz/S2GZ/0lelv9KWpj/TF2c/0ldm/9JX5v/S2Cd/0tfnv9JXp3/SFyc/0hbnP9KXKD/S12h/0dZnf9GV5z/Rlec/0hXnP9IV5z/R1eb/0dWm/9HV5v/Rlib/0RXmv9EWZr/Rlqc/0danv9HWZ7/R1eg/0ZXn/9JWZ//S1ug/0lZnv9IWJz/R1mc/0dZm/9HWpv/Rlqc/0VZm/9AVpr/P1aY/0FZmP9DWJj/RFea/0hZn/9NW6T/TFmg/0pYnP9LXJ3/SFqa/0dbnP9IXZ//SF6f/0ddmv9IX5f/UGWa/1NlmP9YZJr/XWSW/5mdvf/g4ez/9/f4AP7//wD///8A/f39AP7+/gD///8A/v7+AP7+/gD///8A////AP///wD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////APz8/AD+/v4A////AP///wD///8A/f7+APv9/wD5+v8A+/3/APr7+wD+/vsA///5AP//+gD8/vwA9vz/AOLt9v+Ml8H/VGSc/09go/9LYan/RFuj/0Vcov9OZKT/TWKd/0tgmv9LX53/TWGg/09ho/9OYKP/S12h/0haoP9IWqH/Slul/0tcpv9JWqT/SFqi/0haov9HWqD/Rlic/0VXmf9GWpn/SV2b/0pbmv9JVpz/S1if/0pYnv9GV5z/RFib/0Vcnv9DXJ7/P1ia/0Jbnf9DWp3/QVeb/0FWm/9EV5z/Rlmd/0Vanf9AWJf/PVeW/z5Yl/9BWJn/RVmb/0ZZnf9HWp7/R1qe/0hbn/9IW5//R1qe/0VYnP9FWZz/R1ud/0ldn/9KXqD/Sl6g/0peoP9NYaL/T2Ol/1Rmof9mc5//nKTB/+Xq+f/3/P8A+/7/AP///wD9+foA/vv9AP/9/wD//f8A/v7/APr//QD6//sA+v/5AP3/+wD+//0A//7/AP/9/wD//f8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A+/v7AP///wD///8A/v7+APv7+wD8/PwA/v7+AP7//wD8/v8A+/z/APr8/QD+/v0A///8AP79+AD//foA/f3+APn6/wDq7fnMnqXR/1xmn/9MWJv/T1yl/09cqP9NWaX/Slaf/0VQlv9FUpj/Tl+n/0xhq/9LYqr/S2Gp/0xgqP9MYKj/TWCn/05gp/9OX6b/T2Gm/0lboP9JWZ7/TVqg/05bof9NWJ//Slab/0tWnP9MV53/Slec/0dVmv9JWZz/TF2f/0hcnP9DWJf/RFuZ/0pfn/9GWpr/R1ib/0dXm/9HV5v/SFid/0lZnf9FWJz/Q1WZ/0FVmP9DV5r/RFib/0ZZnf9HWp7/SFuf/0hbn/9IW5//SFuf/0danf9FWJz/Rlqc/0hcnv9JXZ//Sl6g/0ldn/9JXZ//S1+h/01ho/9QY6D/VmOW/2lyl/+Zorv/3OPw//T6/wD8/v8A/v7+AP/9/gD//P4A//z+AP/9/wD+/v8A/f/9AP3//AD9//wA/f/9AP79/gD//P8A//3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP39/QD7+/sA////AP///wD+/v4A/v7+AP///wD9/v4A+v37APz+/AD5/fcA/v/5AP//+QD//vgA//76AP7+/QD7+/8A8fT9ANTZ9P+psdT/nafR/6Gs2/+lr+H/pa3h/6Oq3v+fpdf/m6TW/5qm2/+Nn9X/gJLJ/3KDvP9mdrP/XWus/1ZkqP9QXab/S1ej/01Zpv9OWab/Tlmn/01Zpv9NWaX/TVml/05bpf9NW6T/R1ad/0hXnf9HWJz/Rlib/0ZZm/9GWpv/SF2c/0penv9LXZ3/SFqa/0pYmf9KV5n/S1ea/0xXnP9MV53/SVWd/0RTmv9DUpv/RVWd/0ZXnf9HWZ7/SVuf/0lcoP9JW5//SFqe/0hanv9HWZ3/Rlic/0hanf9KXJ//S12g/0pdoP9JXJ7/Slyf/0tdoP9MXqH/TV+f/1Jimv9YZpX/aHWZ/5iivP/l7Pr/9vv+APz//wD+//4A//7+AP/9/gD//f8A//7/AP7//wD+//4A/v/+AP7//gD+//4A/v7/AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/Pz8AP///wD///8A////AP///wD///8A/v7+APv+/QD9//0A+/76APz9+QD+/voA//36AP/++wD///0A/v7+APr7/gDx9PsA5Ovz/+Ho9P/h6vj/4ev6/+Lr+//k6vv/5Of4/+Dl9//i5fj/3OH0/9Xa7//M0ej/w8jj/7m+4P+ssd7/naPb/5CW1P99hMf/Z3G3/1Vgq/9MWaX/SVil/0laqP9IXKn/R1yp/0Vapf9CV5//Rlmg/0RZn/9BVpz/Qlec/0hcof9JW5//Rleb/0lZnP9LWJv/S1eZ/0xWmv9MV5z/S1ec/0lUnf9DU5z/RFOd/0VWn/9FVp3/R1id/0lbn/9KXKD/SVuf/0dZnf9HWZ3/R1md/0dZnf9JW57/Slyf/0tdoP9KXJ//SVue/0lbnv9KXJ//S12g/0tcnv9PYKD/UWKd/1JjlP9ndJn/l6O8/9zm8P/0+v0A/P7+AP7//QD//v0A//3/AP/+/wD//v8A//7/AP/+/wD+//8A/f/+APz//gD9//4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/f39AP7+/gD///8A/v7+AP///wD///8A/f39APz8/AD8/P4A+/z+APz9/gD8+/0A/vv9AP/7/QD//P0A//z+AP/8/gD+/v4A+/z7APv//QD7//0A+v/+APr//gD7/v4A/P7+AP3+/wD7/P8A/v3/AP79/wD+/P4A/fz9APz7/AD39/0A7/D8IuXn/f/a3/r/xs7u/7C64P+bptX/iJXM/3SDwf9eb7T/TV+o/0ZZpv9GWab/R1un/0lbpf9HWaP/RFij/0NYo/9FWaX/R1ul/0hYo/9GVp7/SVed/0pXm/9JVpr/SVea/0hXm/9HVpz/RFWc/0RVnv9EV57/RFac/0ZYnf9IWp7/SVuf/0hanv9HWZz/R1mc/0dZnf9HWZ3/SFqe/0lbnv9JW57/SVue/0hanf9IWp3/SVue/0pcnv9JW57/Slyh/01fov9QYp7/VWaX/2Bwk/+dqsD/3OXv//X6/QD8/v0A/v7+AP/9/wD//f8A//z/AP/8/wD//f8A/v7/AP3//gD8//0A/P/9AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD///8A/f39AP7+/gD///8A////AP39/QD7+/sA/Pz9APr7/gD9/f8A+vn8APz7/QD+/P4A//z+AP/8/wD/+/8A//7/AP/+/gD///4A/fz7AP79/AD//v4A//7+AP/9/QD//f8A//7/AP/9/wD//f4A//39AP/++wD///oA///5AP//+AD9//oA+//7APf9/ADy+f0A6fH+7tji+v/By+v/p7DY/5GZyP98g7//aXOx/1dlqP9NXaL/SVqi/0lapf9GWab/RFin/0dcqv9LXKr/Q1ah/0dYoP9HWJ3/Rleb/0VXmf9GWJr/Rlic/0VZnP9EV53/Qled/0RWm/9FV5z/R1md/0dZnf9GWJz/Rlic/0ZYnP9GWJz/R1md/0dZnP9HWZz/R1mc/0dZnP9HWZz/SFqd/0lbnv9JW57/SFue/0dZn/9KXKP/UGSm/1Jln/9UZ5b/YXGT/56qwf/a4u3/9vv+AP3+/wD//v8A//3/AP/8/wD/+/8A//z/AP7+/wD9//4A+//8APv//AD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A////APr6+gD+/v4A/v7+AP39/QD9/f0A/v7+APz9/AD6/fsA+v36APf6+AD6/foA/P/7AP3//AD+//wA///8AP///AD+/fsA//79AP77+wD+/P0A//7/AP/+/gD//f4A/v3+AP79/QD9/f4A/v7/AP7//wD+//4A/v/7AP/++QD//vgA//74AP/++AD+/vkA/v77AP3+/gD7/f8A+vv+APv6/QD19fwA3Nzz/7a62P+Hjrn/cXyx/2Fuq/9TYqP/Sluh/0hapP9HXKj/Rlun/0Vbpv9GW6T/RFug/0RZnP9EWJr/RFiZ/0VZm/9GWZz/RFid/0JXnP9EVpv/RVeb/0ZYnP9GWJz/Rlic/0VXm/9FV5v/Rlic/0ZYnP9FV5r/RVea/0ZYm/9HWZz/Rlib/0dZnP9JW57/SVue/0lcn/9IWp//SFuh/0tfo/9NXqH/UGOd/1Bgkf9ZaIz/n6vE/93l8f/3+/8A/f7/AP///wD//f8A//3/AP/9/wD//v8A/v//APv//QD7//wA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP///wD8/PwA/v7+AP39/QD7+/sA+vr6APLy8gDi4+P/5+nq/+Ll4//1+fcA+v75APr/+AD5//cA+//4AP7/+AD+/vkA/Pz3AP/+/AD//v4A//3/AP/+/wD///0A/v/8APz++gD6/vkA+v78APv//gD7//8A+///AP3+/wD+/f8A/v3/AP/8/wD++v8A//z+AP/7/QD//f4A////AP7+/AD+/voA/v75AP7//AD7//4A7vP/RNLa9v+osdj/fIa2/15spP9VZKT/TmGl/0hco/9JX6f/R16l/0Vdov9FXJ7/Rlqb/0VZmv9GWJv/Rlic/0dXnf9GVp3/Rled/0ZYnf9GWJz/Rlic/0VXm/9EVpr/RVeb/0VXm/9EVpr/RFaZ/0VXmv9HWZz/SFqd/0dZnP9IWp3/SVue/0tdoP9LXaD/TF+i/0lcoP9GWZ3/Tl+l/0xcoP9NXpr/WWeZ/2Fvkv+hrsP/4+v0//b7/AD+//0A////AP/+/wD//f8A//7/AP7+/wD8//8A/P/+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+APv7+wD///8A/Pz8AP7+/gD///8A////AO7u70S1tbj/amlz/29qgf97eJD/1tbp//T1/QD6/f8A+v7/APv+/wD9/v8A/f3/AP/8/wD/+v8A//v/AP75/wD++v8A/v3/AP3//gD9//sA+//7AP7+/gD//v8A//7/AP79/wD+/f8A//z/AP/9/wD//f8A//3/AP/9/wD+/P8A/v3/AP3//wD7//8A+///APr//wD4//8A9f39APn8/wD3+/8A8vb/ANjf9/+ns9z/cIC0/1Fjn/9NYaL/T2Sp/0lhpf9GX6L/SF6h/0hcnv9HWJz/R1ab/0lVnP9MVZ//Slaf/0lXnv9IWZ7/R1md/0ZYnP9FV5v/RFaa/0VXm/9EVpr/Q1WZ/0RWmv9GWJv/SVue/0pcn/9KXJ//SVue/0tdoP9MXqL/TmCh/0xfn/9IW5z/R1me/0tdpf9LW6T/T16j/05el/9WY43/XmuF/5qmsf/r8/Sq+Pz4AP///AD///4A//39AP79/gD//v8A/v7/AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///9AP3//gD9//8A/f7/AP39/wD+/v8A//7/AP/+/wD//P4A//r+AP/9/wD7/v8A9f7/AOLx+f+gr8n/W2iU/1Zjmv9daqT/XGqZ/2Nwjv/M2OD/9/79APz/+gD+/fkA/fr8AP/9/wD//f8A//7/AP///QD+/v0A/f3/AP39/wD+/v4A///7AP//+QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/v//AP7+/wD+/P8A/v3/AP7+/QD6/PwA8fT7AN7i9f+7v+f/hIjH/1Nbpf9QXqr/S16o/0Veof9FYJ//RF+e/0Vanf9HWJ7/SVSg/0tVo/9MWaL/TFyh/0lbn/9FV5v/RVeb/0dZnf9GWJz/R1md/0hanv9HWZ3/R1md/0dZnf9JW57/Slyg/01fov9OYKP/TmCk/01fo/9JXqD/RV2d/0Rem/9FYJr/R2Sa/01mnv9PZJ//U2Gg/1hgmv9haZH/kpqt//D3+wD9//8A///8AP39+gD+/vsA/v7+APz9/wD9/f8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///gD9//4A/f//AP3//wD9/v8A/v7/AP/+/wD//v8A//3+AP77/QD9/v8A+f3/APD6/wCxwNH/cYGk/1Zmm/9WZqL/WWim/1xroP9VZIX/lqOy/93l6P/7//sA/f76AP/9/gD//f8A//7/AP///gD///wA/v/9AP3+/wD9/f8A/v7+AP///AD///sA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A//7+AP///gD+/v4A/f3+APv8/wD2+P4A6u/7zMzR8/+rtOL/hZHM/2Bws/9QYqf/TGGl/0Nbnv8+VZf/SV2g/0tbov9JWaH/SFmf/0ZYnP9GWJv/SVue/0pcn/9HWZz/Rlib/0dZnP9IWp3/R1md/0dZnf9HWZ3/R1md/0lbn/9KXKD/S12h/0tdof9LXqL/SVyf/0ZcnP9EXZr/Rl+Z/0Vhmv9KYqD/TmKj/1Jio/9XY5//W2OU/3B3mv+yt8r/9vj/APz//gD9/vwA/v/9AP79/gD+/f8A/v7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD///8A/v//AP3//gD9//4A/f//AP7+/wD//f8A//3/AP/+/gD//vsA/P/+APX8/wDa5fT/i5m5/1dolv9WZ6H/VGak/1Njof9dbKL/XGuQ/3iFnP/H0Nf/9/v7AP7+/QD//f4A//v9AP/+/wD///4A///8AP7//QD9/v8A/f3/AP7+/gD///wA///7AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP7//AD+//wA/v3+AP77/wD9/P8A/f3/APv9/QDy9vwA5u76/8zW8f+hrNn/c366/1JfpP9JWaH/TF+m/0xgpv9GXaD/SV6g/0tfoP9KXZ7/SFuc/0dam/9EVpj/R1mc/0xeof9HWZz/Rlib/0dZnP9HWZz/R1mc/0ZYnP9GWJv/R1mc/0hanf9JW5//Slyg/0pcnv9IW5v/R1yb/0ddm/9FXZ7/Rl2j/0lfpf9PYqT/VWah/1llnP9ja53/ipCz/8/U4//2/P0A+/7+APn7+wD8/P0A//3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//v8A//7/AP///wD+//0A/f/9AP3//QD+/v4A//3/AP/9/wD///4A///7APr//AD0/f4Aw87n/3yItf9UY5z/UmSi/1BjpP9QYqH/W2uj/2Bum/9seJb/tL3M/+7z9kT9//8A//3+AP/7/QD///8A///+AP///QD+//4A/f7/AP39/wD+/v4A///9AP///AD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8/v0A/f/9AP7+/gD//f4A//3+AP/+/QD//voA/f/4APv/+wD0+f4A3uX1/7i/5P+Kkcr/Y22y/1Jgqv9QYa3/SGCk/0hhof9IXp3/SFyb/0lcnP9HWpr/Q1aW/0VXmf9KXZ7/SFqc/0ZYmv9GWZr/R1qb/0dZm/9GWJv/RVea/0VXmv9HWZz/SFqd/0tdn/9LXZz/S1ya/0lcnP9JXJ//SFyi/0dcpv9IXab/TGKj/1BkoP9TZKD/WGWg/2t1pP+Xob3/6PD6//T6/QD4+/wA/f39AP/+/gD//v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//3/AP/9/wD///8A///8AP7/+wD+//wA/v7+AP/9/wD//v4A///9AP7/+gD4/vkA9f7+AL3H5P95hLj/VWSi/1BipP9MYaP/TmOh/1dpo/9ebJ//ZG+W/6WtxP/k6PD//P7/AP78/gD+/f4A////AP///gD+//4A/f/+AP3+/wD9/v8A/v7/AP///gD///wA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A/P7+APz+/wD+//4A///+AP///QD//v0A//37AP//+QD///kA/f77APv9/QDv8/8i0NX0/6Go2f9yfcD/WGew/01ipv9IXqH/SF2d/0hcm/9IW5r/SFyb/0hbm/9EWJf/RViX/0lcnP9HWpr/RlmZ/0damv9HWpv/Rlib/0VXmv9FV5r/Rlib/0hanf9KXZ7/TF6b/0xdmf9LXJv/SVug/0hbo/9JXKX/R1+k/0dhof9KYZ//TGKf/1Binv9WZpr/b3yj/7nE3P/p8ffu+f39AP7//QD//vwA//79AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/9/wD//v8A////AP///QD///wA///8AP/+/gD//f8A//7+AP///AD+//kA+P76APX9/gDH0e3/fIe4/1Jin/9RZKf/S2Cj/0tioP9TaKP/WWih/2Bqmv+Smrr/1drm//r8/gD8/P0A///+AP///wD///4A/f/+AP3//gD9//8A/f7/AP7//wD///4A///8AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3+/wD8//8A/f//AP///gD//v4A//7/AP///gD//v4A///8AP/9+gD//v0A/P3+APT4/gDj6fv/usPs/4OQyP9gbrD/Slyg/0xeoP9NYJ//SV2b/0dbmf9KXpz/SFya/0VZl/9KXZ3/SFub/0damv9IWpv/SFqc/0dZnP9GWJv/Rlib/0ZYm/9HWZz/SFuc/0tfmv9MYJn/Sl2a/0dZn/9HWaH/SF2g/0dgn/9GYaH/RmCh/0dhnv9LY5z/U2ec/15un/95har/ydTg/+/3+CL8//wA///7AP///AD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//f8A//7/AP///wD///4A///9AP///gD//v8A//3/AP/+/wD///4A///9APv//wD1/v8A1+H4/4qXv/9XZ53/UmSl/0tho/9KYp//UGai/1ZkpP9jbKT/d32l/7vA0f/3+/4A/P7+AP///AD///8A////AP3//gD9//8A/f//AP3//wD+//8A///+AP///QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//4A/f/+AP7+/wD+/v8A/v3/AP7+/wD+/v0A//79AP/+/QD//v4A//3/APz8/QD4+/0A9fv/APD3/gDX4f//maDT/2Rtqv9NXJv/TV+d/01hnv9GWpj/RFiW/0xgnv9KXpz/R1qa/0lcnP9JXJ7/SVue/0hanf9IWp3/R1mc/0ZYnP9GWJz/Rlic/0dZm/9JXZv/S2Cb/0ldm/9GWZ7/Rlme/0ddnf9KYZ3/SWGi/0hho/9HYZ//S2Sc/1Zso/9XZ6D/WGWU/4SPq//Q2uX/9fv8AP3/+wD///wA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD///4A////AP/+/wD//v8A//7/AP/+/wD///4A//7/AP/+/wD7/v8A9fv/AOTv/P+jssz/Z3ej/1Fjn/9OY6T/TGii/01loP9TYaX/X2em/1thjv+epb3/7/T9Ivv//gD9//wA///+AP///wD9//4A/f//AP3//wD9//8A/v//AP///wD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//39AP7+/QD//v4A/v7/APz//wD7//8A+//+AP3//gD+/v4A/vz9AP/9/gD//v8A////AP3+/gD3+vwA9/v/AOXp//+nsNT/ZXKk/0hZlf9JXZv/TGCe/0dbmf9LXp3/TGCe/0JVlf9JXJz/S12f/0lbnv9IWp3/R1md/0dZnf9HWZ3/SFqe/0hanv9IW57/SV2f/0lfoP9HXp3/SFye/0dbnf9JXZ//S16h/0tgov9MYqL/S2Kg/0dfmv9EWpT/TF6a/19upP87RXH/maG//+Dn8f/2/PsA/P/8AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///+AP///wD//v8A//7/AP/+/wD///0A//78AP/8/QD//f8A9/f+APf8/wDw/f8ArsHO/2x+o/9TZZ//UWan/0xoov9MZ6T/WWiv/1pkpv9kaZr/qK3K//D0/wD7//0A+v35AP3//gD9//8A/f/+AP3//wD9//8A/f//AP7//wD///8A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/8/wD//f8A/v/9APv/+wD5//oA+P/7APj//gD6//8A+v/+APz//AD+//sA//78AP/+/QD//v8A/v3/AP39/wD6/f8A6/P8qrfE6P9rfrX/RFiW/01hn/9QY6L/Q1aW/01goP9JXJz/SFub/0lbnf9IWp3/R1mc/0ZYnP9GWJz/SFqe/0pcoP9LXKH/S1yi/0ldo/9EXaP/RF6g/0lgn/9KYaD/TF2l/0xcpv9MX6P/TGCh/0xhov9LYKL/SF2d/0xgnv9UY53/VFyT/1phi/+vt8r/8Pf5APn++wD+//4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9/f0A/Pz8AP7+/gD7+/sA////AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9//4A/f/+APz//wD9//8A//3/AP/9/wD//v8A///+AP///AD9/vwA+vz7AP39/wD+/v8A+Pv/ANLa6/+HlLz/TmCc/1FmqP9QZKP/UGWj/1Vmp/9VY5//VGCP/4eSsf/U2uj/+/7+AP7//QD//v8A//7/AP/+/wD+/v8A/f//APz//wD8//4A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//4A/v/+AP7//gD+//8A/v//AP7//wD+//4A///+AP///gD///8A////AP///wD///8A/v//APr+/wDu9vtEws3g/36Ltf9LWZr/R1ii/0Raof9GYKL/R1ue/0hbnf9LXJv/S1ya/0pcnf9JXaH/RVug/0FYnf9FWp3/Slyd/0tcnf9LXaD/Sl6i/0hdoP9IXJ//SV6f/0ldoP9KXKH/Slyh/0pcov9KXKP/S1yk/0tcof9LW53/U2Kb/1hkk/9XY4f/k563/9bh6//4/v4A///+AP/+/gD9+v8A/fz/APv6/wD7/P8A/P//APv//AD6//gA/f/6AP3/+gD///8A//7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD7+/sA/v7+AP///wD///8A/v7+APz8/AD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/+AP3//gD8//8A/f//AP/9/wD//f8A//7/AP/+/gD+//wA/f/9APv9/AD//v8A//7/APv8/wDl6fn/mqXM/1Jkn/9RZaf/UmWl/05ioP9UZ6b/V2ij/1Zlk/+GkbL/0dfn//v8/gD9+/sA//7/AP/+/wD//v8A/v//APz//wD8//8A/f/+AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A+fz/AOvx+KrBy+T/f4y+/0pcmv9LX6T/SWKm/0ldn/9HW5v/Slyb/0pcnP9IW57/SF2h/0dfpP9GXaL/Rlud/0lZmv9KWZn/Slud/0lcoP9HW5//R1qe/0hbnv9IXJz/SFuc/0hanv9IW6D/SFqh/0hZof9IWp//Tl+f/09gmf9XZJX/XWqS/3WCof+2wdP/9Pr/AP7//gD//v0A/v3/AP37/wD8+/8A/P3/APv9/QD6/fsA/P/6AP7/+gD+//sA//7/AP/+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/f39AP7+/gD///8A/v7+AP39/QD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A/f/+AP3//wD//f8A//3/AP/+/wD///0A/v/9AP3//gD8//0A//3+AP/9/wD9/f8A8fT+AK642P9hcqn/U2el/1Ropv9NYZ7/TmGh/1Vnof9ca5r/c4Ch/7S7y//7/P4A/v39AP///gD///4A//7/AP7//wD8//8A/P/+AP7//gD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APz8/wD5+/8A6/H6qsLP5v+Ckrv/SVqY/0xgpP9MYJ//SV6c/0penv9MX6L/S1+k/0hfpP9FXaH/R1yf/0danP9JWJn/S1mb/0tanf9JWp7/R1md/0dZnP9HWpv/SFqa/0hamf9HWpv/SFud/0hbn/9IWp//R1qe/01foP9OYJv/UF+W/1pnlv9dapP/hpGw/8/V4v/7/fwA+vr1AP38+wD+/f8A/fz+APz8/QD8/PwA/f78AP7/+wD///0A///9AP/+/wD//v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f39APr6+gD+/v4A/v7+AP7+/gD+/v4A/f39AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f//AP3//gD9//8A//3/AP/9/wD//v4A///9AP7//AD7//4A/P/+AP/9/gD//f8A/v3/APf6/wDDzeX/eIi5/1Roof9UaKL/T2Sf/0xgnv9RY57/Wmyc/2d1mP+iq7z/+v3/AP///wD///4A///+AP///gD+//8A/P/+APz//QD+//4A////AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//P8A/fv+APn7/gDt9vpmxNPl/3aEsf9PYJr/TV+b/0xfn/9JXZ//SV2i/0phpf9IYKP/RVyf/0danv9IWZ3/Slib/0xanf9MWp7/SVme/0dYnP9HWJv/R1qZ/0lZmf9JWZf/SFqZ/0lbm/9JXJ3/SVyf/0hbnv9JXJ7/TF+d/0xdmf9SYZj/VGOX/2Numv+Tm7T/297h//r79QD9/PsA///+AP79/QD9/PwA/v39AP7+/AD+/fsA////AP///wD+/v8A/v7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+APr6+gD5+fkA////APz8/AD///8A////APz8/AD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9//4A/f//AP/+/wD//f8A//7+AP///QD9//0A+f79APv//wD//f8A//7/AP78/gD6+/8A1t/y/5KizP9Wap3/U2if/1Noov9PZKL/UGSe/1dpm/9odpv/oaq///H1+gD+/v4A//7/AP///gD+//4A/f/+APz//QD8//0A/v/+AP///wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//3/AP77/QD8+/wA+v79AO32+mbE0Ob/d4Wu/1FgmP9LXZ//SF2h/0Vdof9FXqL/Rl6h/0heof9KXKP/SVmg/0pYnf9MW57/TFuf/0hYnf9GWJv/SFia/0hZmP9JWZj/SVmW/0lZl/9IW5n/SFyb/0hbnf9HW53/R1ud/0dcnP9NX5//TV+d/1Rjof9WZJn/bnia/7S5xf/7/v0A+/z8AP39/AD//v0A///9AP/+/QD+/fwA/vz8AP/+/wD//v8A/f7/AP3+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////APz8/AD7+/sA/v7+AP7+/gD8/PwA/v7+AP7+/gD9/f0A/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f/+AP3//wD//v8A//7/AP///gD+//4A/P/+APr+/gD7/v8A//z/AP/9/wD//P0A/Pz+AOjv/f+rutz/XG+d/1Nqnv9SaaH/UWak/1Jlo/9XaJ3/ZHGa/5Weuf/i5vL//P3/AP///wD///4A/f/+APz//QD7//wA/P/8AP7//QD///8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//f0A//39AP3+/gD5/v0A8/z9AMXT6f9ue6v/T1+f/0xho/9KY6X/RmGi/0dfof9KX6P/S12l/0hZov9KWJ7/S1uf/0pbnv9HWJz/Rlia/0dXmv9IWJn/SliY/0pYl/9IWJf/R1mY/0damf9HWpr/R1ub/0hdn/8/Vpf/TWCj/0tdoP9KXJ//UmKe/2t4o/+gqcD/7PP3iPT4+gD5+/oA/v79AP///QD//fwA//39AP/+/wD//f8A/v7/AP3//wD9//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A+/v7APX19QD8/PwA////AP39/QD6+voA/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A////AP///wD9//4A//7/AP///wD///4A/v/+APz//gD7//8A+v3/AP/7/wD//f8A//7+AP3+/gDz+f8Awc7k/22AqP9WbJ3/S2Kb/0tiof9QZaT/Vmeg/2Jvnf+Rmrr/2t/v//j6/wD//v8A///+AP3//gD8//0A+//8APz//AD+//0A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///+AP/+/gD//v4A/v/+APv+/ADs9f2It8Lm/2t7sf9MX53/SmGf/0tkpP9PZKf/Sl6k/0RVnf9IWKD/Sl2i/0hcn/9HW53/R1ud/0ZanP9IWJv/SVmc/01Zm/9MWJr/SleZ/0hYmf9HWJj/RliY/0hcnP9IXJz/QVmY/0ldoP9JXKD/RVeb/01enf9ba57/doGk/7zG2P/x9/0A9/z+AP3+/gD///wA/vz6AP77+gD//v8A//3/AP3//wD9//8A+///AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/v7+AOfn5//Kysr/////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD///4A/f/+AP///wD///8A///+AP3//gD7//4A+/7/APr9/wD/+/8A//3/AP///QD+//4A+P3/ANbh7/+MnsL/TmSU/1pxqv9RZ6f/Uman/1prpv9gbJ3/aHGU/5ecrv/7/P8A//7/AP///gD9//4A/P/9APv//AD8//0A/v7+AP/9/wD//f8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A/P79APv9+wD8/f0A//3+AP/8/QD//v4A+fz/AOXw//+oudr/Y3ip/0NZlf9FW5//TmCo/0xepv9FVpz/Sl2h/0xhpv9DW57/Qlqc/0ddnv9GW5v/SFmc/0tbnv9NWp//Tlme/0tYnP9IV5r/RVaX/0RWlv9GWpr/Rlua/0thoP9IXp//S2Gj/09jpv9QYqT/U2Se/1xqmP9vepz/zdbu//L3/QD5+/0A/Pz6AP/+/AD//v4A//3+AP/+/wD9//8A/f//APv//gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AOXl5f/MzMz/39/f/////wD9/f0A/Pz8AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD///8A///+AP3//gD///8A////AP7//gD8//4A+//+APr//wD6/f8A//3/AP/+/wD//foA/v36APn9/gDj7vv/q73e/1BllP9TaqP/TmSm/05jpf9UZaL/YWyg/3yEqf+0uM3/+Pr/AP39/wD9//8A/f/+APz//QD7//wA/P/9AP7+/gD//f8A//3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f//APr+/gD6/v4A/f/+AP/+/AD/+/sA//v/APv7/wDv/P8i2u76/6O33f9qfrv/TmCo/0hXo/9NXqf/SVuf/0dbnP9GX6P/RF+k/0RfoP9FXp3/RVua/0ZZm/9IWp3/TVmg/0xYn/9KVpz/RlSa/0ZWmv9HV5r/Q1eX/0penv9HXpz/Rlyc/0lgn/9JXp//TGCg/1FkoP9YaJ7/b32n/73J5f/q8vzM+f3/AP7//QD//vwA//79AP/+/gD//v8A/f//AP3//gD7//0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP/+/gD///8A/v/+AP///gD+//4A/v/9ANrb2v+cnp3/oKGg//b39QD///4A///+AP3+/QD///4A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP7//wD9/v4A/f7+AP/+/wD///8A/v79AP79/QD8/f0A9Pj9AM7a6/9rf6r/U2yj/09npv9QaKn/UGSk/1hpov9nc6D/j5a0/+7w/0T8/P8A/f7/AP3//gD7//0A+v/9APz//gD9/f8A/vv/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD+//wA/v/7AP7+/QD//f4A//z+AP/8/wD7/v8A9//6AO79/UTT5vj/mKrU/1xuqf9LXaL/TV6o/0lbpv9FWqH/Rlqe/0hdnv9IXpz/R16a/0Zcmf9HW5v/SV2c/0pcnv9JW5z/R1mb/0VYm/9GWJz/R1md/0danf9KXaD/SV2g/0ldn/9KXqD/Sl6h/0xfov9QYaT/VmOj/2Vyov+Ll67/5e/z//z+/wD//v8A//7+AP/9/AD//vsA///9AP7//gD9/v8A+/7+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A/v39AP79/QD///8A////AP3+/AD9/vwA/v/9APX29ACWmZf/f4KA/8rMy//+/v0A/f38AP///gD8/fsA/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/f39APr8/QDe6PT/jJ7G/1Fqn/9QaKT/UWmo/05ko/9TZqD/XWqa/4GKrP/m6v3/+vz/AP3+/wD9//4A/P/+APv//gD8//8A/f7/AP/8/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///9AP///QD//v4A//3/AP/9/wD//f8A/v7/APr/+wD1//0A6fj/7sHS6/+BkcL/UmCk/09fq/9IWaP/TF6l/0hbn/9HW5z/SF2c/0lem/9IXpr/SF2b/0hdm/9IXJz/Rlqa/0VZm/9FWZv/Rlqc/0Zanf9IW57/SV2g/0ldoP9JXaD/Sl2g/0teof9MX6L/TV6i/1Beof9bZ5z/W2mC/7vHzv/w9PYA/v7+AP/9/gD//v4A//79AP///QD///4A/f//APv+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A///+AP3+/QD9/vwA/v79APX29QCsrqz/b3Jw/5+ioP/19vUA///+AP39/AD8/PwA/f38AP7+/QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD7/f4A6O/6/6u74P9acaL/VWyl/1Jpp/9RZqT/VWih/1xqmv93gKT/w8fe//r8/wD8/v4A/P/+AP3//QD8//4A/P7+AP3+/wD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD+//8A/v/+AP/+/QD//v4A//7/AP/+/wD9//0A+f/+APP+/gDh7/n/sr/i/293uv9QW6f/Slqg/0xfov9KXaH/Rlqe/0dbnf9JXpz/SF6b/0hdm/9IXZv/R1ub/0VZmf9FWZn/Rlqb/0dbm/9HW5z/R1uc/0hcnf9IXJ3/SV2e/0pen/9KX5//S1+g/0xeoP9OXqD/VmWc/1NihP+IlKb/3uTq//z9/gD+/v8A//7/AP/+/wD///8A////AP7//gD8//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A/v7+AP7//QD9/vwA/f79APr7+QDCxcP/bnFv/4yPjf/X2dj//P79AP3+/AD///4A/Pz8AP///wD//v4A//7+AP/+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/P7/APD2/QDI1fD/aX2n/11yqP9Uaqb/UGah/1RpoP9bbJz/b3ie/6mvx//6/P8A+/79APz+/QD9//0A/P/9APz+/QD9/v4A//7/AP/9/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9/v8A/f//AP3//gD///wA///8AP/+/wD//v8A//7/AP3//gD6//0A9Pz+ANrk9/+hptr/W2Gm/0xbnf9MX6D/Sl2i/0VboP9GW57/SFyc/0hdm/9JXZz/SV2c/0dbm/9GWZn/RlmZ/0dbm/9IXJz/SFub/0hcm/9IXJv/SFyc/0ldnP9KXp3/Sl+c/0tfnf9LX5//TmGh/1VmoP9capj/a3eW/9DY5P/3/P8A/P3/AP38/wD+/f8A//7/AP///wD///0A/v/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD+/v4A/P38APz9/AD9//0A/P78APv9/ADb3tz/aWxq/3d6eP/JzMr/+fz6APv9+wD7/PoA///+AP///wD///8A//7+AP79/QD//v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/wD3+/8A4Ov4/4GRtP9ecaP/U2ih/0xjnP9QZp3/WWud/2x1n/+jqsb/+f3/APv//QD8/v0A/f/9AP3//AD8//sA/P79AP/+/wD+/P4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A/f7/APz//wD9//4A///9AP///AD//v8A//7+AP/+/gD///0A/f/8APr+/QDy9/8A09fy/4CGu/9QW5z/VGSp/0pdov9HXJ//R1ye/0hbnP9JXJz/Sl2d/0tenv9KXZ3/SFub/0hbm/9KXZ3/Sl2d/0lcnP9JXJv/SFya/0hcmv9KXpr/Sl6a/0tfm/9LX5v/S1+d/0xgoP9QY6H/Wmmg/2Rxm/+ptMr/4+rx//j8/wD7+/8A/fz/AP/+/wD///8A///9AP///gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A/v/+AP3//gD9//4A+/78APb59wDq7evMkJSR/ycqKP+PkpD/4+Xk//r9+wD8/vwA/Pz7AP///gD//v4A////AP78/gD//f4A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A+v3/AO71+0Swvdr/W2qY/1Rnn/9TaaH/UGed/1Jlmf9cZpX/e4Ki//T5/gD6/v4A/f7+AP///gD+//wA/f/7AP3/+wD//v8A/fz+AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3+/wD8//8A/f//AP///wD///4A///+AP///AD///wA///8AP///gD9/v8A+v3/AO3z+ma9xuT/Ymqo/11qsf9MXqL/SF2d/0hdnP9IW5v/SVyc/0tenv9NYKD/S16e/0lcnP9JW5z/SVyd/0pdnf9JXJv/SFua/0dbmf9HW5n/SFyZ/0ldmv9KXpn/SV+Z/0pfnP9LX5//Sl2d/1xqp/9fa53/V2SE/7fCzv/1/P8A+v3/AP3+/gD//v8A//7/AP///gD///4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP7//gD7/vwA+v37APv+/ADs8O2Io6mk/zxCPf8lKST/vL+7//3//QD7/vwA+/38AP///wD//v4A/Pr6AP/+/gD//v8A//3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APv9/wDz9/8A2uP7/3J9qf9Za6H/UWef/1Bnn/9WaqL/YGyf/3Z+ov/m7Pb/+f39AP7+/wD///8A/v/9AP3/+wD9//sA/v3/APz7/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/f//AP3+/wD9/f8A/f7/AP///gD///sA///7AP///QD//v8A/v3/APz+/wD4/v4A6vP6zJSbz/9YY6X/T2Gg/0lfnP9HXJr/SFub/0lcnP9KXZ3/S16e/0pcn/9IWp3/Rlib/0ZYm/9HWZv/R1qa/0ZZmf9FWZf/RVmX/0dbmf9IXJr/SV2Z/0ldmf9MYJ3/UmWl/1Jlpf9VZKP/WGSa/2t4nv++yNv/8vn/APj9/QD6/PsA//7/AP/+/wD//v8A//7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP7//wD9//4A+Pv6APf7+QD4/PkA2N7Z/2dtaP8wNjH/lJqV/+Dl4f/2+vcA+fz6AP3//gD+/v4A/f39AP/+/wD8+fsA/v3+APz6/AD9+/wA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9/v8A+/v/AO3w/2Z8ha7/Xm6k/1pup/9SaaH/UmWf/19qov9tdp7/0trk//X6+gD8/v4A/v3/AP3+/QD8//oA/P76AP/+/wD9/P8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///9AP///QD+/v4A/fz/AP39/wD9//8A/f/7AP///QD///8A/v3+APz9/gD7/v4A+f//APD6/gDO2fn/aXal/1Vmnv9NYJ3/Slyb/0pcnP9KW5v/S1yc/0xdnf9LW57/SVmc/0hYm/9IWJv/SFib/0lZnP9IWJr/R1iY/0dYmP9IWZn/SVqa/0pbmv9LXZv/SVua/0dbmf9RYqH/UWCf/1hmnv9eaZX/nqjF/97m8f/4//0A+//7AP///gD//v8A//z/AP/9/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD9//4A+/78APr9+wD6/vsA6+/sqq60r/9MUk3/a3Fs/9Ta1f/1+vYA+/78APz+/QD9//4A/v7+AP7+/gD//v8A/v3+APz6+wD9+/wA/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7/AP38/wD19v8ApazV/2p5r/9XbKT/VGuj/1VppP9daKL/b3ei/8bO2v/y+PcA/f3+AP79/wD+/v0A/f/6APv++gD9/v8A/f3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP///AD///0A//3+AP77/wD9/f8A+///APv//QD9//8A/f//AP7//gD9/v4A+/7/APf8/wDy/P8A6fb+7penxf9cbJ3/Tl6c/05foP9NXp7/TF2d/0xdnf9OX5//TV6g/0xcn/9MXJ//TFyf/0tbnv9JWZz/SVmc/0hYmv9IWJr/SVmb/0lanP9LXJz/TF2d/05fn/9QYaD/TV2b/1FenP9cZ5//WmOT/2lxk/+0vcr/9v78APz/+gD///wA//7/AP/7/wD/+/8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP7+/gD9/f0A////AP///wD+/v4A/f/+APr9+gD6/vkA8/jzAMTKxP9jamP/Njs2/4KHgv/n6uf/+/78AP3+/QD+//4A/v/+AP///gD///4A///+AP///gD+/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A+/v/AMrP7P91g67/U2qf/05ppf9Raqb/WGyi/2t4n/+lq7v/8/X3AP79/QD//v4A/f/+APv//QD7//0A/v/9AP7+/AD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v4A//7+AP/+/gD+/vwA/P77APv//AD6//8A/P3/AP79/wD//f8A//3+AP79/gD8/v0A+P7+APD9/wDK2fD/d4ez/1Vmof9PYKD/TF6e/0xeoP9OYKL/TF6g/01eof9MXaH/TF2i/0tcof9KW6D/Slyf/0hZnf9HWJv/R1mc/0ham/9JW5z/Slyc/0pdnP9MXZ//TV6h/05gof9MX5r/VGie/1dlnP9eaJX/p7DJ//H3/QD8//0A//79AP///gD//f4A/f7+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP39/QD9/f0A/v7+AP///wD+/v4A////APz+/AD7/voA+f33ANLY0f+OlY7/XmVe/zs/Ov+4vLf/8vXzAPz+/QD+//0A///+AP///gD///8A///+AP///gD///4A///+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP37/wDk6Pn/h5W4/1huov9MaKX/TWqm/1Ztof9od57/nqG1//T09wD+/f4A/f39APr9/QD5/v4A+//+AP///AD///wA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD///4A/v/7APz/+QD7//wA+///AP39/wD/+/8A//z/AP/7/gD//PwA/v/8APr//gDx+/8A5PL//5yq0P9icaj/Tl6d/01fof9OYKT/UGKl/0xeov9NX6L/Slyg/0pcof9KW6D/Slug/0pcn/9IWp3/Rlib/0dZnP9IW5z/SVyc/0pdnP9KXZ7/S12g/0pbof9LX6L/SF6Z/01lnP9WZ6H/X2uf/5mjwv/d5O7/+Pv8AP/+/gD///4A///+AP3//QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD9/f0A/f39AP///wD+/v4A/v7+AP///wD8/vwA+v35AObt5f+mrqX/bnVu/3+Ffv9jZ2L/6u3p7vn7+QD9/v0A///+AP///gD///8A////AP///wD///4A///+AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/9/gD8+/8A8vX/AKCsy/9hdaj/TWei/05qpf9Ua6D/Y3Oc/4aJof/n5+//+vr9APv9/QD5/P0A+f3+APz//gD//vwA//77AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//v8A///+AP7/+wD9//oA+//9APv//wD+/f8A//z/AP/8/wD//P4A//38AP7//AD7//0A9Pz/AO76/0TEzu7/e4e4/1Jhnf9PX6T/UWKp/09hp/9NX6T/TV+j/0lcn/9IW57/SFue/0lcnv9JXJ3/R1qc/0ZZm/9HWpz/SFyd/0lcnf9JXZ7/Sl2g/0tdof9KXKH/SV2f/0lgm/9JYZr/UmOf/1tonf+Dja//xcvY//j6/AD//v8A///+AP///gD9//0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/v7+AP7+/gD+/v4A/f39AP7+/gD///8A/f/+APj9+QDI0Mn/h5CH/2pya/9/hoD/lpuW//b59gD6+/kA/f78AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A/Pz/APf6/wC6xOD/bn6t/09lnv9Saqb/U2mg/15tmv9gZ4L/1dfl//X2+wD8/v8A+/7/APr9/gD7/v4A//38AP7++wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP/+/gD+//wA/f/8APz//gD8//8A/v3/AP/9/wD//P8A//3+AP///AD///oA/f/7APr+/wD1/f8A5Oz//6Gq0P9icKj/TV2j/1Bhq/9NX6f/TF6k/0teo/9IXJ7/R1uc/0hcnf9JXZ3/SFyc/0dbm/9HW5v/R1ub/0hcnv9IXJ7/SFuf/0pcof9LXqL/S12h/0ldnv9LY5//SGCb/05gnv9ZZZz/cHug/7C2x//3+f4A/v7/AP/+/gD+/f0A/f/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD+/v4A/f39AP39/QD///8A/f39AP3+/gDv9PEiqLGr/3aAef9zfHT/cHhx/7/FwP/2+/cA/v/9AP7+/QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP39/wD5+v8A1dz1/4KPuf9Xap//VWul/1Noof9cbZz/YWuL/8/T5P/09fsA/P7/APr8/gD3+/0A+vz9AP7+/gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD+//8A/f/+AP3//QD9//4A/f//AP/9/wD//f8A//z+AP/9/QD///sA///4AP//+gD8//8A+P3/APT4/wDK0ev/gIq9/05doP9OX6j/TF2m/0pcov9KXaH/Sl2e/0hcnP9JXp3/Sl+d/0lenP9IXZv/SFyc/0hcnP9IXJ7/R1ye/0dbn/9JXKH/S16i/0xfof9HXJv/SmCg/0phoP9QY6P/W2mj/2ZxnP+Xn7n/5un0//3+/wD+//0A/f3+AP3+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+/v4A/v7+APz8/AD///8A////APf39wD6/PsAusK9/2Vxav9ea2P/lqGZ/7K8tf/6//wA9Pr1AP3+/AD7/PoA/f38AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD9/v8A+vr/AO7y/0SirM3/Z3am/1Rpof9TaaL/VWmb/1lkiv+yudH/6ez27vn7/wD7/f8A/P7/AP7//wD//v8A//7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD///8A/v//AP3//wD9//4A////AP///wD//f8A//7/AP/+/gD//v0A//77AP7++QD///sA///+APj6/QD6+/4A6+7/qqCo0/9XZaX/TV6l/01eo/9KXKD/SFuf/0tfoP9LX5//S1+e/0tfnv9KX53/SV6c/0penv9KXp7/SV6g/0heoP9GXaD/SF2h/0xgo/9QY6P/Sl+d/0heoP9JX6H/R1qd/01dmv9UX5D/eYCh/8TI2P/7/v8A/f//AP3//wD9//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/f39AP7+/gD9/f0A////AP///wD+/v4A/P/+AJujnv+IlI3/lKCZ/3yGgP+MlZD/+v/9APv//gD8/fwA/v79AP///gD///8A////AP///wD///8A////AP///wD+//8A/f//AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8//0A/f//AP39/wD3+P8Av8bh/3eFsf9SZZr/Vm2l/1Vrnv9kcpj/oqvF/+js8//8/f8A/f7/AP3+/wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/f//AP7//wD///8A////AP/+/wD//v8A//7/AP///wD///8A/v39AP78+wD///wA///8APv7+wD8/f0A/v7+APf5/wC6w+D/bX20/05hof9PYqL/Sl2d/0lcnP9MX5//TGCg/0tfn/9LX5//Sl6e/0ldnf9KXqD/TGCi/0pgov9KYKL/SmCi/0pgof9LYZ//TGCe/0lenf9HYKL/SWKk/0hcoP9QYJ//X2me/4CHrv++wtj/9/v/APr//QD7/v8A+/7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP7+/gD+/v4A/f39AP7+/gD9/f0A////AN3h3/+UnZj/h5eP/3yMhP90f3n/xs/K//r//gD7//4A/v/+AP///wD///8A////AP///wD///8A////AP///wD///8A/f//AP3//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A/f/+AP3//gD+/v8A+vr/ANjd8/+NmsL/WGue/1hvpv9Ua5//Xm2X/4SPrP/e4u3/+/z/AP3+/wD8/v8A/P7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/+AP3//gD+//4A//7/AP/+/wD//v8A//7/AP///wD///8A////AP3+/AD7/fsA/f//AP3//gD8/PwA/v7+AP///gD5+/8A0tv0/4ybzP9RZZ7/UmWg/01fm/9MXp3/TV6d/0xeoP9LXqD/S12g/0pdoP9JXJ//SVyg/0teov9MYKP/TGCi/0pgof9JYJ//S2Ge/01hnv9IXZz/RF2f/0VdpP9KYKb/UmKj/1lknP9ob5r/mZ+4/+/1+iL3/fsA+/7/APv+/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+/v4A/v7+APz8/AD///8A/v7+AP7+/gCprav/kpyX/5Wlnf9/j4f/hI+J/+Hq5f/6//4A+v/9AP7//wD9/f0A/v7+AP///wD///8A//7/AP/+/wD//v8A//7/AP3//wD9//8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f/+APz+/QD9//0A//7+AP38/wDr7/yqpbHR/2R2p/9Ybqb/UWid/19wmv9wfJv/09jn//b4/gD8/f8A/v7/AP7+/gD+/v4A/Pz8AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//gD9//4A/v/+AP/+/wD//v8A//7/AP/+/wD///8A////AP3+/gD8/f0A/P7+APv+/wD5/P0A/f7+APz+/QD+/v0A+v3+AOXu/f+ks9f/U2eb/1Nmnv9OYZr/TmCc/0xdnP9LXZ//TF6h/0tdov9KXKH/SVqg/0hbn/9KXqD/S1+h/0tfoP9JXp3/SF6b/0lfmv9LYJv/SF6a/0Zfov9DWqH/SmCn/1Bhpf9ZY5//Z26b/5OatP/p8Pfu9fv6APr+/wD6//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//7/AP7+/gD9/f0A///+APz+/gDW2tf/f4eD/4uWkP+eq6T/iZaQ/5Odmf/w9/QA/P/+APz+/AD+//8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD+/v8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//gD8//0A+v77AP3+/gD9/f8A+fj/AMfK4f9/i7T/VGmd/01nm/9Xb5z/YHKT/7rA0v/u7/ZE/v7+AP///QD//v0A/v3+AP38/QD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/v//AP///wD///8A////AP///wD///8A////AP///wD+/v4A/v7+AP7//wD9//8A/P7+AP7//wD+//4A///+APz+/wDy+f8Avcng/2p5pv9SY5z/TGCf/0hfn/9EXJ3/SF2f/0peoP9KXJ//SVqe/0hbnf9IXZv/R1+b/0Zen/9GXqD/R12f/0hdn/9JXp//S1+f/0lenf9GX5//RV2e/0tgpP9OYqT/VGSf/15qmf9+iKj/x9Lg//X9/QD6/f8A+v7+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP79/gD+/v4A/f39AP7//gD5/vwAvMO+/4mUjv+Tn5n/l6Od/4+alf+utrP/+P37APv9/AD+/v0A////AP39/QD+/v4A////AP///wD///8A////AP///wD///8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9//8A/f/+APr+/AD8/v4A/P3/AP36/wDY1+n/j5e7/1Fmlv9Oap3/UW2c/1lvlP+Nlav/3d3k///+/gD+/vsA/f37AP/8/gD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9/v8A9/3/AM7Z5/+Cj7b/UF+Z/01fo/9FX6H/Qlye/0dfoP9KX5//Slyd/0panf9IXJv/SF+Y/0ZfmP9EXp3/RF6g/0heof9KXqL/S16j/0teof9JXZ//SF2d/0henf9KYKH/TmKi/1Jkn/9YZpj/bnuf/664zf/1/f8A+v3/APz+/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+/f4A/v7+AP39/QD9/v0A8/j2AKatqP+gq6X/n6ul/4uXkf+Rm5f/xczK//r+/QD6/fsA///+AP/+/gD9/PwA/v39AP///wD///8A/v//AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//gD6/v0A/P7+APv8/wD7+f8A0tLl/4eQt/9RZpf/UWyf/1Bsnf9bcZr/cnqV/8/P2v/8/P4A/f78AP39/AD//v8A/v3+AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f7/APj9/wDZ4/D/laLG/05ck/9PYaL/R1+g/0Rdnv9KYKD/Sl+e/0pbnf9LXJ7/SVyb/0hfmP9HYJn/RV6c/0Ven/9JXaH/S16i/0tdof9KXKD/SVye/0hcnP9HXZz/Sl+g/01hoP9RYp7/VmWY/2dzmf+Yobf/8/n/APn7/gD9/v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/v3+AP7+/QD9/v0A+fv6ANvi3v99hX//lZ+Z/5yoov+KlpD/maOe/9LY1v/6/v0A/f/9AP3+/AD//f0A//7+AP///wD///8A/v//AP3//wD9//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A+vz8AP3//wD5/P8A9vb/AL6/2P9yfab/U2ia/1Bpn/9PaZ3/W3Ge/3Z/of/Bw9b/8PL5APz9/gD+/v4A/v7/APz8/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3//wD5/f8A5u76/6242P9SXpL/T2Ce/0pgnv9GXZz/Sl+e/0lcnP9JWpv/TVye/0tdnP9JXpj/R1+Y/0VenP9HXZ7/SFyf/0lbn/9KW5//Sluf/0lbnv9JXJz/SF2c/0ldnv9KXp7/TmCc/1Rjl/9daI//e4Sb//D2/wD2+PwA/f3+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP39/AD6+/kA+fz5APL18QCwt7L/h5KK/5aimv+bqKH/j5qU/7O8t//2/PoA/P/+AP7+/QD5+vgA/v39AP78/AD+/v4A////AP7//wD9//8A/f//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD9//8A+fz/AOns/O6gpsb/XWqW/1Zqnv9QZ5//UWmf/1hsnf9fa5T/i5Ox/9DU4f/4+vwA+vz8AP3//wD8/v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A+v7/APH4/wDFz+f/ZG6f/05fmf9OYZ//R16b/0lenP9IXJr/Slub/05dn/9MXZ3/Sl+Z/0lgmf9IXp3/SF2e/0hbnv9JWp3/Slqd/0xboP9KXJ//S16e/0pdnf9JXZ//Sl2e/05fnP9WY5j/XGeQ/3Z/mP/y+P0A9/v/AP39/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///4A/v/+APv+/ADo7+r/oKqk/6Kup/+Wpp7/maag/3F8dv+UnJj/9/36APv9+wD5+vgA///+AP79/AD//v4A////AP///wD+//8A/f//AP3//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/gD///8A/f39APn9/gDM0eb/hY60/1Zllf9QZZv/TWSc/0xjnP9TZ57/Wmia/1Rfh/+gpr//7vH5RPz+/wD9//8A+/3+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APz//wD4/f8A2eDu/4KKuv9RYJn/UWSg/0xhnP9JXZv/SVub/01bnv9QXKH/T12f/01fm/9MYJz/S1+e/0teoP9LXJ//S1qd/0xanv9NXKH/TF6h/01goP9MYKD/S16h/0xeof9QYaD/Vmad/1lmkP9mb4v/3OPx//X4/gD8/v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/Pz7APz++wD5/fkA3eTf/0pVTf+PnJL/maqf/5SgmP+cpqD/ytLO//v//gD9//4A/v79APz6+QD8+voA/v7+AP///wD///8A/v//AP3//wD9//8A/f//AP3//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//f0A/Pz8AP7+/gD3+/8AuMHa/3uIs/9bbaD/TmOb/0xinP9OY6D/Umah/1hqpf9cbJ3/jJW0/8/U4P/2+/0A+///APv//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8//8A+P3/AODp9v+eqNT/U2OZ/1Bknv9LYZz/RlqZ/0dZmv9LWp3/TFqe/0pbnf9KXZv/SmCb/0tfnv9MXqD/TF2g/0xbnv9LWp3/Slqf/0pcn/9KXqD/SV6g/0hdof9LX6L/UGKi/1ZnoP9gbZn/eYOg/9La6//2+v0A/P7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP3+/QD7/vwA8/n0ANXd1v9wfHT/lKSa/5Okmf9+i4T/ipSO/8LKxv/3+/kA+vz6AP///gD+/fwA/v79AP/+/gD///8A////AP7//wD9//8A/f//AP3//wD9//8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP7+/gD///8A9fn/ALvG4f99jLr/W26k/1Roof9QY5//TWGg/01gof9TZqj/XW6m/2Rvk/+jqrj/9/3+APn9/gD5/v0A/f//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/P7+APn8/gDo7/z/tb7o/1lnnv9OYpz/SF+b/0VZmP9GWJr/Slme/0tYnv9JWZz/R1ua/0hdmv9JXZ7/Sl2g/0tboP9MWp7/S1me/0lZnf9IW57/SFye/0ddn/9FXKH/R16i/01hov9TZp//W2uW/2t3lP+ttcb/8PX6APv9/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD7/fsA+v77AOjt6f+sta7/eIV9/5eonf+Wp5z/eYZ+/5Wfmf/V29j/+f38APz9/AD//v0A///+AP///wD//v4A////AP///wD+//8A/f//AP3//wD9//8A/f//AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/8/QD///8A/fz8APb6/gDDzur/eYu7/1BknP9TZqD/UWOg/1Bjo/9PYqX/UGOp/1Zopf9ZZo3/iJGh/9nf4//5/v8A+v7+AP3//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//gD6/f4A7fT+ZsXO8v9jcKb/T2Od/0lgnf9IXJz/SFue/0tbof9MWqL/SVqf/0hbm/9HW5v/R1ye/0hcn/9KW5//S1qf/0tZnv9LW5//SV2f/0ldn/9GXqD/RV2h/0Zeov9LYaL/UmWf/1trl/9pdpP/lZ+w/+zz+Ij5/f8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/P38APz+/ADh5uL/lJ6X/4eWjv+YqZ//mKqf/3mFfv+psav/6/Dtqvz//gD9//4A/v39AP39/AD///8A/v7+AP/+/gD///8A////AP7//wD+//8A/v//AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/wD7+/4A/f//APz9+wD5/f4A0Nnw/4mWwf9WZ53/UmWg/09kof9SZ6T/Umek/1BlpP9SZ6D/V2eT/32Jpf/FzNr/+vz/AP3//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8//8A+v7/APD4/wDR3fT/bnyn/1Nkn/9LXqL/S12i/0pdov9KXaL/S12i/0lcn/9IW53/SFud/0dboP9IW6D/SFuf/0hbnf9IW53/Slyf/0pdoP9MXaH/Sl2h/0ldoP9JXqD/TGGh/1FloP9Ya53/ZnSc/4KOqv/m7vf/+P3/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP39/AD7/foA1tzX/3eCfP+ZqaH/mKmf/5WnnP99h4D/tr24//b69wD8/v0A/f/+AP7+/gD8/PwA////AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8/v8A+vz/APv//wD9//wA+v7+AN/k9f+bo8f/YG6g/1Jlof9MYqH/TmSf/1Foof9RZ5//Umed/1hqmv9yf6b/rrbO//j5/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/P//APn+/wDz+/8A2+fz/3yJq/9XZKD/Tlul/01dpv9KXqL/SF2g/0pen/9JXZ7/Sluf/0lan/9JWqH/SVqh/0hcnv9HXZz/R12d/0ldn/9KXJ//TVyh/01cof9NXKD/S12f/0xgoP9PZKH/VGmh/2Bwo/99iq7/5e73//j9/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8/PsA9vn2AMfOyf9ZZF3/l6ef/5Smm/+SpJn/doF6/7K4s//1+fYA+v37AP3//gD///8A/v7+AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/f//AP3+/wD7/v8A/P76APv//gDp7vvuoavL/19tnf9TZaD/S2Cf/0thnP9OZZ7/TmWd/1Jnnf9bbJ//Z3Sd/5WeuP/09v4A/f7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APz+/wD6/v8A9f3/AOLs9v+Omrv/WGOg/1Bcpv9NXaX/SVyg/0hcnv9JXZ7/SVyd/0tanv9KWZ7/SVmg/0hZoP9IW57/SF2d/0hdnv9JXJ//SVuf/0xboP9NXKH/TVyh/0tdof9LX6D/TWKf/1Jmn/9ebqH/e4es/+Tt9f/4/f4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/P39APP39ADByMP/aHNt/5Gimf+Ro5j/kqWa/1tmX/+Zn5r/7vLvRPn8+gD9//4A/v7+APz8/AD8/PwA/f39AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//wD9/v8A/f//AP3++wD7/v4A8vf/AK+50v9pd6L/VWeh/05joP9TZ6T/S2Ca/0pgmf9UaJ//V2md/2Vzof+ZosL/5+r2//r8/QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9/v8A+/3+APj9/wDo8fj/pK/O/1hinv9RXaX/TV2k/0lbn/9IXJ3/SV2d/0pdnf9KWp3/SVid/0hYnv9IWZ//SFud/0ldnf9JXZ7/SVug/0haoP9LWqD/TVyi/0xdo/9LXqL/S1+h/0xhn/9RZqD/XWyg/2Julf/T3Ov/8/j7AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPz+/QDn6+j/pa2o/1dkXf+NoJb/k6ic/5apnv9mcmv/qrGs//v//AD9//4A/P79APr6+gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A+/z/AP3//wD///4A+/7/APb8/wDDzt//fIuy/1Rlnf9SZqT/Umak/1BkoP9UaaP/VWmi/1doof9daZz/eYOl/8HF0//7//8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/wD6/f8A7vX7RLzF5P9bZJ7/VWCo/1Fhp/9MX6L/S1+g/0xfn/9LXp7/TFyf/0pZnv9JWJ7/SFie/0hanf9KXZ3/SV2e/0hbn/9IWqD/TFqj/01bpP9MXKP/S16i/0tfov9NYaL/UGOg/1dlnP9kb5f/zNXk//P4+wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8//4AzdLP/36Ggf9+i4T/k6ac/5Cmmv+Gm4//X25m/6ivqv/3/PgA+/78AP3//gD///8A////AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP38/gD///8A//7/APz9/gD3/P8A1uLv/46ewP9LW5H/UmOi/0tfnf9VaaX/TWOe/01jnf9Yaab/X22m/3yGrf/Hzd3/9fr8AP3//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A+/7/APD3/QDI0fD/WGGb/1JepP9PX6X/TF+h/0xgoP9NYaH/TWCh/05eof9MW6D/S1qg/0taoP9KXJ7/Sl6e/0pen/9IXJ//SVyg/0tcov9OXaP/TFyi/0pdof9LX6L/TWKk/1Jmo/9XZ57/WmWN/8zV5P/y9/sAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA+v39AM7U0f+Hkoz/kqGb/5Gkm/+KoZX/f5SI/3qHf/++xcH/+/78AP3//gD9//4A/f39AP39/QD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//3+AP/9/gD+/v8A+v3/AOTv+v+ercz/SVqN/09hnv9RZKT/UGSi/1Bmof9SZqL/VGal/1tqpv9oc57/i5Wl//j+/gD9//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+APr9/gDx+P4A0Nr3/1pim/9PWqD/S1yh/0dcnf9JXp3/TGGf/0tgn/9LXaD/Slyg/0taoP9KWZ//Slud/0ldnP9IXZ3/SFye/0ldn/9MXqL/TF6i/0tcov9JXaH/Sl+j/05ipf9TZ6X/V2if/15ok/+7w9T/7vP3RAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPf7+gDEy8f/fYiC/5Ggmv+UqJ//iaCU/32Shv+Qnpb/z9bR//r9+wD5/PoA/f/+AP7+/gD+/v4A/f39AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP77/QD//f4A//7/APr+/wDs9/6IssHb/15un/9PYZ7/T2Gh/1FkpP9TaaX/Vmun/1Vnqf9YZ6b/Ym+d/4OPov/x+fwA+///AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD8/f8A9Pr/ANnj/P9jaqP/Ul2k/05fo/9JX5//SF+d/0hfnf9JXp3/Slyf/0pcoP9KWqD/SVqg/0hbnf9HW5v/R1ya/0ddnP9JXZ3/S16f/0xfoP9LXaD/SV2g/0dfoP9LYqH/UGSi/1dnn/9odJ//xM3e/+70+EQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4/PsAyc/M/4OOiP+Sopz/lamg/4+nm/98k4f/lKOb/9HZ1P/5/foA+/38AP3//gD+/v4A/v7+AP///wD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/9/wD+/P4A//3+AP///wD7//4A8vz+AMbV6v99jLz/U2Wi/09goP9QY6P/Umej/1FnpP9SZaj/VmWo/15rnP98iZ7/4uvv//r+/gD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD+/v0A+/z9APX7/wDd6P7/aHGo/1Rgpf9OYaT/SGGg/0Vfnf9FX5z/R16d/0ldn/9KXaD/S1yi/0pbof9JW57/R1ya/0Zcmf9GXpr/R16a/0pdnP9LX57/S16f/0hdn/9HXp7/SWCe/0xhnv9VZZ3/ZXKc/7vF1f/r8faqAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA+P37AMjPy/+CjYf/nKmi/5epof+TqJ7/hJeN/5SfmP/P1dD/+/77AP3+/QD+//4A/f39AP7+/gD///8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//f8A//3/AP/+/wD+/v4A+/7+APb9/gDY4vP/mKPP/1VjnP9TZKH/TWGe/09moP9PZaH/T2Kj/1Rio/9caJn/doKa/9Xe5v/5/f4A/v//AP///wD8/PwA/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v4A/f39APv9/QD3/P8A4uz9/3J+sP9XZ6f/T2Sk/0phoP9HXp3/R16d/0henv9JXqD/Sl2h/01dov9NXKL/S1yg/0dbnP9GW5r/R1yb/0hdm/9LXZz/S16c/0pdnf9JXZ//SF6f/0len/9MYJ//VWSd/2Rvmv+2wdD/7PL1iAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPj9+gC+xcD/cXx1/6CspP+ZqaH/l6ef/4uYkP+LlI7/y83J//7+/AD+/v0A///+AP39/QD///8A/v7+AP39/QD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP79/gD//v8A/f7+APz+/gD6/P8A6Oz6/7K43P9faJr/Vmig/0tim/9OZZ//UGai/09hoP9UYZ7/XWma/298mf+7xdL/9/r8AP7+/gD+/v4A+/v7APz8/AD///8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7/AP3+/wD8//8A+f3/AOjw/f96ibb/Wm2o/1Fmpf9NYaD/SV2e/0lcn/9JXqD/SV+g/0peoP9OXKD/T1yi/01cof9IW5//RVqd/0danf9JW57/TVyd/0tbm/9KW5r/SVyd/0pdoP9LXqL/TmCi/1hkof9ncZ3/vcjV/+/19yIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD5/fsAu8G9/2l0bf+bqKD/l6ef/5yupf+EkYn/fYV//8PGwv////wA/v79AP///gD///8A////APz8/AD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD9/f0A///+AP7//QD+//4A+v3/APL2/wDM0+z/doGw/1donv9QZp7/TmSf/05jof9QYaH/VmSh/1xpmv9kcJH/l6Kz//T3+wD+/v4A////AP7+/gD///8A/v7+AP39/QD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A/P//APn9/wDm8P3/dIOv/1dqpP9RZaP/TWKg/0ldnf9IW57/SV2e/0hfnv9JXZ//TVyg/09cof9NXaH/SV2g/0Zbnv9GWZ3/SVud/0tcnP9KW5n/SVuZ/0lcm/9JXZ7/S1+h/09hov9aZ6P/Y26Z/7fCz//t9PRmAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8PXyALG5s/9sd3D/maig/5iooP+Upp3/hpSL/4ePif/JzMj//f77APz9+wD9/v0A/v7+AP7+/gD6+voA/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A/Pz8AP///gD+//wA///9APr8/wD0+P8A2t/v/4eSu/9SY5X/WGyl/1JnpP9QZKT/UWOj/1Fin/9VZJj/b3qf/662zv/1+PwA/Pz9AP39/QD8/PwA/f39AP///wD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7/APz//wD4/f8A5e/9/2t6pv9SZp//TmOg/0xhn/9KXZ3/SVye/0ldnf9IX57/Sl6e/05doP9QXaH/Tl6i/0tfof9IXaD/SFue/0pdn/9NXp3/TF2a/0pdm/9JXZz/Sl6f/0tgov9QYqP/WWah/15ok/+wu8j/6vHvzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOvw7aqpsKv/Z3Jr/5emnv+Zq6L/lqif/4iYkP+MlY//zM/L/////QD///4A/v79APr6+gD///8A////APz8/AD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A+/39APr9+wD+//wA///7AP///QD7/v8A9vz/AOTr9/+otNX/UmOS/1FlnP9QZKH/Umal/1Jlpf9TZaL/WGmf/2Fvm/91f5z/8vX5AP7+/wD+/v4A/f39AP7+/gD///8A////AP///wD8/PwA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD8/v4A9/z/AOLs+/9icZ3/TmKb/0xhnv9KX53/Sl6d/0tenv9KXp7/SF+d/0pfnf9OXZ//T12g/01en/9LX5//SV+g/0ldof9LX6H/TWGf/05gnP9MX53/S1+e/0tgof9MYaP/T2Ki/1Rjnf9kbpn/vcjV/+/19SIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADn7en/ucK8/4qWkP+Yp5//lqif/5Gjmv+Vpp3/n6ii/9TX0//+/vsA/f38AP///gD+/v4A////AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3//wD9//4A///8AP//+gD+//sA/P/9APn+/wDv9/4iy9jt/1pplf9YbKH/UGSj/0tgof9PY6P/VGej/1dpoP9jcaL/fIeq/+Pk8v/6+vwA/v7+AP///wD///8A/v7+AP7+/gD///8A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A/P7/APj8/gDf6ff/Xm6a/01gmf9LYJ3/Sl+d/0penf9LX57/SV+d/0denP9IXZv/TFuc/0xanP9LXJz/SF2c/0ddnf9IXaD/Sl+h/01hn/9MX5z/Sl6c/0lfnv9KYKH/S2Gj/05io/9WZp//X2uV/7K9yv/o8O7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA6e/s7qy1r/9xfXf/nKyk/5epoP+Po5r/lqae/5Kclv/Lzsr//f36APv8+gD+//4A////AP///wD7+/sA/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9/v8A/f//AP///AD///oA///6AP7//AD4/PwA7/f7It3r9f+BkLf/WGue/09jof9PZab/UGak/05knP9QZZv/YHCm/3iBqv/U1+f/9vb5AP39/QD+/v4A////AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7/APz+/wD3+/4A2uP0/11smf9OYZv/TGCe/0tgnv9LX57/S1+f/0lenf9GXZv/SF2b/0xbnf9MWpz/Slqb/0dcnP9FXZ3/RV2g/0lfof9MYKD/S1+d/0lenf9JXp//SV+h/0pfo/9NYKP/VmWh/2dzn/+/ytf/7vX1RAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPL49QC9xcD/eIV//5mpof+RpZz/lKif/5aooP90fXf/sbWw//n69wD9/vwA/f79APv7+wD///8A/f39AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A+/z+AP7+/wD//v0A///7AP//+wD///wA+v38APP7/ADl8/n/pbPW/2R3qP9Xaqj/T2Wn/0xkoP9NZpv/Ummf/2Bxqf93ga7/0NTk//b3+gD+/v4A////AP///wD///8A////AP///wD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/wD8/v8A9vr9ANPc7/9dbJr/TmGd/0xgn/9LYJ//TF+f/0tdn/9IXZ3/Rl2c/0henP9NXJ7/TVue/0pbnP9IXZ3/Rl6f/0Zeof9KX6L/TWGi/0tfn/9KXp7/SV6f/0lfov9IX6P/TWCj/1Zlof9odKD/wcva//D2+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD3/foAw8zH/3iFf/+Wp5//kKSb/5aqof+XqqH/d4F6/7G0sP/29vMA/f38AP7+/QD7+/sA/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v3/APv8/wD//v8A//79AP///AD///wA/v77APv++wD3/v0A6ff97sHQ8P9oeqv/WGup/09lp/9NZqH/UGmd/1Nqn/9bbaf/cn2s/8LF1//z8/YA/v7+AP///wD///8A////AP///wD+/v4A/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD+/v8A/P3/APX5/ADN1er/XWua/05gnf9MYKD/TGCg/0teoP9JW53/R1ub/0Vcm/9IXZz/TVye/01bnv9LXJ7/SV2f/0heof9GXqL/SF+j/0xgo/9LX5//Sl6f/0heoP9HX6L/R1+j/01go/9WZaL/ZnKf/73H1//v9fgiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA9vv5AL/Hw/93g3z/l6af/5Onnv+Tpp3/l6mf/4KNhf+1u7T/8fTvIvz9+wD+//4A/v7+AP7+/gD//v8A//7/AP///wD///8A////AP7//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD+/f8A//7/AP7+/AD+//oA///8AP39/AD8/fsA+f7+APD6/wDc6v7/eYuy/1htpP9PZ6X/TWij/09pn/9QZ57/Vmij/2l1o/+0uM7/7vD0RPv8/QD9/v4A///+AP///QD///0A/v39AP///wD///8A/v//AP7//wD9//8A/v//AP7//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A/v7/APv9/wDx9/wAw83l/11rmv9PYZ7/TGCh/0tgof9KX5//SFya/0ZbmP9GW5n/SFub/0xan/9MWqD/Slyf/0len/9IX5//R12f/0lfof9MX6H/S16f/0len/9GXp//Rl+h/0hfo/9NYqP/Vmeh/2h0n//Ezt//8fb6AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPT59wDGzcn/iJKM/5mmn/+TpZz/k6Wa/5WnnP9ue3D/oKqg/+vx6sz9//wA/f/+AP/9/QD//v8A//3/AP/9/wD//v8A////AP7//wD9//4A/f/+AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP///wD9//kA/f/4AP7//AD//v8A//3/AP7+/wD5/v8A8fv+AKS1zf9ieaT/Tmef/0llov9KZKH/T2Wh/1dpoP9lcJz/qK7I/+zv9oj6/P0A+/78AP7/+wD///sA//77AP78/AD+/f4A//7/AP3//wD8//8A+//+APz//gD9//4A/f//AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//gD6/f8A6vP7zLLA3v9ca5r/UWSf/0xho/9IYKP/SWCe/0hfl/9IXZT/SVqX/0lYm/9KWKH/Slmj/0ldoP9IX5z/R1+a/0hdmf9KXpz/TFyf/0pcn/9JXZ3/R1+d/0ZgoP9IYKL/TWOf/1hpnP9reJz/1d3v//X4/QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD3/PsAxczI/4ONh/+eq6T/i52U/5WnnP+SpJn/YG1j/5Wflf/o7uf//P/7AP3//gD//f0A//7/AP/+/wD//v8A//7/AP///wD+//8A/f/+AP3//gD///4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD///8A/v/6AP3/+QD+/vwA//7/AP/9/wD+/f8A+/7/APP8/gDAz+b/bIGs/1JpoP9LY6H/SGGg/09kof9ZaaH/ZXGd/3+EoP/j5u3/+/3/APz//AD9//oA/v/6AP///AD//v4A/Pr8AP/9/wD+//8A/f//APz//gD8//0A/f/9AP3//gD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//4A+v3/AOjw+P+grc3/WWiY/1Bjn/9LYKH/SF6h/0lgnv9JYJn/SF2W/0tbl/9LWZv/S1mh/0tbo/9KXKD/R12b/0ddmf9IXJn/SV2b/0tbnv9JW57/SV2d/0dfnf9GYJ//R1+g/0xinf9VaJn/aXWa/93j9P/4+v4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA+v/+AMXMyf95hH7/naqj/42flv+SpJn/mKqf/4WSh/+tt63/6e/o//3//AD8/v0A/vz8AP/+/wD//v8A/fv8AP79/QD///8A/v//AP3//gD9//4A///+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//f8A//7/AP///AD+//sA/v/9AP/+/wD//f8A//7/AP7+/wD2/f8A4vD//4SVvP9bcaX/UGWh/01hof9RZKP/WGii/2Zwn/9xdpT/1dnn//b5/QD8//4A+/36APv8+AD//vwA/vz8AP/+/wD//v8A//7/AP7//wD9//4A/f/9AP3//QD9//4A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f7+APn7/wDm7fX/ipe4/1dmmf9RZKD/S2Ch/0hfn/9IX5z/SF2Y/0dblv9JWZX/SliZ/0pZnv9KW6D/SVye/0dcmv9GXZn/SF2b/0hcnP9JW57/SV2f/0pfnv9IYJ7/RmCg/0Zfn/9LYZz/WGqc/32Krv/i6Pj/+Pr/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPr+/QDDycb/bXdx/4aTjf+TpZz/k6Wa/5Smm/+Kl43/lJ6U/8jOx//9//0A/f/+AP/+/wD++/0A//3/AP/9/wD//v8A////AP7//wD9//4A/f/+AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//3/AP/+/wD///wA/v/8AP7//QD///8A//7/AP/+/wD+/v8A+f3/APL6/wCmtNL/aHqp/1JmoP9SZqb/U2Wm/1Rln/9gbZz/W2SD/8TK2//w9foA+v//APv//gD9//0A//79AP37+wD8+vsA//3/AP/+/wD+//4A/f/9AP3//AD9//0A/f/+AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/gD4/P8A4ejz/3aBp/9WZpr/U2ai/0xiov9JX6D/SF+c/0hdl/9JW5b/SlmY/0lXmP9KWJv/SVqd/0lcnf9KXp3/Sl+d/0penf9JXZ3/Slyf/0teof9MYJ//SmGf/0hhoP9JYZ//T2Sd/2Bvn/94hKb/7O/3iP39/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD7//4A1tzZ/5Odl/+HlI3/k6Wc/5Olmv+Qopf/jJmP/4iSiP+3vbb/+/77AP3//gD///8A//z+AP77/QD//f8A////AP///wD+//8A/f/+AP3//gD///4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD///8A///+AP7//gD+//4A///+AP///wD//v8A/v7/APv8/wD2/P8AxtPr/3eHsP9UZ57/VWeo/1Nlp/9RYp7/W2ma/19qiv/O1uf/7fT9ZvP4/QD4/P0A/P79APz9+wD//f0A/v3+AP/9/wD//v8A///+AP///QD///wA///9AP///gD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9/v4A9vn/ANbd8v9pdJz/V2Wc/1Jmov9MYqH/SWCg/0pgnv9JXpz/Sl2b/0xam/9LWZr/SVmZ/0hamv9JXJz/Sl+f/0phoP9JX5//SF2f/0pcoP9MX6L/TWGg/0xin/9KYp//S2Gf/1Fknf9kc6H/l6HC/+3x+mb9/f8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA+v/+AOXs6f+uubP/e4iB/5Gjmv+OoJX/kqSZ/4+ckv+GkIb/r7au//D08AD5+/kA/vz9AP/9/wD//f4A//3/AP/+/wD///8A/v//AP3//gD9//4A///+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP7//wD8//8A/f/+AP7//QD///0A///+AP///gD9/v8A9vz/AODr+v+Onr//XW6j/1Rlp/9SZKj/UGOg/1dpm/9baIr/qLLH/+Hq8v/1+/4A9vv8APj8+wD9/f0A/vv9AP/+/wD//v8A//7/AP///gD///0A///8AP///QD///4A//3/AP/9/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9/v4A+/3+AO/0/SLDyur/YWyb/1Rknf9PZKH/SmGg/0dhoP9JYKD/Sl+g/0xen/9MXJ7/TFqb/0lZmf9HWpn/SFyb/0pfn/9KYKD/SF+e/0dcnv9JW5//S1+h/01hoP9LYp7/S2Kf/0xjn/9QY5v/XGmY/5efwP/s7vmI/fz/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPr//gDn7uv/qrSu/2BsZv+Qopn/jZ+U/5Olmv+ToJX/iZOJ/661rf/v8+8i+vz7AP79/QD//f8A//3/AP/+/wD//v8A////AP7//wD9//4A/f/+AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD8/v8A+///APv//gD9//0A/v/8AP///gD///4A///+APj9/wDv+P8irr3X/21+sP9RYqT/UWSo/1BlpP9WaZz/WGeM/6SwyP/h6vL/9Pz+APX8/QD4/PsA+/38AP78/gD+/P4A//7/AP///wD///4A///9AP///AD///0A///+AP/9/wD//f8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A+/7+APn9/gDo7fj/rrXa/11pm/9RYp3/S2Gh/0Zgn/9FYJ//Rl+h/0leof9KXKH/Slue/0panP9IWpn/R1uZ/0hcnP9JXqD/SF6g/0Vdnv9EW57/SFqg/0teof9MYaD/S2Ge/0tinv9OZKD/U2ad/2Rxnv+xudn/8fP8AP39/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD6//4A6/LuqrK8tv9ea2T/k6Wc/5Kkmf+SpJn/mKWa/4ONg/+hqKD/7O/rqvv9/AD///8A//7/AP/9/wD//v8A////AP///wD+//8A/f/+AP3//gD///4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A/P7/APr//wD7//4A/P/8AP7//AD///0A///+AP///AD7//8A8vv/AMva8f+AkMD/TmCh/09jp/9RZ6b/VGmd/1ppj/+fq8X/3efx//L7/gD2/f4A+/7+APz9/AD//f4A/vz+AP/+/wD///8A///+AP///QD///wA///9AP///gD//f8A//3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APv+/wD4/f8A4efz/5ujyv9baJ3/Tl+d/0ZfoP9DX5//Ql+f/0Neof9FXKL/SFqg/0hZnv9JWZ3/SVya/0lem/9JXp7/SF2h/0VdoP9EXZ//Q1ud/0haoP9LXqH/TGCf/0thnf9MYp7/UGWh/1dpoP9ufKj/zdb0//b5/gD+/v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/P/+APf8+QDM1tD/a3hx/5OlnP+Wqp7/kKSY/5amm/92gnf/ho6G/9Xa1f/4+/gA////AP/+/wD//P4A//7/AP///wD///8A/v//AP3//gD9//4A/v/+AP7//gD+//8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP3+/wD7//4A+//9APz//AD+//wA///9AP///gD//vsA/P//APT7/wDj7///mqnQ/1pspf9OZKX/T2Wn/1FnoP9ZaZP/g5Cs/9Tc6P/2/P8A+v7+AP3+/AD//v0A//v9AP/+/wD+/f4A/v//AP3//AD+//wA///8AP/+/QD//f4A/v3+AP/+/gD//v8A//7/AP/+/wD//v8A//7/AP/+/wD+/v4A/v/+AP7//QD+//0A/v/9AP7//QD///4A///+AP/+/gD///8A////AP/+/wD7+fwA9/b6APDw9ADl5+r/3+Ll/9/i5f/P09b/5+ru//f4/AD9/f8A//3/AP/9/wD//v8A//7/AP///gD///4A/v//APz//wD6//4A9fz/ANfe7v+EjLn/WWWh/01dn/9GXaH/Ql2f/0Fdnv9CXZ//RFug/0Zan/9HWZ7/Slue/0tdnv9LX5//Sl6h/0leov9GXaH/RFyg/0Nanf9HWZ7/Sl2f/0pfnv9LYZ7/TGGg/1Fjo/9YaJ//cHqj/9jf+f/3+v4A/v//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP7//QD9//0A0NbR/1ppYP+DlYr/lKue/4yklv+NoZX/dYV6/3aCef+yvLT/+f75AP///gD///4A//39AP/+/gD///8A////AP7//wD9//8A/f//AP3//wD9//8A/f/+AP3//gD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD///4A/v/9APz//QD8//4A/v/+AP///wD///8A//79AP///wD7/f8A9fv/AMPP4v9/j7j/U2ih/0phqP9MZKf/VGia/32Lrf/S2ej/+fr/AP77+wD//PgA//75AP/6+wD//v8A+/39APv+/QD6/vgA+/75AP39/QD/+/8A//v+APz9+gD+//sA/v/9AP7+/QD+/f0A/v3+AP79/gD+/P4A/Pz9APz+/QD9/voA/f76APz++gD5/PgA9/n2APT29QDv7/Ei7O7xiOnr7+7j5Ov/0tHg/8fI3P+3u9H/m6O1/4yYp/+Vn67/c3+N/7rD0f/p7Pbu+vn+AP76/QD/+/4A/vz+AP79/wD//v0A///9APz//gD5//0A9f/9AOz5/YjCzeX/ZGug/1depP9RW6T/TVuk/0Zanv9DW5z/Qlyb/0Jbmv9EXJz/SFye/0lcoP9KW6T/TFym/0xcpP9LXaL/SVyh/0dZof9HWZ7/R1mc/0hbm/9GXJz/SmCh/0tepP9PXqT/W2Wd/3p/n//v8v4i+v3/APz//gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8/fsA/P77ANvh3P99ioL/aHpv/4mgk/+Oppj/kKSY/4SUif9yf3X/lJyW//v/+wD///4A///+AP79/AD+/v4A////AP///wD+//8A/f//AP3//wD9//8A/f//AP7//gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD///8A///+AP7//QD8//0A/P/+AP7//gD///8A//7+AP/7+gD//v4A9/r9APX7/gDU3/D/kJ/E/1Rnm/9OZKX/SV+d/1drnP9rep7/wcni//Dx/wD7+v8A//7/AP/+/gD//P0A///+APz/+gD7//kA/f/7APz8+wD7+fsA/vn+AP/9/wD///wA/f/5APv/+gD5//gA+v/4APv/+AD7//gA/v75APz8+wD6+PoA9vP6APPx9wDv8Pgi4eXt/87X3f+7yM//n625/5Ojtv+CkK3/ZHSZ/0JZhv9CXY//WXKl/2l7rv9wfqv/oKnM/9HY7//v8/oi+f37AP3/+AD+//YA/f/3APz/+wD8/v4A/f//AP3//wD9//8A+v/+APb//gDm8/n/sb3X/1dflP9TWqD/T1ui/01do/9KXZ//R12c/0Rbmv9CWpn/RVua/0dbm/9IXJ7/Slqj/0tbpP9KXKL/SVuf/0danv9GWJ7/SVuh/0lbnv9JXJz/SV+e/0lfoP9IXKH/TV2h/2VvpP+Zn7z/+Pv/AP39/wD8/v0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA///+APv9+wDo7+r/t8K6/19vZ/+JnpL/jaKW/5Klmv+YqJ3/h5SK/5Sdlv/2/PYA+/78APr7+QD+/v4A+/r6AP38/AD///8A/v//AP3//wD9//8A/f//AP3//wD+//4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A////AP///gD+//0A/f/9AP3//gD+//4A//7/AP/+/wD///8A/Pz8APr8/gD3/f8A6vX+zKi30P9Xapb/VWqj/2F1sf9abaD/bn2l/8TP5f/y9/8A/Pv/AP76/gD++P0A//7+APz/+wD9//gA+//3AP3/+gD+//4A//7/AP/+/wD///8A///+APn8+AD8/PgA/v77AP//+wD+//sA+Pv2AOjt5//P1dP/rba5/6CotP9jbIX/aHGU/2JsmP9OW43/VWWc/09jnP9Zcqn/S2ac/0tlmP9jeKj/hJO+/6q22f/S3PP/7fT6ZvL3+gD7/fkA/v74AP//9wD///cA///5AP//+gD//vsA///+AP3//wD9//8A/P//APn//wD1//8A2Ojw/5elxP9NVo7/UFqe/05dov9LXqH/Sl6f/0hdnP9EW5r/QlqZ/0Vbmv9HXJr/R1ub/0hZn/9JWqD/SVug/0hbn/9FWZ3/Q1me/0xepP9LXaH/Slyf/01gof9LX6D/Sl2g/1Bgn/9qdaX/qa3H//j6/wD9/v8A/f/+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///gD8//wA7vXwRMHJw/9UYVn/kqSZ/5Wpnv+WqJ3/m6ug/4uYjv+RmpP/4+nj//3//gD+/v0A/fv7AP79/QD//v4A////AP7//wD9//8A/f//AP3//wD9//8A/v/+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD///4A///9AP7//AD9//0A/v/+AP///wD//v8A//7+AP7+/gD9//8A9fr+APH4/gDV4PD/laTH/1hmlf9Zap7/X2+k/1pokv+cp8D/2uLq//j6/QD//P8A//z/AP/9/wD9//4A/f/8APn99wD8/vsA/v79AP78+wD9/PsA/Pz9APn7/wD2+v4A1tve/7q/xf+gpLT/jpOw/3uBqf9hZ5j/ZW+j/2NwpP9cbaD/VGiZ/1NomP9QY5T/Tl+S/1dnm/9qd63/hZDE/56o2/++xur/3N7w/+rr8szx8/UA9fj4AO/48yLi7uv/2uXm/93j6f/i5e//8/L4AP/6/gD/+/4A//z+AP/+/gD///4A//79AP3+/gD5/v8A9P3/AMrZ5f9+jK//TlqS/1Jdof9OX6D/R1qb/0hcm/9IXpz/Rlyc/0Zam/9HW5v/SFya/0dbmf9HWZ3/SFqf/0lboP9IXKD/RVuf/0VboP9KX6T/S12h/0pcn/9MYKL/TGCh/09iov9UZJ//cXym/77C1v/4+/8A/v7/AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD9/fwA/P78APf7+ADc5N7/kZ2W/4iYj/+Ro5r/laed/5mqn/+LmY//fYiA/6Wrpv/9//4A///+AP37+wD+/f0A////AP///wD+//8A/f//AP3//wD+//8A/v//AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//v8A///+AP///QD///wA/v/9AP///gD///8A//7/AP/+/wD9/v8A/f//APj8/wD1/P8A7PT9iNDa8P+gqc3/ZG6a/15om/9lcJv/qbTG/+Pq7f/4+/oA/Pv7AP/9/wD//P8A+/v/APn8/AD6/fwA/f/9APz9+wD7/PsA7/HzIr/Cy/94e5P/gYqq/3B9pf9nd6X/XnGj/1ptpP9UZ6X/SFyf/09jqv9MXqn/SFqi/1Fjpf9jda3/fIq4/5ijx/+0vdj/yc7j/9vc6//n6Pj/7uz3/+Lk6//M1Nz/u8fV/6670/+UosH/coGm/2Z1m/+UocL/y9jt/+30/Wb5+/8A/f3/AP/+/wD///4A//79AP/9/AD+/v0A+/7+APH6/wC6yd3/Znag/1Jfmv9TYKP/TV6f/0VZmP9GXJr/R12c/0ZcnP9HW53/SFyc/0hcmP9GWpf/Rlmb/0dZnv9IW5//SFyg/0dcoP9EXKD/R1yg/0pboP9LXKD/S12h/0xgof9RZKH/V2ac/3uFq//c4O///f3/AP78/gD//v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/v3+APz9/AD4/PoA6O7r/4qUjv9ygXn/kKGY/5epn/+Vp5z/kKCW/46Zkf+lrKb/9/r4AP7//QD8+/sA/v39AP///wD///8A////AP7//wD+//8A////AP///wD///4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v8A/v//AP///gD///0A///8AP///QD///4A//7/AP/9/wD//v8A/P7/AP3//wD7/v4A+P7+APb7/wDx9v4A3+P0/66z1P+GjLf/iZC0/8LK1//u8/FE/P75APz8+QD8/PsA/vv/APv7/wD6/P4A+/7/APv9/QD6/PwA6ezw7ri8yv9yeZX/U1uG/1Rflv9MXJn/TmOi/1NnqP9QY6T/TmCh/1Vnp/9bbKn/b3+4/4aWx/+drdT/r8Dc/8HS4//R4er/2uft/93l7f+8wcv/vcDS/6mrxP+MlLT/eISq/298p/9yfqz/gIm4/4iPvf+aoMj/vcTg/+Pr9//1+/4A+v79APz//AD9//wA/f/8AP3//AD9/v0A/v7+APr+/wDl7/v/pbTU/1Znmf9SYJ//Tl+i/0penf9FWpf/R1ya/0dcnP9GW5z/SFue/0lbnf9JW5n/RlqW/0VYmf9GWZz/R1uf/0hdof9GXaH/RFyg/0Nan/9IWaH/TFyj/0lboP9MXp//UWGd/1tomv+Kk7T/8PP8AP/+/gD//P0A//3/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP79/gD9/v0A+v38APD08wCnsaz/fomC/5Kimv+Xp57/l6ie/5ennf+Wopr/o6qk/+Pm5P/9/vwA/fz7AP7+/gD///8A////AP///wD///8A////AP///wD///8A///+AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f7/AP3//wD///4A///9AP///AD///0A///+AP/+/wD//f8A/v3/APz9/wD8//4A+//9APr+/QD5/fwA+fv8APn6/wDt7f1my87l/8fK3//j5u3/9vj2AP3+9wD+/vgA/Pz5AP79/wD7/P8A+/7/APv+/wD5/f4A7vL5RL7B1v90eZ7/VmCQ/1hnov9TYqL/VmWk/1hmo/9lca3/cn21/3+KvP+Un8n/m6fI/7XE3P/D1OP/ydrl/7THz/+qvsn/qbzN/5utxP+Lm7v/Z3ea/2t7ov9seqb/bHep/3uCt/+OlMP/oabK/72+3f/U1Oz/6Ob1/9DO1//Y19r/8PDxAP7+/QD9//wA/P/7APv/+wD7//wA+//+APv//wD2+/8Azdft/4eWwP9QYZn/Tl2g/0pcof9IXJv/R1yY/0dcmv9HW5z/R1qd/0hanv9JW57/Slua/0Zalv9FWZj/Rlqc/0dbn/9GXaL/RV2i/0RdoP9EXKD/SFih/05cpf9KW6H/TF6e/1Fgmv9nc6L/pKzI//j6/wD///0A//3+AP/9/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+/P0A/v//APz+/gD5/fwA6fLu7n6Igv+OnZX/laWd/5ipn/+YqJ7/j5qS/4uSjP++wb//+vr5AP79/QD//v4A////AP///wD///8A////AP///wD///8A////AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/wD9//8A///+AP///QD///wA///9AP///gD//v8A//7/AP38/wD8/v4A+v78APr/+wD8//sA+/36APr7+gD//P4A/fz/APr8/gD2+v4A/Pz/AP38+wD9/fkA///5AP//+QD9/vwA+v79APv//gD5//4A9Pj+AM/R5f+JjLb/VFiV/1lkqP9MYKf/TWKk/2Fvqv90frL/jpfF/6Kt1P+uvNv/usjh/7XE2P+0xNb/p7fN/5akwv9xfqX/YW2d/2JupP9ZaJ//Vmed/2F2pv9yirP/i6DF/6Gv0v+2vt//xMrl/8nP4v/GzuH/ub/R/7m+zv+SlqT/nqGs/9bZ3//7/f8A/P/+AP3//QD8//wA/P79APz//gD6/P8A8PX/AK+51f9nd6b/UGKe/0pan/9LXKL/Rlua/0ddmf9HXJn/Rlqb/0dZnv9IWZ//Slue/0pcmv9HW5f/RlqZ/0danf9HW6D/Rlyi/0Rdov9FXqH/SWGl/0dXoP9OXKX/Tl6k/1BhoP9TY5r/eoay/8XO5v/7/f4A///6AP/9/QD//f4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/v7/AP3+/gD6/fwA+v78APT6+ABgamX/hZKM/5Wlnf+UpJr/k6Sa/5ShmP+LlY7/k5iV//L18gD9/v4A/v79AP/+/gD//v4A/v7+AP///wD///8A////AP///wD///4A/v/+AP7//gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP///wD///8A////AP///wD9/v8A/f/+AP7//QD+//wA///8AP7//QD+//4A/v7/AP7+/gD//v8A/f/+APr8+QD8/vsA/v/8AP7+/AD+/fwA//z9AP///wD8//4A/P79AP79/gD+/P8A/v3+AP7//AD9//oA+/74APr/+wD5//wA8/v9ANvg8f+cn8L/Z2qh/1tgqP9OWqf/Rl2n/0ljo/9gc6n/iprH/6O02v+es9X/jaPE/3ySt/9keaX/UWGZ/1Ngov9VXaj/XmSx/2tvuf90ebv/f4e//5eizf+su9v/wtLq/73Q4f+4zNn/orfF/4aasP92ian/coSt/1ptnP9neaf/e4yx/5Ggu//L1uP/9fz/APr//wD9/v4A/v37AP/9/wD+/v8A+vr/AOLm9f+Rnb7/T2CT/1Bjof9JW5//TF6i/0Zbmf9HXZn/RlqZ/0Zam/9IWZ7/SVmf/0tbn/9LXZz/SFyZ/0dbmv9GXJ3/R1yh/0Zco/9FXKL/RV+i/0tjp/9GWKH/TFym/09fpf9TY6L/VWSY/4iUuf/g6fv//P/+AP//+gD//PwA//z9AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP7+/gD8/v4A+f79APv//wD4/f0ApKyr/4iTkP+QoJf/lKea/5Smmv+WqJ3/j6CW/3uHgf/e6OL/9vz6APz+/QD+//4A/fv8AP35+gD//v8A//3+AP/+/wD//v8A/f//APn9/QD7/v0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD///8A/v7+AP39/QD+/v4A////AP///wD+//4A/f/+APz//AD8//sA/f/8AP3//AD8//sA/P/8AP3//gD8/v0A///+APz9/AD+/v4A/v7/AP/+/wD9/f8A/Pz/AP38/wD+/v8A/P3/AP3+/wD9/f4A/Pv+APz8/gD8/v4A+//9APr//AD3//0A8fv9ANrl9f+bpsf/aXOi/1ljnf9UYqL/SVme/01epP9MYKL/TmOd/0timP9IYJX/SGCX/0xloP9Saaf/TWGl/1Zmrv9te8L/go3N/5eh1/+yvOb/xtLv/8bU6v+7ytz/kqS0/4+et/97jK7/XHGj/0tgnv9OYqD/YHCq/3yIvP+psd3/w8vv/+Ln/f/z9/8A+vz/AP39/wD//v4A/vz8AP/6+AD//v8A+/7/APL8/wC3ydb/bIOk/0ZdkP9HX5v/SWGc/0hfm/9HXJr/R1ya/0hbmv9HWpr/R1qa/0lcnP9KXZ3/SV2e/0ZanP9DWpz/Rlud/0dcn/9HXKD/SFyg/0pdof9KX6P/RV+m/0dhpv9GXaL/T2Ke/1Nhi/+Mla3/7vL7RP39/wD//v8A//3/AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+/v4A/f//APv//wD7//8A+f/+AOLq6f+eqKb/kaCY/5epnf+XqZ3/lKab/5Okmv+Tn5n/kZuV/+Pr5//7/v0A/v//AP/+/wD//v8A//3+AP75+gD/+/wA/Pv7AP3+/gD8//8A/f//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD+/v4A////AP///wD///8A/v7+AP7+/gD+/v4A/fv9AP/8/wD//P8A//3/AP/7/wD//P8A//7/AP/+/wD///8A////AP3//gD9//4A+Pz6APn+/AD7//4A+v/+APn+/QD3/fsA+//+AP/9/gD//PwA/vv4AP/9+AD///kA/f/4APn+9gD3/voA9v//ANXg8P+Aiqn/XGeS/1lkm/9UYaD/TFuf/1Fjp/9LXqL/RV6d/0Zmn/9EY5z/R16e/01bof9karD/kpPV/6ur3v/Gyuz/z9jq/8/d6P+4y9f/pbjQ/4+hyf9meK7/SFib/ztKlv8/Tpz/WmSw/3p9wf+hn9n/y8nx/+np/+7z9v0A9Pv5APf++wD4//0A9///APj9/wD7/P8A/Pr8APr29wD//v8A/f7/APX6/wDi7f3/kaG+/1ltmP9PZZz/TWOh/09kov9OY6H/S1+d/0hcmv9GWpn/R1qa/0hbm/9JXJz/Sl2d/0lcnP9HWpr/RVmb/0ZanP9HXJ7/R12f/0leof9LX6P/Qlmd/0tlrP9DXaP/S2Cj/09gmv9ue6P/tb3P//r8/wD//f8A//7/AP/9/wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/v//APv9/QD4+voA+v/+APr//wDr8fCqYGlm/3OAef+TpZv/lqic/5Olmv+UpZv/jpqU/0ZRS//Hz8v/9Pj2APr8+wD8/PwA//7+AP/7/AD//P0A//7/APz8/AD9/v4A/P7+AP3+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v4A//7+AP79/QD+/f0A//7+AP/9/QD//v8A/Pv8APz8+wD8/PsA/v79AP///gD9/v0A/Pz7AP///gD///8A////AP7//gD+//8A+vr7APr6+wD+/v8A/f3+APv5+wD//f8A//78AP//+QD///YA/f/0APv/9AD5//oA8fn7AMnT5P+KlLf/Ymub/1Zgmv9PXZ3/SVmc/0hbnf9NY6P/R12d/0xhof9NXqD/cH6+/5um4f+0v/L/usjx/7rI6f+qu9X/lajD/4SYtv9nfKT/WW2g/1Fjof9LW6D/UmCm/257u/+RndX/qbXi/8vU8v/g5ff/7O/6iPX3/QD3+P0A7O70iN7h6v/DyNT/p62//42Vp/+2vcr/6O/0//r+/QD9/vgA/f/4APf8+wD0/P4Ay9Xu/3qHsP9RYpb/UGSg/0xgoP9NYKD/TmGh/0xfn/9IW5v/R1qa/0lcnP9KXZ3/Sl2d/0pdnf9JXJz/RlmZ/0VYmf9GWpr/SFuc/0hdnf9JXZ7/SV2g/0tgpP9KYqj/Q1yg/0xfn/9aap//k5/D/9nf7//7/P8A//3/AP/+/wD//v8A///+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPv8/AD5+/sA/P7+APz+/gD5+/sA9/v7AKyzsf96hoH/gZKK/5SmnP+TpZz/l6mg/5Wknv9we3X/vsjD/+708ET8/vwA/v79AP/+/gD//P0A//3+AP///wD+/v4A/f39AP3+/gD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP///wD//v4A/vz8AP/+/gD+/v4A/v7+AP///wD9//4A+v/+APn+/gD6/v0A+v79APv//QD8//wA/P77APz9+QD8/PkA//77AP///QD//PoA//z6AP79+wD+/fwA//38AP/6+gD+/P4A//r/AP/9/gD6+/kA9/z4APb++gDg6+v/tsPO/6q20f9reKD/Xmqg/1popv9SYqP/TV+g/0tfn/9JXZ3/R12b/0xhoP9PYJ3/XWeh/4GJvf+XpNH/k6fP/32Yvf9lg6v/WHqn/0lnn/8/XZr/S2Sl/1xwr/98jsX/o7Db/7jE4//M2Or/4+r1/+jv9f/j7fD/0+Xm/7bM0f+escD/iJWx/2dwl/92e6v/ZGma/4CItP+NmLn/wMzd/+r3+cz1//gA+//1APj+9QD5/v4A7PL/iJykx/9jb6D/UF+Z/01env9JW53/SFud/0pcnv9LXJ7/Slqd/0pbnP9LXZ3/TF6e/0tdnf9LXJz/Slub/0hZmf9FWJj/RlmZ/0damv9IW5v/SFyd/0tfof9RZan/SV6m/1Blqf9SYp//WmeY/5Wfv//c4e//+vv/AP/+/wD///8A////AP7//gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD9/f0A/f39AP7//wD8/v4A+/39APz//wDk6uj/k56Z/4CQif+TpZz/lqif/5eooP+XpJ7/lqKc/4yWkP/Q1tH/9vn2APz+/AD9/fwA//7+AP/9/QD+/PwA//7+AP39/QD9/v4A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/gD///8A//7+AP7+/gD///8A////AP39/QD8/f0A/f7+APz9/wD8/v8A+/3+AP3+/gD9/v0A+/38APz9+wD+/vwA/f77AP7/+wD+//wA/v77AP3++gD7/fgA+/35APv8+ADw8O1m2dze/9ba5f+0vsv/pK+9/6Gvvv+Sobb/eYqm/2x+pf9jdan/U2Wh/09go/9PYaX/S2Ch/0pgnv9LYZz/SV6Z/0dcmP9OX6H/TF6d/1Vkn/9icqn/Znqw/1xzq/9MZZ3/Q16X/0xmnv9nfrX/fpXG/5+y2/+wwuL/wtLo/9De7f/N2ub/xM/b/6exv/+hrMH/ipiz/3mOsP9mgKn/WnKg/1hrnf9ibqH/hY2//6Cn0f/AyeP/z9ro/+fv9f/2/fsA+v/5AP3/+wD7/PsA+Pn/AMXL5/9teKX/UmGZ/1Bgnv9MXJ3/Slue/0lanf9IWZz/Slqd/0tbnv9LXJ7/Slud/0lbnP9JXJz/SVub/0pbm/9JWpr/R1mZ/0dYmP9HWZn/R1qa/0hbnP9MXqH/UWSp/0pcpP9NXqL/Vmae/3WBqv+2vtX/7/L5Iv39/wD//v8A////AP7//gD8//4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A/f7+APz9/QD8/f0A9vv5ALS9uf+Bjoj/gpOL/5eooP+UpZ3/k6Oc/56rpf91gHr/vMS//+/z8CL7/vsA/P77AP/+/QD//v0A/v39AP///wD8/PwA/P7+AP7//wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/Pz8AP3+/gD9/v4A/P39AP3+/gD9/v4A/f39AP7+/gD+/vwA//76AP79+wD//vwA/v79AP39/AD8/f0A+vz8APD09wDw9fsA2d/m/9HY4v+6xM//tsHN/666xv+ap7X/laKx/3+Oof+Ck67/bH+f/2J1mf9idp7/VmqZ/0ldlP9OYqH/SFyj/01gqf9FW6P/SWCk/0tiov9JYJ3/R16Y/0ldmP9KXZv/TFyg/0tdof9LX6P/SV+h/0xhpP9Waar/aHm1/4KOxP+Vos//q7fa/7bD3/+/zOP/rrzT/6Kwyv+Zpsb/hpO5/3yHtP9kcqL/Y3On/1FhmP9QYJz/YHCs/3uHvf+Yo83/t7/d/9DU6v/q7vrM9vn9APr+/gD8/v4A/f39AP78/QD/+/8A/fr/AOvt/qqXocT/WGqc/0lfmf9KXp3/Slyd/0tbnv9KWp3/R1ib/0hZnP9LXJ//S12g/0hanf9HWZv/R1qc/0hanP9IWpz/SFqc/0lZm/9HWJn/R1iZ/0dYmv9IWpz/S16g/01gpP9MXaX/TVyf/1Ngk/+FkLP/ztPk//n6/wD//v8A//7/AP///gD8//4A+//+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP7+/gD+/v4A/v7+AP///wD9/f0A/f39APr9/ADZ4N3/kZ2Z/3eHgf+TpJ3/kKGa/5Gim/+Yp6H/h5SN/6ewqv/a4Nz/+Pz4AP3++wD//fwA///+AP///wD+/v4A/Pz8APz9/QD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f7+APv7+wD8/v4A/P7+APv9/QD5/PwA+v39APz+/gD7/fsA/P75APb48gD5+/gA6+7tquDj5P/a3eD/0NXc/7vCzP+eqLj/r7rR/42atv+CkbH/anug/2h5ov9bb5v/Q1mI/0hej/9FXY7/TmSU/05llv9OZJv/UWej/1FmqP9PYqn/TWGs/0perP9FWqX/RVuk/0lgpf9MYqT/S2Ch/0pfnv9KXp7/S16f/01dov9KXKH/Sl2j/0lcof9UZqb/dIO9/5ml2P+qst7/qLLX/5qixf+VnsP/hZG6/215qf9gbab/WGal/0lXmf9LV5v/VmSk/217uP92grn/jJTE/6uv1//Nzuv/6er37vn5/AD9/foA///5AP/+/AD//f4A//z/AP/7/wD/+v8A//r/APj3/QDIz+n/bXyi/1Fpnf9FYp//Q1yd/0hbnf9KW53/SFmb/0ZXmf9HWZv/Slye/0pdn/9IW57/R1qc/0danP9HWZz/R1mc/0hanf9IWZz/R1ib/0dXm/9IWZz/SFud/0ldnv9MX6L/TFym/1Feof9VX4//l6C+/9zg7v/7+v8A//7/AP/+/wD///4A/P/9APr//QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD9/f0A/Pz8AP39/QD//v4A////AP/+/gD9//8A9fv5AL3Ixf+LmpX/iJmS/5Gim/+Ropv/lqWf/5mooP+KlI3/r7aw/+Po4//9/vsA//78AP///QD///8A/f39AP39/QD8/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//8A/v//AP3//wD8/v4A/P/+APr9/QD3+/sA6u/uzOfs6//n7O3/2N7i/8zU3f+1v8v/xM3b/6izw/+TnrD/iJOq/3mGof9ca4n/PUxv/1pqk/9OYI7/Sl2Q/01imP9MYp3/Sl6e/0leoP9JYaP/TmWm/1FmpP9MYZ7/Sl6i/05hqf9OYav/TF2p/0xeqP9KXKX/S16k/09iqf9JXKP/SVuj/05gqP9QYqr/TF6m/0pdo/9OYaT/SFqa/01cnP9RX5z/Xmyl/3qIvv+KnM7/eYm7/1xwo/9HWJL/T2Cf/0dXnP9PXqb/VGKp/1Zjp/9hbav/bXit/4ONuv+osdj/xc3q/+Xq+f/3+f8A/Pz9AP7++gD+/voA/v/5AP7/+wD9/f0A/fz9AP38/gD8/P4A/Pz+APr7/gDm6vP/kJq5/1NlkP9JY5v/RWKj/0Veof9IW5z/SFqa/0ZZmf9GWZn/R1qa/0dbm/9HW5z/R1ud/0hcnv9HW53/Rlqc/0dZnP9IWp3/R1md/0ZYnP9IWJ3/SVqe/0lbnv9IXJ7/TWGj/0xcpv9RXZ//a3Oh/7zD2//z9P4A//z/AP/+/wD//v8A///+AP3//AD6//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD//v4A/vz8AP///wD9/PwA/f79APr//gDn8e7/qriz/3iIgv+Wp6D/laaf/5Olnv+VpZ3/jZqS/4ONhf+1vLX/9/v2AP///AD//vsA////AP///wD///8A/Pz8AP39/QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP///wD///4A///+AP///gD+//4A+v38APb5+QDy9fcA6+/xquft7//a4OT/09rf/7e+xP+ttLv/qrK+/4OPpv9aapH/NEZ3/1FjlP9NX5D/R1qM/0ldj/9OYpP/UWWW/1Fllv9OYpf/Umab/0ximv9MYZv/S2Cb/0xhnf9OYp7/SV2a/0hdm/9JXZ3/Sl6h/09hqf9TY63/VWSt/1dmrf9XZ6r/U2Og/2Bxrf9gcK7/XGqu/11qtP9aaLX/U2Gx/0xdq/9JXqf/RVyf/0lfnf9NXJ//UV6h/1Jho/9PYaP/SWCg/0VgoP9HZKT/SWWn/0Zgo/9QYqb/YG6v/2dwqv97grL/rbPY/8/V8P/q8f7M8/r9APb+/QD4//0A+v/+APz//gD8/v4A/P39APv+/AD7/vwA+v78APj/+gD3//oA9v/7APT9/ADo8fj/tcDY/1xok/9YaZ//S2Gj/0Rdo/9HXqL/TF6f/0lbmv9GWZn/R1qa/0Zamv9FWZn/RVmZ/0ZanP9IXJ3/R1yd/0danP9HWp3/SFue/0hanv9HWZ3/SVme/0tboP9LXqL/S2Ci/0xgo/9SX6j/VGCe/5GZwv/Z3u3/+vr+AP77/QD//v4A//7/AP///gD8//wA+v/9AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///gD///4A/f/8AMzTzf+Ml5H/jJyV/5iso/+Qpp3/j6aa/5Wqnv93iX//m6ig/+jw6v/9//wA///8AP///gD//v0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//3+AP76+gD///wA///7AP7/+QD7/fgA+Pv4AObs6//L1Nf/uMPM/5Kerf9/jaT/TVt7/0ZVff8/T4D/QVGL/05dnf9PYaD/UmWf/1Rnof9VaKP/UmOh/0tdmv9MX5n/TWCZ/0ldlf9MYJf/TF+Z/0xemv9KXZ7/SVuf/0pcoP9LXaL/TF+i/0xgoP9JYJ//Ql+h/0FeoP9PZa//U2Kt/11nr/9/g8X/qKri/7+/6//V1fv/2dn+/9PU///DxPr/o6fm/3uEzP9babT/SF2m/0Nfo/9EYqX/QWCk/0Ffpf9EYKn/SmOu/1BksP9SYa3/UF2m/1Jcov9ZXp//f4O8/6ms3P/R0vT/7vD/RPn8/wD6//4A+//5AP//8wD+//EA/v/2AP7/+wD8//0A+v7+APv+/wD8+/8A/vn/AP/6/wD//f0A///6APz/+gDw+vwAxdfn/1xzlv9IXJT/T2Gj/05bo/9SXqb/Ul6k/01coP9KW53/R1qa/0damv9IXJr/SFya/0dcmv9HXJr/R1ya/0hdm/9KXp3/TF+f/0xfn/9LXaD/S12g/01dof9NXqL/S1+j/0hgpf9MZKf/W2qp/2Junf+xvNL/6e/07vn8+gD6+voA//3/AP/7/wD//f8A///7AP//+QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A/f38APv9+wDm6+f/nqah/0ZUTv+RpZz/laui/4+lnP+TqJ//i52V/5ilnf/I0cr//P/8AP3++wD4+fcA/v79AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD9/v4A+/3+APn9/wDx9fwAsrjD/3uElP9ncoj/ZHCL/2Fuj/9ZZ4z/WmmS/1FjkP9UZpj/U2ac/09jnf9NYJ3/S16d/09env9NXZn/T2GY/1Bjl/9MYJP/TGGU/0pflv9GW5X/S1+b/0hdm/9MYp3/S2Gb/0lgmv9JXpz/TF+i/09gqf9NXK7/TFmt/1pmuv9YY67/eIG+/6Cs2P/I1O//5e/6/+/3+iLw9/QA+Pv4APj8+AD1+/kA8vr8AOv2/arh7Pr/ytT1/56o4v9ueML/T1yr/1Bdq/9RYav/UWGn/1BfoP9TYZ3/ZHCm/4eRv/+zveT/1d31/+Tq+f/u8vxE9fj+APn8/wD6/P8A/fz/AP/+/gD///4A//79APz7+QD9/PsA/f7+APr9/gD8/v8A//3/AP/9/wD//f8A//z8AP/9/AD7//4A2eXs/46guv9NYor/V2yl/01gof9NXaL/Tluh/01boP9LW5//SVqd/0dZnP9IWp3/SFub/0hbm/9IXJv/SF2b/0hdm/9HW5v/SFyc/0pdnf9KXZ3/Slyf/0tdoP9NXqL/Tl+j/0xgpP9HYKL/SWCe/1NhnP94g6//0Nrq//T7/QD7/v0A//39AP/7/QD/+/8A//3/AP///AD///oAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP7//gD8/v0A9/r5ANDX1f98iIP/XGxl/5KknP+Po5r/jqGY/5Gimv+Snpj/qrOu/+Xr6f/7//0A/f79AP///wD9/f4A/v3+AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/AD6/vkA+v78APX6/wDv9v8imqK5/213mP9ea5P/WWeW/1Zmmv9YZ6D/VWah/0xfmv9OYZz/S1+b/0pgmv9JYJn/Rl6X/0hdl/9NXZj/Tl+Z/1Fim/9QZJv/SV+W/0Zdk/9EW5P/QFeT/0Vcmv9FW5z/R1yf/0pcof9RY6n/UmGn/1Beo/9TYaT/XGmp/3F8tv+lq9r/wsjq/9rg8f/o7/b/8fj3APj99wD9//sA/f39AP35/AD+/f0A/v/+AP3/+gD5/vcA9v34APL4/ADj7Pr/z9Xz/7W65f+fotb/kZbL/5GWyv+ip9T/u8Pl/9HZ7P/e5fL/6O/5//P6/QD5/fsA+/76AP3++wD+//wA/v79AP/+/wD//f8A//v/AP/8/wD+/f4A//7+AP78/gD8+v4A/v7/AP39/QD7/PkA/v78AP7+/gD8/P4A6Oz2/6+60/9ndp3/VGeY/05jnf9JXJz/S12h/0xdov9MW6H/S1qf/0lZnv9IWp3/SFqd/0hanf9IWp3/SFyc/0ldnf9JXZ3/SFyc/0hcnP9JW57/SVue/0pcn/9LXaD/TV2h/05eov9NYKP/TmOj/1Jmof9SYpb/pbLW/+Ts9//2+/wA+/38AP/+/gD//P0A//z/AP/9/wD///4A///7AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A/v//APv//gDu9fNEwszK/3aCfv+CkY3/jJ2W/5KjnP+Sopv/jJmT/5Cblv+4wL3/9/z6APz+/gD9/v4A/vz+AP/9/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+//wA/P/6APr//QD1+/8AxtDl/2x4nP9WZJL/U2Wa/1Bkn/9PY6P/TF+h/01hpP9JXKH/TF+j/01hov9KX57/SWCa/0dhmP9FXpT/SV6U/0xhmf9MYJr/S2Cc/0Zdmf9GXZr/R16c/0Rbnf9IXaH/TF2o/09dqv9MV6X/Vl+r/2Nrrv95g7r/maXP/7PB3P/M2Oj/5er1//T2/AD6+/0A/f79AP3+/QD9/fsA//38AP/9/wD//P4A//3/AP/9/wD//v0A/v78AP3+/AD9//4A+/39APb3/ADu8PtE5OT1/97f8f/f3/D/5+f1//Hz/AD6+/8A+/3/APv9/gD9//0A/f75AP39+AD+/vkA///7AP///AD//v0A/vz6AP/9/QD//v4A//7/AP/9/gD+/P4A/vz+AP/9/gD+/vwA/P76APz//AD4/f4A8fb/AMHH4f96ha//VmWZ/1Fkm/9OY53/SV6b/0xfoP9JXKD/SVme/0pZnv9JWZ7/SFqe/0lbnv9IWp3/R1mc/0hbnf9JXZ7/SV2f/0ldnv9JXJ7/Slyf/0pcn/9KXJ//S12g/0xdoP9NXaD/TF6e/01gnP9UZpv/ZHGc/8vV7f/v9v0i9/z9APz9/AD//v4A//3/AP/9/wD//v8A///+AP7//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A/v3+AP3+/gD8//8A+f39AObt7P+psq//go6L/46blf+Topz/kqKb/42clv+Gko7/jpiV/8nRzv/6/v0A+vz8AP79/gD//f4A//7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///9AP3++wD3+vwA8fb/AJSeuv9fbJb/UWOW/09knP9MZJ//SWOg/0lgnv9LYaD/SVug/0lan/9MXaH/SFub/0ddmf9JYZn/RF2W/0ZcmP9IX53/Rl6b/0lgnv9HX53/SF6f/0peov9KW6P/TVym/1Vgqv9ia7L/dXu6/5ed0f+yuNz/yM7m/93k8f/r8vaq+f/9APz9/QD+/v4A/vv+AP78/gD//f8A//z/AP79/gD+/vwA/f/5AP7++gD+/vsA//7+AP/9/wD//f8A//z/AP78/gD+/f0A/v7+AP39/AD9/fsA/v37AP39+gD8/PoA/v37AP///gD//v8A//7+AP79/AD+/fwA//78AP///QD///0A/v/9AP3++gD+/vwA/f/8AP7+/QD//f4A//3+AP78/gD9+/sA///8APj9+AD4//0A8/z/ANPc7/+RmsL/WWWa/1Ninv9LXZr/T2Sf/0lfmv9IXJz/R1qd/0dZnv9JWZ7/SVme/0hanv9IWp7/SFqd/0dZnP9HWp3/SFuf/0lcn/9JXZ//SV2f/0pcn/9LXaD/S12g/0tdoP9MXqD/TV6d/09fnP9TY5v/XGmX/5CbvP/g6fb/9fv/APv9/gD+/v4A//7+AP/9/gD///8A////AP///wD9//4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP79/gD8/P0A/P39APz+/gD0+PcA2d/c/5afnf+AioX/jZqU/5OinP+Sopv/i5qU/4aSjf+fqaX/2d/d//T39gD9/v4A//7+AP/+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/QD8+/oA9/j7AOTn+v9wepz/XWqY/1Nmmv9NZZr/SWSZ/0hjmP9LZJn/TWOb/0tcnP9LWZz/TFqe/0lZnP9JXJv/S2Cc/0dem/9HXJ3/SV+i/0heoP9OY6T/TWCh/0xen/9TYqP/X2yt/3J9uP+Qmcz/pa7V/77G4v/f5fX/8/f8APr8+wD8/fwA+/v7AP7+/QD+/fwA/v79APz8/QD9/P4A/f3/AP39/wD8/v4A+//8APr/+AD6//kA+//7AP3//gD8/f4A/Pv+AP38/gD+/P4A/v7+APv9+gD9//kA/v/4AP7/+QD+/vkA/f34AP39+gD+//wA/v79AP/+/gD+/v0A/v7+AP7//wD+//8A/v/+APz//gD8//0A/P/9APr9/AD8/f0A/v7+AP/+/wD+/f4A/fz8AP7//QD0+vkA8/3+AN3q9v+hrs//ZnSm/05emf9RYqH/TV+d/0tdm/9HXJj/RVmZ/0ZanP9IW57/SVqe/0lZnv9IWp7/SFqe/0dZnf9GWJz/Rlic/0ZZnf9HW57/R1ud/0hcnv9KXJ//S12g/0xeoP9MXqD/TV+f/09fnf9UYpz/Xmqd/3J+o//H0eb/7vb8RPn+/wD+/v8A//7/AP/9/gD//P0A////AP7//wD+//8A/f//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD+/f4A/Pv8APz8/AD9//4A/P7+APb7+ADCycX/fIV//4SRiv+QoJj/k6Wc/5Olnf+SoZr/kJyW/6Gppf/t8u9m/f79AP7+/gD//v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/f0A/Pr6APb2/QDT1PH/YGqT/1pom/9SZ57/S2Sa/0hjl/9HYpT/SmOU/05hl/9OXZr/UFyf/09bof9OXKL/TV+i/01ioP9NYp//TmGe/09hn/9OXJ3/W2ip/2VxsP9yfLX/h5C//6Cpzv+6xN3/4ev5//H5/AD4/v0A+//8APv9+gD8/fkA//79AP/9/gD//v8A/v79AP3//QD7/vwA+/78APv//QD7//0A+//+APv//gD5//wA+f78APr//AD6//0A+v38APr9+wD7/fwA/P79AP3//QD5/PgA+/76APz++gD9//sA/v78AP3++wD9/vwA/f77AP7++wD9/voA/f77AP7+/AD+//4A/f//APz+/gD7/v4A+///APz+/wD5+/4A+/3/AP3+/gD+/v4A///+AP///gD8/v8A8/n+AODs+P+ouNb/aXyp/01hl/9OYZ3/Tl+d/1FioP9IWJf/RlmY/0lcnP9HW5z/R1qd/0pbnv9JWZ3/R1mc/0hanf9HWZ3/Rlic/0VYnP9FWJz/Rlmc/0ZanP9HW53/SVud/0pdn/9LXp7/TF+e/05gn/9RYZ7/VmOb/2dwn/+ZosH/7PX+iPf+/gD8/v4A/v7+AP/+/wD//f4A//z9AP///gD9//8A/f7/APv//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A/Pz8AP39/QD///4A///+AP7//QD6/fkA9vv2AK21rv+FkYn/hpaN/5Cjmv+Tpp3/kaOa/4SUjP94gnz/4Ofi//v9+gD9/PsA/v38AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////APz9/wDw8vwAyM3s/19pmf9VZJ//TmKi/0tioP9KY5z/SWGY/0xhmP9OXpn/UVyf/1Fbov9PWqT/T16m/0xgpf9LYqH/UWaf/1xqoP9kbqL/aW+i/4WLuf+lrNP/xszr/+Po+f/0+v4A+f/+APv++gD9/vkA/f75AP7+/QD//v8A//7/AP/9/gD//P0A/v/+AP38/gD9/v4A/f/+APz//AD8//oA/P/7AP3+/AD9/v0A/P39AP3+/gD8/vsA+/76APz++gD9/vsA/f78APv+/AD9/v8A/f7+APr9/gD6/v4A+/7/APv9/QD6/PwA+/78AP3//gD//v0A+/v5AP7++wD///wA///+AP7+/gD9/f0A+/39AP3+/wD9/f4A/f3/APz+/wD6/f0A/P37AP//+wD+//wA+vz/AOzy+oiuutr/ZXaj/0hekf9IYJf/SWCZ/0pemf9LXZn/Tlyd/0pam/9NX6H/Rlqc/0ZYmv9KWp3/SFmb/0ZYm/9HWZz/R1md/0ZYnP9FWJz/RVic/0VZnP9FWZv/R1qc/0hbm/9JXJz/SV2c/0penP9OYJ3/U2Oe/1hkmv9yeaT/xcvl//X9/gD6//wA/Pz7AP79/gD//f8A//3/AP/+/wD///4A/f/+AP39/wD7/v8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A//79AP79/AD///4A///8APj79wDl6+X/naih/3uJgP+ClYr/kKSa/4+jmv+Km5P/VWBZ/7a9t//s7unu/f35AP///QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//AP3//wD7/f8A8/f/AODn//9+iLr/V2Wl/1BhqP9MY6n/SWGj/0lenv9NXp7/TFqc/1hhqv9UXan/VF+s/1JirP9PZKf/Tmej/1tupf9/hLj/sbLd/9fZ9v/w9f0A9/3+APn/+wD8//oA/f/7AP3+/QD/+/8A//v/AP/7/wD++v8A/vv/AP7+/wD+//0A/f/7AP3/+wD///wA/v37AP/+/gD//v8A//7/AP/9/wD//P8A//n/AP/7/wD/+/8A//v/AP/8/wD//P8A//z/AP/9/gD///8A///+APv8+wD8/v4A+v79APj9/QD6/f4A/P//AP3//wD7/f4A/fz/AP/+/wD//v8A//3+AP/9/gD//v4A////AP///gD///4A///8AP3//QD8//4A+v79APv++wD+//oA/P38APH0/ADCxuD/YmyX/0pbkP9MYpr/S2Oc/0hhmf9MYpr/TF+Z/0xZmP9QXqD/Q1WW/0dam/9LXp7/Slub/0hamv9GWJz/R1mc/0dZnP9HWZz/Rlmc/0VYnP9FWZz/Rlqd/0dbnf9HW5v/R1ya/0hcmv9JXZn/TWCc/1NjnP9ibqD/k5i9//D0/gD3/v4A+//6AP7//QD//v8A//3/AP/9/wD//v4A///9AP3//gD9/v8A/P7/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP3//wD9//8A/f//AP7//wD//v4A//39AP/+/wD//v8A9/b1AN/h3/94fnr/ZW9p/5eknf+PoJf/jqCX/4mXjf9ea2H/o62i/+Pq4f/8//sA/v79AP79/QD///8A//7+APz8/AD+/v4A/v/+AP3//gD9//4A/f/+AP3//gD9//8A/f//AP3//wD9//8A/f//AP3//wD9/v8A/Pz/APf4/wD19/8Axcvu/3uFtv9WZJ//VGSp/1JlsP9IXqv/TGOu/05krP9PYaL/SluT/1hkl/9pc5//iZC1/7e82f/f4/X/9PX7APj4+wD7+/0A/f3+AP7//wD///8A/v/+AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A+///APr//wD6/v8A/P7/AP7//wD///8A///+AP///gD///4A//7/APz9/gD6/P4A+/z/APz+/wD9//8A/P7/APz+/gD9/f0A/v7+AP///gD7/fwA9vz7AO33+2bBzub/XWyT/01alP9MWZ//S1uk/0tdo/9MYJ//TWSY/0xklP9JYpH/SF+T/0hZm/9IWJ//Rlie/0Vam/9GW53/SVmi/0lZov9MW57/TFyc/0xdnP9KXJz/RVmd/0BXof9BWaT/QFqg/z9Ymv9GXZf/Sl+W/1Rknv9YYpj/gYer/97e6//39/cA////AP7+/gD9/f0A/v7+AP///wD///8A/v7+AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD9//8A/P7+APz+/gD9//8A////AP///wD//v8A////AP7+/QD19/UA1NnW/6Gqpf+IlY//mamh/5Smnf+TpJn/hZWK/257b/+ut63/7PDrqv///gD+/f0A+/r6AP/+/gD//v4A/v/9APv8+gD7/PoA/f79AP3//gD9//4A/f//AP3//wD+//8A////AP///wD///4A///8AP7++gD6/PkA+f3/AO7z/UTFy+z/j5fH/2dxq/9WYaP/UV6i/05cnv9ebKn/d4S6/5SgzP+9x+b/1t/v/+Xr8//v9Pki9vr9APv9/QD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f//AP3+/wD7/v8A/P//AP3//wD+//8A////AP///gD///8A////AP38/QD8/f4A/f7/APz+/gD7/f4A////AP///wD///4A///+AP///QD7/vwA+Pz9AO3y+ma/yeD/Y2+T/05civ9RYZv/T1+f/0tcn/9KXJ//Sl6d/0pfm/9JXpj/SVyW/0hal/9IWZn/R1ib/0ZZm/9HXJz/R12e/0pboP9JW5//Slqd/0pcnP9KXZ3/SFyd/0NZnv9DWKH/Rl6m/0Ncof9EXJz/TGKd/01gl/9MW5H/bnio/7S62f/y8/wA/v7+AP///wD///8A////AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP3//wD8/v4A/f7+AP///wD///8A//3+AP/+/gD///4A/P79APX5+ADCysf/gY6I/3eHgP+Zq6L/kqOY/5KjmP9+j4P/gI2D/6+3sf/n6eb//f38AP79/QD///4A///+APz9+wD9/fwA/f78AP7//QD+//8A/v//AP7//wD+//8A////AP///wD///8A///+AP/++wD+/fgA/v77AP7+/wD6/P8A7/H8Itrd8v++xeL/rrbZ/6622v+2vuD/x9Dn/9Pb7f/e5PP/7fL8ZvX5/wD6/P8A/f7/AP3+/wD9/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD+//8A/v//AP7//wD+//8A////AP/+/wD//v8A/v//AP7//wD9/v4A/f7/APz+/gD7/PsA+/z7AP///gD//vsA/vz5AP7+/AD8//0A+P7+AOvy+6rCyeT/eoOr/1RekP9SX5f/TV6Z/0tfmf9KX5n/Sl6a/0pdnP9LW57/Slmf/0hXnf9IWJv/SVqY/0hbmP9HW5v/R1ud/0dcnv9JXJ3/SFua/0hZmv9IWpz/SVue/0dbn/9EWp7/RFqf/0Vdof9KYKP/Rlya/09hnP9UZJv/VGCS/4yVvP/d4vf/+vv/AP///wD///8A////AP///wD+/v4A/f39AP7+/gD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD+//8A/f7+AP39/QD//v4A////AP/+/gD+/P0A/v7+AP3+/gD7//4A5u3q/7zGwf+QnJb/b394/46flv+SpJr/kKOY/4SVi/+Jk4z/tbm1/+/y7yL8/v0A/P38AP///gD8/fsA/P38AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v0A//78AP///QD9/f0A+/v8APr8/QD4+v4A8fT7AOvv9qrq7vXM7vL4RPj8/QD7/fwA+fv6APv9/QD9/f8A/v3/AP79/gD9/P0A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///8A////AP///wD//v8A/v7/APz+/wD8//8A/v//AP39/gD9/f0A/f38AP39/AD///4A//78AP79+wD9/v0A9vz+AOHs9/+zv9j/e4Su/15pnf9VYp7/Tl+d/0hclv9JX5f/SmCY/0pfmf9KXJz/Slmf/0lXof9IVaD/SFed/0lamf9JXZn/SFub/0Zanv9GW53/SFuZ/0Zalv9IWZj/SFqc/0laoP9HWqH/R1uh/0Vcn/9DXJz/TGGh/0tfnP9QYZr/WGaY/3B7pP+xutT/8fb/APv9/wD+/v4A/f39AP7+/gD///8A/v7+AP39/QD+/v4A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/f0A//7+AP///wD///8A//7+AP7+/gD9/f0A/P7+APr//QDv9fMiyNDM/3uHgv9/jof/kKKa/5OmnP+Uppz/iZaN/5yknf/T2dT/7PDtiPn9+gD+//4A/P78APz9/AD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///4A///9AP3+/AD7/foA/f38AP39/AD8/f0A/f7+AP7+/wD9/v0A/P37APv8+gD+//wA/v/8AP3++QD+/voA///7AP/+/AD+/vwA/vz8AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///0A//7+AP/+/wD///8A/v7/APz+/wD7/v8A+///AP7//wD9/fwA/f38AP///gD///4A//7+AP7+/gD9/f4A9/r/ANvk9P+ottL/coCp/1RhlP9UZJz/UGGe/0pdnP9HXJn/SV+a/0tfm/9LXpr/Slub/0pYnv9IVZ7/R1Wd/0dXnP9JWpv/SVyb/0hbnv9HWqD/Rlue/0hcmP9GW5X/R1qY/0hbnP9JWqL/SVuk/0ddpP9GXqD/RVyb/0xhn/9RZJ7/VmSZ/2Jtmf+bpMP/2uDu//j8/wD8/v8A/v7+APz8/AD9/f0A////AP///wD+/v4A/v7+AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD//v4A//7+AP///wD///8A/f7+AP7//wD///8A/v7+AP3+/gD9/v4A/P/+APD08wDBycb/iZSO/3yMhf+JmpL/k6Wc/5Wjmv+Yo5v/oaqi/6+2sP/0+/UA/P/9APn8+wD8/f0A/v7+AP/+/wD///8A////AP///wD///8A////AP///wD///4A///9AP3//AD7/fsA+fz5AP7//QD9/v8A/f3/AP79/wD//f8A//3/AP/9/gD//v8A//79AP39+wD///wA///7AP7++wD+//sA/v/8AP7//QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///9AP///gD///8A/v//APz+/wD7/v8A/P//APz//wD+//8A/f78AP7+/AD//v4A/v3+APr7/wD6/f8A7vH6RM7U5f+fq8z/anqn/09hlP9TZZv/TGGZ/0tfmv9KX5v/SV6d/0ldnf9LXJ7/TFue/0tanP9KV5r/SFaZ/0ZWmf9HV5n/SFmc/0hbnv9JW6H/SFui/0hdn/9JXZr/R1yW/0dbmP9HW5z/SVuh/0lbpP9GXKP/Rl6h/0lfoP9KXZv/V2ef/1xnl/+EjLD/ys/j//T4/wD8/v8A/f7/AP///wD+/v4A/v7+AP///wD///8A/v7+AP7+/gD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD//v4A////AP///wD///8A/v7+APr8/AD8/v4A////AP///wD//v4A/v3+AP7+/gD9/v0A8vf1AMTNyP94hX//gZGK/4uclP+To5r/j5yT/3SAdv9rdGz/1d/X//T59wD0+fcA+/38AP7+/gD+/v4A//7/AP/+/wD///8A////AP///wD///8A///+AP7//gD8/v4A+/3+APz+/wD9/f8A+/z/APz7/wD+/f8A//3+AP/9/QD//v0A///9AP79+gD6+/YA/v77AP7//gD+//8A/v7/AP3+/wD9//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///QD///4A/v//APz//wD7/v8A+v//AP3//wD///4A///+AP///AD//vwA/v3+APv7/wDx9P0A5ez1/7rF3P9/ja7/XGua/05gmf9QY57/Umef/0phl/9MY5v/SmGa/0hbm/9JW57/TFug/05aoP9MWZ3/SliY/0hXlv9HV5b/SFmZ/0hanf9JW6D/Slyi/0pcov9KXaD/SVyc/0dbmP9GWpj/Rlua/0danf9HWqD/Q1mg/0Vbof9NYKX/S1ya/1hnnP9pc5z/usHb/+3x+2b7/f4A/f79AP7+/gD///8A////AP39/QD+/v4A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v//APz+/gD5/PwA+/39AP7+/wD//v8A//3+AP/8/QD//f4A//7/AP///wD7//4A2eHf/5eloP+DlIz/jZ6U/5KkmP+MnZH/iZeO/4yZkf/V3dj/8PbyAPn8+wD6/PwA/P39AP/+/wD//v8A//7/AP/+/wD///8A////AP7//gD9//4A/v7/AP/9/gD//P4A/Pv/AP79/wD//v8A/v7+AP39/QD9/fsA//77AP//+wD///oA/f/6AP///AD9/v4A/f3/AP38/wD9/P8A/v3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP///gD///4A/v//APz//wD7//8A+v//APv//wD9//8A///8AP///AD//vsA/fv6APv5+wD2+PwA3+bv/6Kuy/9icZb/T2GP/01dl/9NXp//S16e/0hdmf9IXpf/SWCY/0del/9CVpP/TV+d/1Bfof9PW5//Tlmc/0tYmf9JWJf/SViX/0dZmv9IW53/SVyg/0lcof9JXKD/Slyg/0lbnv9HWpr/RVmY/0VamP9GW5r/Rlud/0ZZof9FWKT/Slqj/1poqf9XZJf/oavK/+Ho8v/6/v8A/f78AP39+gD+/v0A////AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/f39AP3+/gD9//8A+///AP3//wD//v8A//3/AP/+/wD//f4A//z9AP/+/wD+/P0A+fz6APr//QDFzsz/aHZx/2V1bf+RpJj/mKue/42dkv9wfnP/dH54/73Ev//p7evu+vz8AP7//wD//v8A//7/AP/+/wD//v8A/f//AP3//wD9//4A/v/9AP//+wD///kA///5AP///AD///8A//z/AP/7/wD/+/8A//v/AP76/wD/+/8A//3/AP79/wD///4A/P37AP3++wD///wA///7AP///AD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A/v/+APz//wD7//8A+v//APr//wD7//8A/f/+AP///AD///sA/fr2AP35+QD5+P4A1tnn/4KKpP9NWn3/TmKN/09lmP9KXpz/TF6i/01gov9IXZv/SV+a/0hemf9KYZr/TF+Z/0temP9NXZr/T1yb/01Zmv9KVpr/R1WY/0hWmf9FV5r/Rlqc/0Vcnv9HXJ7/SV2g/0pcof9KW6H/SFqe/0dbmv9HXZf/SF2a/0ddnf9HW6L/Slup/1Bgq/9TYaP/eoe5/9zl8//y+P4A+vz+AP///AD///sA///+AP///wD///8A////AP///wD///8A/f39AP39/QD+/v4A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7+AP7+/gD+/v4A////AP7//gD+//8A9vf3AOfr6f+yvLb/eYmA/3aIff+fr6f/kqKa/2ZwbP9nb2z/rrSz/+fr6v/7/v4A/fz+APv6+wD///8A/v7+AP/+/QD///4A///+AP///gD///4A///+AP///gD///8A////AP///wD///8A////AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//3/AP/9/wD//f8A//7/AP///wD///4A///8AP///AD///sA/f36AP7//AD///0A///+AP7//QD7//oA/P/6AP3/+wD6/fkA+/7+APv+/wD9//0A/v/7AP7++QD+/PkA///+AP/+/wD//f8A/v//APr9/gDl6vX/tb7Z/3yHr/9RYJb/Sl6c/0xipP9FXaH/SFuh/0xbof9NWp//TFqe/01cnv9NXJz/TFub/0tamv9MWpr/TFmc/0tYnf9LWJz/Slec/0lWmv9HVZj/R1WX/0hXmP9KWZn/Slmb/0pZnf9KWqD/SVmh/0Ran/9IXp3/Q12a/0Zbnv9GV5//SVef/1Fbn/9WXZn/YWWX/8jO7v/v8/0i+f3+AP3+/gD+/f8A/vz/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/v7+AP///wD///8A////AP7+/gD6/fsA6PDr/73Jwv97i4P/SFRO/1xpY/97hoH/bHhy/46Wk//Fy8n/7fLwZvv+/gD6/f0A+vv7AP3//wD///8A/v38AP38/AD9/PwA/v39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/wD9/v8A//7/AP/+/wD///8A///+AP///gD///0A///9AP7+/gD+/v4A/f3+AP38/gD+/f4A/v7+AP7+/gD+/v0A/v/9AP39/QD+/v8A+vr7APr6+wD///0A//77AP3//AD9//0A/P7/AOHl8v+2vNX/iZG0/2hyn/9YZ5r/UmSb/0lfm/9FXJ7/Sl+k/0tdo/9LW6D/S1qf/0tanv9MW57/TFyc/0pamv9JWZn/SVqa/0pZnP9LWZ7/S1me/0tZnv9KWJ3/SFea/0dXmf9IWJn/SVmZ/0lZmv9HV5n/RVeY/0lcnf9HXp7/SmCi/0Vcnv9JXp//S16d/09dnv9YX53/aXCi/6200//s8fuI9/z/APv+/gD9/v8A/v3/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A////AP///wD///8A/P7+APn+/ADr8u+qy9XR/6Wvq/+PmZX/anZw/3aCfP9zgHn/eIR+/5Wgmv/I0Mv/+f/8APn9+wD6/vwA/v//AP79/QD+/f0A/v3+AP/+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD9//4A/f/9AP3//gD+//4A/v/+AP///wD///8A//7/AP/+/wD9+v0A/vz/AP78/wD+/P4A//3/AP/+/wD+/f4A+vr7AP///wD+/f4A//7+AP38/QD8/P0A/f/+APr+/QD3/v0A6fH37tDa6P+TnL3/b3in/11nn/9RXZv/Tl6c/05hnf9KYJv/Rl2a/0ZcnP9JXJ//Slug/0pan/9JWp7/SVud/0hbm/9HWZn/RlmZ/0dZmf9IWZz/SVqe/0pbn/9KW6D/Slqg/0lanv9IWZz/R1mb/0ham/9HWZn/RlmY/0dbmP9IXpn/SF+a/0dcn/9MYKT/SVyd/05gm/9TYZf/Ymub/5ehwv/i7Pb/9v3+APr+/gD9/v8A/v7+AP/+/QD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A////AP7//wD9/v8A+v7+APf8/ADv9vUi1d7b/32Ig/9baGH/W2pi/2Nzav9nd27/jJmQ/93n4P/5/vwA+v78APz+/QD+/v4A//7/AP78/gD+/P0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A/f/9AP3//AD9//0A/f/+AP7//wD//v8A//7/AP/9/wD//f8A/fv+AP/8/wD//v8A//3+AP79/QD+/v0A/v7+APz7/AD+/v4A//3+AP/+/gD+/v0A+/7+APf9/wDm7vT/r7zK/5yqxf9/j7P/Xm2e/1Bhm/9OX5//TF2i/0pcov9JXKD/SV2d/0lfm/9JXpr/Sl6d/0pdoP9JXJ//SVye/0hbnf9GWpr/RVmZ/0VYmP9FWJj/RVia/0ZYnP9IWp7/SVuh/0lbof9IW5//SFue/0hbnf9IXJz/R1ya/0hdmv9KX5r/RVyW/0pgnP9HXJ3/TF6h/09fof9TYJz/X2uY/4iUsP/S3un/9///APr//wD7/fwA/v7+AP/+/QD//v0A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/v7+AP7+/gD///8A/v3/AP39/wD7/f4A+fz8APP7+ADByMb/ipaQ/3yLg/93h33/Z3lu/25/dP+ir6b/wMjD//T59QD6/fwA/v7+AP/+/wD+/P4A/vz9AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/+AP3//QD8//wA/f/+AP3//wD+/v8A//7/AP/+/wD//f8A//7/AP/+/wD//v0A/v/9AP7+/AD9/fsA/P77AP3+/AD9//4A+/38AP3+/gD9/v4A9fj6AOLo7//I0d//prHK/2V0m/9dbqH/Sl2V/0pfm/9LYp//SGGh/0Zeo/9FXKL/R1mh/0han/9LXp3/TGCd/0tenv9KXaD/Sl2h/0ldn/9IXJ3/Rlqa/0VZmf9EWJn/RFiZ/0RXmv9EV5z/Rlie/0haof9IWqH/SFqf/0hbnv9IXJ//SV2e/0henf9JX53/SWCd/0NZl/9MYaH/TF+g/01cnv9UX6H/YGii/4SOs//Bzdr/9v//APv//wD+//8A/f39AP7+/gD//f4A//3+AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A////AP///wD///8A/v7+AP7+/gD+/v4A/v7+AP78/wD++/8A/vz+AP79/gD3+/oA+v7+APf/+wDP2tT/l6Ob/3GBd/9icmf/UmBW/11nX//Fzcb/5+rn//j6+AD+/v0A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//gD9//4A/P/+AP3//wD+//8A/v7/AP/9/wD//f8A//7/AP///wD+//0A///6AP7/+QD9//gA/P/5APv/+gD5/voA+v79APH3+ADo7vH/3+Xs/8TL2v+fqML/fYar/11pmv9RYZr/U2Wl/0ZdoP9DW53/Q16e/0RgoP9EX5//Ql2d/0Rbnf9IXJ7/Slug/0lbnv9KXKD/S12i/0teof9JXZ//R1ud/0Zamv9FWZn/RVmb/0VZm/9FWJz/RVed/0ZYn/9HWaH/R1mh/0dZn/9HWp7/SFuf/0pdoP9IXZ//R16e/0hen/9FWJr/Tl+i/1Bfov9TYKD/WWGc/4CGtf+9xeD/7fT8Zvj8/QD8/v0A///+AP/9/wD//P8A//3/AP/9/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/v7+AP7+/gD+/f4A/fr9APz5/QD//v8A/f//APz+/gD5/vwA+v/8ANng3P+hqqX/YWtk/ys0Lf9DSkT/Zm1n/5idmP/GysX/6Ovn//n7+AD9/vwA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f/+AP3//gD+/v8A//7/AP/+/wD//f8A//7/AP/+/wD///4A/P75AP7/+QD9//kA+v/4APn++QD4/f0A8ff7AOPp7//K1OD/qrbH/46btv9hbZP/TViK/1ZinP9TYaD/T2Cg/0dbnf9GXp//R1+i/0Fanv89WJf/QFqW/0Vgmf9JYpv/RV2Y/0dYn/9KWqT/Sluj/0pdov9KXqH/SV2f/0ZanP9FWZn/RVmZ/0VZm/9FWZv/Rlmd/0haoP9IWqH/SFqi/0haov9HWZ//R1mf/0han/9JXKD/SV2h/0dbn/9HWp7/S1yf/09fov9RXaD/WGGc/3Z/qv+3v9v/7PH+iPj5/gD39voA+vr3AP///AD++/8A/fn/AP78/wD+/v8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD+/v4A/fz9AP37/QD//f8A//z+AP/+/wD9/f0A//7/AP7//wD9//8A/f//APHz8gDO0c//n6Wg/1ddWP8VGxX/GB8Y/4eOhf/h5+D/+/36AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP3//gD9//4A/v//AP/+/wD//v8A//3/AP/+/wD//v8A///+AP39+gD9/vsA/P/9APf9/QD1/P0A7/T8IsvS6P+BhqL/XGaJ/1pnk/9SY5j/VWai/1VlqP9QYKX/TmCk/0dZnP9MX6D/RVmY/0dbnP9IXJz/SF2a/0phm/9MY5z/S2Kb/0ddmP9HWp7/SVui/0lbof9JW6D/SVyg/0hcnv9GWpv/RFiY/0RYmP9FWZr/RVmb/0dZnv9IWqD/SVui/0lbo/9JW6P/R1mf/0dZn/9IWp//SVyg/0xfo/9KXaH/Rlic/09eov9WZKX/VWCd/2dwoP+5w9//7fb+Zvj8/wD8+/8A//z+AP/6/AD//f8A//7/AP79/gD9/v4A+//9APv+/QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v4A/vz8AP78/QD///8A//7/AP78/QD//f4A//3+AP/9/gD9/f0A+Pn3APP28wDc4Nv/lJuU/y41LP8iLCD/eYJ3/9rg2f/+//4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD9//wA/f/7AP7//QD///4A//7/AP/+/wD//v8A////AP///gD///4A+f37APn9/QD2/P8A9Pn/AOTp9v+rsNP/W1yK/2Nrov9TYqb/S2Cr/0hepv9KYKP/TWKk/01ho/9LXqD/S1ue/0tcm/9LXpr/TF+Z/0pemP9HXJj/RlqZ/0danf9KXaD/SV+e/0denP9HWpz/SFqe/0lbn/9IWp3/Rlia/0NWlv9EV5f/RVeZ/0VXmv9GWJz/SFmf/0laof9KWqP/Slqj/0lZov9JWqD/Sluh/0tcoP9JXJ7/S16g/09gpf9LWKH/U1+i/1dilf+wu93/4Ov6//P7/wD6/v8A/v3+AP/9/QD/+/8A/Pn+AP/+/gD8//kA+//6APv//AD8//0A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/gD//v8A//7/AP///wD//v8A////AP///wD///4A/v/9AP3+/AD+//0A+vv4AOzu6szDxsH/jpGM/3V3cv9wcm3/fH16/8LCv//v8OyI/f37AP///gD9/fwA/f79AP7//gD+/v0A////AP///wD///8A////AP/+/wD//v8A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP3//gD9//8A/f//AP7+/wD//f8A//7+AP///AD///oA+/76APz+/AD9//4A/f7/APr7/wDs7/uIu8Ph/01cjP9WaKX/T2Gk/0xeo/9KXaH/Sl2g/0xeof9MXqH/S12g/0lcnv9JXJ7/SV2e/0tfn/9LX6D/SF2g/0Zanf9FWZ3/R1uf/0hdof9IXaD/S1qe/0tXnP9KV5z/Slie/0hZn/9FV53/Q1eb/0RYnf9GWp3/SFue/0lanP9JWZr/Tluc/1Benf9PW5v/TVqY/01Zmv9NWpz/S1mf/0xco/9TYaT/Vl6Z/2Rql/+7vdz/5uf4//f5/gD9/v4A/v/9AP7//gD8//8A/P7/APr+/wD9//8A/v/9AP7//AD+//wA/v/9AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD//v8A//7/AP/+/wD///8A////AP///wD///8A///+AP///gD///0A///+APv8+gD+//wA9/j0AOHi3v/R0s7/xMXB/6usqP+Tk4//mpuX//Pz8AD8/PoA/f38AP7//QD+//0A///+AP///wD///8A////AP///wD//v8A//7/AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//8A/f//AP3//wD+/v8A//3/AP/+/wD///0A///6APz/+gD9//0A/f7+AP7+/gD9/f8A8vb+AMnS7P9VZ5j/Umek/05ho/9MXqH/Slyg/0pcn/9LXaD/S12g/0pcn/9IXJ7/SFye/0hcnv9KXqD/Sl+i/0hdof9EWp7/Q1md/0Vbn/9IXaL/SV2h/0tan/9KWJz/Sleb/0pZn/9KXKD/R1me/0RYnP9GWp3/R1ud/0hbnP9LXZ3/TV2c/0pZmP9MWpn/Tlub/0xZmf9PXJ7/T1yf/1Beov9TYaT/WWWh/2tzpP+wtdn/6+v4qvr5/gD9/P0A/v78AP///QD+//4A+///APv//wD6//8A/P//AP7//wD///4A///9AP///QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD//v8A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A///+AP///gD6+/kA/P37AP7//AD7/PkA/Pz6APv7+QDq6uj/ysrI/8LDwP/39/UA/f38APz8+wD8/PsA/v79AP///wD///8A////AP///wD///8A//7/AP/+/wD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f//AP3//wD9//8A/v7/AP/9/wD//v8A///+AP//+wD9//wA/f/+AP3+/gD+/f8A/f3/APb6/wDW3/L/aXur/1Vppf9PYaP/TF6i/0tdoP9KXJ//S12g/0tdoP9KXZ//SFye/0hcnv9IXJ7/SV2f/0ldoP9IXKD/RVmd/0JYnP9FW5//SF2i/0pdov9KW6D/SVic/0hXm/9KWp7/TF2g/0hanv9GWZz/SFuc/0hbm/9HWJn/SVqa/0xdnv9LXJ3/SVmb/0panf9MXJ//TV2i/09fof9RX57/VGGa/253p/+wttv/5un7//j5/wD+/f8A/v39AP79+gD+/vsA/v/+APz//wD8//8A+/7/AP3//wD+//8A////AP///gD///4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A/v7+AP39/QD///4A/P38AP39/AD+//0A/f78AP3+/AD///4A/v/9AP7+/QD9/fwA/v79AP///gD8/PsA/Pz7AP39/QD+/v4A////AP///wD///8A////AP/+/wD//v8A//7/AP/+/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//wD9//4A/f//AP7+/wD//f8A//7/AP///gD///sA/P/9APz//gD+/v8A/f3+APz9/gD5/P8A3+n1/4OSwP9bbqj/UGOj/01fov9LXaH/S12g/0tdoP9MXqH/Slyf/0lcnv9IXJ7/R1ud/0hcnv9JXJ//SFuf/0ZZnf9FWZ3/R1uf/0pdo/9LXaP/Slyh/0dYnf9GV5v/SFmc/0tcnv9KWp3/SVqb/0pbm/9JWpr/SFma/0dYmv9IWpz/Sl2g/0hanv9IWp7/TF+j/0tdov9NX5//Tl2U/2l0oP+qsdP/5ur///b4/wD8/P8A/fz9AP/++wD+/vkA/v76AP///gD+//8A/f//APz+/wD9//8A/f//AP7//wD///8A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD//v8A//7/AP/+/wD///8A////AP///wD///8A////AP39/QD8/PwA////AP///wD+/v0A/v79AP7+/QD+/v0A/v/9AP7//QD+/v0A/f38AP///gD///8A/v7+AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A//7/AP/+/wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A/v/+AP7//wD+/v8A//3/AP/9/wD///4A///8APr+/AD6//0A/v//AP79/QD9/f0A+/3/AOfu+P+aqNP/XnGo/1Fkov9NX6L/S12h/0tdoP9LXaD/S12g/0pcn/9JW57/SVue/0hbnf9IW57/SFue/0danv9GWZ3/R1qe/0lcoP9LXaP/TF6k/0lcof9FWZz/Q1ea/0ZYmv9JW5v/S1qa/0tamf9KW5v/SVub/0tdnv9JW57/RVic/0Zanf9HW57/R1qc/0ten/9OYKH/UWGc/2Jum/+epcP/6+7/qvT3/wD4+f8A/v3+AP38+wD+/foA///6AP///AD///0A///+AP7//wD+//8A/f//AP3//wD+//8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD//v8A//7/AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP///wD///8A/Pz8APz8/AD9/f0A/v7+AP7+/gD///8A/v7+APn5+QD+/v4A////AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///gD///8A//7/AP/9/wD//f8A////AP///AD5/vwA+v79AP3//gD//v0A//39AP3+/wDs8/uIrbvi/11wpP9SY6H/TV+i/0tdoP9KXJ//Slyf/0pcn/9JW57/SFqd/0hanf9IWp3/SFqd/0hanv9HWp7/R1md/0hanv9JXKD/S12i/0xepP9JXaH/RVqd/0NXmv9GWJr/SFqa/0pZmP9LWpn/S1yc/0pcnf9KXaD/Slyg/0Zanv9EWZz/Rluc/0hbmf9LXZf/WGif/2p1pv+epcb/3d/u//r6/gD9/f8A/f7/AP38+wD+/vwA///8AP///AD///0A///+AP///gD//v8A//7/AP7//wD9//8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A/v7+AP38/QD+/v8A////AP7+/gD8/PwA/f39AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///AD///0A////AP/+/wD//f8A//3/AP///wD///4A/P//APn+/QD9//4A//79AP/+/QD9/v4A7/b9Ir/N7/9fcaP/UWOf/01eof9KXJ//SVue/0lbnv9JW57/SFqd/0ZYm/9GWJv/R1mc/0hanf9IWp3/SFqe/0hanv9JW5//Slyg/0tcov9LXaL/Sl2i/0dbnv9GWZv/R1mb/0pbm/9LWZr/Slub/0xcoP9MXqL/TWCl/0daoP9DV5v/TWCi/0hbmP9QYJX/V2ST/2dxmv+psdH/5uj4//r6/QD7+vwA//7/AP///wD8/fsA/v79AP/+/QD//v4A///+AP///wD///8A//7/AP/+/wD///8A////AP7//gD9//4A/v/+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP/+/wD+/f4A+/r8APv6/AD+/f8A/v3/AP/+/wD//v8A/v3/AP/+/wD+/f4A+fj6AP79/gD7+vsA/fz9AP79/gD9/P4A/v7/AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP///gD///wA///9AP///wD//v8A//3/AP/9/wD///8A///+APz//wD4/fwA/P79AP/+/QD//v0A/f/9APP6/wDS4fz/bH+t/1Rmov9OX6L/S12g/0pcn/9KXJ//SVue/0hanf9HWZz/Rlib/0dZnP9IWp3/SFqd/0hanv9JW5//SVuf/0lbn/9JWqD/SVqg/0pcof9HW57/R1mb/0panP9MXJ3/TFqd/0tbnv9NXaP/TGCl/01gqP9KX6f/S2Ck/1BjpP9QYZn/TFeE/36Fqf/Mzej/8/H+APv5/gD8/P4A////AP7+/gD7+/sA/f/+APv9/QD9/f4A/v3/AP79/wD//v8A//7/AP/+/wD//v8A////AP///wD+//4A/f/+AP7//gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/wD+/f4A//7/AP79/wD+/f8A//7/AP79/wD//v8A//7/AP38/gD8+/wA//7/AP79/gD+/f8A/v3+AP/+/wD9/P4A/fz9AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///8AP///QD+//8A/v7/AP/8/wD//P8A//7/AP3//wD7//8A+P38APz+/QD///4A//78APz9+wDz+v8A4u/8/3+Qvf9aa6f/T2Gj/0xeov9LXaD/S12g/0pcn/9JW57/SFqd/0hanf9IWp3/SFqd/0hanf9IWp7/SVuf/0lbn/9IWp7/SFmf/0hZn/9JW6D/R1qe/0dZnP9LW57/TV2f/01bnv9LWp//TF2k/0pfqP9KYan/SF+m/0ZdoP9LYJz/V2iZ/5Kbw//Y2+z/9PD8AP35/wD//f8A//7/AP7+/QD8+voA/f7+AP3//wD7//8A/P7/APz9/wD9/f8A//3/AP/9/wD//v8A////AP///wD///4A/v/9AP3//AD+//0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//7/AP///wD///8A////AP///wD///8A////AP///wD//v8A/v7+AP7+/gD///8A//7/AP7+/gD+/v4A/v7+AP79/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v/+AP7//gD///4A////AP/+/wD//f8A//7/AP///wD+//8A/v79AP3+/QD+//4A/f//APz+/gD7/f4A9/v/AO71/USYo8D/X2+i/01fn/9LXqH/S1+h/0teof9JXJ//SVyf/0lcnP9IWpv/SVud/0dZnP9IWp3/Slyg/0pcoP9IWZ7/SFmf/0lYoP9KWKD/Tlui/01Zn/9KV57/SVmg/0hdoP9HYqH/Q2Cc/0JfnP9GYaD/TmKm/05do/9OWZr/aXCl/6yx2f/f4vH/7/L7Ivr6/gD9/v8A/v//AP///wD///4A/v7+AP7//wD+//8A/v//AP7//wD+/v8A/v7/AP/+/wD///8A////AP///wD///8A////AP///gD+//4A///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP39/QD6+voA////APz8/AD9/f0A////AP7+/gD+/v4A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3//gD9//4A/v//AP///wD///8A//7/AP/+/wD///8A////AP/+/QD+/v0A///+APz+/wD6/v8A+/7/APn8/wD0+f0As7vT/2Z0pP9OYJ//TF+j/01go/9LX6L/SFye/0hcnv9IWpz/RVeZ/0ZYm/9IWp3/Slyg/0xeof9IWp7/R1id/0dZnv9JWJ//S1mg/0xYn/9MWZ7/Slqd/0hcnf9IYqH/RmKg/0Ffnv9FXqH/S2Cj/1Bfov9cZqP/foW3/7a73v/l6fj/8/b7APj6/QD8/f4A/f7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A+/v7APz8/ADNzc3/8PDwAP7+/gD+/v4A/f39AP39/QD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD9//4A/f/+AP7//wD///8A////AP/+/wD//v8A////AP///wD///4A/f78AP3+/QD8/v4A+v7+APv+/wD5/f8A9/v+AMzU6/9pdqb/TV+e/01gpP9NYKT/S16h/0hbnv9HWpz/R1mc/0RWmf9EVpn/SVue/0tdoP9JW57/R1mc/0hanv9HWZ3/R1id/0lbn/9LXJ7/SVyc/0dcmf9IX5r/S2Oh/0leo/9MYKj/TVyn/1Nhpv9ncqv/kJnB/8PM5P/o7/v/9/z8APf6/AD6/PsA/f78AP7+/AD+/v0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+APv7+wD7+/sAwsLC/+3t7Wb+/v4A////AP39/QD9/f0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/f/+AP3//gD+//8A////AP///wD//v8A//7/AP///wD///8A///+AP39/AD9/v0A/P7+APz+/gD7/v8A+fz/APj7/wDh5vv/bXqp/0xenf9NX6P/TV+j/0tdoP9JW57/R1mc/0lbnv9FV5r/RVea/0hanf9IWp3/Rlib/0hanf9KXJ//Rlib/0ZYnP9IW53/SV+d/0dfm/9FX5v/SF+f/0tepP9QXqv/TVml/15nrP99hrv/o63Q/9Lc6f/0+/wA9fr7APr9/AD+/P8A+/r7AP7++wD///sA///8AP///gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP39/QD4+PgA////AP7+/gD+/v4A////AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7//gD+//4A////AP///wD///8A//7/AP/+/wD///8A////AP///gD9/vwA/f79AP3+/gD9/v4A/P7/APr8/wD4+f8A7PH/iHiDsP9RYqD/T2Cj/05gov9MXqD/S12g/0hanf9KXJ//R1mc/0dZnP9IWp3/R1mc/0hanf9LXaD/SVue/0RWmf9IWp3/SVye/0Zdm/9FYJz/SWGj/01dqP9NWqb/V1+q/1xko/+SmsX/yNHq/+ry+sz4/vwA+//8AP7+/gD//f8A//n/AP/6/wD//fwA//76AP///AD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD6+voA/v7+AP7+/gD///8A/f39APz8/AD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///wD///8A////AP/+/wD//v8A//7/AP///wD+/v4A/f79AP7//QD+/v0A/f79AP3//wD9/v8A+/v/APX5/wCMl8D/XGun/1NkpP9SY6P/T2Gh/05gov9LXZ//S12g/0lbnv9JW57/SVue/0pcoP9NX6P/TF6i/0ZYnP9FV5v/Slyg/0lcoP9LX6P/Sl6i/05epv9ZY67/YGes/2Vrof+4v+L/6O/6//j9/gD7//oA/f/6AP/+/QD//f8A//z/AP/8/gD//P8A/vr8AP/8/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD6+voA+vr6AP7+/gD7+/sA/Pz8AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///4A///+AP///AD///4A////AP///wD//v8A//7/AP/+/wD///8A/v7+AP///gD///4A/f38AP39/AD///8A////AP79/wD3+v8AoqzT/2R0rP9TZKL/UmOi/1BhoP9RYqL/T2Cg/05eof9PX6L/TmCj/0tdof9LXaH/S12h/0dZnf9GWJz/T2Gl/01fo/9JW5//TVyj/0tZn/9TXp//dn20/6613P/g6Pv/7/b6Ivv9/AD8/vkA/v/8AP/9/wD/+/4A/vv9AP78/AD+/PsA///+AP/8/wD/+v8A//z/AP/+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A/v7+AP39/QD///8A/v7+AP7+/gD///8A/v7+AP7+/gD+/v4A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A///+AP///gD///wA///+AP///wD///8A//7/AP/+/wD//v8A////AP///wD///4A///+AP7+/QD9/v0A/v7+AP7+/gD//f8A9/n/ALbB5f9qe7D/UGOc/1Binf9OYJz/UGGh/1Fiov9NXaD/UGCj/1Fhpf9OXaL/S1yh/0laoP9GV53/TV6k/1Nlqv9KW6H/V2ao/09bmf9sdqv/qbPZ/9ri+P/s8fuI+f3+AP3++wD///wA//z8AP/8/gD9/P8A/fz+AP///wD9//4A/P/7APr9+wD+/f4A/vv/AP/8/wD//v8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD+/v4A/f39AP///wD9/f0A/v7+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///4A///8AP///gD///8A////AP/+/wD//v8A//7/AP///wD+/v4A///+AP7+/QD+/v0A///+AP///gD///4A//7/APj7/wDH0fP/dIO2/1Nlnf9SY53/UGGb/1Bin/9RYqL/S1yc/09fof9OXaL/TFuh/09epf9PX6f/TFyl/1Jiqv9OXqb/RVWd/2x6uv+dqdf/xtHs/+Xt+P/1+/4A+v79AP7//AD///0A//v8AP/8/gD//v8A/v7/AP3+/gD9//8A+f39APv+/gD7/v4A+/34AP3++AD//vwA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP7+/gD///8A/v7+AP7+/gD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///gD///8A////AP///wD///8A////AP///wD///8A/v7+AP///wD+//4A///+AP///wD///8A////AP///wD5/f8A1+P9/36Ouf9XZ57/VmOh/1Ngof9SYKD/T2Ge/0hfmf9JYp7/SGGj/0dfp/9KYK3/TV+v/1Fgqf9jb6//eYO3/52jzP/FzOb/5Oj3//T3/gD7/v8A/f//AP7//gD///4A////AP/9/gD//v8A////AP///wD+//8A/v//AP3+/gD+//8A/v//AP3+/AD+//wA///+AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A+f7/AOLy//+Oo8P/YnOm/1lnpv9TYKb/UV2m/1Bipf9NZKH/TGWg/0tlpf9GXqP/TWCn/2Nytv+FkMr/q7Pb/8LJ4//l5vP/+Pn8AP7+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////APn+/wDo+///mrLI/2d8qP9XaKb/U2Gp/1Rhrv9VZK3/TF+e/1Jlnv9gcaj/b32x/4iTwf+sstj/z9Tw/+3w/2b2+P8A/P3/AP38+wD+/v0A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD5//8A7f7/Zqe9z/9sf6j/VGSe/1Rjpv9baq//YXCw/2V1p/99jLX/mqXE/7zF3f/h5PP/9/j+APv7/gD+/v0A//79AP/+/gD8+/kA/v38AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A+/7/APL6/wDH1Of/hZG2/2Jsnf9qdqj/eYa0/4qZwP+9zOT/3+v6//P6/gD8//4A/v/9AP///AD///sA///6AP/9+QD+/f4A/v7/AP7+/wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP3+/wD7/P8A3uHu/6yvx/+jp8T/y9Pq/+z0/4js9/+I9P7/APn//wD8/vwA/v78AP///AD///wA///7AP//+QD///sA+v/+APn+/wD8//8A/v//AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAA////////////AAD//////////////gAA///////////8AAAAf////////////gAA///////////8AAAAB////////////gAA///////////8AAAAAH///////////gAA///////////8AAAAAAf//////////gAA///////////8AAAAAAH//////////gAA///////////+AAAAAAB//////////gAA///////////+AAAAAAAf/////////gAA///////////+AAAAAAAH/////////gAA///////////+AAAAAAAA/////////gAA///////////+AAAAAAAAH////////gAA////////////AAAAAAAAD////////gAA////////////AAAAAAAAA////////gAA////////////AAAAAAAAAP///////gAA////////////AAAAAAAAAH///////gAA////////////AAAAAAAAAD///////gAA////////////AAAAAAAAAA///////gAA////////////gAAAAAAAAAf//////gAA////////////gAAAAAAAAAH//////gAA////////////gAAAAAAAAAD//////gAA////////////gAAAAAAAAAB//////gAA////////////gAAAAAAAAAA//////gAA////////////wAAAAAAAAAAf/////gAA////////////wAAAAAAAAAAP/////gAA////////////wAAAAAAAAAAH/////gAA////////////wAAAAAAAAAAD/////gAA////////////4AAAAAAAAAAB/////gAA////////////8AAAAAAAAAAA/////gAA///////////////AAAAAAAAAf////gAA///////////////+AAAAAAAAP////gAA////////////////8AAAAAAAH////gAA//////////+P/////AAAAAAAD////gAA//////////4H/////4AAAAAAB////gAA//////////wD/////+AAAAAAB////gAA//////////wB//////AAAAAAA////gAA//////////gB//////wAAAAAAf///gAA//////////gA//////8AAAAAAP///gAA//////////gA//////+AAAAAAH///gAA//////////gA///////gAAAAAD///gAA//////////gA///////4AAAAAD///gAA//////////gAf//////8AAAAAB///gAA//////////wA///////+AAAAAB///gAA//////////wAf///////AAAAAA///gAA//////////wAf///////gAAAAA///gAA//////////4Af///////wAAAAAf//gAA//////////4Af///////4AAAAAP//gAA//////////4Af///////8AAAAAP//gAA//////////4AP////////AAAAAH//gAA//////////8AP////////AAAAAH//gAA///8//////8AP////////gAAAAD//gAA///4//////8AP////////gAAAAB//gAA///x//////+AH////////wAAAAB//gAA///x//////+AH////////4AAAAB//gAA///j//////+AH////////8AAAAA//gAA///D///////AH////////+AAAAA//gAA//+H///////AH/////////AAAAAf/gAA//8H///////AH/////////AAAAAf/gAA//4P///////gD/////////gAAAAf/gAA//4P///////gD/////////wAAAAP/gAA//wf///////wD/////////wAAAAP/gAA//wf///////wD/////////4AAAAP/gAA//g////////wD/////////4AAAAH/gAA//A////////4B/////////4AAAAH/gAA//B////////4B/////////8AAAAH/gAA/+B////////4B/////////+AAAAD/gAA/+D////////4A/////////+AAAAD/gAA/+D////////8A//////////AAAAD/gAA/8D////////8A//////////AAAAB/gAA/8D////////8A//////////AAAAB/gAA/4H////////+Af/////////gAAAB/gAA/4H////////+Af/////////gAAAB/gAA/4H////////+Af/////////gAAAB/gAA/wH////////+Af/////////gAAAB/gAA/wP////////8AP/////////wAAAB/gAA/gP////////8AH/////////wAAAA/gAA/gP////////8AH/////////wAAAA/gAA/gP////////8AH/////////wAAAA/gAA/AP////////8AD/////////wAAAAfgAA/AP////////8AD/////////4AAAAfgAA/Af////////8AD/////////4AAAAfgAA/Af////////8AD/////////4AAAAfgAA/AP////////+AB/////////4AAAAfgAA+Af////////+AB/////////4AAAAfgAA+Af////////+AB/////////8AAAAfgAA+Af////////+AB/////////8AAAAPgAA+Af////////+AB/////////8AAAAPgAA+Af/////////AA/////////8AAAAPgAA+Af/////////AA/////////8AAAAPgAA+Af/////////AA/////////8AAAAPgAA+Af/////////gA/////////8AAAAPgAA+Af/////////gA/////////8AAAAPgAA8Af/////////gA/////////8AAAAPgAA8Af/////////gAf////////8AAAAPgAA8Af/////////gAf////////8AAAAPgAA+Af/////////wAf////////8AAAAfgAA+Af/////////wAf////////8AAAAPgAA+AP/////////4AP////////8AAAAfgAA+AP/////////8AP////////4AAAAfgAA+AP/////////8AP////////4AAAAfgAA+AP/////////8AP////////4AAAAfgAA+AP/////////+AP////////4AAAAfgAA+AP/////////+AH////////4AAAAfgAA+AP/////////+AH////////wAAAAfgAA+AH/////////+AH////////wAAAA/gAA+AH//////////AH////////wAAAA/gAA/AH//////////AH/////4P/wAAAA/gAA/AH//////////gH////wAH/gAAAA/gAA/AH//////////gH///8AAP/gAAAB/gAA/AH//////////gH//+AAB//gAAAB/gAA/AD//////////wD//AAAMH/gAAAB/gAA/gD//////////wD/wAAAAD/gAAAB/gAA/gD//////////8D/gAAAAH/AAAAD/gAA/wB//////////+H/AAAAAH/AAAAD/gAA/wB/////////////AAAAAD/AAAAD/gAA/4B////////////+AAAAAD+AAAAD/gAA/4A////////////8AAAAAP+AAAAD/gAA/4Af///////////4AAAAD/8AAAAH/gAA/4Af///////////wAAAAMB8AAAAH/gAA/8AP//////////+AAAAAAB4AAAAH/gAA/8AP/////////8AAAAAAAD4AAAAH/gAA/+AH////////8AAAAAAAAfwAAAAP/gAA/+AH///////4AAAAAAAAD/wAAAAP/gAA//AD//////wAAAAAAAAAf/gAAAAf/gAA//AD/////8AAAAAAAAAD//AAAAAf/gAA//gB/////gAAAAAAAAAf//AAAAAf/gAA//gB////+AAAAAAfAAB//+AAAAA//gAA//wA////4AAAAAD/4AP//8AAAAA//gAA//wA////4AAAAAf/+D///8AAAAA//gAA//4Af///4AAAAB///////4AAAAB//gAA//8AP///wAAAAf///////wAAAAB//gAA//+AH///wAAAD////////gAAAAD//gAA///AH///wAAAf////////AAAAAH//gAA///AD///wAAH/////////AAAAAP//gAA///gB///4AA/////////8AAAAAP//gAA///wA///4AB/////////4AAAAAf//gAA///4Af//8AP/////////wAAAAAf//gAA///4AP///j//////////gAAAAA///gAA///8AH//////////////AAAAAA///gAA////AH/////////////8AAAAAB///gAA////gD/////////////4AAAAAB///gAA////wB/////////////wAAAAAD///gAA////4Af////////////gAAAAAH///gAA////8AP///////////+AAAAAAH///gAA////+AH///////////4AAAAAAP///gAA/////AD///////////gAAAAAAf///gAA/////wB//////////+AAAAAAA////gAA/////8A//////////4AAAAAAB////gAA//////AP/////////AAAAAAAB////gAA//////gD////////8AAAAAAAD////gAA//////8B////////wAAAAAAAH////gAA///////A////////wAAAAAAAP////gAA///////gH///////wAAAAAAAf////gAA///////4H///////4AAAAAAA/////gAA////////H///////4AAAAAAB/////gAA////////////////4AAAAAAD/////gAA////////////////4AAAAAAH/////gAA////////////////4AAAAAAP/////gAA////////////////4AAAAAAf/////gAA////////////////8AAAAAB//////gAA////////////////8AAAAAD//////gAA////////////////8AAAAAD//////gAA////////////////+AAAAAP//////gAA////////7///////+AAAAAf//////gAA////////5///////+AAAAB///////gAA////////////////+AAAAD///////gAA/////////////////AAAAP///////gAA/////////////////AAAAf///////gAA/////////////////AAAB////////gAA/////////////////AAAH////////gAA/////////////////AAAf////////gAA/////////////////AAB/////////gAA/////////////////AAH/////////gAA/////////////////AA//////////gAA/////////////////gH//////////gAA/////////////////gf//////////gAA"; }

function logo() {
    return
        "data:image/png;base64,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"; }



// Document
?><!DOCTYPE html><html>
<head>
<title>Tischlein Deck Dich</title>
<link href="<?php echo favicon(); ?>" rel="icon" type="image/x-icon" />
<?php jquery(); jsbarcode(); js(); css(); ?>
</head>
<body>

<div id="header" class="header"><div><span><a href=""><img src="<?php echo logo(); ?>" style="max-height:120px;max-width:100%" /></a></span></div></div>

<p style="text-align: right; padding: 0 10px; margin: 2px 0;"><a class="button" href="?print" target="_blank">Druckversion</a>  <a class="button" href="?login">Log Out</a></p>
<img id="barcode" style="display:none" />

&nbsp;


<div id="tabs">
    <div id="tab-head">
        <ul class="tab">
            <li><a href="#tab1">Start</a></li>
            <li><a href="#tab2">Ausgabe</a></li>
            <li><a href="#tab3">Verwaltung</a></li>
            <li><a href="#tab4">Logs</a></li>
            <li><a href="#tab5">Einstellungen</a></li>
            <li><a href="#tab6">Hilfe</a></li>
        </ul>
    </div>
    
    <div id="tab-body">
        <div id="tab1"><h1>Willkommen</h1>
            <div style="display: inline-table;">
                <div class="cols3"><h2>Das neue TDD-Programm</h2><p>Willkommen zum neuen "Tischlein Deck Dich"-Lebensmittel&shy;ausgabe&shy;programm.</p><p>Über die Tabs können Sie die verschiedenen Sektionen des Programms erreichen, links bzw. unten finden Sie weitere Informationen zu selbigen.</div>
                <div class="cols3"><h2>Ausgabe</h2><p>Im Tab "Lebensmittel&shy;ausgabe" wird verwaltet, wer anwesend ist, für wieviele Kinder und Erwachsene diese Person Essen abholt und welche Nummer sie bekommt.</p><p>Familien sind sortiert nach Ort und Gruppe. Jede Familie darf nur an einem Ausgabeort erscheinen (Ausnahmen bei Feiertagen), für jeden dieser Orte können mehrere Gruppen angelegt werden.</p><p>Bitte immer die Daten der Familien überprüfen!</p><br><p>Das Programm speichert automatisch sobald eine neue Familie geöffnet wird. Alternativ wird auch nach 20 Sekunden automatisch gespeichert.</p></div>
                <div class="cols3"><h2>Verwaltung</h2><p>Die Familien&shy;verwaltung ist dazu da, neue Familien anzulegen oder die Daten vorhandener Familien zu bearbeiten.</p><p>Hier können auch vorhandene Familien gelöscht werden.</p><br><p>Vor dem anlegen neuer Familien nach dem Namen suchen, um Doppel-Einträge zu verhindern!</p></div>
            </div>
            <p style="text-align: right;">2018 by Constantin, Version <?php echo VERSION; ?></p>
        </div>
        <div id="tab2"><h1>Lebensmittelausgabe</h1>
            <div>
                <div class="cols2" style="margin-bottom: 10px">
                    <div class="cols2 cw100p">
                        <select id="ort-select">
                        </select><br>
                        <select id="gruppe-select">
                        </select><button id="fam-reload"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"><path d="M9 13.5c-2.49 0-4.5-2.01-4.5-4.5S6.51 4.5 9 4.5c1.24 0 2.36.52 3.17 1.33L10 8h5V3l-1.76 1.76C12.15 3.68 10.66 3 9 3 5.69 3 3.01 5.69 3.01 9S5.69 15 9 15c2.97 0 5.43-2.16 5.9-5h-1.52c-.46 2-2.24 3.5-4.38 3.5z" /></svg></button>
                    </div>
                    <div class="cols2 cw100p" style="vertical-align: bottom">
                        <form action="#" onsubmit="search(searchFamA);return false" class="cw100p"><input id="familie-search" type="text" placeholder="Suche"><input type="submit" value="Suchen"></form>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="cols2 select-list"><ul id="familie-list">
                </ul></div>
                <div class="cols2 familie-data">
                    <p style="font-weight: bolder;" id="fam-ab"></p>
                    <p><b>Familien-Nummer: <span id="familie-count">0</span></b> <button class="fam-count o" onclick="return void(--fam)">-</button><button class="fam-count o" onclick="return void(fam=0)">0</button><button class="fam-count o" onclick="return void(++fam)">+</button></p>
                    <p class="w100pm400px"><a href="#" class="link" onclick="postFamKarte(selected_fam);return false">Karte drucken</a> <span id="fam-info"style="font-size:85%"></span></p>
                    <p>Ort: <span id="fam-ort"></span> | Gruppe: <span id="fam-gruppe"></span> | Nummer: <span id="fam-num"></span></p>
                    <p>Letzte Anwesenheit: <span id="fam-lan"></span></p>
                    <p>Karte gültig bis: <input id="fam-karte" type="date"></p>
                    <p>Erwachsene / Kinder: <span id="fam-erw"></span> / <span id="fam-kinder"></span></p>
                    <p>zu zahlen: <span id="fam-preis"></span>€</p>
                    <p>Schulden: <input id="fam-schuld" type="number" style="width:60px">€</p>
                    <p>Notizen:<br><textarea id="fam-notiz" class="w100pm400px"></textarea></p>
                    <p>Zusatzinfo: <button onclick="this.nextSibling.style.display=this.nextSibling.style.display=='none'?'':'none'">Umschalten</button><span style="display:none;"><br />Adresse:<br /><span id="fam-adresse"></span><br /><br />Telefonnummer:<br /><span id="fam-tel"></span></span></p>
                    <br>
                    <div class="w100pm400px">
                        <div class="cols3"><input id="fam-anw" type="checkbox" onclick="if(this.checked){++fam}else{--fam}"> Anwesend</div>
                        <div class="cols3"><input id="fam-gv" type="checkbox" title="Fügt gesamten Preis zu den Schulden hinzu"> Geld vergessen</div>
                        <div class="cols3"><input id="fam-sb" type="checkbox" title="Setzt Schulden auf Null&#xA;&#013;Nur wenn ALLE Schulden bezahlt wurden"> Schulden beglichen</div>
                    </div>
                    <p>&nbsp;</p>
                    <p class="w100pm400px msg-box" id="fam-szh"></p>
                    <p>&nbsp;</p>
                    <button class="w100pm400px" onclick="famInV()">Familie bearbeiten</button>
                </div>
            </div>
        </div>
        <div id="tab3"><h1>Familienverwaltung</h1>
            <div>
                <div class="cols2">
                    <form action="#" onsubmit="searchV(searchFamV);return false" class="cw100p"><input id="verwaltung-search" type="text" placeholder="Suche"><input type="submit" value="Suchen"></form>
                    <div class="select-list"><ul id="verwaltung-list"></ul></div>
                    <button id="verw-bneu" style="width:100%;border:2px outset #606060;">+</button>
                </div>
                <div class="cols2 familie-data">
                    <p class="w100pm400px"><a href="#" class="link" onclick="postFamKarte(verw_fam);return false">Karte drucken</a>&nbsp;&nbsp;&nbsp; ID: <span id="verw-id"></span></p>
                    <p>Name:<br><input id="verw-name" class="w100pm400px" type="text" placeholder="Name"></p>
                    <p>Ort: <select id="verw-ort"></select></p>
                    <p>Gruppe: <select id="verw-gruppe"></select></p>
                    <p>Nummer: <input id="verw-num" type="number" style="width:60px;"></p>
                    <p>Erwachsene: <input id="verw-erw" type="number" style="width:60px;"></p>
                    <p>Kinder: <input id="verw-kinder" type="number" style="width:60px;"></p>
                    <p>Letzte Anwesenkeit: <input id="verw-lan" type="date"></p>
                    <p>Ablaufdatum Karte: <input id="verw-karte" type="date"></p>
                    <p>Schulden: <input id="verw-schuld" type="number" style="width:60px;">€</p>
                    <p>Notizen:<br><textarea id="verw-notiz" class="w100pm400px"></textarea></p>
                    <p>Adresse:<br><textarea id="verw-adresse" class="w100pm400px"></textarea></p>
                    <p>Telefonnummer:<br><input id="verw-tel" class="w100pm400px" type="text" placeholder="Telefon"></p>
                    <br>
                    <p><button id="verw-save" class="w100pm400px">Speichern</button>
                    <button id="verw-neu" class="w100pm400px">Neu anlegen</button></p>
                    <p><button id="verw-del" class="w100pm400px">Löschen</button></p>
                </div>
            </div>
        </div>
        <div id="tab4"><h1>Logs</h1>
            <h2>Einnahmen</h2>
            <p><input type="datetime-local" id="log-from"> - <input type="datetime-local" id="log-to"> <button id="log-go">Go</button><br>
            Einnahmen im angegebenen Bereich: <span id="einnahmen"></span><br>Personen im angegebenen Bereich: <span id="log_erw"></span> Erwachsene(r), <span id="log_kinder"></span> Kind(er)</p>
            <p>&nbsp;</p>
            <h2>Kompletter Log</h2>
            <div id="complete-log"></div><br>
            <select id="log-pagination"></select>
        </div>
        <div id="tab5"><h1>Einstellungen</h1>
            <div>
                <div class="cols3"><h2>Orte</h2><div class="select-list w100pm400px"><ul id="orte" style="max-height:180px"></ul></div></div>
                <div class="cols3" id="actions"><h2>Aktionen</h2>
                    <p><button id="del8w" class="w100pm400px" onclick="delFamDate()" title="Löscht alle Familien, die seit 8 Wochen nicht mehr anwesend waren.">8 Wochen nicht anwesend löschen</button><br /><button id="del8wab" class="w100pm400px" onclick="delFamDate(-1,'Karte')" title="Löscht alle Familien, deren Karte seit 8 Wochen abgelaufen ist.">Karte 8 Wochen abgelaufen löschen</button></p>
                    <p><button id="resetnum" class="w100pm400px" onclick="post('?reset_fam',{},resetComplete)" title="Setzt alle Nummern der Familien zurück, d.h. alle Familien werden neu durchnummeriert.">Nummern zurücksetzen</button></p>
                    <p><button id="backup" class="w100pm400px" onclick="post('?backup_db',{},backupComplete)" title="Backup aller Daten (Einstellungen, Familien, ...) als Datenbank erstellen">Datenbank Backup</button></p>
                    <p><button id="createBackup" class="w100pm400px" onclick="window.open('?create_backup')" title="Backup aller Daten (Einstellungen, Familien, ...) herunterladen">Backup herunterladen</button><br /><button id="loadBackup" class="w100pm400px" onclick="window.open('?load_backup')" title="Backup aller Daten (Einstellungen, Familien, ...) laden">Backup laden</button></p>
                </div>
                <div class="cols3" id="settings"><h2>Allgemein</h2>
                    <p class="heading" style="display: inline;">Preis Formel: <span class="help" title="e ... Anzahl Erwachsene, k ... Anzahl Kinder&#xA;&#013;z.B.: e + k * 0.5&#xA;&#013;oder: (e > 0) * 2 + (k > 0)" onclick="alert(preist,'Hilfe zur Preis-Formel')">(?)</span></p><br><input id="preisf" class="w100pm400px" type="text" data-name="Preis" placeholder="Preisformel" />
                    <p></p>
                    <p class="heading" style="display: inline;">Karten-Designs: <span class="help" onclick="alert(kartent,'Hilfe zu Kartendesigns')">(?)</span></p><br><textarea id="kartend" class="w100pm400px" data-name="Kartendesigns" style="height: 120px;"></textarea>
                    <p><br></p><button id="sett-save" class="w100pm400px" title="Felder speichern bei ENTER automatisch">Alle Speichern</button>
                </div>
            </div>
        </div>
        <div id="tab6"><h1>Hilfe</h1>
            <h2>Ausgabe</h2>
            <p>Im Tab "Ausgabe" wird die Hauptarbeit gemacht.</p>
            <p>Links oben kann der aktuelle Ort ausgewählt werden und darunter die aktuelle Gruppe. Daneben findet sich alternativ das Suchfeld.</p>
            <p>Mit dem Barcodescanner kann ganz einfach gesucht werden: Das Suchfeld auswählen, scannen, fertig. Die gescannte Person wird automatisch gewählt und als anwesend eingetragen.</p><br>
            <p>Rechts oben befindet sich ein Zähler; diese Zahl ist dazu da, die anwesenden Personen zu sortieren.</p>
            <p>Jede anwesende Person bekommt Erwachsene / Kinder auf die Hand geschrieben ( z.B. 2 / 1 ) und darunter die eben genannte Nummer.</p>
            <p>Vor dem Schreiben sollte immer überprüft werden, ob die Anzahl der Personen auf der Karte mit denen im Computer übereinstimmt, damit der richtige Preis kalkuliert werden kann. Diese können sich ändern, wenn die anwesende Person nach Änderungen in der Familie eine neue Karte beantragt hat.</p><br>
            <p>Sollte eine Familie Schulden haben, so lassen sich diese direkt bearbeiten. Alternativ können auch alle Schulden beglichen werden (=0) oder der komplette Betrag hinzugefügt werden mit der jeweiligen Checkbox. Sollte der Betrag nur teilweise fehlen/beglichen werden, muss NUR das Textfeld verwendet werden.</p>
            <p>Wenn eine Person Schulden in Höhe des dreifachen des jeweiligen Preises hat (oder höher) muss diese Person erst ALLE Schulden zurückzahlen um wieder Essen holen zu drüfen. Dazu kann das Feld manuell auf 0 gesetzt werden oder "Schulden beglichen" gedrückt werden.</p><br>
            <h4>Barcode drucken</h4>
            <p>Sollte eine Person noch keinen Barcode auf der Karte haben (etwa neue Karte), so lässt sich dieser mit dem Befehl "Karte drucken" (rechts oben in Ausgabe und Verwaltung) ausdrucken.</p>
            <p>Die Darstellung ist optimiert für den Brother QL-500, in den Druckeinstellungen beachten, dass KEINE RÄNDER mitgedruckt werden dürfen! Der Code sollte im Querformat gedruckt werden.</p><br>
            <p>Mit dem Dropdown-Menü unten lassen sich auch andere Designs auswählen. Standard ist ein unformatiertes Papier (kann etwa auf A4 gedruckt werden) mit allen wichtigen Informationen, etwa um einen Bescheid zu drucken, sowie auch ein Visitenkartenformat mit dem Barcode in der Mitte.</p>
            <p>Weitere Designs lassen sich in den Einstellungen anlegen (mehr unten bzw. in Einstellungen).</p><br>
            <h5>Drucker (Brother QL-500)</h5>
            <p>Design 1 (nur Barcode) ist dafür ausgelegt, mit dem <a href="https://www.amazon.de/Brother-P-Touch-QL-500-BW-Etikettendrucker/dp/B002V4I8TI" target="_blank" class="link">Brother QL-500 Etikettendrucker</a> auf einen Streifen Klebe-Etiketten gedruckt zu werden (ähnliche Drucker sollten ebenfalls kompatibel sein). Die Größe des Barcodes ist optimiert für ein 12mm Endlos-Band DK-22214 (<a href="https://www.amazon.de/Brother-DK-22214-Endlosetiketten-Papier-QL-Etikettendrucker/dp/B0006HIQPS" target="_blank" class="link">Original</a>/<a href="https://www.amazon.de/Bubprint-Etiketten-kompatibel-Brother-DK-22214/dp/B00UN2CQB6" target="_blank" class="link">Alternative</a>).</p>
            <p>In den Druckeinstellungen (Systemeinstellungen "Geräte und Drucker"; Druck, nicht Drucker!) muss außerdem noch die Länge des Etiketts festgelegt werden, hier sind 25mm optimal.</p><br>
            <h4>Navigation über Tasten</h4>
            <p>Dieses Programm lässt sich in der Lebensmittel&shy;ausgabe auch nur über Tasten benutzen:<br>
                <ul><li><b>Alt + Pfeil Ab/Auf</b>: Nächste/Vorige Familie</li><li><b>Alt + n</b>: Ort wechseln, <b>Alt + m</b>: Gruppe wechseln (Je mit Pfeiltasten Auf/Ab), <b>Alt + ,</b>: Suchfeld, <b>Alt + .</b>: Gruppe neu laden</li><li><b>Alt + j</b>: Ablaufdatum der Karte, <b>Alt + k</b>: Schulden, <b>Alt + l</b>: Notizen</li><li><b>Alt + u</b>: Anwesend, <b>Alt + i</b>: Geld vergessen, <b>Alt + o</b>: Schulden beglichen</li></ul>
            </p><br>

            <h2>Verwaltung</h2>
            <p>Um neue Personen anzulegen oder existierende Personen zu bearbeiten, muss man in diesen Tab wechseln.</p>
            <p>Existierende Personen können direkt vom Ausgabe-Tab unten mit "Familie bearbeiten" aufgerufen werden oder mithilfe der Suche.</p>
            <p>Um eine neue Familie anzulegen, erst den "+"-Knopf unter der Liste drücken, nach Eingabe der Daten mit "Neu anlegen" speichern.</p><br>
            <p>Das Programm unterstützt in diesem Modus die Bearbeitung aller Felder. Alle Daten können nun über die Textfelder verändert werden. Hier sollte vor allem darauf geachtet werden, das richtige Feld zu wählen.</p>
            <p>Beim Neuanlegen wird außerdem automatisch die Gruppe mit den wenigsten Personen ausgewählt.</p>
            <p>Die Daten der Familie müssen manuell mit dem Knopf unten gespeichert werden.</p><br>
            <p>Bitte vor dem Anlegen immer überprüfen, ob diese Familie bereits eingetragen ist (möglicherweise vertippt)!</p><br>

            <h2>Suche</h2>
            <p>Sowohl im Tab "Ausgabe" als auch im Tab "Verwaltung" findet sich ein Suchfeld.</p>
            <p>Der Inhalt wird bei der Suche bei den Leerzeichen aufgebrochen und als mehrere Parameter verwendet. Das heißt, Begriffe in der Suche müssen nicht in dieser Reihenfolge im Ergebnis erscheinen.</p>
            <p>Die Suche erstreckt sich über die Felder ID, Name und Ort.</p><br>
            <p>Standardmäßig wird nach "Wildcard" gesucht; zu Deutsch, es können auch Buchstaben (und Zahlen) vor und nach dem Begriff sein.</p>
            <p>Um nach einem Begriff inklusive Leerzeichen zu suchen, kann der gesamte Begriff in Anführungszeichen <span class="code">"</span> oder <span class="code">'</span> gegeben werden. Zum Beispiel: <span class="code">"Vorname Nachname"</span> sucht nach "Vorname Nachname", wobei davor und danach Buchstaben (und Zahlen) sein dürfen, allerdings nicht dazwischen.</p>
            <p>Um nach genau nach einem Begriff zu suchen (das gesamte Feld muss dem Begriff entsprechen, ohne Wildcard) kann ein Gleichheitszeichen <span class="code">=</span> vor dem Begriff (obiges auch möglich) angebracht werden. Beispiel: <span class="code">=Name</span> oder <span class="code">="Vorname Nachname"</span>.</p>
            <p>Um einen Begriff aus der Suche auszuschließen, also dass der Begriff in keinem der Felder erscheinen darf, kann ein Ausrufezeicen <span class="code">!</span> vor dem Begriff angebracht werden. Beispiel: <span class="code">!Feld</span> schließt alle mit "Feld" in Name und Ort aus (Begriffe mit Anführungszeichen erlaubt).</p>
            <p>Eine Kombination des obigen ist ebenfalls möglich: <span class="code">!=</span> schließt alle Familien aus, bei denen ein gesamtes Feld dem Begriff entspricht (Begriffe mit Anführungszeichen erlaubt).</p><br>
            <p>Wenn nur eine Zahl eingegeben wird (gesamtes Feld), so wird dieses als ID interpretiert (etwa vom Barcode), somit wird nur in diesem Feld nach genau diesem Wert gesucht.</p><br>

            <h2>Logs</h2>
            <p>Dieser Tab ermöglicht das Abrufen der Einnahmen in jedem beliebigen Zeitraum mittels der zwei Felder oben.</p>
            <p>Bei den Einnahmen werden sowohl der Preis als auch die Änderungen in Schulden zusammengerechnet.</p><br>
            <p>Darunter findet sich außerdem eine Liste mit allen Aktionen, die über das Programm getätigt worden sind. Die Darstellung ist etwas kompliziert, enthält jedoch alle Informationen.</p><br>

            <h2>Einstellungen</h2>
            <p>In den Einstellungen lassen sich alle administrativen Operationen betätigen.</p>
            <p>Wenn genügend Platz ist, ist dieser Tab in drei Spalten aufgeteilt: Orte, Aktionen und allgemeine Einstellungen. Bei kleineren Bildschirmen werden diese Spalten untereinander (je nach Platz) angeordnet.</p><br>
            <h4>Orte</h4>
            <p>Dieses Menü ermöglicht das Anlegen und Bearbeiten (und Löschen) aller Ausgabeorte. Mit dem "+"-Knopf können Orte angelegt werden, per Klick lassen sich alle Daten bearbeiten. Das Programm speichert nur bei Knopfdruck!</p>
            <p>Orte besitzen zwei Felder: Name und Gruppen. Zweiters definiert die Anzahl der auswählbaren Gruppen pro Ort.</p><br>
            <h4>Aktionen</h4>
            <p>Hier finden sich Knöpfe für Massenoperationen oder allgemeine Aktionen.</p>
            <p>Weitere Informationen lassen sich mit Hovering (Maus über den Knopf halten) anzeigen.</p><br>
            <h4>Allgemeines</h4>
            <p>Diese Spalte beinhalten Einstellungen im wahren Sinne des Wortes; hier lassen sich Eigenschaften über Inputs festlegen.</p>
            <p>Textfelder mit nur einer Zeile speichern automatisch mit "Enter", mehrzeilige Textareas lassen sich nur mit dem Knopf "Alle speichern" unten festsetzen. Dieser Knopf speichert alle Felder in dieser Spalte, es werden also Änderungen in jedem Feld aufgenommen, auch die einzeiligen.</p><br>
            <p>Per Hovering über oder klicken auf (?) werden weitere Informationen angezeigt.</p><br>

            <h2>Druckversion</h2>
            <p>In der Kopfzeile finden sich zwei weitere Knöpfe: Druckversion und Logout.</p>
            <p>Druckversion öffnet eine neue Seite, mit welcher Sektionen des Programms gedruckt werden können.</p>
            <p>Die Seite ermöglicht es, sowohl Ort als auch Gruppe auszuwählen (alternativ auch Alle), mit "OK" wird dann eine Tabelle generiert, welche die gewünschten Daten enthält. Ebenfalls wird ein Rechteck hinter dem Name eingefügt, um Personen als Anwesend abzuhacken.</p>
            <p>Um eine einzelne Gruppe zu wählen, muss zuerst der gewünschte Ort gesetzt werden und danach mit "OK" bestätigt werden. Erst dann werden die verschiedenen Gruppen angezeigt.</p>
        </div>
    </div>
</div>

<div id="modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2 class="modal-head">Modal Header</h2>
    </div>
    <div class="modal-body">
      <p>Some text in the Modal Body</p>
      <p>Some other text...</p>
    </div>
    <div class="modal-footer">
      <h3 class="modal-foot">Modal Footer</h3>
    </div>
  </div>

</div>

</body></html>