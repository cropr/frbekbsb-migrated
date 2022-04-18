CREATE TABLE `a_tournaments` (
  `parameter_url` int(10) NOT NULL AUTO_INCREMENT COMMENT '?trn=1',
  `event_code_fide` char(100) NOT NULL COMMENT 'code FIDE des tournois séparés par virgule (TIPC)',
  `name` varchar(100) NOT NULL,
  `adress` varchar(100) DEFAULT NULL COMMENT 'Local de jeu',
  `city` varchar(50) DEFAULT NULL COMMENT 'pour le rapport FIDE',
  `system` varchar(60) DEFAULT NULL COMMENT 'SWISS SWISS_DBL SWISS_ACCELERE SWISS_321 SWISS_BAKU SW_AMERICAIN\r\nSW_AMERICAIN_DBL ROBIN ROBIN_DBL ROBIN_AR',
  `rounds` int(2) DEFAULT NULL,
  `category` varchar(100) NOT NULL COMMENT 'SEULEMENT à partir 2ème catégorie, séparé par une virgule. ELO <2000,1800,1600,....> AGE <20,18,16,...>',
  `opening_registrations` varchar(40) DEFAULT NULL COMMENT 'AAAA-MM-JJ HH:MM:SS',
  `closing_registrations` varchar(40) DEFAULT NULL COMMENT 'AAAA-MM-JJ HH:MM:SS',
  `obligatory_presence` varchar(40) DEFAULT NULL COMMENT 'AAAA-MM-JJ HH:MM:SS - Heure max. où il faut être pour présent pour relevé des précences ',
  `date_start` varchar(40) DEFAULT NULL COMMENT 'Date et heure de début AAAA-MM-JJ HH:MM:SS',
  `date_end` varchar(20) DEFAULT NULL COMMENT 'Date de fin AAAA-MM-JJ',
  `chief_arbitrer` varchar(60) DEFAULT NULL COMMENT 'luc.cornet@telenet.be pas gmail',
  `chief_arbiter_id` int(12) DEFAULT NULL,
  `email_chief_arbiter` varchar(60) DEFAULT NULL,
  `gsm_chief_arbiter` varchar(20) DEFAULT NULL,
  `deputy_arbiter_1` varchar(60) DEFAULT NULL,
  `deputy_arbiter_id_1` int(12) DEFAULT NULL,
  `email_deputy_chief_arbiter_1` varchar(60) DEFAULT NULL,
  `deputy_arbiter_2` varchar(60) DEFAULT NULL,
  `deputy_arbiter_id_2` int(12) DEFAULT NULL,
  `email_deputy_chief_arbiter_2` varchar(60) DEFAULT NULL,
  `chief_organizer` varchar(60) DEFAULT NULL,
  `chief_organizer_id` int(12) DEFAULT NULL,
  `email_chief_organizer` varchar(60) DEFAULT NULL,
  `gsm_chief_organizer` varchar(20) DEFAULT NULL,
  `time_control` varchar(50) DEFAULT NULL COMMENT 'Std, Rapid ou Blitz',
  `time_control_details` varchar(60) DEFAULT NULL COMMENT 'Important: cadences SWAR uniquement',
  `numero_cadence_swar` int(2) DEFAULT NULL COMMENT ' N° de la cadence provenant du fichier Swar.Lang.fr.ini',
  `url` varchar(255) DEFAULT NULL COMMENT 'du site web de l''organisateur',
  `club_organisateur` varchar(100) DEFAULT NULL COMMENT 'seulement le n° de club',
  `federation` varchar(255) DEFAULT NULL COMMENT 'ne rien indiquer, sinon FRBE, KBSB, KSB, FEFB, VSF, SVDB, FIDE',
  `email_copy_1` varchar(60) DEFAULT NULL,
  `email_copy_2` varchar(60) DEFAULT NULL,
  `email_copy_3` varchar(60) DEFAULT NULL,
  `filter_message` varchar(12) DEFAULT NULL,
  `date_registered` datetime DEFAULT NULL,
  PRIMARY KEY (`parameter_url`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `a_registrations` (
  `Id` int(12) NOT NULL AUTO_INCREMENT,
  `IdTournament` int(10) DEFAULT NULL COMMENT '=parameter_url',
  `NameTournament` varchar(100) DEFAULT NULL COMMENT '=name tournament',
  `Name` varchar(50) DEFAULT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `Sex` varchar(1) DEFAULT NULL,
  `DateBirth` datetime DEFAULT NULL,
  `PlaceBirth` varchar(50) DEFAULT NULL,
  `CountryResidence` varchar(50) DEFAULT NULL,
  `NationalitePlayer` varchar(5) DEFAULT NULL,
  `Telephone` varchar(20) DEFAULT NULL,
  `GSM` varchar(20) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `YearAffiliation` int(4) DEFAULT NULL,
  `RegistrationNumberBelgian` int(5) DEFAULT NULL,
  `Federation` varchar(12) NOT NULL,
  `ClubNumber` int(6) unsigned DEFAULT NULL,
  `ClubName` varchar(50) DEFAULT NULL,
  `EloBelgian` int(4) DEFAULT NULL,
  `FideId` int(12) DEFAULT NULL,
  `EloFide` int(4) DEFAULT NULL,
  `EloFideR` int(4) DEFAULT NULL,
  `EloFideB` int(4) DEFAULT NULL,
  `Title` varchar(5) DEFAULT NULL,
  `NationalityFide` varchar(5) DEFAULT NULL,
  `Category` varchar(20) DEFAULT NULL COMMENT 'Type de tournoi TIPC',
  `Note` varchar(255) DEFAULT NULL,
  `Contact` varchar(10) DEFAULT NULL,
  `RoundsAbsent` varchar(100) DEFAULT NULL COMMENT 'Numéro des rondes absentes séparé par ,',
  `DateModif` datetime DEFAULT NULL,
  `G` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=350 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;