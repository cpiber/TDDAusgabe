-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.1.72-community - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win32
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Exportiere Datenbank Struktur für tdd_server
CREATE DATABASE IF NOT EXISTS `tdd_server` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `tdd_server`;

-- Exportiere Struktur von Tabelle tdd_server.einstellungen
CREATE TABLE IF NOT EXISTS `einstellungen` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Val` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle tdd_server.familien
CREATE TABLE IF NOT EXISTS `familien` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Erwachsene` int(11) NOT NULL DEFAULT '0',
  `Kinder` int(11) NOT NULL DEFAULT '0',
  `Ort` int(11) NOT NULL,
  `Gruppe` int(11) DEFAULT '0',
  `Schulden` decimal(5,2) NOT NULL DEFAULT '0.00',
  `Karte` date DEFAULT NULL,
  `lAnwesenheit` date DEFAULT NULL,
  `Notizen` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Num` int(11) DEFAULT '0',
  `Adresse` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Telefonnummer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProfilePic` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ProfilePic2` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` bit(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle tdd_server.orte
CREATE TABLE IF NOT EXISTS `orte` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Gruppen` int(11) NOT NULL DEFAULT '0',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` bit(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
