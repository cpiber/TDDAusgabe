
  ---------------------
 | Tischlein Deck Dich |
  ---------------------


=====
EINFÜHRUNG
=====

- Hauptdatei: TDD_Ausgabe.php
- Programm braucht einen Webserver: WebHosting (online Webseite) oder
		lokaler Server (XAMPP, Laragon, etc.)
	- Getestet mit:
			Laragon (MySQL v5.1 Datenbank, PHP 5.4.9)
			XAMPP (MariaDB v10.4 Datenbank, PHP 8.2.4)
			--> sollte mit jedem gewöhnlichen Webserver funktionieren
	- Datei muss über localhost/Adresse der Webseite aufgerufen werden
		- Lokaler Server: localhost ist URL zur Webseite

- Hauptdatei kann beliebig umbenennt werden (Aufruf ändern!)
- Weitere Datein im Repository:
	TDD_Setup.sql: SQL-Befehl zum einlesen einer Datei in Familien-Tabelle
			Liest Datei Familien.txt auf localhost
	TDD_XmlToTxt.js: Wandelt eine xml-Datei in das richtige Format für
			obiges um. Weitere Info in der Datei.


=====
SETUP
=====

- Aufruf der Seite: Log-In-Screen
- In der Adress-Leiste ?setup anhängen
- Administrator-Konto der Datenbank eingeben
	- Lokaler Server: normal UN: root, PW: (leer)
	- Webserver: auf der Administrationsoberfläche nachsehen
- Alle 4 Schritte ausführen
- Fertig



=====
Nutzung
=====

- Login mit den Daten aus dem Setup
	- Administrationslogin nicht empfohlen aber möglich
- Bei richtigen Logindaten erscheint die rote TDD-Oberfläche
- Im Tab "Start" finden sich alle wichtigen Informationen zur Bedienung
- Detaillierte Anweisungen finden sich bei "Hilfe"




Constantin Piber, 2018-2023
 constantin.piber@gmail.com
