-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_users`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `level` char(1) NOT NULL DEFAULT '',
  `status` char(1) NOT NULL,
  `expiration` datetime NOT NULL,
  `email` varchar(255) NOT NULL,
  `time_zone` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `password_generated_at` datetime NOT NULL,
  `temp_password` varchar(255) NOT NULL,
  `password_used` char(1) NOT NULL,
  `ID_packages` int(11) NOT NULL,
  `lastlogin` datetime NOT NULL,
  `profile` int(11) NOT NULL,
  `special` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_packages` (`ID_packages`),
  KEY `profile` (`profile`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `cm_mod_security_users`
--

INSERT INTO `cm_mod_security_users` (`ID`, `ID_domains`, `username`, `password`, `level`, `status`, `expiration`, `email`, `time_zone`, `role`, `created`, `modified`, `password_generated_at`, `temp_password`, `password_used`, `ID_packages`, `lastlogin`, `profile`, `special`, `avatar`, `firstname`, `lastname`) VALUES
(1, 0, 'admin', '*2470C0C06DEE42FD1618BB99005ADCA2EC9D1E19', '3', '1', '0000-00-00 00:00:00', '', 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 0, '0000-00-00 00:00:00', 0, '', '', 'John', 'Doe');

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_users_fields`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_users_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_users` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_users` (`ID_users`),
  KEY `ID_users_2` (`ID_users`,`field`),
  KEY `field` (`field`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_timezones`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_timezones` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `cm_mod_security_timezones`
--

INSERT INTO `cm_mod_security_timezones` (`ID`, `name`) VALUES
(1, 'GMT-14'),
(2, 'GMT-13'),
(3, 'GMT-12'),
(4, 'GMT-11'),
(5, 'GMT-10'),
(6, 'GMT-9'),
(7, 'GMT-8'),
(8, 'GMT-7'),
(9, 'GMT-6'),
(10, 'GMT-5'),
(11, 'GMT-4'),
(12, 'GMT-3'),
(13, 'GMT-2'),
(14, 'GMT-1'),
(15, 'GMT+0'),
(16, 'GMT+1'),
(17, 'GMT+2'),
(18, 'GMT+3'),
(19, 'GMT+4'),
(20, 'GMT+5'),
(21, 'GMT+6'),
(22, 'GMT+7'),
(23, 'GMT+8'),
(24, 'GMT+9'),
(25, 'GMT+10'),
(26, 'GMT+11'),
(27, 'GMT+12');

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_domains`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_domains` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `owner` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `type` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `expiration_date` date NOT NULL,
  `time_zone` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `billing_status` int(11) NOT NULL,
  `db_host` varchar(255) NOT NULL,
  `db_name` varchar(255) NOT NULL,
  `db_user` varchar(255) NOT NULL,
  `db_pass` varchar(255) NOT NULL,
  `ID_packages` int(11) NOT NULL,
  `max_users` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_packages` (`ID_packages`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `cm_mod_security_domains_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_domains` (`ID_domains`),
  KEY `ID_domains_2` (`ID_domains`,`field`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_packages`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_packages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` char(1) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_packages_fields`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_packages_fields` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_packages` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `unlimited` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_packages_2` (`ID_packages`,`field`),
  KEY `ID_packages` (`ID_packages`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ff_international` (`ID_lang`, `word_code`, `description`) VALUES
(1, 'login_username', 'nome utente'),
(1, 'login_password', 'password'),
(1, 'login_confirm', 'accedi'),
(1, 'login_text_logout', 'Logout'),
(1, 'nav_profile', 'Profilo'),
(1, 'logout_title', 'Sei sicuro di voler effettuare il Logout?'),
(1, 'logout_confirm', 'Si'),
(1, 'logout_cancel', 'No'),
(1, 'user_username', 'Username'),
(1, 'user_password', 'Password'),
(1, 'user_confirmpass', 'Conferma Password'),
(1, 'user_email', 'Indirizzo E-Mail'),
(1, 'user_nome', 'Nome'),
(1, 'user_cognome', 'Cognome'),
(1, 'user_indirizzo', 'Indirizzo'),
(1, 'user_citta', 'Città'),
(1, 'user_provincia', 'Provincia'),
(1, 'user_cap', 'CAP'),
(1, 'user_paese', 'Paese'),
(1, 'user_telefono', 'Telefono'),
(1, 'user_datanascita', 'Data di Nascita (gg/mm/aaaa)'),
(1, 'user_sesso', 'Sesso'),
(1, 'user_provincianascita', 'Provincia di Nascita'),
(1, 'user_cittanascita', 'Nato a'),
(1, 'user_codicefiscale', 'Codice Fiscale'),
(1, 'user_sessomaschio', 'Maschio'),
(1, 'user_sessofemmina', 'Femmina'),
(1, 'user_piva', 'Partita IVA'),
(1, 'user_publish', 'Pubblica in Home Page'),
(1, 'back_to_site', 'Torna al sito');

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_profiles`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_profiles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `created_time` datetime NOT NULL,
  `created_user` int(11) NOT NULL,
  `modified_time` datetime NOT NULL,
  `modified_user` int(11) NOT NULL,
  `enabled` char(1) NOT NULL,
  `order` int(11) NOT NULL,
  `special` varchar(255) NOT NULL,
  `acl` varchar(255) NOT NULL,
  `ID_domains` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_security_profiles_pairs`
--

CREATE TABLE IF NOT EXISTS `cm_mod_security_profiles_pairs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_profile` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `view_own` char(1) NOT NULL,
  `view_others` char(1) NOT NULL,
  `modify_own` char(1) NOT NULL,
  `modify_others` char(1) NOT NULL,
  `insert_own` char(1) NOT NULL,
  `insert_others` char(1) NOT NULL,
  `delete_own` char(1) NOT NULL,
  `delete_others` char(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID_profile` (`ID_profile`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cm_mod_security_rel_profiles_users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_user` int(11) NOT NULL,
  `ID_profile` int(11) NOT NULL,
  `enabled` char(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

CREATE TABLE IF NOT EXISTS `support_province` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RegionID` int(11) NOT NULL,
  `Name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `CarAbbreviation` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `RegionID` (`RegionID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `support_province`
--

INSERT INTO `support_province` (`ID`, `RegionID`, `Name`, `CarAbbreviation`, `slug`) VALUES
(1, 1, 'Torino', 'TO', 'torino'),
(2, 1, 'Vercelli', 'VC', 'vercelli'),
(3, 1, 'Novara', 'NO', 'novara'),
(4, 1, 'Cuneo', 'CN', 'cuneo'),
(5, 1, 'Asti', 'AT', 'asti'),
(6, 1, 'Alessandria', 'AL', 'alessandria'),
(7, 2, 'Aosta', 'AO', 'aosta'),
(8, 7, 'Imperia', 'IM', 'imperia'),
(9, 7, 'Savona', 'SV', 'savona'),
(10, 7, 'Genova', 'GE', 'genova'),
(11, 7, 'La Spezia', 'SP', 'la-spezia'),
(12, 3, 'Varese', 'VA', 'varese'),
(13, 3, 'Como', 'CO', 'como'),
(14, 3, 'Sondrio', 'SO', 'sondrio'),
(15, 3, 'Milano', 'MI', 'milano'),
(16, 3, 'Bergamo', 'BG', 'bergamo'),
(17, 3, 'Brescia', 'BS', 'brescia'),
(18, 3, 'Pavia', 'PV', 'pavia'),
(19, 3, 'Cremona', 'CR', 'cremona'),
(20, 3, 'Mantova', 'MN', 'mantova'),
(21, 4, 'Bolzano', 'BZ', 'bolzano'),
(22, 4, 'Trento', 'TN', 'trento'),
(23, 5, 'Verona', 'VR', 'verona'),
(24, 5, 'Vicenza', 'VI', 'vicenza'),
(25, 5, 'Belluno', 'BL', 'belluno'),
(26, 5, 'Treviso', 'TV', 'treviso'),
(27, 5, 'Venezia', 'VE', 'venezia'),
(28, 5, 'Padova', 'PD', 'padova'),
(29, 5, 'Rovigo', 'RO', 'rovigo'),
(30, 6, 'Udine', 'UD', 'udine'),
(31, 6, 'Gorizia', 'GO', 'gorizia'),
(32, 6, 'Trieste', 'TS', 'trieste'),
(33, 8, 'Piacenza', 'PC', 'piacenza'),
(34, 8, 'Parma', 'PR', 'parma'),
(35, 8, 'Reggio Emilia', 'RE', 'reggio-emilia'),
(36, 8, 'Modena', 'MO', 'modena'),
(37, 8, 'Bologna', 'BO', 'bologna'),
(38, 8, 'Ferrara', 'FE', 'ferrara'),
(39, 8, 'Ravenna', 'RA', 'ravenna'),
(40, 8, 'Forlì Cesena', 'FC', 'forli-cesena'),
(41, 11, 'Pesaro Urbino', 'PU', 'pesaro-urbino'),
(42, 11, 'Ancona', 'AN', 'ancona'),
(43, 11, 'Macerata', 'MC', 'macerata'),
(44, 11, 'Ascoli Piceno', 'AP', 'ascoli-piceno'),
(45, 9, 'Massa Carrara', 'MS', 'massa-carrara'),
(46, 9, 'Lucca', 'LU', 'lucca'),
(47, 9, 'Pistoia', 'PT', 'pistoia'),
(48, 9, 'Firenze', 'FI', 'firenze'),
(49, 9, 'Livorno', 'LI', 'livorno'),
(50, 9, 'Pisa', 'PI', 'pisa'),
(51, 9, 'Arezzo', 'AR', 'arezzo'),
(52, 9, 'Siena', 'SI', 'siena'),
(53, 9, 'Grosseto', 'GR', 'grosseto'),
(54, 10, 'Perugia', 'PG', 'perugia'),
(55, 10, 'Terni', 'TR', 'terni'),
(56, 12, 'Viterbo', 'VT', 'viterbo'),
(57, 12, 'Rieti', 'RI', 'rieti'),
(58, 12, 'Roma', 'RM', 'roma'),
(59, 12, 'Latina', 'LT', 'latina'),
(60, 12, 'Frosinone', 'FR', 'frosinone'),
(61, 15, 'Caserta', 'CE', 'caserta'),
(62, 15, 'Benevento', 'BN', 'benevento'),
(63, 15, 'Napoli', 'NA', 'napoli'),
(64, 15, 'Avellino', 'AV', 'avellino'),
(65, 15, 'Salerno', 'SA', 'salerno'),
(66, 13, 'L''Aquila', 'AQ', 'l-aquila'),
(67, 13, 'Teramo', 'TE', 'teramo'),
(68, 13, 'Pescara', 'PE', 'pescara'),
(69, 13, 'Chieti', 'CH', 'chieti'),
(70, 14, 'Campobasso', 'CB', 'campobasso'),
(71, 16, 'Foggia', 'FG', 'foggia'),
(72, 16, 'Bari', 'BA', 'bari'),
(73, 16, 'Taranto', 'TA', 'taranto'),
(74, 16, 'Brindisi', 'BR', 'brindisi'),
(75, 16, 'Lecce', 'LE', 'lecce'),
(76, 17, 'Potenza', 'PZ', 'potenza'),
(77, 17, 'Matera', 'MT', 'matera'),
(78, 18, 'Cosenza', 'CS', 'cosenza'),
(79, 18, 'Catanzaro', 'CZ', 'catanzaro'),
(80, 18, 'Reggio di Calabria', 'RC', 'reggio-di-calabria'),
(81, 19, 'Trapani', 'TP', 'trapani'),
(82, 19, 'Palermo', 'PA', 'palermo'),
(83, 19, 'Messina', 'ME', 'messina'),
(84, 19, 'Agrigento', 'AG', 'agrigento'),
(85, 19, 'Caltanissetta', 'CL', 'caltanissetta'),
(86, 19, 'Enna', 'EN', 'enna'),
(87, 19, 'Catania', 'CT', 'catania'),
(88, 19, 'Ragusa', 'RG', 'ragusa'),
(89, 19, 'Siracusa', 'SR', 'siracusa'),
(90, 20, 'Sassari', 'SS', 'sassari'),
(91, 20, 'Nuoro', 'NU', 'nuoro'),
(92, 20, 'Cagliari', 'CA', 'cagliari'),
(93, 6, 'Pordenone', 'PN', 'pordenone'),
(94, 14, 'Isernia', 'IS', 'isernia'),
(95, 20, 'Oristano', 'OR', 'oristano'),
(96, 1, 'Biella', 'BI', 'biella'),
(97, 3, 'Lecco', 'LC', 'lecco'),
(98, 3, 'Lodi', 'LO', 'lodi'),
(99, 8, 'Rimini', 'RN', 'rimini'),
(100, 9, 'Prato', 'PO', 'prato'),
(101, 18, 'Crotone', 'KR', 'crotone'),
(102, 18, 'Vibo Valentia', 'VV', 'vibo-valentia'),
(103, 1, 'Verbano Cusio Ossola', 'VB', 'verbano-cusio-ossola'),
(104, 20, 'Olbia Tempio', 'OT', 'olbia-tempio'),
(105, 20, 'Ogliastra', 'OG', 'ogliastra'),
(106, 20, 'Medio Campidano', 'VS', 'medio-campidano'),
(107, 20, 'Carbonia Iglesias', 'CI', 'carbonia-iglesias'),
(108, 3, 'Monza e della Brianza', 'MB', 'monza-e-della-brianza'),
(109, 11, 'Fermo', 'FM', 'fermo'),
(110, 16, 'Barletta Andria Trani', 'BT', 'barletta-andria-trani');

CREATE TABLE IF NOT EXISTS `support_regioni` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `support_regioni`
--

INSERT INTO `support_regioni` (`ID`, `Name`, `slug`) VALUES
(1, 'Piemonte', 'piemonte'),
(2, 'Valle d''Aosta', 'valle-d-aosta'),
(3, 'Lombardia', 'lombardia'),
(4, 'Trentino Alto Adige', 'trentino-alto-adige'),
(5, 'Veneto', 'veneto'),
(6, 'Friuli Venezia Giulia', 'friuli-venezia-giulia'),
(7, 'Liguria', 'liguria'),
(8, 'Emilia Romagna', 'emilia-romagna'),
(9, 'Toscana', 'toscana'),
(10, 'Umbria', 'umbria'),
(11, 'Marche', 'marche'),
(12, 'Lazio', 'lazio'),
(13, 'Abruzzo', 'abruzzo'),
(14, 'Molise', 'molise'),
(15, 'Campania', 'campania'),
(16, 'Puglia', 'puglia'),
(17, 'Basilicata', 'basilicata'),
(18, 'Calabria', 'calabria'),
(19, 'Sicilia', 'sicilia'),
(20, 'Sardegna', 'sardegna');


CREATE TABLE IF NOT EXISTS `support_countries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `desc` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `support_countries`
--

INSERT INTO `support_countries` (`ID`, `desc`, `code`) VALUES
(1, 'Albania', 'AL'),
(2, 'Algeria', 'DZ'),
(3, 'Andorra', 'AD'),
(4, 'Angola', 'AN7'),
(5, 'Anguilla', 'AI'),
(6, 'Antigua e Barbuda', 'AG'),
(7, 'Antille Olandesi', 'AN'),
(8, 'Arabia Saudita', 'SA'),
(9, 'Argentina', 'AR'),
(10, 'Armenia', 'AM'),
(11, 'Aruba', 'AW'),
(12, 'Australia', 'AU'),
(13, 'Austria', 'AT'),
(14, 'Azerbaigian', 'AZ'),
(15, 'Bahamas', 'BS'),
(16, 'Bahrain', 'BH'),
(17, 'Bangladesh', 'BD'),
(18, 'Barbados', 'BB'),
(19, 'Barbuda (Antigua)', 'AG1'),
(20, 'Belgio', 'BE'),
(21, 'Belize', 'BZ'),
(22, 'Benin', 'BJ'),
(23, 'Bermuda', 'BM'),
(24, 'Bielorussia', 'BY'),
(25, 'Bolivia', 'BO'),
(26, 'Bonaire (Antille Olandesi)', 'AN1'),
(27, 'Bosnia-Herzegovina', 'BA'),
(28, 'Botswana', 'BW'),
(29, 'Brasile', 'BR'),
(30, 'Brunei', 'BN'),
(31, 'Bulgaria', 'BG'),
(32, 'Burkina Faso', 'BF'),
(33, 'Burundi', 'BI'),
(34, 'Cambogia', 'KH'),
(35, 'Camerun', 'CM'),
(36, 'Canada', 'CA'),
(37, 'Ceca, Repubblica', 'CZ'),
(38, 'Centrafricana, Repubblica', 'CF'),
(39, 'Ceuta', 'XCE'),
(40, 'Ciad', 'TD'),
(41, 'Cile', 'CL'),
(42, 'Cina, Repubblica Popolare', 'CN'),
(43, 'Cipro', 'CY'),
(44, 'Cisgiordania e Gaza', 'XGC'),
(45, 'Colombia', 'CO'),
(46, 'Congo (Brazzaville)', 'CG1'),
(47, 'Congo, Repubblica Dem.', 'CG'),
(48, 'Corea del Sud', 'KR'),
(49, 'Costa d''Avorio', 'CI'),
(50, 'Costa Rica', 'CR'),
(51, 'Croazia', 'HR'),
(52, 'Curaçao (Antille Olandesi)', 'AN2'),
(53, 'Danimarca', 'DK'),
(54, 'Dominica', 'DM'),
(55, 'Dominicana, Repubblica', 'DO'),
(56, 'Ecuador', 'EC'),
(57, 'Egitto', 'EG'),
(58, 'El Salvador', 'SV'),
(59, 'Emirati Arabi Uniti', 'AE'),
(60, 'Eritrea', 'ER'),
(61, 'Estonia', 'EE'),
(62, 'Etiopia', 'ET'),
(63, 'Federazione Yugoslava', 'YU'),
(64, 'Figi', 'FJ'),
(65, 'Filippine', 'PH'),
(66, 'Finlandia', 'FI'),
(67, 'Francia', 'FR'),
(68, 'Gabon', 'GA'),
(69, 'Gambia', 'GM'),
(70, 'Gaza e Cisgiordania', 'XCG'),
(71, 'Georgia', 'GE'),
(72, 'Germania', 'DE'),
(73, 'Ghana', 'GH'),
(74, 'Giamaica', 'JM'),
(75, 'Giappone', 'JP'),
(76, 'Gibilterra', 'GI'),
(77, 'Gibuti', 'DJ'),
(78, 'Giordania', 'JO'),
(79, 'Grecia', 'GR'),
(80, 'Grenada', 'GD'),
(81, 'Groenlandia', 'GL'),
(82, 'Guadalupa', 'GP'),
(83, 'Guam', 'GU'),
(84, 'Guatemala', 'GT'),
(85, 'Guinea', 'GN'),
(86, 'Guinea Equatoriale', 'GQ'),
(87, 'Guinea-Bissau', 'GW'),
(88, 'Guyana', 'GY'),
(89, 'Guyana Francese', 'GF'),
(90, 'Haiti', 'HT'),
(91, 'Honduras', 'HN'),
(92, 'Hong Kong', 'HK'),
(93, 'India', 'IN'),
(94, 'Indonesia', 'ID'),
(95, 'Irlanda, Repubblica', 'IE'),
(96, 'Islanda', 'IS'),
(97, 'Isola Union (St Vincente Grenadines)', 'VC'),
(98, 'Isola Wake', 'XIW'),
(99, 'Isole Canarie', 'XIC'),
(100, 'Isole Capo Verde', 'CV'),
(101, 'Isole Cayman', 'KY'),
(102, 'Isole Cook', 'CK'),
(103, 'Isole Faroe', 'FO'),
(104, 'Isole Marianne del Nord', 'MP'),
(105, 'Isole Marshall', 'MH'),
(106, 'Isole Salomone', 'SB'),
(107, 'Isole Turks e Caicos', 'TC'),
(108, 'Isole Vergini Britanniche', 'VG'),
(109, 'Isole Vergini Statunitensi', 'VI'),
(110, 'Isole Wallis e Futuna', 'WF'),
(111, 'Israele', 'IL'),
(112, 'Italia', 'IT'),
(113, 'Kazakistan', 'KZ'),
(114, 'Kenya', 'KE'),
(115, 'Kiribati', 'KI'),
(116, 'Kosrae (Stati Federali Micronesia)', 'FM1'),
(117, 'Kuwait', 'KW'),
(118, 'Kyrgyzstan', 'KG'),
(119, 'Laos', 'LA'),
(120, 'Lesotho', 'LS'),
(121, 'Lettonia', 'LV'),
(122, 'Libano', 'LB'),
(123, 'Liberia', 'LR'),
(124, 'Liechtenstein', 'LI'),
(125, 'Lituania', 'LT'),
(126, 'Lussemburgo', 'LU'),
(127, 'Macau', 'MO'),
(128, 'Macedonia', 'MK'),
(129, 'Madagascar', 'MG'),
(130, 'Madera,  Isola di', 'XMI'),
(131, 'Malawi', 'MW'),
(132, 'Maldive', 'MV'),
(133, 'Malesia', 'MY'),
(134, 'Mali', 'ML'),
(135, 'Malta', 'MT'),
(136, 'Marocco', 'MA'),
(137, 'Martinica', 'MQ'),
(138, 'Mauritania', 'MR'),
(139, 'Mauritius', 'MU'),
(140, 'Melilla', 'XME'),
(141, 'Messico', 'MX'),
(142, 'Micronesia (Stati Federali della)', 'FM2'),
(143, 'Moldavia', 'MD'),
(144, 'Monaco (Principato di)', 'MC'),
(145, 'Mongolia', 'MO1'),
(146, 'Montserrat', 'MS'),
(147, 'Mozambico', 'MZ'),
(148, 'Myanmar', 'MM'),
(149, 'Namibia', 'NA'),
(150, 'Nepal', 'NP'),
(151, 'Nevis (St.Kitts - Nevis)', 'KN1'),
(152, 'Nicaragua', 'NI'),
(153, 'Niger', 'NE'),
(154, 'Nigeria', 'NG'),
(155, 'Norvegia', 'NO'),
(156, 'Nuova Caledonia', 'NC'),
(157, 'Nuova Zelanda', 'NZ'),
(158, 'Olanda (Paesi Bassi)', 'NL'),
(159, 'Olandesi, Antille', 'AN3'),
(160, 'Oman', 'OM'),
(161, 'Pakistan', 'PK'),
(162, 'Palau', 'PW'),
(163, 'Panama', 'PA'),
(164, 'Papua Nuova Guinea', 'PG'),
(165, 'Paraguay', 'PY'),
(166, 'Per&#249;', 'PE'),
(167, 'Polinesia Francese', 'PF'),
(168, 'Polonia', 'PL'),
(169, 'Ponape (Stati Federali Micronesia)', 'FM3'),
(170, 'Portogallo', 'PT'),
(171, 'Puerto Rico (Portorico)', 'PR'),
(172, 'Qatar', 'QA'),
(173, 'Regno Unito - Galles', 'GBW'),
(174, 'Regno Unito - Inghilterra', 'GBE'),
(175, 'Regno Unito - Irlanda del Nord', 'GBI'),
(176, 'Regno Unito - Scozia', 'GBS'),
(177, 'R&#233;union', 'RE'),
(178, 'Romania', 'RO'),
(179, 'Rota (Isole Marianne del Nord)', 'MP2'),
(180, 'Ruanda', 'RW'),
(181, 'Russia', 'RU'),
(182, 'Saba (Antille Olandesi)', 'AN4'),
(183, 'Saipan (Isole Marianne del Nord)', 'MP1'),
(184, 'Samoa Americane', 'AS'),
(185, 'Samoa Occidentale', 'AS1'),
(186, 'Senegal', 'SN'),
(187, 'Seychelles', 'SC'),
(188, 'Sierra Leone', 'SL'),
(189, 'Singapore', 'SG'),
(190, 'Siria', 'SY'),
(191, 'Slovacca, Repubblica', 'SK'),
(192, 'Slovenia', 'SI'),
(193, 'Spagna', 'ES'),
(194, 'Sri Lanka', 'LK'),
(195, 'St. Barth&#233;lemy', 'XSB'),
(196, 'St. Christopher (St. Kitts-Nevis)', 'KN2'),
(197, 'St. Croix (Isole Vergini Statunitensi)', 'VI1'),
(198, 'St. Eustatius (Antille Olandesi)', 'AN5'),
(199, 'St. John (Isole Vergini Statunitensi)', 'VI2'),
(200, 'St. Kitts (St. Kitts-Nevis)', 'KN3'),
(201, 'St. Lucia', 'LC'),
(202, 'St. Maarten (Antille Olandesi)', 'AN6'),
(203, 'St. Martin (Guadalupa)', 'GP1'),
(204, 'St. Thomas (Isole Vergini Statunitensi)', 'VI3'),
(205, 'St. Vincent e le Grenadine', 'VC1'),
(206, 'Sud Africa', 'ZA'),
(207, 'Suriname', 'SR'),
(208, 'Svezia', 'SE'),
(209, 'Svizzera', 'CH'),
(210, 'Swaziland', 'SZ'),
(211, 'Tahiti', 'HT1'),
(212, 'Tailandia', 'TH'),
(213, 'Taiwan', 'TW'),
(214, 'Tajikistan', 'TJ'),
(215, 'Tanzania', 'TZ'),
(216, 'Tinian (Isole Marianne del Nord)', 'MP3'),
(217, 'Togo', 'TG'),
(218, 'Tonga', 'TO'),
(219, 'Tortola (Isole Vergini Britanniche)', 'VG1'),
(220, 'Trinidad e Tobago', 'TT'),
(221, 'Truk (Stati Federali Micronesia)', 'FM4'),
(222, 'Tunisia', 'TN'),
(223, 'Turchia', 'TR'),
(224, 'Turkmenistan', 'TM'),
(225, 'Tuvalu', 'TV'),
(226, 'Ucraina', 'UA'),
(227, 'Uganda', 'UG'),
(228, 'Ungheria', 'HU'),
(229, 'Uruguay', 'UY'),
(230, 'USA - Alabama', 'USAL'),
(231, 'USA - Alaska', 'USAK'),
(232, 'USA - altri stati', 'US'),
(233, 'USA - Arizona', 'USAZ'),
(234, 'USA - Arkansas', 'USAR'),
(235, 'USA - California', 'USCA'),
(236, 'USA - Colorado', 'USCO'),
(237, 'USA - Conneticut', 'USCT'),
(238, 'USA - Delaware', 'USDE'),
(239, 'USA - Dist. of Columbia', 'USDC'),
(240, 'USA - Florida', 'USFL'),
(241, 'USA - Georgia', 'USGA'),
(242, 'USA - Hawaii', 'USHI'),
(243, 'USA - Idaho', 'USID'),
(244, 'USA - Illinois', 'USIL'),
(245, 'USA - Indiana', 'USIN'),
(246, 'USA - Iowa', 'USIA'),
(247, 'USA - Kansas', 'USKS'),
(248, 'USA - Kentucky', 'USKY'),
(249, 'USA - Louisiana', 'USLA'),
(250, 'USA - Maine', 'USME'),
(251, 'USA - Maryland', 'USMD'),
(252, 'USA - Massachusetts', 'USMA'),
(253, 'USA - Michigan', 'USMI'),
(254, 'USA - Minnesota', 'USMN'),
(255, 'USA - Mississippi', 'USMS'),
(256, 'USA - Missouri', 'USMO'),
(257, 'USA - Montana', 'USMT'),
(258, 'USA - Nebraska', 'USNE'),
(259, 'USA - Nevada', 'USNV'),
(260, 'USA - New Hampshire', 'USNH'),
(261, 'USA - New Jersey', 'USNJ'),
(262, 'USA - New Mexico', 'USNM'),
(263, 'USA - New York', 'USNY'),
(264, 'USA - North Carolina', 'USNC'),
(265, 'USA - North Dakota', 'USND'),
(266, 'USA - Ohio', 'USOH'),
(267, 'USA - Oklahoma', 'USOK'),
(268, 'USA - Oregon', 'USOR'),
(269, 'USA - Pennsylvania', 'USPA'),
(270, 'USA - Rhode Island', 'USRI'),
(271, 'USA - South Carolina', 'USSC'),
(272, 'USA - South Dakota', 'USSD'),
(273, 'USA - Tennessee', 'USTN'),
(274, 'USA - Texas', 'USTX'),
(275, 'USA - Utah', 'USUT'),
(276, 'USA - Vermont', 'USVT'),
(277, 'USA - Virginia', 'USVA'),
(278, 'USA - Washington', 'USWA'),
(279, 'USA - West Virginia', 'USWV'),
(280, 'USA - Wisconsin', 'USWI'),
(281, 'USA - Wyoming', 'USWY'),
(282, 'Uzbekistan', 'UZ'),
(283, 'Vanuatu', 'VU'),
(284, 'Venezuela', 'VE'),
(285, 'Vietnam', 'VN'),
(286, 'Virgin Gorda (Isole Vergini Britanniche)', 'VG2'),
(287, 'Yap (Stati Federali Micronesia)', 'FM5'),
(288, 'Yemen, Repubblica dello', 'YE'),
(289, 'Zambia', 'ZM'),
(290, 'Zimbabwe', 'ZW');



CREATE TABLE IF NOT EXISTS `cm_mod_security_token` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ID_user` int(11) NOT NULL,
  `ID_domain` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `type` (`type`, `ID_user`),
  KEY `ID_user` (`ID_user`),
  KEY `ID_domain` (`ID_domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
