-- --------------------------------------------------------

--
-- Table structure for table `cm_mod_restricted_settings`
--

CREATE TABLE IF NOT EXISTS `cm_mod_restricted_settings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `descrizione` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
