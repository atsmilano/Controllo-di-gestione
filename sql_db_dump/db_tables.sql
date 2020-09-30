SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for allegato
-- ----------------------------
DROP TABLE IF EXISTS `allegato`;
CREATE TABLE `allegato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `filename_md5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `filename_plain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `content_lenght` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `createdAt` datetime(0) NULL DEFAULT NULL,
  `updatedAt` datetime(0) NULL DEFAULT NULL,
  `deletedAt` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for anagrafica_cdc
-- ----------------------------
DROP TABLE IF EXISTS `anagrafica_cdc`;
CREATE TABLE `anagrafica_cdc`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `abbreviazione` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `data_introduzione` date NULL DEFAULT NULL,
  `data_termine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for anagrafica_cdr
-- ----------------------------
DROP TABLE IF EXISTS `anagrafica_cdr`;
CREATE TABLE `anagrafica_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `abbreviazione` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ID_tipo_cdr` int(11) NOT NULL,
  `data_introduzione` date NULL DEFAULT NULL,
  `data_termine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for anno_budget
-- ----------------------------
DROP TABLE IF EXISTS `anno_budget`;
CREATE TABLE `anno_budget`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attivo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_UNIQUE`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for carriera
-- ----------------------------
DROP TABLE IF EXISTS `carriera`;
CREATE TABLE `carriera`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_personale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_tipo_contratto` int(11) NOT NULL,
  `ID_qualifica_interna` int(11) NOT NULL,
  `ID_rapporto_lavoro` int(11) NOT NULL,
  `perc_rapporto_lavoro` int(11) NOT NULL,
  `posizione_organizzativa` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cdc
-- ----------------------------
DROP TABLE IF EXISTS `cdc`;
CREATE TABLE `cdc`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anagrafica_cdc` int(11) NOT NULL,
  `ID_cdr` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_UNIQUE`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cdc_personale
-- ----------------------------
DROP TABLE IF EXISTS `cdc_personale`;
CREATE TABLE `cdc_personale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_personale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codice_cdc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentuale` int(11) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `cod_cdc`(`codice_cdc`(191)) USING BTREE,
  INDEX `matricola`(`matricola_personale`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cdr
-- ----------------------------
DROP TABLE IF EXISTS `cdr`;
CREATE TABLE `cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anagrafica_cdr` int(11) NULL DEFAULT NULL,
  `ID_piano_cdr` int(11) NOT NULL,
  `ID_padre` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_UNIQUE`(`ID`) USING BTREE,
  INDEX `fk_cdr_cdr`(`ID_padre`) USING BTREE,
  INDEX `fk_cdr_distretto1`(`ID_piano_cdr`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_charset_decode
-- ----------------------------
DROP TABLE IF EXISTS `cm_charset_decode`;
CREATE TABLE `cm_charset_decode`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_gs
-- ----------------------------
DROP TABLE IF EXISTS `cm_gs`;
CREATE TABLE `cm_gs`  (
  `ID` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `optin` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `is_default` int(1) NOT NULL,
  `from_email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `reply_to_email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `created_on` datetime(0) NOT NULL,
  `from_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_layout
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout`;
CREATE TABLE `cm_layout`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `layer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `main_theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_body` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enable_cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ignore_defaults` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exclude_ff_js` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exclude_form` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enable_gzip` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `compact_js` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `compact_css` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ignore_defaults_main` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_css` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_js` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `domains` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cm_layout
-- ----------------------------
INSERT INTO `cm_layout` VALUES (1, '/area_riservata', '', '', 'responsive', 'ats', '', '', '1', '0', '0', '', '', '', '', '', '0', '', '', '');

-- ----------------------------
-- Table structure for cm_layout_cdn
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout_cdn`;
CREATE TABLE `cm_layout_cdn`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_layout` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_layout_css
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout_css`;
CREATE TABLE `cm_layout_css`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_layout` int(11) NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `index` int(11) NOT NULL,
  `exclude_compact` int(1) NOT NULL,
  `visible` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme_include` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `priority`(`priority`) USING BTREE,
  INDEX `order`(`order`) USING BTREE,
  INDEX `index`(`index`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_layout_js
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout_js`;
CREATE TABLE `cm_layout_js`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_layout` int(11) NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `plugin_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `js_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `index` int(11) NOT NULL,
  `exclude_compact` int(1) NOT NULL,
  `theme_exclude` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme_include` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `visible` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `priority`(`priority`) USING BTREE,
  INDEX `order`(`order`) USING BTREE,
  INDEX `index`(`index`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_layout_meta
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout_meta`;
CREATE TABLE `cm_layout_meta`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_layout` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_layout`(`ID_layout`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_layout_sect
-- ----------------------------
DROP TABLE IF EXISTS `cm_layout_sect`;
CREATE TABLE `cm_layout_sect`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_layout` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme_include` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cascading` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_restricted_settings
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_restricted_settings`;
CREATE TABLE `cm_mod_restricted_settings`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_domains
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_domains`;
CREATE TABLE `cm_mod_security_domains`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `creation_date` datetime(0) NOT NULL,
  `expiration_date` date NOT NULL,
  `time_zone` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `billing_status` int(11) NOT NULL,
  `db_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_packages` int(11) NOT NULL,
  `max_users` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_packages`(`ID_packages`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_domains_fields
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_domains_fields`;
CREATE TABLE `cm_mod_security_domains_fields`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL,
  `field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_domains`(`ID_domains`) USING BTREE,
  INDEX `ID_domains_2`(`ID_domains`, `field`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_packages
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_packages`;
CREATE TABLE `cm_mod_security_packages`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_packages_fields
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_packages_fields`;
CREATE TABLE `cm_mod_security_packages_fields`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_packages` int(11) NOT NULL,
  `field` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `value` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `unlimited` char(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_packages_2`(`ID_packages`, `field`) USING BTREE,
  INDEX `ID_packages`(`ID_packages`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_profiles
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_profiles`;
CREATE TABLE `cm_mod_security_profiles`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_time` datetime(0) NOT NULL,
  `created_user` int(11) NOT NULL,
  `modified_time` datetime(0) NOT NULL,
  `modified_user` int(11) NOT NULL,
  `enabled` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `special` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `acl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_domains` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_profiles_pairs
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_profiles_pairs`;
CREATE TABLE `cm_mod_security_profiles_pairs`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_profile` int(11) NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_own` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_others` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `modify_own` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `modify_others` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `insert_own` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `insert_others` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delete_own` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delete_others` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_profile`(`ID_profile`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_rel_profiles_users
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_rel_profiles_users`;
CREATE TABLE `cm_mod_security_rel_profiles_users`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_user` int(11) NOT NULL,
  `ID_profile` int(11) NOT NULL,
  `enabled` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_timezones
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_timezones`;
CREATE TABLE `cm_mod_security_timezones`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cm_mod_security_timezones
-- ----------------------------
INSERT INTO `cm_mod_security_timezones` VALUES (1, 'GMT-14');
INSERT INTO `cm_mod_security_timezones` VALUES (2, 'GMT-13');
INSERT INTO `cm_mod_security_timezones` VALUES (3, 'GMT-12');
INSERT INTO `cm_mod_security_timezones` VALUES (4, 'GMT-11');
INSERT INTO `cm_mod_security_timezones` VALUES (5, 'GMT-10');
INSERT INTO `cm_mod_security_timezones` VALUES (6, 'GMT-9');
INSERT INTO `cm_mod_security_timezones` VALUES (7, 'GMT-8');
INSERT INTO `cm_mod_security_timezones` VALUES (8, 'GMT-7');
INSERT INTO `cm_mod_security_timezones` VALUES (9, 'GMT-6');
INSERT INTO `cm_mod_security_timezones` VALUES (10, 'GMT-5');
INSERT INTO `cm_mod_security_timezones` VALUES (11, 'GMT-4');
INSERT INTO `cm_mod_security_timezones` VALUES (12, 'GMT-3');
INSERT INTO `cm_mod_security_timezones` VALUES (13, 'GMT-2');
INSERT INTO `cm_mod_security_timezones` VALUES (14, 'GMT-1');
INSERT INTO `cm_mod_security_timezones` VALUES (15, 'GMT+0');
INSERT INTO `cm_mod_security_timezones` VALUES (16, 'GMT+1');
INSERT INTO `cm_mod_security_timezones` VALUES (17, 'GMT+2');
INSERT INTO `cm_mod_security_timezones` VALUES (18, 'GMT+3');
INSERT INTO `cm_mod_security_timezones` VALUES (19, 'GMT+4');
INSERT INTO `cm_mod_security_timezones` VALUES (20, 'GMT+5');
INSERT INTO `cm_mod_security_timezones` VALUES (21, 'GMT+6');
INSERT INTO `cm_mod_security_timezones` VALUES (22, 'GMT+7');
INSERT INTO `cm_mod_security_timezones` VALUES (23, 'GMT+8');
INSERT INTO `cm_mod_security_timezones` VALUES (24, 'GMT+9');
INSERT INTO `cm_mod_security_timezones` VALUES (25, 'GMT+10');
INSERT INTO `cm_mod_security_timezones` VALUES (26, 'GMT+11');
INSERT INTO `cm_mod_security_timezones` VALUES (27, 'GMT+12');

-- ----------------------------
-- Table structure for cm_mod_security_token
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_token`;
CREATE TABLE `cm_mod_security_token`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ID_user` int(11) NOT NULL,
  `ID_domain` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `type`(`type`, `ID_user`) USING BTREE,
  INDEX `ID_user`(`ID_user`) USING BTREE,
  INDEX `ID_domain`(`ID_domain`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_mod_security_users
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_users`;
CREATE TABLE `cm_mod_security_users`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_domains` int(11) NOT NULL DEFAULT 0,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `level` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` datetime(0) NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_zone` int(11) NOT NULL DEFAULT 0,
  `role` int(11) NOT NULL DEFAULT 0,
  `created` datetime(0) NOT NULL,
  `modified` datetime(0) NOT NULL,
  `password_generated_at` datetime(0) NOT NULL,
  `temp_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password_used` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ID_packages` int(11) NOT NULL DEFAULT 0,
  `lastlogin` datetime(0) NOT NULL,
  `profile` int(11) NOT NULL DEFAULT 0,
  `special` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_packages`(`ID_packages`) USING BTREE,
  INDEX `profile`(`profile`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cm_mod_security_users
-- ----------------------------
INSERT INTO `cm_mod_security_users` VALUES (1, 0, 'admin', '', '3', '1', '0000-00-00 00:00:00', '', 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', 0, '0000-00-00 00:00:00', 0, '', '', NULL, NULL);

-- ----------------------------
-- Table structure for cm_mod_security_users_fields
-- ----------------------------
DROP TABLE IF EXISTS `cm_mod_security_users_fields`;
CREATE TABLE `cm_mod_security_users_fields`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_users` int(11) NOT NULL,
  `field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_users`(`ID_users`) USING BTREE,
  INDEX `ID_users_2`(`ID_users`, `field`(191)) USING BTREE,
  INDEX `field`(`field`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_showfiles
-- ----------------------------
DROP TABLE IF EXISTS `cm_showfiles`;
CREATE TABLE `cm_showfiles`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_temp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_full` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_showfiles_modes
-- ----------------------------
DROP TABLE IF EXISTS `cm_showfiles_modes`;
CREATE TABLE `cm_showfiles_modes`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_showfiles` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shortdesc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dim_x` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dim_y` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_x` int(11) NOT NULL,
  `max_y` int(11) NOT NULL,
  `bgcolor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `when` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alignment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alpha` int(11) NOT NULL,
  `format` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wmk_enable` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wmk_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wmk_alignment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `format_jpg_quality` int(3) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cm_showfiles_where
-- ----------------------------
DROP TABLE IF EXISTS `cm_showfiles_where`;
CREATE TABLE `cm_showfiles_where`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_showfiles` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dbskip` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cms_home_sezione
-- ----------------------------
DROP TABLE IF EXISTS `cms_home_sezione`;
CREATE TABLE `cms_home_sezione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ordinamento` int(11) NULL DEFAULT NULL,
  `tipo` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `testo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `anno_inizio` int(11) NULL DEFAULT NULL,
  `anno_fine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cms_home_sezione_allegato
-- ----------------------------
DROP TABLE IF EXISTS `cms_home_sezione_allegato`;
CREATE TABLE `cms_home_sezione_allegato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_allegato` int(11) NULL DEFAULT NULL,
  `ID_sezione` int(11) NULL DEFAULT NULL,
  `createdAt` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_cdc
-- ----------------------------
DROP TABLE IF EXISTS `coan_cdc`;
CREATE TABLE `coan_cdc`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_cdc_standard_regionale` int(11) NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_distretto` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_cdc_standard_regionale
-- ----------------------------
DROP TABLE IF EXISTS `coan_cdc_standard_regionale`;
CREATE TABLE `coan_cdc_standard_regionale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_consuntivo_periodo
-- ----------------------------
DROP TABLE IF EXISTS `coan_consuntivo_periodo`;
CREATE TABLE `coan_consuntivo_periodo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_conto` int(11) NULL DEFAULT NULL,
  `ID_cdc_coan` int(11) NULL DEFAULT NULL,
  `ID_periodo_coan` int(11) NULL DEFAULT NULL,
  `budget` float NULL DEFAULT NULL,
  `consuntivo` float NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_conto
-- ----------------------------
DROP TABLE IF EXISTS `coan_conto`;
CREATE TABLE `coan_conto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_fp_quarto` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_distretto
-- ----------------------------
DROP TABLE IF EXISTS `coan_distretto`;
CREATE TABLE `coan_distretto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_fp_primo
-- ----------------------------
DROP TABLE IF EXISTS `coan_fp_primo`;
CREATE TABLE `coan_fp_primo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_fp_quarto
-- ----------------------------
DROP TABLE IF EXISTS `coan_fp_quarto`;
CREATE TABLE `coan_fp_quarto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_fp_terzo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_fp_secondo
-- ----------------------------
DROP TABLE IF EXISTS `coan_fp_secondo`;
CREATE TABLE `coan_fp_secondo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_fp_primo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_fp_terzo
-- ----------------------------
DROP TABLE IF EXISTS `coan_fp_terzo`;
CREATE TABLE `coan_fp_terzo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_fp_secondo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for coan_periodo
-- ----------------------------
DROP TABLE IF EXISTS `coan_periodo`;
CREATE TABLE `coan_periodo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `ordinamento_anno` int(11) NULL DEFAULT NULL,
  `data_apertura` date NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for costi_ricavi_conto
-- ----------------------------
DROP TABLE IF EXISTS `costi_ricavi_conto`;
CREATE TABLE `costi_ricavi_conto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_fp` int(11) NOT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `evidenza` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for costi_ricavi_fp
-- ----------------------------
DROP TABLE IF EXISTS `costi_ricavi_fp`;
CREATE TABLE `costi_ricavi_fp`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for costi_ricavi_importo_periodo
-- ----------------------------
DROP TABLE IF EXISTS `costi_ricavi_importo_periodo`;
CREATE TABLE `costi_ricavi_importo_periodo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo` int(11) NOT NULL,
  `ID_conto` int(11) NOT NULL,
  `campo_1` int(11) NULL DEFAULT NULL,
  `campo_2` int(11) NULL DEFAULT NULL,
  `campo_3` int(11) NULL DEFAULT NULL,
  `campo_4` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for costi_ricavi_periodo
-- ----------------------------
DROP TABLE IF EXISTS `costi_ricavi_periodo`;
CREATE TABLE `costi_ricavi_periodo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anno_budget` int(11) NOT NULL,
  `data_riferimento_inizio` date NOT NULL,
  `data_riferimento_fine` date NOT NULL,
  `data_scadenza` date NOT NULL,
  `ordinamento_anno` int(11) NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_tipo_periodo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for costi_ricavi_valutazione_fp_cdr
-- ----------------------------
DROP TABLE IF EXISTS `costi_ricavi_valutazione_fp_cdr`;
CREATE TABLE `costi_ricavi_valutazione_fp_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo` int(11) NOT NULL,
  `ID_fp` int(11) NOT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `campo_1` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `campo_2` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `campo_3` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for delega_accesso
-- ----------------------------
DROP TABLE IF EXISTS `delega_accesso`;
CREATE TABLE `delega_accesso`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_delegato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricola_utente` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `matricola_delegato`(`matricola_delegato`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for delega_accesso_modulo
-- ----------------------------
DROP TABLE IF EXISTS `delega_accesso_modulo`;
CREATE TABLE `delega_accesso_modulo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_delega_accesso` int(11) NOT NULL,
  `ID_modulo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `delega_accesso`(`ID_delega_accesso`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ff_international
-- ----------------------------
DROP TABLE IF EXISTS `ff_international`;
CREATE TABLE `ff_international`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_lang` int(11) NOT NULL DEFAULT 0,
  `word_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_new` int(1) NOT NULL DEFAULT 0,
  `last_update` datetime(0) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID_lang`(`ID_lang`, `word_code`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 98 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ff_international
-- ----------------------------
INSERT INTO `ff_international` VALUES (1, 1, 'login_username', 'nome utente', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (2, 1, 'login_password', 'password', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (3, 1, 'login_confirm', 'accedi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (4, 1, 'login_text_logout', 'Logout', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (5, 1, 'nav_profile', 'Profilo', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (6, 1, 'logout_title', 'Sei sicuro di voler effettuare il Logout?', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (7, 1, 'logout_confirm', 'Si', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (8, 1, 'logout_cancel', 'No', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (9, 1, 'user_username', 'Username', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (10, 1, 'user_password', 'Password', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (11, 1, 'user_confirmpass', 'Conferma Password', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (12, 1, 'user_email', 'Indirizzo E-Mail', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (13, 1, 'user_nome', 'Nome', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (14, 1, 'user_cognome', 'Cognome', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (15, 1, 'user_indirizzo', 'Indirizzo', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (16, 1, 'user_citta', 'Città', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (17, 1, 'user_provincia', 'Provincia', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (18, 1, 'user_cap', 'CAP', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (19, 1, 'user_paese', 'Paese', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (20, 1, 'user_telefono', 'Telefono', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (21, 1, 'user_datanascita', 'Data di Nascita (gg/mm/aaaa)', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (22, 1, 'user_sesso', 'Sesso', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (23, 1, 'user_provincianascita', 'Provincia di Nascita', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (24, 1, 'user_cittanascita', 'Nato a', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (25, 1, 'user_codicefiscale', 'Codice Fiscale', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (26, 1, 'user_sessomaschio', 'Maschio', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (27, 1, 'user_sessofemmina', 'Femmina', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (28, 1, 'user_piva', 'Partita IVA', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (29, 1, 'user_publish', 'Pubblica in Home Page', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (30, 1, 'back_to_site', 'Torna al sito', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (31, 1, 'developer_label', 'Sviluppato da ', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (32, 1, 'developer_url', 'http://www.ats-milano.it', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (33, 1, 'developer_name', 'Controllo di Gestione - ATS Città Metropolitana Milano', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (34, 1, 'ffGrid_addnew', 'Aggiungi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (35, 1, 'articles', 'Pagine', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (36, 1, 'articles_sections', 'Sezioni', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (37, 1, 'catalog_items', 'Prodotti', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (38, 1, 'no_limit_items_begin', 'Puoi inserire ancora', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (39, 1, 'no_limit_items_end', 'prodotti su ', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (40, 1, 'catalog_categories', 'Categorie', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (41, 1, 'catalog_groups', 'Gruppi Campi Aggiuntivi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (42, 1, 'catalog_fields', 'Campi Aggiuntivi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (43, 1, 'article', 'Pagina', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (44, 1, 'article_detail', 'Contenuto', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (45, 1, 'grid_no_record', 'Nessun elemento disponibile', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (46, 1, 'article_title', 'Titolo', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (47, 1, 'article_slug', 'Slug', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (48, 1, 'article_short_desc', 'Descrizione', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (49, 1, 'article_video', 'Video', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (50, 1, 'article_img', 'Immagine', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (51, 1, 'ffRecord_update', 'Aggiorna', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (52, 1, 'ffRecord_delete', 'Elimina', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (53, 1, 'ffRecord_cancel', 'Indietro', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (54, 1, 'ffRecord_close', 'Chiudi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (55, 1, 'articolo', 'Prodotto', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (56, 1, 'ffDetail_addrow', 'Aggiungi', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (57, 1, 'articolo_modify', 'Modifica prodotto', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (58, 1, 'articolo_insert', 'Inserimento prodotto', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (59, 1, 'item_detail', 'Descrizione prodotto', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (60, 1, 'right', 'Destra', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (61, 1, 'left', 'Sinistra', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (62, 1, 'top', 'Evidenza', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (63, 1, 'login_title', 'Log In', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (64, 1, 'recover_password', 'Hai dimenticato la Password?', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (65, 1, 'register_link', 'Registrati', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (66, 1, 'ffRecord_insert', 'Inserisci', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (67, 1, 'video', 'Video', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (68, 1, 'pdf', 'PDF', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (69, 1, 'gallery', 'Galleria Fotografica', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (70, 1, 'yes', 'Si', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (71, 1, 'no', 'No', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (72, 1, 'logout', 'Esci', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (73, 1, 'edit_profile', 'Modifica profilo', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (74, 1, 'frame_per_page', 'Righe per pagina', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (75, 1, 'notify_add_cat', 'Hai aggiunto:', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (76, 1, 'category_name', 'Sezione', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (77, 1, 'category_slug', 'Slug', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (78, 1, 'ldap_login_wrong_user_or_password', 'Nome utente e/o password errati', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (79, 1, 'ldap_user_not_qualified', 'Utente senza autorizzazione', 0, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (80, 1, 'cm::LAYOUT_PRIORITY_TOPLEVEL', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (81, 1, 'cm::LAYOUT_PRIORITY_HIGH', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (82, 1, 'cm::LAYOUT_PRIORITY_NORMAL', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (83, 1, 'cm::LAYOUT_PRIORITY_LOW', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (84, 1, 'cm::LAYOUT_PRIORITY_FINAL', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (85, 1, 'total', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (86, 1, 'require_note', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (87, 1, 'close', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (88, 1, 'account', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (89, 1, 'dialog_title_accessdenied', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (90, 1, 'dialog_accessdenied', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (91, 1, 'datepicker_choose', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (92, 1, 'datepicker_time', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (93, 1, 'datepicker_hour', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (94, 1, 'datepicker_minute', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (95, 1, 'datepicker_second', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (96, 1, 'datepicker_current', '', 1, '0000-00-00 00:00:00');
INSERT INTO `ff_international` VALUES (97, 1, 'datepicker_close', '', 1, '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for ff_languages
-- ----------------------------
DROP TABLE IF EXISTS `ff_languages`;
CREATE TABLE `ff_languages`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `tiny_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `iso6391` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 98 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ff_languages
-- ----------------------------
INSERT INTO `ff_languages` VALUES (1, 'ITA', 'Italiano', 1, '', 'it');
INSERT INTO `ff_languages` VALUES (2, '', '', 0, '', 'ab');
INSERT INTO `ff_languages` VALUES (3, '', '', 0, '', 'af');
INSERT INTO `ff_languages` VALUES (4, '', '', 0, '', 'an');
INSERT INTO `ff_languages` VALUES (5, '', '', 0, '', 'ar');
INSERT INTO `ff_languages` VALUES (6, '', '', 0, '', 'as');
INSERT INTO `ff_languages` VALUES (7, '', '', 0, '', 'az');
INSERT INTO `ff_languages` VALUES (8, '', '', 0, '', 'be');
INSERT INTO `ff_languages` VALUES (9, '', '', 0, '', 'bg');
INSERT INTO `ff_languages` VALUES (10, '', '', 0, '', 'bn');
INSERT INTO `ff_languages` VALUES (11, '', '', 0, '', 'bo');
INSERT INTO `ff_languages` VALUES (12, '', '', 0, '', 'br');
INSERT INTO `ff_languages` VALUES (13, '', '', 0, '', 'bs');
INSERT INTO `ff_languages` VALUES (14, '', '', 0, '', 'ca');
INSERT INTO `ff_languages` VALUES (15, '', '', 0, '', 'ce');
INSERT INTO `ff_languages` VALUES (16, '', '', 0, '', 'co');
INSERT INTO `ff_languages` VALUES (17, '', '', 0, '', 'cs');
INSERT INTO `ff_languages` VALUES (18, '', '', 0, '', 'cu');
INSERT INTO `ff_languages` VALUES (19, '', '', 0, '', 'cy');
INSERT INTO `ff_languages` VALUES (20, '', '', 0, '', 'da');
INSERT INTO `ff_languages` VALUES (21, '', '', 0, '', 'de');
INSERT INTO `ff_languages` VALUES (22, '', '', 0, '', 'el');
INSERT INTO `ff_languages` VALUES (23, 'ENG', 'Inglese', 0, 'en', 'en');
INSERT INTO `ff_languages` VALUES (24, '', '', 0, '', 'eo');
INSERT INTO `ff_languages` VALUES (25, '', '', 0, '', 'es');
INSERT INTO `ff_languages` VALUES (26, '', '', 0, '', 'et');
INSERT INTO `ff_languages` VALUES (27, '', '', 0, '', 'eu');
INSERT INTO `ff_languages` VALUES (28, '', '', 0, '', 'fa');
INSERT INTO `ff_languages` VALUES (29, '', '', 0, '', 'fi');
INSERT INTO `ff_languages` VALUES (30, '', '', 0, '', 'fj');
INSERT INTO `ff_languages` VALUES (31, '', '', 0, '', 'fo');
INSERT INTO `ff_languages` VALUES (32, '', '', 0, '', 'fr');
INSERT INTO `ff_languages` VALUES (33, '', '', 0, '', 'fy');
INSERT INTO `ff_languages` VALUES (34, '', '', 0, '', 'ga');
INSERT INTO `ff_languages` VALUES (35, '', '', 0, '', 'gd');
INSERT INTO `ff_languages` VALUES (36, '', '', 0, '', 'gl');
INSERT INTO `ff_languages` VALUES (37, '', '', 0, '', 'gv');
INSERT INTO `ff_languages` VALUES (38, '', '', 0, '', 'he');
INSERT INTO `ff_languages` VALUES (39, '', '', 0, '', 'hi');
INSERT INTO `ff_languages` VALUES (40, '', '', 0, '', 'hr');
INSERT INTO `ff_languages` VALUES (41, '', '', 0, '', 'ht');
INSERT INTO `ff_languages` VALUES (42, '', '', 0, '', 'hu');
INSERT INTO `ff_languages` VALUES (43, '', '', 0, '', 'hy');
INSERT INTO `ff_languages` VALUES (44, '', '', 0, '', 'id');
INSERT INTO `ff_languages` VALUES (45, '', '', 0, '', 'is');
INSERT INTO `ff_languages` VALUES (47, '', '', 0, '', 'ja');
INSERT INTO `ff_languages` VALUES (48, '', '', 0, '', 'jv');
INSERT INTO `ff_languages` VALUES (49, '', '', 0, '', 'ka');
INSERT INTO `ff_languages` VALUES (50, '', '', 0, '', 'kg');
INSERT INTO `ff_languages` VALUES (51, '', '', 0, '', 'ko');
INSERT INTO `ff_languages` VALUES (52, '', '', 0, '', 'ku');
INSERT INTO `ff_languages` VALUES (53, '', '', 0, '', 'kw');
INSERT INTO `ff_languages` VALUES (54, '', '', 0, '', 'ky');
INSERT INTO `ff_languages` VALUES (55, '', '', 0, '', 'la');
INSERT INTO `ff_languages` VALUES (56, '', '', 0, '', 'lb');
INSERT INTO `ff_languages` VALUES (57, '', '', 0, '', 'li');
INSERT INTO `ff_languages` VALUES (58, '', '', 0, '', 'ln');
INSERT INTO `ff_languages` VALUES (59, '', '', 0, '', 'lt');
INSERT INTO `ff_languages` VALUES (60, '', '', 0, '', 'lv');
INSERT INTO `ff_languages` VALUES (61, '', '', 0, '', 'mg');
INSERT INTO `ff_languages` VALUES (62, '', '', 0, '', 'mk');
INSERT INTO `ff_languages` VALUES (63, '', '', 0, '', 'mn');
INSERT INTO `ff_languages` VALUES (64, '', '', 0, '', 'mo');
INSERT INTO `ff_languages` VALUES (65, '', '', 0, '', 'ms');
INSERT INTO `ff_languages` VALUES (66, '', '', 0, '', 'mt');
INSERT INTO `ff_languages` VALUES (67, '', '', 0, '', 'my');
INSERT INTO `ff_languages` VALUES (68, '', '', 0, '', 'nb');
INSERT INTO `ff_languages` VALUES (69, '', '', 0, '', 'ne');
INSERT INTO `ff_languages` VALUES (70, '', '', 0, '', 'nl');
INSERT INTO `ff_languages` VALUES (71, '', '', 0, '', 'nn');
INSERT INTO `ff_languages` VALUES (72, '', '', 0, '', 'no');
INSERT INTO `ff_languages` VALUES (73, '', '', 0, '', 'oc');
INSERT INTO `ff_languages` VALUES (74, '', '', 0, '', 'pl');
INSERT INTO `ff_languages` VALUES (75, '', '', 0, '', 'pt');
INSERT INTO `ff_languages` VALUES (76, '', '', 0, '', 'rm');
INSERT INTO `ff_languages` VALUES (77, '', '', 0, '', 'ro');
INSERT INTO `ff_languages` VALUES (78, '', '', 0, '', 'ru');
INSERT INTO `ff_languages` VALUES (79, '', '', 0, '', 'sc');
INSERT INTO `ff_languages` VALUES (80, '', '', 0, '', 'se');
INSERT INTO `ff_languages` VALUES (81, '', '', 0, '', 'sk');
INSERT INTO `ff_languages` VALUES (82, '', '', 0, '', 'sl');
INSERT INTO `ff_languages` VALUES (83, '', '', 0, '', 'so');
INSERT INTO `ff_languages` VALUES (84, '', '', 0, '', 'sq');
INSERT INTO `ff_languages` VALUES (85, '', '', 0, '', 'sr');
INSERT INTO `ff_languages` VALUES (86, '', '', 0, '', 'sv');
INSERT INTO `ff_languages` VALUES (87, '', '', 0, '', 'sw');
INSERT INTO `ff_languages` VALUES (88, '', '', 0, '', 'tk');
INSERT INTO `ff_languages` VALUES (89, 'TUR', 'Türkçe', 1, 'tr', 'tr');
INSERT INTO `ff_languages` VALUES (90, '', '', 0, '', 'ty');
INSERT INTO `ff_languages` VALUES (91, '', '', 0, '', 'uk');
INSERT INTO `ff_languages` VALUES (92, '', '', 0, '', 'ur');
INSERT INTO `ff_languages` VALUES (93, '', '', 0, '', 'uz');
INSERT INTO `ff_languages` VALUES (94, '', '', 0, '', 'vi');
INSERT INTO `ff_languages` VALUES (95, '', '', 0, '', 'vo');
INSERT INTO `ff_languages` VALUES (96, '', '', 0, '', 'yi');
INSERT INTO `ff_languages` VALUES (97, '', '', 0, '', 'zh');

-- ----------------------------
-- Table structure for ff_languages_names
-- ----------------------------
DROP TABLE IF EXISTS `ff_languages_names`;
CREATE TABLE `ff_languages_names`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_lang` int(11) NOT NULL,
  `ID_trans` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2497 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ff_languages_names
-- ----------------------------
INSERT INTO `ff_languages_names` VALUES (1, 2, 9, 'абхазки');
INSERT INTO `ff_languages_names` VALUES (2, 2, 17, 'abchazština');
INSERT INTO `ff_languages_names` VALUES (3, 2, 20, 'Abkhazian');
INSERT INTO `ff_languages_names` VALUES (4, 2, 21, 'Abchasisch');
INSERT INTO `ff_languages_names` VALUES (5, 2, 22, 'αμπχαζικά');
INSERT INTO `ff_languages_names` VALUES (6, 2, 23, 'Abkhazian');
INSERT INTO `ff_languages_names` VALUES (7, 2, 25, 'abjazio');
INSERT INTO `ff_languages_names` VALUES (8, 2, 26, 'abhaasi');
INSERT INTO `ff_languages_names` VALUES (9, 2, 29, 'abhaasi');
INSERT INTO `ff_languages_names` VALUES (10, 2, 32, 'abkhaze');
INSERT INTO `ff_languages_names` VALUES (11, 2, 40, 'abhaski');
INSERT INTO `ff_languages_names` VALUES (12, 2, 42, 'Abház');
INSERT INTO `ff_languages_names` VALUES (13, 2, 45, 'Abkasíska');
INSERT INTO `ff_languages_names` VALUES (14, 2, 1, 'Abkhaziano');
INSERT INTO `ff_languages_names` VALUES (15, 2, 59, 'Abchazų');
INSERT INTO `ff_languages_names` VALUES (16, 2, 60, 'Abhāziešu');
INSERT INTO `ff_languages_names` VALUES (17, 2, 66, 'Abkażjan');
INSERT INTO `ff_languages_names` VALUES (18, 2, 70, 'Abchazisch');
INSERT INTO `ff_languages_names` VALUES (19, 2, 72, 'Abkhasisk');
INSERT INTO `ff_languages_names` VALUES (20, 2, 74, 'abchaski');
INSERT INTO `ff_languages_names` VALUES (21, 2, 75, 'Abcaze');
INSERT INTO `ff_languages_names` VALUES (22, 2, 77, 'Abhaziană');
INSERT INTO `ff_languages_names` VALUES (23, 2, 81, 'abcházčina');
INSERT INTO `ff_languages_names` VALUES (24, 2, 82, 'abhaščina');
INSERT INTO `ff_languages_names` VALUES (25, 2, 86, 'abkhaziska');
INSERT INTO `ff_languages_names` VALUES (26, 2, 89, 'Abazaca');
INSERT INTO `ff_languages_names` VALUES (27, 3, 9, 'африканс');
INSERT INTO `ff_languages_names` VALUES (28, 3, 17, 'afrikánština');
INSERT INTO `ff_languages_names` VALUES (29, 3, 20, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (30, 3, 21, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (31, 3, 22, 'αφρικάνς');
INSERT INTO `ff_languages_names` VALUES (32, 3, 23, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (33, 3, 25, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (34, 3, 26, 'afrikaani');
INSERT INTO `ff_languages_names` VALUES (35, 3, 29, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (36, 3, 32, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (37, 3, 40, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (38, 3, 42, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (39, 3, 45, 'Afríkanska');
INSERT INTO `ff_languages_names` VALUES (40, 3, 1, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (41, 3, 59, 'Afrikanų');
INSERT INTO `ff_languages_names` VALUES (42, 3, 60, 'Afrikānss');
INSERT INTO `ff_languages_names` VALUES (43, 3, 66, 'Afrikans');
INSERT INTO `ff_languages_names` VALUES (44, 3, 70, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (45, 3, 72, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (46, 3, 74, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (47, 3, 75, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (48, 3, 77, 'Afrikaans');
INSERT INTO `ff_languages_names` VALUES (49, 3, 81, 'afrikánčina');
INSERT INTO `ff_languages_names` VALUES (50, 3, 82, 'afrikanščina');
INSERT INTO `ff_languages_names` VALUES (51, 3, 86, 'afrikaans');
INSERT INTO `ff_languages_names` VALUES (52, 3, 89, 'Afrikaanca');
INSERT INTO `ff_languages_names` VALUES (53, 4, 9, 'арагонски');
INSERT INTO `ff_languages_names` VALUES (54, 4, 17, 'aragonština');
INSERT INTO `ff_languages_names` VALUES (55, 4, 20, 'Aragonesisk');
INSERT INTO `ff_languages_names` VALUES (56, 4, 21, 'Aragonesisch');
INSERT INTO `ff_languages_names` VALUES (57, 4, 22, 'γλώσσα της Aragon');
INSERT INTO `ff_languages_names` VALUES (58, 4, 23, 'Aragonese');
INSERT INTO `ff_languages_names` VALUES (59, 4, 25, 'aragonés');
INSERT INTO `ff_languages_names` VALUES (60, 4, 26, 'aragoni');
INSERT INTO `ff_languages_names` VALUES (61, 4, 29, 'aragonia');
INSERT INTO `ff_languages_names` VALUES (62, 4, 32, 'aragonais');
INSERT INTO `ff_languages_names` VALUES (63, 4, 40, 'aragonski');
INSERT INTO `ff_languages_names` VALUES (64, 4, 42, 'Aragóniai');
INSERT INTO `ff_languages_names` VALUES (65, 4, 45, 'Aragónska');
INSERT INTO `ff_languages_names` VALUES (66, 4, 1, 'Aragonese');
INSERT INTO `ff_languages_names` VALUES (67, 4, 59, 'Aragoniečių');
INSERT INTO `ff_languages_names` VALUES (68, 4, 60, 'Aragoniešu');
INSERT INTO `ff_languages_names` VALUES (69, 4, 66, 'Aragoniż');
INSERT INTO `ff_languages_names` VALUES (70, 4, 70, 'Aragonees');
INSERT INTO `ff_languages_names` VALUES (71, 4, 72, 'Aragonesisk');
INSERT INTO `ff_languages_names` VALUES (72, 4, 74, 'aragoński');
INSERT INTO `ff_languages_names` VALUES (73, 4, 75, 'Aragonês');
INSERT INTO `ff_languages_names` VALUES (74, 4, 77, 'Aragoneză');
INSERT INTO `ff_languages_names` VALUES (75, 4, 81, 'aragónská španielčina');
INSERT INTO `ff_languages_names` VALUES (76, 4, 82, 'aragonščina');
INSERT INTO `ff_languages_names` VALUES (77, 4, 86, 'aragonesiska');
INSERT INTO `ff_languages_names` VALUES (78, 4, 89, 'Aragonca');
INSERT INTO `ff_languages_names` VALUES (79, 5, 9, 'арабски');
INSERT INTO `ff_languages_names` VALUES (80, 5, 17, 'arabština');
INSERT INTO `ff_languages_names` VALUES (81, 5, 20, 'Arabisk');
INSERT INTO `ff_languages_names` VALUES (82, 5, 21, 'Arabisch');
INSERT INTO `ff_languages_names` VALUES (83, 5, 22, 'αραβικά');
INSERT INTO `ff_languages_names` VALUES (84, 5, 23, 'Arabic');
INSERT INTO `ff_languages_names` VALUES (85, 5, 25, 'árabe');
INSERT INTO `ff_languages_names` VALUES (86, 5, 26, 'araabia');
INSERT INTO `ff_languages_names` VALUES (87, 5, 29, 'arabia');
INSERT INTO `ff_languages_names` VALUES (88, 5, 32, 'arabe');
INSERT INTO `ff_languages_names` VALUES (89, 5, 40, 'arapski');
INSERT INTO `ff_languages_names` VALUES (90, 5, 42, 'Arab');
INSERT INTO `ff_languages_names` VALUES (91, 5, 45, 'Arabíska');
INSERT INTO `ff_languages_names` VALUES (92, 5, 1, 'Arabo');
INSERT INTO `ff_languages_names` VALUES (93, 5, 59, 'Arabų');
INSERT INTO `ff_languages_names` VALUES (94, 5, 60, 'Arābu');
INSERT INTO `ff_languages_names` VALUES (95, 5, 66, 'Għarbi');
INSERT INTO `ff_languages_names` VALUES (96, 5, 70, 'Arabisch');
INSERT INTO `ff_languages_names` VALUES (97, 5, 72, 'Arabisk');
INSERT INTO `ff_languages_names` VALUES (98, 5, 74, 'arabski');
INSERT INTO `ff_languages_names` VALUES (99, 5, 75, 'Árabe');
INSERT INTO `ff_languages_names` VALUES (100, 5, 77, 'Arabă');
INSERT INTO `ff_languages_names` VALUES (101, 5, 81, 'arabčina');
INSERT INTO `ff_languages_names` VALUES (102, 5, 82, 'arabščina');
INSERT INTO `ff_languages_names` VALUES (103, 5, 86, 'arabiska');
INSERT INTO `ff_languages_names` VALUES (104, 5, 89, 'Arapça');
INSERT INTO `ff_languages_names` VALUES (105, 6, 9, 'асамски');
INSERT INTO `ff_languages_names` VALUES (106, 6, 17, 'ásámština');
INSERT INTO `ff_languages_names` VALUES (107, 6, 20, 'Assamesisk');
INSERT INTO `ff_languages_names` VALUES (108, 6, 21, 'Assamesisch');
INSERT INTO `ff_languages_names` VALUES (109, 6, 22, 'ασαμέζικα');
INSERT INTO `ff_languages_names` VALUES (110, 6, 23, 'Assamese');
INSERT INTO `ff_languages_names` VALUES (111, 6, 25, 'asamés');
INSERT INTO `ff_languages_names` VALUES (112, 6, 26, 'assami');
INSERT INTO `ff_languages_names` VALUES (113, 6, 29, 'assami');
INSERT INTO `ff_languages_names` VALUES (114, 6, 32, 'assamais');
INSERT INTO `ff_languages_names` VALUES (115, 6, 40, 'asamski');
INSERT INTO `ff_languages_names` VALUES (116, 6, 42, 'Assamesse');
INSERT INTO `ff_languages_names` VALUES (117, 6, 45, 'Assameíska');
INSERT INTO `ff_languages_names` VALUES (118, 6, 1, 'Assamese');
INSERT INTO `ff_languages_names` VALUES (119, 6, 59, 'Asamų');
INSERT INTO `ff_languages_names` VALUES (120, 6, 60, 'Asāmiešu');
INSERT INTO `ff_languages_names` VALUES (121, 6, 66, 'Assamiż');
INSERT INTO `ff_languages_names` VALUES (122, 6, 70, 'Assamees');
INSERT INTO `ff_languages_names` VALUES (123, 6, 72, 'Assamesisk');
INSERT INTO `ff_languages_names` VALUES (124, 6, 74, 'asamski');
INSERT INTO `ff_languages_names` VALUES (125, 6, 75, 'Assamês');
INSERT INTO `ff_languages_names` VALUES (126, 6, 77, 'Asameză');
INSERT INTO `ff_languages_names` VALUES (127, 6, 81, 'asámčina');
INSERT INTO `ff_languages_names` VALUES (128, 6, 82, 'asamščina');
INSERT INTO `ff_languages_names` VALUES (129, 6, 86, 'assami');
INSERT INTO `ff_languages_names` VALUES (130, 6, 89, 'Assamca');
INSERT INTO `ff_languages_names` VALUES (131, 7, 9, 'азербайджански');
INSERT INTO `ff_languages_names` VALUES (132, 7, 17, 'ázerbajdžánština');
INSERT INTO `ff_languages_names` VALUES (133, 7, 20, 'Aserbajdsjansk');
INSERT INTO `ff_languages_names` VALUES (134, 7, 21, 'Aserbeidschanisch');
INSERT INTO `ff_languages_names` VALUES (135, 7, 22, 'Αζερικά');
INSERT INTO `ff_languages_names` VALUES (136, 7, 23, 'Azerbaijani');
INSERT INTO `ff_languages_names` VALUES (137, 7, 25, 'azerí');
INSERT INTO `ff_languages_names` VALUES (138, 7, 26, 'aserbaidžaani, aseri');
INSERT INTO `ff_languages_names` VALUES (139, 7, 29, 'azeri');
INSERT INTO `ff_languages_names` VALUES (140, 7, 32, 'azéri; azerbaïdjanais');
INSERT INTO `ff_languages_names` VALUES (141, 7, 40, 'azerski');
INSERT INTO `ff_languages_names` VALUES (142, 7, 42, 'Azerbajdzsáni');
INSERT INTO `ff_languages_names` VALUES (143, 7, 45, 'Aserbaídsjanska');
INSERT INTO `ff_languages_names` VALUES (144, 7, 1, 'Azero');
INSERT INTO `ff_languages_names` VALUES (145, 7, 59, 'Azerbaidžaniečių');
INSERT INTO `ff_languages_names` VALUES (146, 7, 60, 'Azerbaidžānu');
INSERT INTO `ff_languages_names` VALUES (147, 7, 66, 'Ażerbajġani');
INSERT INTO `ff_languages_names` VALUES (148, 7, 70, 'Azerbeidzjaans');
INSERT INTO `ff_languages_names` VALUES (149, 7, 72, 'Aserbajdsjansk');
INSERT INTO `ff_languages_names` VALUES (150, 7, 74, 'azerbejdżański');
INSERT INTO `ff_languages_names` VALUES (151, 7, 75, 'Azeri');
INSERT INTO `ff_languages_names` VALUES (152, 7, 77, 'Azerbaijană');
INSERT INTO `ff_languages_names` VALUES (153, 7, 81, 'azerbajdžančina');
INSERT INTO `ff_languages_names` VALUES (154, 7, 82, 'azerščina');
INSERT INTO `ff_languages_names` VALUES (155, 7, 86, 'azerbajdzjanska');
INSERT INTO `ff_languages_names` VALUES (156, 7, 89, 'Azerice');
INSERT INTO `ff_languages_names` VALUES (157, 8, 9, 'белоруски');
INSERT INTO `ff_languages_names` VALUES (158, 8, 17, 'běloruština');
INSERT INTO `ff_languages_names` VALUES (159, 8, 20, 'Hviderussisk');
INSERT INTO `ff_languages_names` VALUES (160, 8, 21, 'Weißrussisch');
INSERT INTO `ff_languages_names` VALUES (161, 8, 22, 'λευκορωσικά');
INSERT INTO `ff_languages_names` VALUES (162, 8, 23, 'Belarusian');
INSERT INTO `ff_languages_names` VALUES (163, 8, 25, 'bielorruso');
INSERT INTO `ff_languages_names` VALUES (164, 8, 26, 'valgevene');
INSERT INTO `ff_languages_names` VALUES (165, 8, 29, 'valkovenäjä');
INSERT INTO `ff_languages_names` VALUES (166, 8, 32, 'biélorusse');
INSERT INTO `ff_languages_names` VALUES (167, 8, 40, 'bjeloruski');
INSERT INTO `ff_languages_names` VALUES (168, 8, 42, 'Belorusz');
INSERT INTO `ff_languages_names` VALUES (169, 8, 45, 'Belarusian');
INSERT INTO `ff_languages_names` VALUES (170, 8, 1, 'Bielorusso');
INSERT INTO `ff_languages_names` VALUES (171, 8, 59, 'Baltarusių');
INSERT INTO `ff_languages_names` VALUES (172, 8, 60, 'Baltkrievu');
INSERT INTO `ff_languages_names` VALUES (173, 8, 66, 'Belarussu');
INSERT INTO `ff_languages_names` VALUES (174, 8, 70, 'Wit-Russisch');
INSERT INTO `ff_languages_names` VALUES (175, 8, 72, 'Hviterussisk');
INSERT INTO `ff_languages_names` VALUES (176, 8, 74, 'białoruski');
INSERT INTO `ff_languages_names` VALUES (177, 8, 75, 'Bielorusso');
INSERT INTO `ff_languages_names` VALUES (178, 8, 77, 'Bielorusă');
INSERT INTO `ff_languages_names` VALUES (179, 8, 81, 'bieloruština');
INSERT INTO `ff_languages_names` VALUES (180, 8, 82, 'beloruščina');
INSERT INTO `ff_languages_names` VALUES (181, 8, 86, 'vitryska');
INSERT INTO `ff_languages_names` VALUES (182, 8, 89, 'Belarusca');
INSERT INTO `ff_languages_names` VALUES (183, 9, 9, 'български');
INSERT INTO `ff_languages_names` VALUES (184, 9, 17, 'bulharština');
INSERT INTO `ff_languages_names` VALUES (185, 9, 20, 'Bulgarsk');
INSERT INTO `ff_languages_names` VALUES (186, 9, 21, 'Bulgarisch');
INSERT INTO `ff_languages_names` VALUES (187, 9, 22, 'βουλγαρικά');
INSERT INTO `ff_languages_names` VALUES (188, 9, 23, 'Bulgarian');
INSERT INTO `ff_languages_names` VALUES (189, 9, 25, 'búlgaro');
INSERT INTO `ff_languages_names` VALUES (190, 9, 26, 'bulgaaria');
INSERT INTO `ff_languages_names` VALUES (191, 9, 29, 'bulgaria');
INSERT INTO `ff_languages_names` VALUES (192, 9, 32, 'bulgare');
INSERT INTO `ff_languages_names` VALUES (193, 9, 40, 'Bugarski');
INSERT INTO `ff_languages_names` VALUES (194, 9, 42, 'Bolgár');
INSERT INTO `ff_languages_names` VALUES (195, 9, 45, 'Búlgarska');
INSERT INTO `ff_languages_names` VALUES (196, 9, 1, 'Bulgaro');
INSERT INTO `ff_languages_names` VALUES (197, 9, 59, 'Bulgarų');
INSERT INTO `ff_languages_names` VALUES (198, 9, 60, 'Bulgāru');
INSERT INTO `ff_languages_names` VALUES (199, 9, 66, 'Bulgaru');
INSERT INTO `ff_languages_names` VALUES (200, 9, 70, 'Bulgaars');
INSERT INTO `ff_languages_names` VALUES (201, 9, 72, 'Bulgarsk');
INSERT INTO `ff_languages_names` VALUES (202, 9, 74, 'bułgarski');
INSERT INTO `ff_languages_names` VALUES (203, 9, 75, 'Búlgaro');
INSERT INTO `ff_languages_names` VALUES (204, 9, 77, 'Bulgară');
INSERT INTO `ff_languages_names` VALUES (205, 9, 81, 'bulharčina');
INSERT INTO `ff_languages_names` VALUES (206, 9, 82, 'bolgarščina');
INSERT INTO `ff_languages_names` VALUES (207, 9, 86, 'bulgariska');
INSERT INTO `ff_languages_names` VALUES (208, 9, 89, 'Bulgarca');
INSERT INTO `ff_languages_names` VALUES (209, 10, 9, 'бенгалски');
INSERT INTO `ff_languages_names` VALUES (210, 10, 17, 'bengálština');
INSERT INTO `ff_languages_names` VALUES (211, 10, 20, 'Bengali');
INSERT INTO `ff_languages_names` VALUES (212, 10, 21, 'Bengalisch');
INSERT INTO `ff_languages_names` VALUES (213, 10, 22, 'μπενγκάλι');
INSERT INTO `ff_languages_names` VALUES (214, 10, 23, 'Bengali');
INSERT INTO `ff_languages_names` VALUES (215, 10, 25, 'bengalí');
INSERT INTO `ff_languages_names` VALUES (216, 10, 26, 'bengali');
INSERT INTO `ff_languages_names` VALUES (217, 10, 29, 'bengali');
INSERT INTO `ff_languages_names` VALUES (218, 10, 32, 'bengali');
INSERT INTO `ff_languages_names` VALUES (219, 10, 40, 'bengalski');
INSERT INTO `ff_languages_names` VALUES (220, 10, 42, 'Bengáli');
INSERT INTO `ff_languages_names` VALUES (221, 10, 45, 'Bengalska');
INSERT INTO `ff_languages_names` VALUES (222, 10, 1, 'Lingua bengali');
INSERT INTO `ff_languages_names` VALUES (223, 10, 59, 'Bengalų');
INSERT INTO `ff_languages_names` VALUES (224, 10, 60, 'Bengāļu');
INSERT INTO `ff_languages_names` VALUES (225, 10, 66, 'Bengali');
INSERT INTO `ff_languages_names` VALUES (226, 10, 70, 'Bengalees');
INSERT INTO `ff_languages_names` VALUES (227, 10, 72, 'Bengali');
INSERT INTO `ff_languages_names` VALUES (228, 10, 74, 'bengalski');
INSERT INTO `ff_languages_names` VALUES (229, 10, 75, 'Bengali');
INSERT INTO `ff_languages_names` VALUES (230, 10, 77, 'Bengaleză');
INSERT INTO `ff_languages_names` VALUES (231, 10, 81, 'bengálčina');
INSERT INTO `ff_languages_names` VALUES (232, 10, 82, 'bengalščina');
INSERT INTO `ff_languages_names` VALUES (233, 10, 86, 'bengaliska');
INSERT INTO `ff_languages_names` VALUES (234, 10, 89, 'Bangladeşçe');
INSERT INTO `ff_languages_names` VALUES (235, 11, 9, 'тибетски');
INSERT INTO `ff_languages_names` VALUES (236, 11, 17, 'tibetština');
INSERT INTO `ff_languages_names` VALUES (237, 11, 20, 'Tibetansk');
INSERT INTO `ff_languages_names` VALUES (238, 11, 21, 'Tibetisch');
INSERT INTO `ff_languages_names` VALUES (239, 11, 22, 'θιβετιανά');
INSERT INTO `ff_languages_names` VALUES (240, 11, 23, 'Tibetan');
INSERT INTO `ff_languages_names` VALUES (241, 11, 25, 'tibetano');
INSERT INTO `ff_languages_names` VALUES (242, 11, 26, 'tiibeti');
INSERT INTO `ff_languages_names` VALUES (243, 11, 29, 'tiibet');
INSERT INTO `ff_languages_names` VALUES (244, 11, 32, 'tibétain');
INSERT INTO `ff_languages_names` VALUES (245, 11, 40, 'tibetski');
INSERT INTO `ff_languages_names` VALUES (246, 11, 42, 'Tibeti');
INSERT INTO `ff_languages_names` VALUES (247, 11, 45, 'Tibetan');
INSERT INTO `ff_languages_names` VALUES (248, 11, 1, 'Tibetano');
INSERT INTO `ff_languages_names` VALUES (249, 11, 59, 'Tibetiečių');
INSERT INTO `ff_languages_names` VALUES (250, 11, 60, 'Tibetas');
INSERT INTO `ff_languages_names` VALUES (251, 11, 66, 'Tibetjan');
INSERT INTO `ff_languages_names` VALUES (252, 11, 70, 'Tibetaans');
INSERT INTO `ff_languages_names` VALUES (253, 11, 72, 'Tibetansk');
INSERT INTO `ff_languages_names` VALUES (254, 11, 74, 'tybetański');
INSERT INTO `ff_languages_names` VALUES (255, 11, 75, 'Tibetano');
INSERT INTO `ff_languages_names` VALUES (256, 11, 77, 'Tibetană');
INSERT INTO `ff_languages_names` VALUES (257, 11, 81, 'tibetčina');
INSERT INTO `ff_languages_names` VALUES (258, 11, 82, 'tibetanščina');
INSERT INTO `ff_languages_names` VALUES (259, 11, 86, 'tibetanska');
INSERT INTO `ff_languages_names` VALUES (260, 11, 89, 'Tibetçe');
INSERT INTO `ff_languages_names` VALUES (261, 12, 9, 'бретонски');
INSERT INTO `ff_languages_names` VALUES (262, 12, 17, 'bretonština');
INSERT INTO `ff_languages_names` VALUES (263, 12, 20, 'Bretonsk');
INSERT INTO `ff_languages_names` VALUES (264, 12, 21, 'Bretonisch');
INSERT INTO `ff_languages_names` VALUES (265, 12, 22, 'βρετονικά');
INSERT INTO `ff_languages_names` VALUES (266, 12, 23, 'Breton');
INSERT INTO `ff_languages_names` VALUES (267, 12, 25, 'bretón');
INSERT INTO `ff_languages_names` VALUES (268, 12, 26, 'bretooni');
INSERT INTO `ff_languages_names` VALUES (269, 12, 29, 'bretoni');
INSERT INTO `ff_languages_names` VALUES (270, 12, 32, 'breton');
INSERT INTO `ff_languages_names` VALUES (271, 12, 40, 'bretonski');
INSERT INTO `ff_languages_names` VALUES (272, 12, 42, 'Breton');
INSERT INTO `ff_languages_names` VALUES (273, 12, 45, 'Breton');
INSERT INTO `ff_languages_names` VALUES (274, 12, 1, 'Bretone');
INSERT INTO `ff_languages_names` VALUES (275, 12, 59, 'Bretonų');
INSERT INTO `ff_languages_names` VALUES (276, 12, 60, 'Bretoņu');
INSERT INTO `ff_languages_names` VALUES (277, 12, 66, 'Breton');
INSERT INTO `ff_languages_names` VALUES (278, 12, 70, 'Bretons');
INSERT INTO `ff_languages_names` VALUES (279, 12, 72, 'Bretonsk');
INSERT INTO `ff_languages_names` VALUES (280, 12, 74, 'bretoński');
INSERT INTO `ff_languages_names` VALUES (281, 12, 75, 'Bretão');
INSERT INTO `ff_languages_names` VALUES (282, 12, 77, 'Bretonă');
INSERT INTO `ff_languages_names` VALUES (283, 12, 81, 'bretónčina');
INSERT INTO `ff_languages_names` VALUES (284, 12, 82, 'bretonščina');
INSERT INTO `ff_languages_names` VALUES (285, 12, 86, 'bretonska');
INSERT INTO `ff_languages_names` VALUES (286, 12, 89, 'Bretonca');
INSERT INTO `ff_languages_names` VALUES (287, 13, 9, 'босненски');
INSERT INTO `ff_languages_names` VALUES (288, 13, 17, 'bosenština');
INSERT INTO `ff_languages_names` VALUES (289, 13, 20, 'Bosnisk');
INSERT INTO `ff_languages_names` VALUES (290, 13, 21, 'Bosnisch');
INSERT INTO `ff_languages_names` VALUES (291, 13, 22, 'βοσνιακά');
INSERT INTO `ff_languages_names` VALUES (292, 13, 23, 'Bosnian');
INSERT INTO `ff_languages_names` VALUES (293, 13, 25, 'bosnio');
INSERT INTO `ff_languages_names` VALUES (294, 13, 26, 'bosnia');
INSERT INTO `ff_languages_names` VALUES (295, 13, 29, 'bosnia');
INSERT INTO `ff_languages_names` VALUES (296, 13, 32, 'bosniaque');
INSERT INTO `ff_languages_names` VALUES (297, 13, 40, 'bosanski');
INSERT INTO `ff_languages_names` VALUES (298, 13, 42, 'Bosnyák');
INSERT INTO `ff_languages_names` VALUES (299, 13, 45, 'Bosnian');
INSERT INTO `ff_languages_names` VALUES (300, 13, 1, 'Bosniaco');
INSERT INTO `ff_languages_names` VALUES (301, 13, 59, 'Bosnių');
INSERT INTO `ff_languages_names` VALUES (302, 13, 60, 'Bosniešu');
INSERT INTO `ff_languages_names` VALUES (303, 13, 66, 'Bożnijaku');
INSERT INTO `ff_languages_names` VALUES (304, 13, 70, 'Bosnisch');
INSERT INTO `ff_languages_names` VALUES (305, 13, 72, 'Bosnisk');
INSERT INTO `ff_languages_names` VALUES (306, 13, 74, 'bośniacki');
INSERT INTO `ff_languages_names` VALUES (307, 13, 75, 'Bósnio');
INSERT INTO `ff_languages_names` VALUES (308, 13, 77, 'Bosniacă');
INSERT INTO `ff_languages_names` VALUES (309, 13, 81, 'bosniačtina');
INSERT INTO `ff_languages_names` VALUES (310, 13, 82, 'bosanščina');
INSERT INTO `ff_languages_names` VALUES (311, 13, 86, 'bosniska');
INSERT INTO `ff_languages_names` VALUES (312, 13, 89, 'Boşnakça');
INSERT INTO `ff_languages_names` VALUES (313, 14, 9, 'каталонски');
INSERT INTO `ff_languages_names` VALUES (314, 14, 17, 'kataláština');
INSERT INTO `ff_languages_names` VALUES (315, 14, 20, 'Catalansk');
INSERT INTO `ff_languages_names` VALUES (316, 14, 21, 'Katalanisch');
INSERT INTO `ff_languages_names` VALUES (317, 14, 22, 'καταλανικά');
INSERT INTO `ff_languages_names` VALUES (318, 14, 23, 'Catalan / Valencian');
INSERT INTO `ff_languages_names` VALUES (319, 14, 25, 'catalán / valenciano');
INSERT INTO `ff_languages_names` VALUES (320, 14, 26, 'katalaani');
INSERT INTO `ff_languages_names` VALUES (321, 14, 29, 'katalaani');
INSERT INTO `ff_languages_names` VALUES (322, 14, 32, 'catalan / valencien');
INSERT INTO `ff_languages_names` VALUES (323, 14, 40, 'katalonski');
INSERT INTO `ff_languages_names` VALUES (324, 14, 42, 'Katalán/ valenciai');
INSERT INTO `ff_languages_names` VALUES (325, 14, 45, 'Catalan');
INSERT INTO `ff_languages_names` VALUES (326, 14, 1, 'Catalano / Valenciano');
INSERT INTO `ff_languages_names` VALUES (327, 14, 59, 'Katalonų / Valensijos');
INSERT INTO `ff_languages_names` VALUES (328, 14, 60, 'Kataloniešu / valensiešu');
INSERT INTO `ff_languages_names` VALUES (329, 14, 66, 'Katalan / Valenzjan');
INSERT INTO `ff_languages_names` VALUES (330, 14, 70, 'Catalaans / Valenciaans');
INSERT INTO `ff_languages_names` VALUES (331, 14, 72, 'Katalansk');
INSERT INTO `ff_languages_names` VALUES (332, 14, 74, 'kataloński / walencjański');
INSERT INTO `ff_languages_names` VALUES (333, 14, 75, 'Catalão / Valenciano');
INSERT INTO `ff_languages_names` VALUES (334, 14, 77, 'Catalană');
INSERT INTO `ff_languages_names` VALUES (335, 14, 81, 'katalánčina');
INSERT INTO `ff_languages_names` VALUES (336, 14, 82, 'katalonščina / valencijščina');
INSERT INTO `ff_languages_names` VALUES (337, 14, 86, 'katalanska / valenciska');
INSERT INTO `ff_languages_names` VALUES (338, 14, 89, 'Katalanca');
INSERT INTO `ff_languages_names` VALUES (339, 15, 9, 'чеченски');
INSERT INTO `ff_languages_names` VALUES (340, 15, 17, 'čečenština');
INSERT INTO `ff_languages_names` VALUES (341, 15, 20, 'Tjetjensk');
INSERT INTO `ff_languages_names` VALUES (342, 15, 21, 'Tschetschenisch');
INSERT INTO `ff_languages_names` VALUES (343, 15, 22, 'τσετσενικά');
INSERT INTO `ff_languages_names` VALUES (344, 15, 23, 'Chechen');
INSERT INTO `ff_languages_names` VALUES (345, 15, 25, 'checheno');
INSERT INTO `ff_languages_names` VALUES (346, 15, 26, 'tšetšeeni');
INSERT INTO `ff_languages_names` VALUES (347, 15, 29, 'tšetšeeni');
INSERT INTO `ff_languages_names` VALUES (348, 15, 32, 'tchétchène');
INSERT INTO `ff_languages_names` VALUES (349, 15, 40, 'čečenski');
INSERT INTO `ff_languages_names` VALUES (350, 15, 42, 'Csecsen');
INSERT INTO `ff_languages_names` VALUES (351, 15, 45, 'Téténska');
INSERT INTO `ff_languages_names` VALUES (352, 15, 1, 'Ceceno');
INSERT INTO `ff_languages_names` VALUES (353, 15, 59, 'Čečenų');
INSERT INTO `ff_languages_names` VALUES (354, 15, 60, 'Čečenu');
INSERT INTO `ff_languages_names` VALUES (355, 15, 66, 'Ċeċen');
INSERT INTO `ff_languages_names` VALUES (356, 15, 70, 'Tsjetsjeens');
INSERT INTO `ff_languages_names` VALUES (357, 15, 72, 'Tsjetsjensk');
INSERT INTO `ff_languages_names` VALUES (358, 15, 74, 'czeczeński');
INSERT INTO `ff_languages_names` VALUES (359, 15, 75, 'Tchetcheno');
INSERT INTO `ff_languages_names` VALUES (360, 15, 77, 'Cecenă');
INSERT INTO `ff_languages_names` VALUES (361, 15, 81, 'čečenčina');
INSERT INTO `ff_languages_names` VALUES (362, 15, 82, 'čečenščina');
INSERT INTO `ff_languages_names` VALUES (363, 15, 86, 'tjetjenska');
INSERT INTO `ff_languages_names` VALUES (364, 15, 89, 'Çeçence');
INSERT INTO `ff_languages_names` VALUES (365, 16, 9, 'корсикански');
INSERT INTO `ff_languages_names` VALUES (366, 16, 17, 'korsičtina');
INSERT INTO `ff_languages_names` VALUES (367, 16, 20, 'Korsikansk');
INSERT INTO `ff_languages_names` VALUES (368, 16, 21, 'Korsisch');
INSERT INTO `ff_languages_names` VALUES (369, 16, 22, 'κορσικανικά');
INSERT INTO `ff_languages_names` VALUES (370, 16, 23, 'Corsican');
INSERT INTO `ff_languages_names` VALUES (371, 16, 25, 'corso');
INSERT INTO `ff_languages_names` VALUES (372, 16, 26, 'korsika');
INSERT INTO `ff_languages_names` VALUES (373, 16, 29, 'korsika');
INSERT INTO `ff_languages_names` VALUES (374, 16, 32, 'corse');
INSERT INTO `ff_languages_names` VALUES (375, 16, 40, 'korzikanski');
INSERT INTO `ff_languages_names` VALUES (376, 16, 42, 'Korzikai');
INSERT INTO `ff_languages_names` VALUES (377, 16, 45, 'Corsican');
INSERT INTO `ff_languages_names` VALUES (378, 16, 1, 'Corso');
INSERT INTO `ff_languages_names` VALUES (379, 16, 59, 'Korsikiečių');
INSERT INTO `ff_languages_names` VALUES (380, 16, 60, 'Korsikāņu');
INSERT INTO `ff_languages_names` VALUES (381, 16, 66, 'Korsiku');
INSERT INTO `ff_languages_names` VALUES (382, 16, 70, 'Corsicaans');
INSERT INTO `ff_languages_names` VALUES (383, 16, 72, 'Korsikansk');
INSERT INTO `ff_languages_names` VALUES (384, 16, 74, 'korsykański');
INSERT INTO `ff_languages_names` VALUES (385, 16, 75, 'Corso');
INSERT INTO `ff_languages_names` VALUES (386, 16, 77, 'Corsicană');
INSERT INTO `ff_languages_names` VALUES (387, 16, 81, 'korzičtina');
INSERT INTO `ff_languages_names` VALUES (388, 16, 82, 'korzijščina');
INSERT INTO `ff_languages_names` VALUES (389, 16, 86, 'korsikanska');
INSERT INTO `ff_languages_names` VALUES (390, 16, 89, 'Korsikaca');
INSERT INTO `ff_languages_names` VALUES (391, 17, 9, 'чешки');
INSERT INTO `ff_languages_names` VALUES (392, 17, 17, 'čeština');
INSERT INTO `ff_languages_names` VALUES (393, 17, 20, 'Tjekkisk');
INSERT INTO `ff_languages_names` VALUES (394, 17, 21, 'Tschechisch');
INSERT INTO `ff_languages_names` VALUES (395, 17, 22, 'τσεχικά');
INSERT INTO `ff_languages_names` VALUES (396, 17, 23, 'Czech');
INSERT INTO `ff_languages_names` VALUES (397, 17, 25, 'checo');
INSERT INTO `ff_languages_names` VALUES (398, 17, 26, 'tšehhi');
INSERT INTO `ff_languages_names` VALUES (399, 17, 29, 'tšekki');
INSERT INTO `ff_languages_names` VALUES (400, 17, 32, 'tchèque');
INSERT INTO `ff_languages_names` VALUES (401, 17, 40, 'Češki');
INSERT INTO `ff_languages_names` VALUES (402, 17, 42, 'Cseh');
INSERT INTO `ff_languages_names` VALUES (403, 17, 45, 'Tékkneska');
INSERT INTO `ff_languages_names` VALUES (404, 17, 1, 'Ceco');
INSERT INTO `ff_languages_names` VALUES (405, 17, 59, 'Čekų');
INSERT INTO `ff_languages_names` VALUES (406, 17, 60, 'Čehu');
INSERT INTO `ff_languages_names` VALUES (407, 17, 66, 'Ċek');
INSERT INTO `ff_languages_names` VALUES (408, 17, 70, 'Tsjechisch');
INSERT INTO `ff_languages_names` VALUES (409, 17, 72, 'Tsjekkisk');
INSERT INTO `ff_languages_names` VALUES (410, 17, 74, 'czeski');
INSERT INTO `ff_languages_names` VALUES (411, 17, 75, 'Checo');
INSERT INTO `ff_languages_names` VALUES (412, 17, 77, 'Cehă');
INSERT INTO `ff_languages_names` VALUES (413, 17, 81, 'čeština');
INSERT INTO `ff_languages_names` VALUES (414, 17, 82, 'češčina');
INSERT INTO `ff_languages_names` VALUES (415, 17, 86, 'tjeckiska');
INSERT INTO `ff_languages_names` VALUES (416, 17, 89, 'Çekçe');
INSERT INTO `ff_languages_names` VALUES (417, 18, 9, 'черковнославянски');
INSERT INTO `ff_languages_names` VALUES (418, 18, 17, 'staroslověnština');
INSERT INTO `ff_languages_names` VALUES (419, 18, 20, 'Kirkeslavisk');
INSERT INTO `ff_languages_names` VALUES (420, 18, 21, 'Kirchenslawisch');
INSERT INTO `ff_languages_names` VALUES (421, 18, 22, 'εκκλησιαστική σλαβική');
INSERT INTO `ff_languages_names` VALUES (422, 18, 23, 'Church Slavic');
INSERT INTO `ff_languages_names` VALUES (423, 18, 25, 'eslavo eclesiástico');
INSERT INTO `ff_languages_names` VALUES (424, 18, 26, 'kirikuslaavi');
INSERT INTO `ff_languages_names` VALUES (425, 18, 29, 'kirkkoslaavi');
INSERT INTO `ff_languages_names` VALUES (426, 18, 32, 'slavon d\'église');
INSERT INTO `ff_languages_names` VALUES (427, 18, 40, 'crkvenoslavenski');
INSERT INTO `ff_languages_names` VALUES (428, 18, 42, 'Templomi szláv');
INSERT INTO `ff_languages_names` VALUES (429, 18, 45, 'Church Slavic');
INSERT INTO `ff_languages_names` VALUES (430, 18, 1, 'Church Slavonic');
INSERT INTO `ff_languages_names` VALUES (431, 18, 59, 'Bažnytinė slavų kalba');
INSERT INTO `ff_languages_names` VALUES (432, 18, 60, 'Baznīcslāvu');
INSERT INTO `ff_languages_names` VALUES (433, 18, 66, 'Slaviku tal-Knisja');
INSERT INTO `ff_languages_names` VALUES (434, 18, 70, 'Kerkslavisch');
INSERT INTO `ff_languages_names` VALUES (435, 18, 72, 'Gammelslavisk');
INSERT INTO `ff_languages_names` VALUES (436, 18, 74, 'cerkiewno-słowiański');
INSERT INTO `ff_languages_names` VALUES (437, 18, 75, 'Eslavo de Igreja');
INSERT INTO `ff_languages_names` VALUES (438, 18, 77, 'Slavă bisericească');
INSERT INTO `ff_languages_names` VALUES (439, 18, 81, 'cirkevnoslovanské jazyky');
INSERT INTO `ff_languages_names` VALUES (440, 18, 82, 'stara cerkvena slovanščina');
INSERT INTO `ff_languages_names` VALUES (441, 18, 86, 'kyrkslaviska');
INSERT INTO `ff_languages_names` VALUES (442, 18, 89, 'Slavca');
INSERT INTO `ff_languages_names` VALUES (443, 19, 9, 'уелски');
INSERT INTO `ff_languages_names` VALUES (444, 19, 17, 'velština');
INSERT INTO `ff_languages_names` VALUES (445, 19, 20, 'Walisisk');
INSERT INTO `ff_languages_names` VALUES (446, 19, 21, 'Kymrisch');
INSERT INTO `ff_languages_names` VALUES (447, 19, 22, 'ουαλικά');
INSERT INTO `ff_languages_names` VALUES (448, 19, 23, 'Welsh');
INSERT INTO `ff_languages_names` VALUES (449, 19, 25, 'galés');
INSERT INTO `ff_languages_names` VALUES (450, 19, 26, 'kõmri');
INSERT INTO `ff_languages_names` VALUES (451, 19, 29, 'kymri');
INSERT INTO `ff_languages_names` VALUES (452, 19, 32, 'gallois');
INSERT INTO `ff_languages_names` VALUES (453, 19, 40, 'velški');
INSERT INTO `ff_languages_names` VALUES (454, 19, 42, 'Welszi');
INSERT INTO `ff_languages_names` VALUES (455, 19, 45, 'Velska');
INSERT INTO `ff_languages_names` VALUES (456, 19, 1, 'Gallese');
INSERT INTO `ff_languages_names` VALUES (457, 19, 59, 'Velsiečių');
INSERT INTO `ff_languages_names` VALUES (458, 19, 60, 'Velsiešu');
INSERT INTO `ff_languages_names` VALUES (459, 19, 66, 'Welx');
INSERT INTO `ff_languages_names` VALUES (460, 19, 70, 'Welsh');
INSERT INTO `ff_languages_names` VALUES (461, 19, 72, 'Walisisk');
INSERT INTO `ff_languages_names` VALUES (462, 19, 74, 'walijski');
INSERT INTO `ff_languages_names` VALUES (463, 19, 75, 'Galês');
INSERT INTO `ff_languages_names` VALUES (464, 19, 77, 'Galeză / Velşă');
INSERT INTO `ff_languages_names` VALUES (465, 19, 81, 'waleština');
INSERT INTO `ff_languages_names` VALUES (466, 19, 82, 'valižanščina');
INSERT INTO `ff_languages_names` VALUES (467, 19, 86, 'walesiska');
INSERT INTO `ff_languages_names` VALUES (468, 19, 89, 'Gaelce');
INSERT INTO `ff_languages_names` VALUES (469, 20, 9, 'датски');
INSERT INTO `ff_languages_names` VALUES (470, 20, 17, 'dánština');
INSERT INTO `ff_languages_names` VALUES (471, 20, 20, 'Dansk');
INSERT INTO `ff_languages_names` VALUES (472, 20, 21, 'Dänisch');
INSERT INTO `ff_languages_names` VALUES (473, 20, 22, 'δανικά');
INSERT INTO `ff_languages_names` VALUES (474, 20, 23, 'Danish');
INSERT INTO `ff_languages_names` VALUES (475, 20, 25, 'danés');
INSERT INTO `ff_languages_names` VALUES (476, 20, 26, 'taani');
INSERT INTO `ff_languages_names` VALUES (477, 20, 29, 'tanska');
INSERT INTO `ff_languages_names` VALUES (478, 20, 32, 'danois');
INSERT INTO `ff_languages_names` VALUES (479, 20, 40, 'Danski');
INSERT INTO `ff_languages_names` VALUES (480, 20, 42, 'Dán');
INSERT INTO `ff_languages_names` VALUES (481, 20, 45, 'Danska');
INSERT INTO `ff_languages_names` VALUES (482, 20, 1, 'Danese');
INSERT INTO `ff_languages_names` VALUES (483, 20, 59, 'Danų');
INSERT INTO `ff_languages_names` VALUES (484, 20, 60, 'Dāņu');
INSERT INTO `ff_languages_names` VALUES (485, 20, 66, 'Daniż');
INSERT INTO `ff_languages_names` VALUES (486, 20, 70, 'Deens');
INSERT INTO `ff_languages_names` VALUES (487, 20, 72, 'Dansk');
INSERT INTO `ff_languages_names` VALUES (488, 20, 74, 'duński');
INSERT INTO `ff_languages_names` VALUES (489, 20, 75, 'Dinamarquês');
INSERT INTO `ff_languages_names` VALUES (490, 20, 77, 'Daneză');
INSERT INTO `ff_languages_names` VALUES (491, 20, 81, 'dánčina');
INSERT INTO `ff_languages_names` VALUES (492, 20, 82, 'danščina');
INSERT INTO `ff_languages_names` VALUES (493, 20, 86, 'danska');
INSERT INTO `ff_languages_names` VALUES (494, 20, 89, 'Danca');
INSERT INTO `ff_languages_names` VALUES (495, 21, 9, 'немски');
INSERT INTO `ff_languages_names` VALUES (496, 21, 17, 'němčina');
INSERT INTO `ff_languages_names` VALUES (497, 21, 20, 'Tysk');
INSERT INTO `ff_languages_names` VALUES (498, 21, 21, 'Deutsch');
INSERT INTO `ff_languages_names` VALUES (499, 21, 22, 'γερμανικά');
INSERT INTO `ff_languages_names` VALUES (500, 21, 23, 'German');
INSERT INTO `ff_languages_names` VALUES (501, 21, 25, 'alemán');
INSERT INTO `ff_languages_names` VALUES (502, 21, 26, 'saksa');
INSERT INTO `ff_languages_names` VALUES (503, 21, 29, 'saksa');
INSERT INTO `ff_languages_names` VALUES (504, 21, 32, 'allemand');
INSERT INTO `ff_languages_names` VALUES (505, 21, 40, 'Njemački');
INSERT INTO `ff_languages_names` VALUES (506, 21, 42, 'Német');
INSERT INTO `ff_languages_names` VALUES (507, 21, 45, 'Þýska');
INSERT INTO `ff_languages_names` VALUES (508, 21, 1, 'Tedesco');
INSERT INTO `ff_languages_names` VALUES (509, 21, 59, 'Vokiečių');
INSERT INTO `ff_languages_names` VALUES (510, 21, 60, 'Vācu');
INSERT INTO `ff_languages_names` VALUES (511, 21, 66, 'Ġermaniż');
INSERT INTO `ff_languages_names` VALUES (512, 21, 70, 'Duits');
INSERT INTO `ff_languages_names` VALUES (513, 21, 72, 'Tysk');
INSERT INTO `ff_languages_names` VALUES (514, 21, 74, 'niemiecki');
INSERT INTO `ff_languages_names` VALUES (515, 21, 75, 'Alemão');
INSERT INTO `ff_languages_names` VALUES (516, 21, 77, 'Germană');
INSERT INTO `ff_languages_names` VALUES (517, 21, 81, 'nemčina');
INSERT INTO `ff_languages_names` VALUES (518, 21, 82, 'nemščina');
INSERT INTO `ff_languages_names` VALUES (519, 21, 86, 'tyska');
INSERT INTO `ff_languages_names` VALUES (520, 21, 89, 'Almanca');
INSERT INTO `ff_languages_names` VALUES (521, 22, 9, 'гръцки');
INSERT INTO `ff_languages_names` VALUES (522, 22, 17, 'řečtina');
INSERT INTO `ff_languages_names` VALUES (523, 22, 20, 'Græsk');
INSERT INTO `ff_languages_names` VALUES (524, 22, 21, 'Griechisch');
INSERT INTO `ff_languages_names` VALUES (525, 22, 22, 'ελληνικά');
INSERT INTO `ff_languages_names` VALUES (526, 22, 23, 'Greek');
INSERT INTO `ff_languages_names` VALUES (527, 22, 25, 'griego');
INSERT INTO `ff_languages_names` VALUES (528, 22, 26, 'kreeka');
INSERT INTO `ff_languages_names` VALUES (529, 22, 29, 'kreikka');
INSERT INTO `ff_languages_names` VALUES (530, 22, 32, 'Grec');
INSERT INTO `ff_languages_names` VALUES (531, 22, 40, 'Grčki');
INSERT INTO `ff_languages_names` VALUES (532, 22, 42, 'görög');
INSERT INTO `ff_languages_names` VALUES (533, 22, 45, 'Gríska');
INSERT INTO `ff_languages_names` VALUES (534, 22, 1, 'Greco');
INSERT INTO `ff_languages_names` VALUES (535, 22, 59, 'graikų');
INSERT INTO `ff_languages_names` VALUES (536, 22, 60, 'grieķu');
INSERT INTO `ff_languages_names` VALUES (537, 22, 66, 'Grieg');
INSERT INTO `ff_languages_names` VALUES (538, 22, 70, 'Grieks');
INSERT INTO `ff_languages_names` VALUES (539, 22, 72, 'Gresk');
INSERT INTO `ff_languages_names` VALUES (540, 22, 74, 'grecki');
INSERT INTO `ff_languages_names` VALUES (541, 22, 75, 'Grego');
INSERT INTO `ff_languages_names` VALUES (542, 22, 77, 'Greacă');
INSERT INTO `ff_languages_names` VALUES (543, 22, 81, 'gréčtina');
INSERT INTO `ff_languages_names` VALUES (544, 22, 82, 'grščina');
INSERT INTO `ff_languages_names` VALUES (545, 22, 86, 'grekiska');
INSERT INTO `ff_languages_names` VALUES (546, 22, 89, 'Yunanca');
INSERT INTO `ff_languages_names` VALUES (547, 23, 9, 'английски');
INSERT INTO `ff_languages_names` VALUES (548, 23, 17, 'angličtina');
INSERT INTO `ff_languages_names` VALUES (549, 23, 20, 'Engelsk');
INSERT INTO `ff_languages_names` VALUES (550, 23, 21, 'Englisch');
INSERT INTO `ff_languages_names` VALUES (551, 23, 22, 'αγγλικά');
INSERT INTO `ff_languages_names` VALUES (552, 23, 23, 'English');
INSERT INTO `ff_languages_names` VALUES (553, 23, 25, 'inglés');
INSERT INTO `ff_languages_names` VALUES (554, 23, 26, 'inglise');
INSERT INTO `ff_languages_names` VALUES (555, 23, 29, 'englanti');
INSERT INTO `ff_languages_names` VALUES (556, 23, 32, 'anglais');
INSERT INTO `ff_languages_names` VALUES (557, 23, 40, 'Engleski');
INSERT INTO `ff_languages_names` VALUES (558, 23, 42, 'Angol');
INSERT INTO `ff_languages_names` VALUES (559, 23, 45, 'Enska');
INSERT INTO `ff_languages_names` VALUES (560, 23, 1, 'Inglese');
INSERT INTO `ff_languages_names` VALUES (561, 23, 59, 'Anglų');
INSERT INTO `ff_languages_names` VALUES (562, 23, 60, 'Angļu');
INSERT INTO `ff_languages_names` VALUES (563, 23, 66, 'Ingliż');
INSERT INTO `ff_languages_names` VALUES (564, 23, 70, 'Engels');
INSERT INTO `ff_languages_names` VALUES (565, 23, 72, 'Engelsk');
INSERT INTO `ff_languages_names` VALUES (566, 23, 74, 'angielski');
INSERT INTO `ff_languages_names` VALUES (567, 23, 75, 'Inglês');
INSERT INTO `ff_languages_names` VALUES (568, 23, 77, 'Engleză');
INSERT INTO `ff_languages_names` VALUES (569, 23, 81, 'angličtina');
INSERT INTO `ff_languages_names` VALUES (570, 23, 82, 'angleščina');
INSERT INTO `ff_languages_names` VALUES (571, 23, 86, 'engelska');
INSERT INTO `ff_languages_names` VALUES (572, 23, 89, 'İngilizce');
INSERT INTO `ff_languages_names` VALUES (573, 24, 9, 'есперанто');
INSERT INTO `ff_languages_names` VALUES (574, 24, 17, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (575, 24, 20, 'Eperanto');
INSERT INTO `ff_languages_names` VALUES (576, 24, 21, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (577, 24, 22, 'εσπεράντο');
INSERT INTO `ff_languages_names` VALUES (578, 24, 23, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (579, 24, 25, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (580, 24, 26, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (581, 24, 29, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (582, 24, 32, 'espéranto');
INSERT INTO `ff_languages_names` VALUES (583, 24, 40, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (584, 24, 42, 'Eszperantó');
INSERT INTO `ff_languages_names` VALUES (585, 24, 45, 'Esperantó');
INSERT INTO `ff_languages_names` VALUES (586, 24, 1, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (587, 24, 59, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (588, 24, 60, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (589, 24, 66, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (590, 24, 70, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (591, 24, 72, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (592, 24, 74, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (593, 24, 75, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (594, 24, 77, 'Esperanto');
INSERT INTO `ff_languages_names` VALUES (595, 24, 81, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (596, 24, 82, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (597, 24, 86, 'esperanto');
INSERT INTO `ff_languages_names` VALUES (598, 24, 89, 'Esperantoca');
INSERT INTO `ff_languages_names` VALUES (599, 25, 9, 'испански');
INSERT INTO `ff_languages_names` VALUES (600, 25, 17, 'španělština / kastilština');
INSERT INTO `ff_languages_names` VALUES (601, 25, 20, 'Spansk');
INSERT INTO `ff_languages_names` VALUES (602, 25, 21, 'Spanisch / Kastilisch');
INSERT INTO `ff_languages_names` VALUES (603, 25, 22, 'ισπανικά / καστιλιάνικα');
INSERT INTO `ff_languages_names` VALUES (604, 25, 23, 'Spanish / Castilian');
INSERT INTO `ff_languages_names` VALUES (605, 25, 25, 'español / castellano');
INSERT INTO `ff_languages_names` VALUES (606, 25, 26, 'hispaania / kastiilia');
INSERT INTO `ff_languages_names` VALUES (607, 25, 29, 'espanja / kastilian');
INSERT INTO `ff_languages_names` VALUES (608, 25, 32, 'espagnol / castillan');
INSERT INTO `ff_languages_names` VALUES (609, 25, 40, 'Španjolski / Kastiljski');
INSERT INTO `ff_languages_names` VALUES (610, 25, 42, 'Spanyol / Kasztíliai');
INSERT INTO `ff_languages_names` VALUES (611, 25, 45, 'Spænska / Kastilíska');
INSERT INTO `ff_languages_names` VALUES (612, 25, 1, 'Spagnolo / Castigliano');
INSERT INTO `ff_languages_names` VALUES (613, 25, 59, 'Ispanų / Kastilų');
INSERT INTO `ff_languages_names` VALUES (614, 25, 60, 'Spāņu / Kastīliešu');
INSERT INTO `ff_languages_names` VALUES (615, 25, 66, 'Spanjol / Kastilljan');
INSERT INTO `ff_languages_names` VALUES (616, 25, 70, 'Spaans / Castiliaans');
INSERT INTO `ff_languages_names` VALUES (617, 25, 72, 'Spansk / Kastiljansk');
INSERT INTO `ff_languages_names` VALUES (618, 25, 74, 'hiszpański / kastylijski');
INSERT INTO `ff_languages_names` VALUES (619, 25, 75, 'Espanhol / Castelhano');
INSERT INTO `ff_languages_names` VALUES (620, 25, 77, 'Spaniolă / Castiliană');
INSERT INTO `ff_languages_names` VALUES (621, 25, 81, 'španielčina / kastílčina');
INSERT INTO `ff_languages_names` VALUES (622, 25, 82, 'španščina / kastiljščina');
INSERT INTO `ff_languages_names` VALUES (623, 25, 86, 'spanska / kastiljanska');
INSERT INTO `ff_languages_names` VALUES (624, 25, 89, 'İspanyolca / Kastilyaca');
INSERT INTO `ff_languages_names` VALUES (625, 26, 9, 'естонски');
INSERT INTO `ff_languages_names` VALUES (626, 26, 17, 'estonština');
INSERT INTO `ff_languages_names` VALUES (627, 26, 20, 'Estisk');
INSERT INTO `ff_languages_names` VALUES (628, 26, 21, 'Estnisch');
INSERT INTO `ff_languages_names` VALUES (629, 26, 22, 'εσθονικά');
INSERT INTO `ff_languages_names` VALUES (630, 26, 23, 'Estonian');
INSERT INTO `ff_languages_names` VALUES (631, 26, 25, 'estonio');
INSERT INTO `ff_languages_names` VALUES (632, 26, 26, 'eesti');
INSERT INTO `ff_languages_names` VALUES (633, 26, 29, 'viro');
INSERT INTO `ff_languages_names` VALUES (634, 26, 32, 'estonien');
INSERT INTO `ff_languages_names` VALUES (635, 26, 40, 'Estonski');
INSERT INTO `ff_languages_names` VALUES (636, 26, 42, 'Észt');
INSERT INTO `ff_languages_names` VALUES (637, 26, 45, 'Eistneska');
INSERT INTO `ff_languages_names` VALUES (638, 26, 1, 'Estone');
INSERT INTO `ff_languages_names` VALUES (639, 26, 59, 'Estų');
INSERT INTO `ff_languages_names` VALUES (640, 26, 60, 'Igauņu');
INSERT INTO `ff_languages_names` VALUES (641, 26, 66, 'Estonjan');
INSERT INTO `ff_languages_names` VALUES (642, 26, 70, 'Estisch');
INSERT INTO `ff_languages_names` VALUES (643, 26, 72, 'Estisk');
INSERT INTO `ff_languages_names` VALUES (644, 26, 74, 'estoński');
INSERT INTO `ff_languages_names` VALUES (645, 26, 75, 'Estónio');
INSERT INTO `ff_languages_names` VALUES (646, 26, 77, 'Estoniană');
INSERT INTO `ff_languages_names` VALUES (647, 26, 81, 'estónčina');
INSERT INTO `ff_languages_names` VALUES (648, 26, 82, 'estonščina');
INSERT INTO `ff_languages_names` VALUES (649, 26, 86, 'estniska');
INSERT INTO `ff_languages_names` VALUES (650, 26, 89, 'Estonca');
INSERT INTO `ff_languages_names` VALUES (651, 27, 9, 'баски');
INSERT INTO `ff_languages_names` VALUES (652, 27, 17, 'baskičtina');
INSERT INTO `ff_languages_names` VALUES (653, 27, 20, 'Baskisk');
INSERT INTO `ff_languages_names` VALUES (654, 27, 21, 'Baskisch');
INSERT INTO `ff_languages_names` VALUES (655, 27, 22, 'βασκικά');
INSERT INTO `ff_languages_names` VALUES (656, 27, 23, 'Basque');
INSERT INTO `ff_languages_names` VALUES (657, 27, 25, 'eusquera');
INSERT INTO `ff_languages_names` VALUES (658, 27, 26, 'baski');
INSERT INTO `ff_languages_names` VALUES (659, 27, 29, 'baski');
INSERT INTO `ff_languages_names` VALUES (660, 27, 32, 'basque');
INSERT INTO `ff_languages_names` VALUES (661, 27, 40, 'baskijski');
INSERT INTO `ff_languages_names` VALUES (662, 27, 42, 'Baskír');
INSERT INTO `ff_languages_names` VALUES (663, 27, 45, 'Baskneska');
INSERT INTO `ff_languages_names` VALUES (664, 27, 1, 'basco');
INSERT INTO `ff_languages_names` VALUES (665, 27, 59, 'Baskų');
INSERT INTO `ff_languages_names` VALUES (666, 27, 60, 'Basku');
INSERT INTO `ff_languages_names` VALUES (667, 27, 66, 'Bask');
INSERT INTO `ff_languages_names` VALUES (668, 27, 70, 'Baskisch');
INSERT INTO `ff_languages_names` VALUES (669, 27, 72, 'Baskisk');
INSERT INTO `ff_languages_names` VALUES (670, 27, 74, 'baskijski');
INSERT INTO `ff_languages_names` VALUES (671, 27, 75, 'Basco');
INSERT INTO `ff_languages_names` VALUES (672, 27, 77, 'Bască');
INSERT INTO `ff_languages_names` VALUES (673, 27, 81, 'baskilčtina');
INSERT INTO `ff_languages_names` VALUES (674, 27, 82, 'baskovščina');
INSERT INTO `ff_languages_names` VALUES (675, 27, 86, 'baskiska');
INSERT INTO `ff_languages_names` VALUES (676, 27, 89, 'Baskca');
INSERT INTO `ff_languages_names` VALUES (677, 28, 9, 'персийски');
INSERT INTO `ff_languages_names` VALUES (678, 28, 17, 'perština');
INSERT INTO `ff_languages_names` VALUES (679, 28, 20, 'Persisk');
INSERT INTO `ff_languages_names` VALUES (680, 28, 21, 'Persisch');
INSERT INTO `ff_languages_names` VALUES (681, 28, 22, 'περσικά');
INSERT INTO `ff_languages_names` VALUES (682, 28, 23, 'Persian');
INSERT INTO `ff_languages_names` VALUES (683, 28, 25, 'persa');
INSERT INTO `ff_languages_names` VALUES (684, 28, 26, 'pärsia');
INSERT INTO `ff_languages_names` VALUES (685, 28, 29, 'farsi; persia');
INSERT INTO `ff_languages_names` VALUES (686, 28, 32, 'persan');
INSERT INTO `ff_languages_names` VALUES (687, 28, 40, 'perzijski');
INSERT INTO `ff_languages_names` VALUES (688, 28, 42, 'Perzsa');
INSERT INTO `ff_languages_names` VALUES (689, 28, 45, 'Persneska');
INSERT INTO `ff_languages_names` VALUES (690, 28, 1, 'Persiano');
INSERT INTO `ff_languages_names` VALUES (691, 28, 59, 'Persų');
INSERT INTO `ff_languages_names` VALUES (692, 28, 60, 'Persiešu');
INSERT INTO `ff_languages_names` VALUES (693, 28, 66, 'Persjan');
INSERT INTO `ff_languages_names` VALUES (694, 28, 70, 'Perzisch');
INSERT INTO `ff_languages_names` VALUES (695, 28, 72, 'Persisk');
INSERT INTO `ff_languages_names` VALUES (696, 28, 74, 'perski');
INSERT INTO `ff_languages_names` VALUES (697, 28, 75, 'Persa');
INSERT INTO `ff_languages_names` VALUES (698, 28, 77, 'Persană');
INSERT INTO `ff_languages_names` VALUES (699, 28, 81, 'perzština');
INSERT INTO `ff_languages_names` VALUES (700, 28, 82, 'perzijščina');
INSERT INTO `ff_languages_names` VALUES (701, 28, 86, 'persiska');
INSERT INTO `ff_languages_names` VALUES (702, 28, 89, 'Farsça');
INSERT INTO `ff_languages_names` VALUES (703, 29, 9, 'фински');
INSERT INTO `ff_languages_names` VALUES (704, 29, 17, 'finština');
INSERT INTO `ff_languages_names` VALUES (705, 29, 20, 'Finsk');
INSERT INTO `ff_languages_names` VALUES (706, 29, 21, 'Finnisch');
INSERT INTO `ff_languages_names` VALUES (707, 29, 22, 'φινλανδικά');
INSERT INTO `ff_languages_names` VALUES (708, 29, 23, 'Finnish');
INSERT INTO `ff_languages_names` VALUES (709, 29, 25, 'finés');
INSERT INTO `ff_languages_names` VALUES (710, 29, 26, 'soome');
INSERT INTO `ff_languages_names` VALUES (711, 29, 29, 'suomi');
INSERT INTO `ff_languages_names` VALUES (712, 29, 32, 'finnois');
INSERT INTO `ff_languages_names` VALUES (713, 29, 40, 'Finski');
INSERT INTO `ff_languages_names` VALUES (714, 29, 42, 'Finn');
INSERT INTO `ff_languages_names` VALUES (715, 29, 45, 'Finnska');
INSERT INTO `ff_languages_names` VALUES (716, 29, 1, 'Finnico');
INSERT INTO `ff_languages_names` VALUES (717, 29, 59, 'Suomių');
INSERT INTO `ff_languages_names` VALUES (718, 29, 60, 'Somu');
INSERT INTO `ff_languages_names` VALUES (719, 29, 66, 'Finlandiż');
INSERT INTO `ff_languages_names` VALUES (720, 29, 70, 'Fins');
INSERT INTO `ff_languages_names` VALUES (721, 29, 72, 'Finsk');
INSERT INTO `ff_languages_names` VALUES (722, 29, 74, 'fiński');
INSERT INTO `ff_languages_names` VALUES (723, 29, 75, 'Finlandês');
INSERT INTO `ff_languages_names` VALUES (724, 29, 77, 'Finlandeză');
INSERT INTO `ff_languages_names` VALUES (725, 29, 81, 'fínčina');
INSERT INTO `ff_languages_names` VALUES (726, 29, 82, 'finščina');
INSERT INTO `ff_languages_names` VALUES (727, 29, 86, 'finska');
INSERT INTO `ff_languages_names` VALUES (728, 29, 89, 'Fince');
INSERT INTO `ff_languages_names` VALUES (729, 30, 9, 'фиджийски');
INSERT INTO `ff_languages_names` VALUES (730, 30, 17, 'fidžijština');
INSERT INTO `ff_languages_names` VALUES (731, 30, 20, 'Fijian');
INSERT INTO `ff_languages_names` VALUES (732, 30, 21, 'Fidschi');
INSERT INTO `ff_languages_names` VALUES (733, 30, 22, 'γλώσσα νησιών Φίτζι');
INSERT INTO `ff_languages_names` VALUES (734, 30, 23, 'Fijian');
INSERT INTO `ff_languages_names` VALUES (735, 30, 25, 'fidji');
INSERT INTO `ff_languages_names` VALUES (736, 30, 26, 'fidži');
INSERT INTO `ff_languages_names` VALUES (737, 30, 29, 'fidži');
INSERT INTO `ff_languages_names` VALUES (738, 30, 32, 'fidjien');
INSERT INTO `ff_languages_names` VALUES (739, 30, 40, 'fidžijski');
INSERT INTO `ff_languages_names` VALUES (740, 30, 42, 'Fijian');
INSERT INTO `ff_languages_names` VALUES (741, 30, 45, 'Fijian');
INSERT INTO `ff_languages_names` VALUES (742, 30, 1, 'Fijian');
INSERT INTO `ff_languages_names` VALUES (743, 30, 59, 'Fidžių');
INSERT INTO `ff_languages_names` VALUES (744, 30, 60, 'Fidži');
INSERT INTO `ff_languages_names` VALUES (745, 30, 66, 'Fiġjan');
INSERT INTO `ff_languages_names` VALUES (746, 30, 70, 'Fijisch');
INSERT INTO `ff_languages_names` VALUES (747, 30, 72, 'Fijisk');
INSERT INTO `ff_languages_names` VALUES (748, 30, 74, 'fidżyjski');
INSERT INTO `ff_languages_names` VALUES (749, 30, 75, 'Fidjiano');
INSERT INTO `ff_languages_names` VALUES (750, 30, 77, 'Fiji');
INSERT INTO `ff_languages_names` VALUES (751, 30, 81, 'fidžijčina');
INSERT INTO `ff_languages_names` VALUES (752, 30, 82, 'fidžijščina');
INSERT INTO `ff_languages_names` VALUES (753, 30, 86, 'fidjianska');
INSERT INTO `ff_languages_names` VALUES (754, 30, 89, 'Fijice');
INSERT INTO `ff_languages_names` VALUES (755, 31, 9, 'фарьорски');
INSERT INTO `ff_languages_names` VALUES (756, 31, 17, 'faerština');
INSERT INTO `ff_languages_names` VALUES (757, 31, 20, 'Færøsk');
INSERT INTO `ff_languages_names` VALUES (758, 31, 21, 'Färöisch');
INSERT INTO `ff_languages_names` VALUES (759, 31, 22, 'φαροεζικά');
INSERT INTO `ff_languages_names` VALUES (760, 31, 23, 'Faroese');
INSERT INTO `ff_languages_names` VALUES (761, 31, 25, 'feroés');
INSERT INTO `ff_languages_names` VALUES (762, 31, 26, 'fääri');
INSERT INTO `ff_languages_names` VALUES (763, 31, 29, 'fääri');
INSERT INTO `ff_languages_names` VALUES (764, 31, 32, 'féroïen');
INSERT INTO `ff_languages_names` VALUES (765, 31, 40, 'ferojski');
INSERT INTO `ff_languages_names` VALUES (766, 31, 42, 'Faroese');
INSERT INTO `ff_languages_names` VALUES (767, 31, 45, 'Færeyska');
INSERT INTO `ff_languages_names` VALUES (768, 31, 1, 'Faroese');
INSERT INTO `ff_languages_names` VALUES (769, 31, 59, 'Farerų');
INSERT INTO `ff_languages_names` VALUES (770, 31, 60, 'Faroju');
INSERT INTO `ff_languages_names` VALUES (771, 31, 66, 'Fawriż');
INSERT INTO `ff_languages_names` VALUES (772, 31, 70, 'Faeröers');
INSERT INTO `ff_languages_names` VALUES (773, 31, 72, 'Færøysk');
INSERT INTO `ff_languages_names` VALUES (774, 31, 74, 'farerski');
INSERT INTO `ff_languages_names` VALUES (775, 31, 75, 'Feroês');
INSERT INTO `ff_languages_names` VALUES (776, 31, 77, 'Feroeză / Faroeză');
INSERT INTO `ff_languages_names` VALUES (777, 31, 81, 'faerčina');
INSERT INTO `ff_languages_names` VALUES (778, 31, 82, 'ferščina');
INSERT INTO `ff_languages_names` VALUES (779, 31, 86, 'färoiska');
INSERT INTO `ff_languages_names` VALUES (780, 31, 89, 'Faraoece');
INSERT INTO `ff_languages_names` VALUES (781, 32, 9, 'френски');
INSERT INTO `ff_languages_names` VALUES (782, 32, 17, 'francouzština');
INSERT INTO `ff_languages_names` VALUES (783, 32, 20, 'Fransk');
INSERT INTO `ff_languages_names` VALUES (784, 32, 21, 'Französisch');
INSERT INTO `ff_languages_names` VALUES (785, 32, 22, 'γαλλικά');
INSERT INTO `ff_languages_names` VALUES (786, 32, 23, 'French');
INSERT INTO `ff_languages_names` VALUES (787, 32, 25, 'francés');
INSERT INTO `ff_languages_names` VALUES (788, 32, 26, 'prantsuse');
INSERT INTO `ff_languages_names` VALUES (789, 32, 29, 'ranska');
INSERT INTO `ff_languages_names` VALUES (790, 32, 32, 'français');
INSERT INTO `ff_languages_names` VALUES (791, 32, 40, 'Francuski');
INSERT INTO `ff_languages_names` VALUES (792, 32, 42, 'Francia');
INSERT INTO `ff_languages_names` VALUES (793, 32, 45, 'Franska');
INSERT INTO `ff_languages_names` VALUES (794, 32, 1, 'Francese');
INSERT INTO `ff_languages_names` VALUES (795, 32, 59, 'Prancūzų');
INSERT INTO `ff_languages_names` VALUES (796, 32, 60, 'Franču');
INSERT INTO `ff_languages_names` VALUES (797, 32, 66, 'Franċiż');
INSERT INTO `ff_languages_names` VALUES (798, 32, 70, 'Frans');
INSERT INTO `ff_languages_names` VALUES (799, 32, 72, 'Fransk');
INSERT INTO `ff_languages_names` VALUES (800, 32, 74, 'francuski');
INSERT INTO `ff_languages_names` VALUES (801, 32, 75, 'Francês');
INSERT INTO `ff_languages_names` VALUES (802, 32, 77, 'Franceză');
INSERT INTO `ff_languages_names` VALUES (803, 32, 81, 'francúzština');
INSERT INTO `ff_languages_names` VALUES (804, 32, 82, 'francoščina');
INSERT INTO `ff_languages_names` VALUES (805, 32, 86, 'franska');
INSERT INTO `ff_languages_names` VALUES (806, 32, 89, 'Fransızca');
INSERT INTO `ff_languages_names` VALUES (807, 33, 9, 'фризийски');
INSERT INTO `ff_languages_names` VALUES (808, 33, 17, 'západní fríština');
INSERT INTO `ff_languages_names` VALUES (809, 33, 20, 'Frisisk');
INSERT INTO `ff_languages_names` VALUES (810, 33, 21, 'Friesisch');
INSERT INTO `ff_languages_names` VALUES (811, 33, 22, 'φριζικά (Δυτική Φριζία)');
INSERT INTO `ff_languages_names` VALUES (812, 33, 23, 'Western Frisian');
INSERT INTO `ff_languages_names` VALUES (813, 33, 25, 'frisón occidental');
INSERT INTO `ff_languages_names` VALUES (814, 33, 26, 'läänefriisi');
INSERT INTO `ff_languages_names` VALUES (815, 33, 29, 'friisi');
INSERT INTO `ff_languages_names` VALUES (816, 33, 32, 'frison occidental');
INSERT INTO `ff_languages_names` VALUES (817, 33, 40, 'frizijski');
INSERT INTO `ff_languages_names` VALUES (818, 33, 42, 'Nyugati fríz');
INSERT INTO `ff_languages_names` VALUES (819, 33, 45, 'Frísneska');
INSERT INTO `ff_languages_names` VALUES (820, 33, 1, 'Frisone');
INSERT INTO `ff_languages_names` VALUES (821, 33, 59, 'Vakarų fryzų');
INSERT INTO `ff_languages_names` VALUES (822, 33, 60, 'Rietumfrīzu');
INSERT INTO `ff_languages_names` VALUES (823, 33, 66, 'Friżjan tal-Punent');
INSERT INTO `ff_languages_names` VALUES (824, 33, 70, 'Fries');
INSERT INTO `ff_languages_names` VALUES (825, 33, 72, 'Frisisk');
INSERT INTO `ff_languages_names` VALUES (826, 33, 74, 'zachodniofryzyjski');
INSERT INTO `ff_languages_names` VALUES (827, 33, 75, 'Frísio Ocidental');
INSERT INTO `ff_languages_names` VALUES (828, 33, 77, 'Frisiană');
INSERT INTO `ff_languages_names` VALUES (829, 33, 81, 'frízština');
INSERT INTO `ff_languages_names` VALUES (830, 33, 82, 'zahodna frizijščina');
INSERT INTO `ff_languages_names` VALUES (831, 33, 86, 'västfrisiska');
INSERT INTO `ff_languages_names` VALUES (832, 33, 89, 'Frizyaca');
INSERT INTO `ff_languages_names` VALUES (833, 34, 9, 'ирландски');
INSERT INTO `ff_languages_names` VALUES (834, 34, 17, 'irština');
INSERT INTO `ff_languages_names` VALUES (835, 34, 20, 'Irsk');
INSERT INTO `ff_languages_names` VALUES (836, 34, 21, 'Irisch');
INSERT INTO `ff_languages_names` VALUES (837, 34, 22, 'ιρλανδικά');
INSERT INTO `ff_languages_names` VALUES (838, 34, 23, 'Irish');
INSERT INTO `ff_languages_names` VALUES (839, 34, 25, 'irlandés');
INSERT INTO `ff_languages_names` VALUES (840, 34, 26, 'iiri');
INSERT INTO `ff_languages_names` VALUES (841, 34, 29, 'iiri');
INSERT INTO `ff_languages_names` VALUES (842, 34, 32, 'irlandais');
INSERT INTO `ff_languages_names` VALUES (843, 34, 40, 'irski');
INSERT INTO `ff_languages_names` VALUES (844, 34, 42, 'Ír');
INSERT INTO `ff_languages_names` VALUES (845, 34, 45, 'Írska');
INSERT INTO `ff_languages_names` VALUES (846, 34, 1, 'Gaelico irlandese');
INSERT INTO `ff_languages_names` VALUES (847, 34, 59, 'Airių');
INSERT INTO `ff_languages_names` VALUES (848, 34, 60, 'Īru');
INSERT INTO `ff_languages_names` VALUES (849, 34, 66, 'Irlandiż');
INSERT INTO `ff_languages_names` VALUES (850, 34, 70, 'Iers');
INSERT INTO `ff_languages_names` VALUES (851, 34, 72, 'Irsk');
INSERT INTO `ff_languages_names` VALUES (852, 34, 74, 'irlandzki');
INSERT INTO `ff_languages_names` VALUES (853, 34, 75, 'Irlandês');
INSERT INTO `ff_languages_names` VALUES (854, 34, 77, 'Irlandeză');
INSERT INTO `ff_languages_names` VALUES (855, 34, 81, 'írčina');
INSERT INTO `ff_languages_names` VALUES (856, 34, 82, 'irščina');
INSERT INTO `ff_languages_names` VALUES (857, 34, 86, 'iriska');
INSERT INTO `ff_languages_names` VALUES (858, 34, 89, 'İrlandaca');
INSERT INTO `ff_languages_names` VALUES (859, 35, 9, 'шотландски');
INSERT INTO `ff_languages_names` VALUES (860, 35, 17, 'skotština');
INSERT INTO `ff_languages_names` VALUES (861, 35, 20, 'Gælisk / Skotsk');
INSERT INTO `ff_languages_names` VALUES (862, 35, 21, 'Schottisch-Gälisch');
INSERT INTO `ff_languages_names` VALUES (863, 35, 22, 'σκωτικά');
INSERT INTO `ff_languages_names` VALUES (864, 35, 23, 'Gaelic / Scottish Gaelic');
INSERT INTO `ff_languages_names` VALUES (865, 35, 25, 'gaélico / gaélico escocés');
INSERT INTO `ff_languages_names` VALUES (866, 35, 26, 'gaeli');
INSERT INTO `ff_languages_names` VALUES (867, 35, 29, 'gaeli');
INSERT INTO `ff_languages_names` VALUES (868, 35, 32, 'gaélique / gaélique écossais');
INSERT INTO `ff_languages_names` VALUES (869, 35, 40, 'škotski gaelski');
INSERT INTO `ff_languages_names` VALUES (870, 35, 42, 'Gael / skót gael');
INSERT INTO `ff_languages_names` VALUES (871, 35, 45, 'Scottish Gaelic');
INSERT INTO `ff_languages_names` VALUES (872, 35, 1, 'Gaelico scozzese');
INSERT INTO `ff_languages_names` VALUES (873, 35, 59, 'Gėlų / Škotijos gėlų');
INSERT INTO `ff_languages_names` VALUES (874, 35, 60, 'Gēļu / skotu gēļu');
INSERT INTO `ff_languages_names` VALUES (875, 35, 66, 'Galliku / Galliku Skoċċiż');
INSERT INTO `ff_languages_names` VALUES (876, 35, 70, 'Gaelic / Schots Gaelic');
INSERT INTO `ff_languages_names` VALUES (877, 35, 72, 'Skotsk / Gælisk');
INSERT INTO `ff_languages_names` VALUES (878, 35, 74, 'gaelicki / gaelski szkocki');
INSERT INTO `ff_languages_names` VALUES (879, 35, 75, 'Gaélico / Gaélico Escocês');
INSERT INTO `ff_languages_names` VALUES (880, 35, 77, 'Gaelica Scoţiană');
INSERT INTO `ff_languages_names` VALUES (881, 35, 81, 'škótska gaelčina');
INSERT INTO `ff_languages_names` VALUES (882, 35, 82, 'gelščina / škotska gelščina');
INSERT INTO `ff_languages_names` VALUES (883, 35, 86, 'gaeliska / skotsk gaeliska');
INSERT INTO `ff_languages_names` VALUES (884, 35, 89, 'Gaelce');
INSERT INTO `ff_languages_names` VALUES (885, 36, 9, 'галисийски');
INSERT INTO `ff_languages_names` VALUES (886, 36, 17, 'galicijština');
INSERT INTO `ff_languages_names` VALUES (887, 36, 20, 'Gallegan');
INSERT INTO `ff_languages_names` VALUES (888, 36, 21, 'Galicisch');
INSERT INTO `ff_languages_names` VALUES (889, 36, 22, 'γαλικιανά');
INSERT INTO `ff_languages_names` VALUES (890, 36, 23, 'Galician');
INSERT INTO `ff_languages_names` VALUES (891, 36, 25, 'gallego');
INSERT INTO `ff_languages_names` VALUES (892, 36, 26, 'galeegi');
INSERT INTO `ff_languages_names` VALUES (893, 36, 29, 'galicia');
INSERT INTO `ff_languages_names` VALUES (894, 36, 32, 'galicien');
INSERT INTO `ff_languages_names` VALUES (895, 36, 40, 'galicijski');
INSERT INTO `ff_languages_names` VALUES (896, 36, 42, 'Galíciai');
INSERT INTO `ff_languages_names` VALUES (897, 36, 45, 'Galician');
INSERT INTO `ff_languages_names` VALUES (898, 36, 1, 'Galiziano');
INSERT INTO `ff_languages_names` VALUES (899, 36, 59, 'Galisų');
INSERT INTO `ff_languages_names` VALUES (900, 36, 60, 'Gallu');
INSERT INTO `ff_languages_names` VALUES (901, 36, 66, 'Galizzjan');
INSERT INTO `ff_languages_names` VALUES (902, 36, 70, 'Galicisch');
INSERT INTO `ff_languages_names` VALUES (903, 36, 72, 'Galisisk');
INSERT INTO `ff_languages_names` VALUES (904, 36, 74, 'galisyjski');
INSERT INTO `ff_languages_names` VALUES (905, 36, 75, 'Galego');
INSERT INTO `ff_languages_names` VALUES (906, 36, 77, 'Galică');
INSERT INTO `ff_languages_names` VALUES (907, 36, 81, 'galicijčina');
INSERT INTO `ff_languages_names` VALUES (908, 36, 82, 'galicijščina');
INSERT INTO `ff_languages_names` VALUES (909, 36, 86, 'galiciska');
INSERT INTO `ff_languages_names` VALUES (910, 36, 89, 'Galicyaca');
INSERT INTO `ff_languages_names` VALUES (911, 37, 9, 'манкс');
INSERT INTO `ff_languages_names` VALUES (912, 37, 17, 'manština');
INSERT INTO `ff_languages_names` VALUES (913, 37, 20, 'Manx');
INSERT INTO `ff_languages_names` VALUES (914, 37, 21, 'Manxs');
INSERT INTO `ff_languages_names` VALUES (915, 37, 22, 'γλώσσα Νήσου Μαν');
INSERT INTO `ff_languages_names` VALUES (916, 37, 23, 'Manx');
INSERT INTO `ff_languages_names` VALUES (917, 37, 25, 'manés');
INSERT INTO `ff_languages_names` VALUES (918, 37, 26, 'mänksi');
INSERT INTO `ff_languages_names` VALUES (919, 37, 29, 'manx');
INSERT INTO `ff_languages_names` VALUES (920, 37, 32, 'manx; mannois');
INSERT INTO `ff_languages_names` VALUES (921, 37, 40, 'manski');
INSERT INTO `ff_languages_names` VALUES (922, 37, 42, 'Manx');
INSERT INTO `ff_languages_names` VALUES (923, 37, 45, 'Manx');
INSERT INTO `ff_languages_names` VALUES (924, 37, 1, 'Manx');
INSERT INTO `ff_languages_names` VALUES (925, 37, 59, 'Manksų');
INSERT INTO `ff_languages_names` VALUES (926, 37, 60, 'Menksu');
INSERT INTO `ff_languages_names` VALUES (927, 37, 66, 'Manks');
INSERT INTO `ff_languages_names` VALUES (928, 37, 70, 'Manx');
INSERT INTO `ff_languages_names` VALUES (929, 37, 72, 'Mansk');
INSERT INTO `ff_languages_names` VALUES (930, 37, 74, 'manx');
INSERT INTO `ff_languages_names` VALUES (931, 37, 75, 'Manx');
INSERT INTO `ff_languages_names` VALUES (932, 37, 77, 'Limba manx');
INSERT INTO `ff_languages_names` VALUES (933, 37, 81, 'mančina');
INSERT INTO `ff_languages_names` VALUES (934, 37, 82, 'manska gelščina');
INSERT INTO `ff_languages_names` VALUES (935, 37, 86, 'manx');
INSERT INTO `ff_languages_names` VALUES (936, 37, 89, 'Manksça');
INSERT INTO `ff_languages_names` VALUES (937, 38, 9, 'иврит');
INSERT INTO `ff_languages_names` VALUES (938, 38, 17, 'hebrejština');
INSERT INTO `ff_languages_names` VALUES (939, 38, 20, 'Hebraisk');
INSERT INTO `ff_languages_names` VALUES (940, 38, 21, 'Hebräisch');
INSERT INTO `ff_languages_names` VALUES (941, 38, 22, 'εβραϊκά');
INSERT INTO `ff_languages_names` VALUES (942, 38, 23, 'Hebrew');
INSERT INTO `ff_languages_names` VALUES (943, 38, 25, 'hebreo');
INSERT INTO `ff_languages_names` VALUES (944, 38, 26, 'heebrea');
INSERT INTO `ff_languages_names` VALUES (945, 38, 29, 'heprea');
INSERT INTO `ff_languages_names` VALUES (946, 38, 32, 'hébreu');
INSERT INTO `ff_languages_names` VALUES (947, 38, 40, 'hebrejski');
INSERT INTO `ff_languages_names` VALUES (948, 38, 42, 'Héber');
INSERT INTO `ff_languages_names` VALUES (949, 38, 45, 'Hebreska');
INSERT INTO `ff_languages_names` VALUES (950, 38, 1, 'Ebraico');
INSERT INTO `ff_languages_names` VALUES (951, 38, 59, 'Hebrajų');
INSERT INTO `ff_languages_names` VALUES (952, 38, 60, 'Ēbreju');
INSERT INTO `ff_languages_names` VALUES (953, 38, 66, 'Ebrajk');
INSERT INTO `ff_languages_names` VALUES (954, 38, 70, 'Hebreeuws');
INSERT INTO `ff_languages_names` VALUES (955, 38, 72, 'Hebraisk');
INSERT INTO `ff_languages_names` VALUES (956, 38, 74, 'hebrajski');
INSERT INTO `ff_languages_names` VALUES (957, 38, 75, 'Hebraico');
INSERT INTO `ff_languages_names` VALUES (958, 38, 77, 'Ebraică');
INSERT INTO `ff_languages_names` VALUES (959, 38, 81, 'hebrejčina');
INSERT INTO `ff_languages_names` VALUES (960, 38, 82, 'hebrejščina');
INSERT INTO `ff_languages_names` VALUES (961, 38, 86, 'hebreiska');
INSERT INTO `ff_languages_names` VALUES (962, 38, 89, 'İbranice');
INSERT INTO `ff_languages_names` VALUES (963, 39, 9, 'хинди');
INSERT INTO `ff_languages_names` VALUES (964, 39, 17, 'hindština');
INSERT INTO `ff_languages_names` VALUES (965, 39, 20, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (966, 39, 21, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (967, 39, 22, 'χίντι');
INSERT INTO `ff_languages_names` VALUES (968, 39, 23, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (969, 39, 25, 'hindi');
INSERT INTO `ff_languages_names` VALUES (970, 39, 26, 'hindi');
INSERT INTO `ff_languages_names` VALUES (971, 39, 29, 'hindi');
INSERT INTO `ff_languages_names` VALUES (972, 39, 32, 'hindi');
INSERT INTO `ff_languages_names` VALUES (973, 39, 40, 'hindski');
INSERT INTO `ff_languages_names` VALUES (974, 39, 42, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (975, 39, 45, 'Hindí');
INSERT INTO `ff_languages_names` VALUES (976, 39, 1, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (977, 39, 59, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (978, 39, 60, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (979, 39, 66, 'Ħindi');
INSERT INTO `ff_languages_names` VALUES (980, 39, 70, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (981, 39, 72, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (982, 39, 74, 'hindi');
INSERT INTO `ff_languages_names` VALUES (983, 39, 75, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (984, 39, 77, 'Hindi');
INSERT INTO `ff_languages_names` VALUES (985, 39, 81, 'hindčina');
INSERT INTO `ff_languages_names` VALUES (986, 39, 82, 'hindijščina');
INSERT INTO `ff_languages_names` VALUES (987, 39, 86, 'hindi');
INSERT INTO `ff_languages_names` VALUES (988, 39, 89, 'Hintçe');
INSERT INTO `ff_languages_names` VALUES (989, 40, 9, 'хърватски');
INSERT INTO `ff_languages_names` VALUES (990, 40, 17, 'chorvatština');
INSERT INTO `ff_languages_names` VALUES (991, 40, 20, 'Kroatisk');
INSERT INTO `ff_languages_names` VALUES (992, 40, 21, 'Kroatisch');
INSERT INTO `ff_languages_names` VALUES (993, 40, 22, 'κροατικά');
INSERT INTO `ff_languages_names` VALUES (994, 40, 23, 'Croatian');
INSERT INTO `ff_languages_names` VALUES (995, 40, 25, 'croata');
INSERT INTO `ff_languages_names` VALUES (996, 40, 26, 'horvaadi');
INSERT INTO `ff_languages_names` VALUES (997, 40, 29, 'kroatia');
INSERT INTO `ff_languages_names` VALUES (998, 40, 32, 'croate');
INSERT INTO `ff_languages_names` VALUES (999, 40, 40, 'Hrvatski');
INSERT INTO `ff_languages_names` VALUES (1000, 40, 42, 'Horvát');
INSERT INTO `ff_languages_names` VALUES (1001, 40, 45, 'króatíska');
INSERT INTO `ff_languages_names` VALUES (1002, 40, 1, 'Croato');
INSERT INTO `ff_languages_names` VALUES (1003, 40, 59, 'Kroatų');
INSERT INTO `ff_languages_names` VALUES (1004, 40, 60, 'Horvātu');
INSERT INTO `ff_languages_names` VALUES (1005, 40, 66, 'Kroat');
INSERT INTO `ff_languages_names` VALUES (1006, 40, 70, 'Kroatisch');
INSERT INTO `ff_languages_names` VALUES (1007, 40, 72, 'Kroatisk');
INSERT INTO `ff_languages_names` VALUES (1008, 40, 74, 'chorwacki');
INSERT INTO `ff_languages_names` VALUES (1009, 40, 75, 'Croata');
INSERT INTO `ff_languages_names` VALUES (1010, 40, 77, 'Croată');
INSERT INTO `ff_languages_names` VALUES (1011, 40, 81, 'chorvátčina');
INSERT INTO `ff_languages_names` VALUES (1012, 40, 82, 'hrvaščina');
INSERT INTO `ff_languages_names` VALUES (1013, 40, 86, 'kroatiska');
INSERT INTO `ff_languages_names` VALUES (1014, 40, 89, 'Hırvatça');
INSERT INTO `ff_languages_names` VALUES (1015, 41, 9, 'хаитянски');
INSERT INTO `ff_languages_names` VALUES (1016, 41, 17, 'haitská kreolština');
INSERT INTO `ff_languages_names` VALUES (1017, 41, 20, 'Haitisk');
INSERT INTO `ff_languages_names` VALUES (1018, 41, 21, 'Haitianisch');
INSERT INTO `ff_languages_names` VALUES (1019, 41, 22, 'Κρεολή Αϊτής+G94');
INSERT INTO `ff_languages_names` VALUES (1020, 41, 23, 'Haitian; Haitian Creole');
INSERT INTO `ff_languages_names` VALUES (1021, 41, 25, 'haitiano, criollo haitiano');
INSERT INTO `ff_languages_names` VALUES (1022, 41, 26, 'haiti; haiti kreooli');
INSERT INTO `ff_languages_names` VALUES (1023, 41, 29, 'haiti');
INSERT INTO `ff_languages_names` VALUES (1024, 41, 32, 'haïtien; créole haïtien');
INSERT INTO `ff_languages_names` VALUES (1025, 41, 40, 'haićanski');
INSERT INTO `ff_languages_names` VALUES (1026, 41, 42, 'Haiti; haiti kreol');
INSERT INTO `ff_languages_names` VALUES (1027, 41, 45, 'Haitian');
INSERT INTO `ff_languages_names` VALUES (1028, 41, 1, 'Creolo Haitiano; Haitiano');
INSERT INTO `ff_languages_names` VALUES (1029, 41, 59, 'Haitiečių; Haičio kreolų');
INSERT INTO `ff_languages_names` VALUES (1030, 41, 60, 'Haiti; Haiti kreolu');
INSERT INTO `ff_languages_names` VALUES (1031, 41, 66, 'Ħaitjan; Kreol tal-Ħaiti');
INSERT INTO `ff_languages_names` VALUES (1032, 41, 70, 'Haïtiaans; Haïtiaans Creools');
INSERT INTO `ff_languages_names` VALUES (1033, 41, 72, 'Haitisk kreolsk');
INSERT INTO `ff_languages_names` VALUES (1034, 41, 74, 'haitański; kreolski haitański');
INSERT INTO `ff_languages_names` VALUES (1035, 41, 75, 'Haitiano; Criolo Haitiano');
INSERT INTO `ff_languages_names` VALUES (1036, 41, 77, 'Haitiană');
INSERT INTO `ff_languages_names` VALUES (1037, 41, 81, 'haitská francúzska kreolčina');
INSERT INTO `ff_languages_names` VALUES (1038, 41, 82, 'haitščina; haitska kreolščina');
INSERT INTO `ff_languages_names` VALUES (1039, 41, 86, 'haitiska; haitisk kreolska');
INSERT INTO `ff_languages_names` VALUES (1040, 41, 89, 'Haitice');
INSERT INTO `ff_languages_names` VALUES (1041, 42, 9, 'унгарски');
INSERT INTO `ff_languages_names` VALUES (1042, 42, 17, 'maďarština');
INSERT INTO `ff_languages_names` VALUES (1043, 42, 20, 'Ungarsk');
INSERT INTO `ff_languages_names` VALUES (1044, 42, 21, 'Ungarisch');
INSERT INTO `ff_languages_names` VALUES (1045, 42, 22, 'ουγρικά');
INSERT INTO `ff_languages_names` VALUES (1046, 42, 23, 'Hungarian');
INSERT INTO `ff_languages_names` VALUES (1047, 42, 25, 'húngaro');
INSERT INTO `ff_languages_names` VALUES (1048, 42, 26, 'ungari');
INSERT INTO `ff_languages_names` VALUES (1049, 42, 29, 'unkari');
INSERT INTO `ff_languages_names` VALUES (1050, 42, 32, 'hongrois');
INSERT INTO `ff_languages_names` VALUES (1051, 42, 40, 'Mađarski');
INSERT INTO `ff_languages_names` VALUES (1052, 42, 42, 'Magyar');
INSERT INTO `ff_languages_names` VALUES (1053, 42, 45, 'Ungverska');
INSERT INTO `ff_languages_names` VALUES (1054, 42, 1, 'Ungherese');
INSERT INTO `ff_languages_names` VALUES (1055, 42, 59, 'Vengrų');
INSERT INTO `ff_languages_names` VALUES (1056, 42, 60, 'Ungāru');
INSERT INTO `ff_languages_names` VALUES (1057, 42, 66, 'Ungeriż');
INSERT INTO `ff_languages_names` VALUES (1058, 42, 70, 'Hongaars');
INSERT INTO `ff_languages_names` VALUES (1059, 42, 72, 'Ungarsk');
INSERT INTO `ff_languages_names` VALUES (1060, 42, 74, 'węgierski');
INSERT INTO `ff_languages_names` VALUES (1061, 42, 75, 'Húngaro');
INSERT INTO `ff_languages_names` VALUES (1062, 42, 77, 'Maghiară');
INSERT INTO `ff_languages_names` VALUES (1063, 42, 81, 'maďarčina');
INSERT INTO `ff_languages_names` VALUES (1064, 42, 82, 'madžarščina');
INSERT INTO `ff_languages_names` VALUES (1065, 42, 86, 'ungerska');
INSERT INTO `ff_languages_names` VALUES (1066, 42, 89, 'Macarca');
INSERT INTO `ff_languages_names` VALUES (1067, 43, 9, 'арменски');
INSERT INTO `ff_languages_names` VALUES (1068, 43, 17, 'arménština');
INSERT INTO `ff_languages_names` VALUES (1069, 43, 20, 'Armensk');
INSERT INTO `ff_languages_names` VALUES (1070, 43, 21, 'Armenisch');
INSERT INTO `ff_languages_names` VALUES (1071, 43, 22, 'αρμενικά');
INSERT INTO `ff_languages_names` VALUES (1072, 43, 23, 'Armenian');
INSERT INTO `ff_languages_names` VALUES (1073, 43, 25, 'armenio');
INSERT INTO `ff_languages_names` VALUES (1074, 43, 26, 'armeenia');
INSERT INTO `ff_languages_names` VALUES (1075, 43, 29, 'armenia');
INSERT INTO `ff_languages_names` VALUES (1076, 43, 32, 'arménien');
INSERT INTO `ff_languages_names` VALUES (1077, 43, 40, 'armenski');
INSERT INTO `ff_languages_names` VALUES (1078, 43, 42, 'Örmény');
INSERT INTO `ff_languages_names` VALUES (1079, 43, 45, 'Armenska');
INSERT INTO `ff_languages_names` VALUES (1080, 43, 1, 'Armeno');
INSERT INTO `ff_languages_names` VALUES (1081, 43, 59, 'Armėnų');
INSERT INTO `ff_languages_names` VALUES (1082, 43, 60, 'Armēņu');
INSERT INTO `ff_languages_names` VALUES (1083, 43, 66, 'Armen');
INSERT INTO `ff_languages_names` VALUES (1084, 43, 70, 'Armeens');
INSERT INTO `ff_languages_names` VALUES (1085, 43, 72, 'Armensk');
INSERT INTO `ff_languages_names` VALUES (1086, 43, 74, 'ormiański');
INSERT INTO `ff_languages_names` VALUES (1087, 43, 75, 'Arménio');
INSERT INTO `ff_languages_names` VALUES (1088, 43, 77, 'Armeană');
INSERT INTO `ff_languages_names` VALUES (1089, 43, 81, 'arménčina');
INSERT INTO `ff_languages_names` VALUES (1090, 43, 82, 'armenščina');
INSERT INTO `ff_languages_names` VALUES (1091, 43, 86, 'armeniska');
INSERT INTO `ff_languages_names` VALUES (1092, 43, 89, 'Ermenice');
INSERT INTO `ff_languages_names` VALUES (1093, 44, 9, 'индонезийски');
INSERT INTO `ff_languages_names` VALUES (1094, 44, 17, 'indonéština');
INSERT INTO `ff_languages_names` VALUES (1095, 44, 20, 'Indonesisk');
INSERT INTO `ff_languages_names` VALUES (1096, 44, 21, 'Bahasa Indonesia');
INSERT INTO `ff_languages_names` VALUES (1097, 44, 22, 'ινδονησιακά');
INSERT INTO `ff_languages_names` VALUES (1098, 44, 23, 'Indonesian');
INSERT INTO `ff_languages_names` VALUES (1099, 44, 25, 'indonesio');
INSERT INTO `ff_languages_names` VALUES (1100, 44, 26, 'indoneesia');
INSERT INTO `ff_languages_names` VALUES (1101, 44, 29, 'indonesia');
INSERT INTO `ff_languages_names` VALUES (1102, 44, 32, 'indonésien');
INSERT INTO `ff_languages_names` VALUES (1103, 44, 40, 'indonezijski');
INSERT INTO `ff_languages_names` VALUES (1104, 44, 42, 'Indonéziai');
INSERT INTO `ff_languages_names` VALUES (1105, 44, 45, 'Indonesian');
INSERT INTO `ff_languages_names` VALUES (1106, 44, 1, 'Indonesiano');
INSERT INTO `ff_languages_names` VALUES (1107, 44, 59, 'Indoneziečių');
INSERT INTO `ff_languages_names` VALUES (1108, 44, 60, 'Indonēziešu');
INSERT INTO `ff_languages_names` VALUES (1109, 44, 66, 'Indoneżjan');
INSERT INTO `ff_languages_names` VALUES (1110, 44, 70, 'Indonesisch');
INSERT INTO `ff_languages_names` VALUES (1111, 44, 72, 'Indonesisk');
INSERT INTO `ff_languages_names` VALUES (1112, 44, 74, 'indonezyjski');
INSERT INTO `ff_languages_names` VALUES (1113, 44, 75, 'Indonésio');
INSERT INTO `ff_languages_names` VALUES (1114, 44, 77, 'Indoneziană');
INSERT INTO `ff_languages_names` VALUES (1115, 44, 81, 'indonézsky jazyk');
INSERT INTO `ff_languages_names` VALUES (1116, 44, 82, 'indonezijščina');
INSERT INTO `ff_languages_names` VALUES (1117, 44, 86, 'indonesiska');
INSERT INTO `ff_languages_names` VALUES (1118, 44, 89, 'Endonezce');
INSERT INTO `ff_languages_names` VALUES (1119, 45, 9, 'исландски');
INSERT INTO `ff_languages_names` VALUES (1120, 45, 17, 'islandština');
INSERT INTO `ff_languages_names` VALUES (1121, 45, 20, 'Islandsk');
INSERT INTO `ff_languages_names` VALUES (1122, 45, 21, 'Isländisch');
INSERT INTO `ff_languages_names` VALUES (1123, 45, 22, 'ισλανδικά');
INSERT INTO `ff_languages_names` VALUES (1124, 45, 23, 'Icelandic');
INSERT INTO `ff_languages_names` VALUES (1125, 45, 25, 'islandés');
INSERT INTO `ff_languages_names` VALUES (1126, 45, 26, 'islandi');
INSERT INTO `ff_languages_names` VALUES (1127, 45, 29, 'islanti');
INSERT INTO `ff_languages_names` VALUES (1128, 45, 32, 'islandais');
INSERT INTO `ff_languages_names` VALUES (1129, 45, 40, 'Islandski');
INSERT INTO `ff_languages_names` VALUES (1130, 45, 42, 'Izlandi');
INSERT INTO `ff_languages_names` VALUES (1131, 45, 45, 'Íslenska');
INSERT INTO `ff_languages_names` VALUES (1132, 45, 1, 'Islandese');
INSERT INTO `ff_languages_names` VALUES (1133, 45, 59, 'Islandų');
INSERT INTO `ff_languages_names` VALUES (1134, 45, 60, 'Islandiešu');
INSERT INTO `ff_languages_names` VALUES (1135, 45, 66, 'Iżlandiż');
INSERT INTO `ff_languages_names` VALUES (1136, 45, 70, 'IJslands');
INSERT INTO `ff_languages_names` VALUES (1137, 45, 72, 'Islandsk');
INSERT INTO `ff_languages_names` VALUES (1138, 45, 74, 'islandzki');
INSERT INTO `ff_languages_names` VALUES (1139, 45, 75, 'Islandês');
INSERT INTO `ff_languages_names` VALUES (1140, 45, 77, 'Islandeză');
INSERT INTO `ff_languages_names` VALUES (1141, 45, 81, 'islandčina');
INSERT INTO `ff_languages_names` VALUES (1142, 45, 82, 'islandščina');
INSERT INTO `ff_languages_names` VALUES (1143, 45, 86, 'isländska');
INSERT INTO `ff_languages_names` VALUES (1144, 45, 89, 'İzlandaca');
INSERT INTO `ff_languages_names` VALUES (1145, 1, 9, 'италиански');
INSERT INTO `ff_languages_names` VALUES (1146, 1, 17, 'italština');
INSERT INTO `ff_languages_names` VALUES (1147, 1, 20, 'Italiensk');
INSERT INTO `ff_languages_names` VALUES (1148, 1, 21, 'Italienisch');
INSERT INTO `ff_languages_names` VALUES (1149, 1, 22, 'ιταλικά');
INSERT INTO `ff_languages_names` VALUES (1150, 1, 23, 'Italian');
INSERT INTO `ff_languages_names` VALUES (1151, 1, 25, 'italiano');
INSERT INTO `ff_languages_names` VALUES (1152, 1, 26, 'itaalia');
INSERT INTO `ff_languages_names` VALUES (1153, 1, 29, 'italia');
INSERT INTO `ff_languages_names` VALUES (1154, 1, 32, 'italien');
INSERT INTO `ff_languages_names` VALUES (1155, 1, 40, 'Talijanski');
INSERT INTO `ff_languages_names` VALUES (1156, 1, 42, 'Olasz');
INSERT INTO `ff_languages_names` VALUES (1157, 1, 45, 'Ítalska');
INSERT INTO `ff_languages_names` VALUES (1158, 1, 1, 'Italiano');
INSERT INTO `ff_languages_names` VALUES (1159, 1, 59, 'Italų');
INSERT INTO `ff_languages_names` VALUES (1160, 1, 60, 'Itāļu');
INSERT INTO `ff_languages_names` VALUES (1161, 1, 66, 'Taljan');
INSERT INTO `ff_languages_names` VALUES (1162, 1, 70, 'Italiaans');
INSERT INTO `ff_languages_names` VALUES (1163, 1, 72, 'Italiensk');
INSERT INTO `ff_languages_names` VALUES (1164, 1, 74, 'włoski');
INSERT INTO `ff_languages_names` VALUES (1165, 1, 75, 'Italiano');
INSERT INTO `ff_languages_names` VALUES (1166, 1, 77, 'Italiană');
INSERT INTO `ff_languages_names` VALUES (1167, 1, 81, 'taliančina');
INSERT INTO `ff_languages_names` VALUES (1168, 1, 82, 'italijanščina');
INSERT INTO `ff_languages_names` VALUES (1169, 1, 86, 'italienska');
INSERT INTO `ff_languages_names` VALUES (1170, 1, 89, 'İtalyanca');
INSERT INTO `ff_languages_names` VALUES (1171, 47, 9, 'японски');
INSERT INTO `ff_languages_names` VALUES (1172, 47, 17, 'japonština');
INSERT INTO `ff_languages_names` VALUES (1173, 47, 20, 'Japansk');
INSERT INTO `ff_languages_names` VALUES (1174, 47, 21, 'Japanisch');
INSERT INTO `ff_languages_names` VALUES (1175, 47, 22, 'ιαπωνικά');
INSERT INTO `ff_languages_names` VALUES (1176, 47, 23, 'Japanese');
INSERT INTO `ff_languages_names` VALUES (1177, 47, 25, 'japonés');
INSERT INTO `ff_languages_names` VALUES (1178, 47, 26, 'jaapani');
INSERT INTO `ff_languages_names` VALUES (1179, 47, 29, 'japani');
INSERT INTO `ff_languages_names` VALUES (1180, 47, 32, 'japonais');
INSERT INTO `ff_languages_names` VALUES (1181, 47, 40, 'japanski');
INSERT INTO `ff_languages_names` VALUES (1182, 47, 42, 'Japán');
INSERT INTO `ff_languages_names` VALUES (1183, 47, 45, 'Japanska');
INSERT INTO `ff_languages_names` VALUES (1184, 47, 1, 'Giapponese');
INSERT INTO `ff_languages_names` VALUES (1185, 47, 59, 'Japonų');
INSERT INTO `ff_languages_names` VALUES (1186, 47, 60, 'Japāņu');
INSERT INTO `ff_languages_names` VALUES (1187, 47, 66, 'Ġappuniż');
INSERT INTO `ff_languages_names` VALUES (1188, 47, 70, 'Japans');
INSERT INTO `ff_languages_names` VALUES (1189, 47, 72, 'Japansk');
INSERT INTO `ff_languages_names` VALUES (1190, 47, 74, 'japoński');
INSERT INTO `ff_languages_names` VALUES (1191, 47, 75, 'Japonês');
INSERT INTO `ff_languages_names` VALUES (1192, 47, 77, 'Japoneză');
INSERT INTO `ff_languages_names` VALUES (1193, 47, 81, 'japončina');
INSERT INTO `ff_languages_names` VALUES (1194, 47, 82, 'japonščina');
INSERT INTO `ff_languages_names` VALUES (1195, 47, 86, 'japanska');
INSERT INTO `ff_languages_names` VALUES (1196, 47, 89, 'Japonca');
INSERT INTO `ff_languages_names` VALUES (1197, 48, 9, 'явански');
INSERT INTO `ff_languages_names` VALUES (1198, 48, 17, 'javánština');
INSERT INTO `ff_languages_names` VALUES (1199, 48, 20, 'Javanesisk');
INSERT INTO `ff_languages_names` VALUES (1200, 48, 21, 'Javanisch');
INSERT INTO `ff_languages_names` VALUES (1201, 48, 22, 'ιαβανικά');
INSERT INTO `ff_languages_names` VALUES (1202, 48, 23, 'Javanese');
INSERT INTO `ff_languages_names` VALUES (1203, 48, 25, 'javanés');
INSERT INTO `ff_languages_names` VALUES (1204, 48, 26, 'jaava');
INSERT INTO `ff_languages_names` VALUES (1205, 48, 29, 'jaava');
INSERT INTO `ff_languages_names` VALUES (1206, 48, 32, 'javanais');
INSERT INTO `ff_languages_names` VALUES (1207, 48, 40, 'javanski');
INSERT INTO `ff_languages_names` VALUES (1208, 48, 42, 'Jávai');
INSERT INTO `ff_languages_names` VALUES (1209, 48, 45, 'Javanska');
INSERT INTO `ff_languages_names` VALUES (1210, 48, 1, 'Giavanese');
INSERT INTO `ff_languages_names` VALUES (1211, 48, 59, 'Javiečių');
INSERT INTO `ff_languages_names` VALUES (1212, 48, 60, 'Javiešu');
INSERT INTO `ff_languages_names` VALUES (1213, 48, 66, 'Ġavaniż');
INSERT INTO `ff_languages_names` VALUES (1214, 48, 70, 'Javaans');
INSERT INTO `ff_languages_names` VALUES (1215, 48, 72, 'Javanesisk');
INSERT INTO `ff_languages_names` VALUES (1216, 48, 74, 'jawajski');
INSERT INTO `ff_languages_names` VALUES (1217, 48, 75, 'Javanês');
INSERT INTO `ff_languages_names` VALUES (1218, 48, 77, 'Javaneză');
INSERT INTO `ff_languages_names` VALUES (1219, 48, 81, 'jávčina');
INSERT INTO `ff_languages_names` VALUES (1220, 48, 82, 'javanščina');
INSERT INTO `ff_languages_names` VALUES (1221, 48, 86, 'javanesiska');
INSERT INTO `ff_languages_names` VALUES (1222, 48, 89, 'Javaca');
INSERT INTO `ff_languages_names` VALUES (1223, 49, 9, 'грузински');
INSERT INTO `ff_languages_names` VALUES (1224, 49, 17, 'gruzínština');
INSERT INTO `ff_languages_names` VALUES (1225, 49, 20, 'Georgisk');
INSERT INTO `ff_languages_names` VALUES (1226, 49, 21, 'Georgisch');
INSERT INTO `ff_languages_names` VALUES (1227, 49, 22, 'γεωργιανά');
INSERT INTO `ff_languages_names` VALUES (1228, 49, 23, 'Georgian');
INSERT INTO `ff_languages_names` VALUES (1229, 49, 25, 'georgiano');
INSERT INTO `ff_languages_names` VALUES (1230, 49, 26, 'gruusia');
INSERT INTO `ff_languages_names` VALUES (1231, 49, 29, 'georgia');
INSERT INTO `ff_languages_names` VALUES (1232, 49, 32, 'géorgien');
INSERT INTO `ff_languages_names` VALUES (1233, 49, 40, 'gruzijski');
INSERT INTO `ff_languages_names` VALUES (1234, 49, 42, 'Grúz');
INSERT INTO `ff_languages_names` VALUES (1235, 49, 45, 'Georgíska');
INSERT INTO `ff_languages_names` VALUES (1236, 49, 1, 'Georgiano');
INSERT INTO `ff_languages_names` VALUES (1237, 49, 59, 'Gruzinų');
INSERT INTO `ff_languages_names` VALUES (1238, 49, 60, 'Gruzīnu');
INSERT INTO `ff_languages_names` VALUES (1239, 49, 66, 'Ġeorġjan');
INSERT INTO `ff_languages_names` VALUES (1240, 49, 70, 'Georgisch');
INSERT INTO `ff_languages_names` VALUES (1241, 49, 72, 'Georgisk');
INSERT INTO `ff_languages_names` VALUES (1242, 49, 74, 'gruziński');
INSERT INTO `ff_languages_names` VALUES (1243, 49, 75, 'Geórgio');
INSERT INTO `ff_languages_names` VALUES (1244, 49, 77, 'Georgiană');
INSERT INTO `ff_languages_names` VALUES (1245, 49, 81, 'gruzínčina');
INSERT INTO `ff_languages_names` VALUES (1246, 49, 82, 'gruzinščina');
INSERT INTO `ff_languages_names` VALUES (1247, 49, 86, 'georgiska');
INSERT INTO `ff_languages_names` VALUES (1248, 49, 89, 'Gürcüce');
INSERT INTO `ff_languages_names` VALUES (1249, 50, 9, 'конгоански');
INSERT INTO `ff_languages_names` VALUES (1250, 50, 17, 'konžština');
INSERT INTO `ff_languages_names` VALUES (1251, 50, 20, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1252, 50, 21, 'Kongo-Sprache');
INSERT INTO `ff_languages_names` VALUES (1253, 50, 22, 'Κονγκό');
INSERT INTO `ff_languages_names` VALUES (1254, 50, 23, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1255, 50, 25, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1256, 50, 26, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1257, 50, 29, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1258, 50, 32, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1259, 50, 40, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1260, 50, 42, 'Kongó');
INSERT INTO `ff_languages_names` VALUES (1261, 50, 45, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1262, 50, 1, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1263, 50, 59, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1264, 50, 60, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1265, 50, 66, 'Kongo');
INSERT INTO `ff_languages_names` VALUES (1266, 50, 70, 'Congolees');
INSERT INTO `ff_languages_names` VALUES (1267, 50, 72, 'Kongolesisk');
INSERT INTO `ff_languages_names` VALUES (1268, 50, 74, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1269, 50, 75, 'Congo');
INSERT INTO `ff_languages_names` VALUES (1270, 50, 77, 'Congoleză');
INSERT INTO `ff_languages_names` VALUES (1271, 50, 81, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1272, 50, 82, 'kongovščina');
INSERT INTO `ff_languages_names` VALUES (1273, 50, 86, 'kongo');
INSERT INTO `ff_languages_names` VALUES (1274, 50, 89, 'Kongoca');
INSERT INTO `ff_languages_names` VALUES (1275, 51, 9, 'корейски');
INSERT INTO `ff_languages_names` VALUES (1276, 51, 17, 'korejština');
INSERT INTO `ff_languages_names` VALUES (1277, 51, 20, 'Koreansk');
INSERT INTO `ff_languages_names` VALUES (1278, 51, 21, 'Koreanisch');
INSERT INTO `ff_languages_names` VALUES (1279, 51, 22, 'κορεατικά');
INSERT INTO `ff_languages_names` VALUES (1280, 51, 23, 'Korean');
INSERT INTO `ff_languages_names` VALUES (1281, 51, 25, 'coreano');
INSERT INTO `ff_languages_names` VALUES (1282, 51, 26, 'korea');
INSERT INTO `ff_languages_names` VALUES (1283, 51, 29, 'korea');
INSERT INTO `ff_languages_names` VALUES (1284, 51, 32, 'coréen');
INSERT INTO `ff_languages_names` VALUES (1285, 51, 40, 'korejski');
INSERT INTO `ff_languages_names` VALUES (1286, 51, 42, 'Koreai');
INSERT INTO `ff_languages_names` VALUES (1287, 51, 45, 'Korean');
INSERT INTO `ff_languages_names` VALUES (1288, 51, 1, 'Coreano');
INSERT INTO `ff_languages_names` VALUES (1289, 51, 59, 'Korėjiečių');
INSERT INTO `ff_languages_names` VALUES (1290, 51, 60, 'Korejiešu');
INSERT INTO `ff_languages_names` VALUES (1291, 51, 66, 'Korean');
INSERT INTO `ff_languages_names` VALUES (1292, 51, 70, 'Koreaans');
INSERT INTO `ff_languages_names` VALUES (1293, 51, 72, 'Koreansk');
INSERT INTO `ff_languages_names` VALUES (1294, 51, 74, 'koreański');
INSERT INTO `ff_languages_names` VALUES (1295, 51, 75, 'Coreano');
INSERT INTO `ff_languages_names` VALUES (1296, 51, 77, 'Coreană');
INSERT INTO `ff_languages_names` VALUES (1297, 51, 81, 'kórejčina');
INSERT INTO `ff_languages_names` VALUES (1298, 51, 82, 'korejščina');
INSERT INTO `ff_languages_names` VALUES (1299, 51, 86, 'koreanska');
INSERT INTO `ff_languages_names` VALUES (1300, 51, 89, 'Korece');
INSERT INTO `ff_languages_names` VALUES (1301, 52, 9, 'кюрдски');
INSERT INTO `ff_languages_names` VALUES (1302, 52, 17, 'kurdština');
INSERT INTO `ff_languages_names` VALUES (1303, 52, 20, 'Kurdisk');
INSERT INTO `ff_languages_names` VALUES (1304, 52, 21, 'Kurdisch');
INSERT INTO `ff_languages_names` VALUES (1305, 52, 22, 'κουρδικά');
INSERT INTO `ff_languages_names` VALUES (1306, 52, 23, 'Kurdish');
INSERT INTO `ff_languages_names` VALUES (1307, 52, 25, 'kurdo');
INSERT INTO `ff_languages_names` VALUES (1308, 52, 26, 'kurdi');
INSERT INTO `ff_languages_names` VALUES (1309, 52, 29, 'kurdi');
INSERT INTO `ff_languages_names` VALUES (1310, 52, 32, 'kurde');
INSERT INTO `ff_languages_names` VALUES (1311, 52, 40, 'kurdski');
INSERT INTO `ff_languages_names` VALUES (1312, 52, 42, 'Kurd');
INSERT INTO `ff_languages_names` VALUES (1313, 52, 45, 'Kurdish');
INSERT INTO `ff_languages_names` VALUES (1314, 52, 1, 'Curdo');
INSERT INTO `ff_languages_names` VALUES (1315, 52, 59, 'Kurdų');
INSERT INTO `ff_languages_names` VALUES (1316, 52, 60, 'Kurdu');
INSERT INTO `ff_languages_names` VALUES (1317, 52, 66, 'Kurd');
INSERT INTO `ff_languages_names` VALUES (1318, 52, 70, 'Koerdisch');
INSERT INTO `ff_languages_names` VALUES (1319, 52, 72, 'Kurdisk');
INSERT INTO `ff_languages_names` VALUES (1320, 52, 74, 'kurdyjski');
INSERT INTO `ff_languages_names` VALUES (1321, 52, 75, 'Curdo');
INSERT INTO `ff_languages_names` VALUES (1322, 52, 77, 'Kurdă');
INSERT INTO `ff_languages_names` VALUES (1323, 52, 81, 'kurdčina');
INSERT INTO `ff_languages_names` VALUES (1324, 52, 82, 'kurdščina');
INSERT INTO `ff_languages_names` VALUES (1325, 52, 86, 'kurdiska');
INSERT INTO `ff_languages_names` VALUES (1326, 52, 89, 'Kürtçe');
INSERT INTO `ff_languages_names` VALUES (1327, 53, 9, 'корнуолски');
INSERT INTO `ff_languages_names` VALUES (1328, 53, 17, 'kornština');
INSERT INTO `ff_languages_names` VALUES (1329, 53, 20, 'Cornisk');
INSERT INTO `ff_languages_names` VALUES (1330, 53, 21, 'Kornisch');
INSERT INTO `ff_languages_names` VALUES (1331, 53, 22, 'κορνουαλικά');
INSERT INTO `ff_languages_names` VALUES (1332, 53, 23, 'Cornish');
INSERT INTO `ff_languages_names` VALUES (1333, 53, 25, 'córnico');
INSERT INTO `ff_languages_names` VALUES (1334, 53, 26, 'korni');
INSERT INTO `ff_languages_names` VALUES (1335, 53, 29, 'korni');
INSERT INTO `ff_languages_names` VALUES (1336, 53, 32, 'cornique');
INSERT INTO `ff_languages_names` VALUES (1337, 53, 40, 'Cornish');
INSERT INTO `ff_languages_names` VALUES (1338, 53, 42, 'Cornwalli kelta');
INSERT INTO `ff_languages_names` VALUES (1339, 53, 45, 'Cornish');
INSERT INTO `ff_languages_names` VALUES (1340, 53, 1, 'Cornico');
INSERT INTO `ff_languages_names` VALUES (1341, 53, 59, 'Kornų');
INSERT INTO `ff_languages_names` VALUES (1342, 53, 60, 'Korniešu');
INSERT INTO `ff_languages_names` VALUES (1343, 53, 66, 'Korniku');
INSERT INTO `ff_languages_names` VALUES (1344, 53, 70, 'Cornish');
INSERT INTO `ff_languages_names` VALUES (1345, 53, 72, 'Kornisk');
INSERT INTO `ff_languages_names` VALUES (1346, 53, 74, 'kornijski');
INSERT INTO `ff_languages_names` VALUES (1347, 53, 75, 'Córnico');
INSERT INTO `ff_languages_names` VALUES (1348, 53, 77, 'Cornică');
INSERT INTO `ff_languages_names` VALUES (1349, 53, 81, 'kornčina');
INSERT INTO `ff_languages_names` VALUES (1350, 53, 82, 'kornijščina');
INSERT INTO `ff_languages_names` VALUES (1351, 53, 86, 'corniska');
INSERT INTO `ff_languages_names` VALUES (1352, 53, 89, 'Kornişce');
INSERT INTO `ff_languages_names` VALUES (1353, 54, 9, 'киргизки');
INSERT INTO `ff_languages_names` VALUES (1354, 54, 17, 'kyrgyzština');
INSERT INTO `ff_languages_names` VALUES (1355, 54, 20, 'Kirgisisk');
INSERT INTO `ff_languages_names` VALUES (1356, 54, 21, 'Kirgisisch');
INSERT INTO `ff_languages_names` VALUES (1357, 54, 22, 'κιργιζικά');
INSERT INTO `ff_languages_names` VALUES (1358, 54, 23, 'Kirghiz');
INSERT INTO `ff_languages_names` VALUES (1359, 54, 25, 'kirguís');
INSERT INTO `ff_languages_names` VALUES (1360, 54, 26, 'kirgiisi');
INSERT INTO `ff_languages_names` VALUES (1361, 54, 29, 'kirgiisi');
INSERT INTO `ff_languages_names` VALUES (1362, 54, 32, 'kirghize');
INSERT INTO `ff_languages_names` VALUES (1363, 54, 40, 'kirgiski');
INSERT INTO `ff_languages_names` VALUES (1364, 54, 42, 'Kirgíz');
INSERT INTO `ff_languages_names` VALUES (1365, 54, 45, 'Kirghiz');
INSERT INTO `ff_languages_names` VALUES (1366, 54, 1, 'Kirghiso');
INSERT INTO `ff_languages_names` VALUES (1367, 54, 59, 'Kirgizų');
INSERT INTO `ff_languages_names` VALUES (1368, 54, 60, 'Kirgīzu');
INSERT INTO `ff_languages_names` VALUES (1369, 54, 66, 'Kirgiż');
INSERT INTO `ff_languages_names` VALUES (1370, 54, 70, 'Kirgizisch');
INSERT INTO `ff_languages_names` VALUES (1371, 54, 72, 'Kirgisisk');
INSERT INTO `ff_languages_names` VALUES (1372, 54, 74, 'kirgiski');
INSERT INTO `ff_languages_names` VALUES (1373, 54, 75, 'Quirguize');
INSERT INTO `ff_languages_names` VALUES (1374, 54, 77, 'Chirghiză');
INSERT INTO `ff_languages_names` VALUES (1375, 54, 81, 'kirgizština');
INSERT INTO `ff_languages_names` VALUES (1376, 54, 82, 'kirgiščina');
INSERT INTO `ff_languages_names` VALUES (1377, 54, 86, 'kirgisiska');
INSERT INTO `ff_languages_names` VALUES (1378, 54, 89, 'Kırgızca');
INSERT INTO `ff_languages_names` VALUES (1379, 55, 9, 'латински');
INSERT INTO `ff_languages_names` VALUES (1380, 55, 17, 'latina');
INSERT INTO `ff_languages_names` VALUES (1381, 55, 20, 'Latin');
INSERT INTO `ff_languages_names` VALUES (1382, 55, 21, 'Latein');
INSERT INTO `ff_languages_names` VALUES (1383, 55, 22, 'λατινικά');
INSERT INTO `ff_languages_names` VALUES (1384, 55, 23, 'Latin');
INSERT INTO `ff_languages_names` VALUES (1385, 55, 25, 'latín');
INSERT INTO `ff_languages_names` VALUES (1386, 55, 26, 'ladina');
INSERT INTO `ff_languages_names` VALUES (1387, 55, 29, 'latina');
INSERT INTO `ff_languages_names` VALUES (1388, 55, 32, 'latin');
INSERT INTO `ff_languages_names` VALUES (1389, 55, 40, 'latinski');
INSERT INTO `ff_languages_names` VALUES (1390, 55, 42, 'Latin');
INSERT INTO `ff_languages_names` VALUES (1391, 55, 45, 'Latína');
INSERT INTO `ff_languages_names` VALUES (1392, 55, 1, 'Latino');
INSERT INTO `ff_languages_names` VALUES (1393, 55, 59, 'Lotynų');
INSERT INTO `ff_languages_names` VALUES (1394, 55, 60, 'Latīņu');
INSERT INTO `ff_languages_names` VALUES (1395, 55, 66, 'Latin');
INSERT INTO `ff_languages_names` VALUES (1396, 55, 70, 'Latijn');
INSERT INTO `ff_languages_names` VALUES (1397, 55, 72, 'Latin');
INSERT INTO `ff_languages_names` VALUES (1398, 55, 74, 'łaciński');
INSERT INTO `ff_languages_names` VALUES (1399, 55, 75, 'Latim');
INSERT INTO `ff_languages_names` VALUES (1400, 55, 77, 'Latină');
INSERT INTO `ff_languages_names` VALUES (1401, 55, 81, 'latinčina');
INSERT INTO `ff_languages_names` VALUES (1402, 55, 82, 'latinščina');
INSERT INTO `ff_languages_names` VALUES (1403, 55, 86, 'latin');
INSERT INTO `ff_languages_names` VALUES (1404, 55, 89, 'Latince');
INSERT INTO `ff_languages_names` VALUES (1405, 56, 9, 'люксембургски');
INSERT INTO `ff_languages_names` VALUES (1406, 56, 17, 'lucemburština');
INSERT INTO `ff_languages_names` VALUES (1407, 56, 20, 'Letzeburgesch');
INSERT INTO `ff_languages_names` VALUES (1408, 56, 21, 'Luxemburgisch');
INSERT INTO `ff_languages_names` VALUES (1409, 56, 22, 'λουξεμβουργιανά');
INSERT INTO `ff_languages_names` VALUES (1410, 56, 23, 'Luxembourgish; Letzeburgesch');
INSERT INTO `ff_languages_names` VALUES (1411, 56, 25, 'luxemburgués');
INSERT INTO `ff_languages_names` VALUES (1412, 56, 26, 'letseburgi');
INSERT INTO `ff_languages_names` VALUES (1413, 56, 29, 'luxemburg');
INSERT INTO `ff_languages_names` VALUES (1414, 56, 32, 'luxembourgeois');
INSERT INTO `ff_languages_names` VALUES (1415, 56, 40, 'luksemburški');
INSERT INTO `ff_languages_names` VALUES (1416, 56, 42, 'Luxemburgi');
INSERT INTO `ff_languages_names` VALUES (1417, 56, 45, 'Lúxemborgíska');
INSERT INTO `ff_languages_names` VALUES (1418, 56, 1, 'Lussemburghese');
INSERT INTO `ff_languages_names` VALUES (1419, 56, 59, 'Liuksemburgiečių');
INSERT INTO `ff_languages_names` VALUES (1420, 56, 60, 'Luksemburgiešu');
INSERT INTO `ff_languages_names` VALUES (1421, 56, 66, 'Lussemburgiż; Letzurgiż');
INSERT INTO `ff_languages_names` VALUES (1422, 56, 70, 'Luxemburgs');
INSERT INTO `ff_languages_names` VALUES (1423, 56, 72, 'Luxembourgisk');
INSERT INTO `ff_languages_names` VALUES (1424, 56, 74, 'luksemburski');
INSERT INTO `ff_languages_names` VALUES (1425, 56, 75, 'Luxemburguês');
INSERT INTO `ff_languages_names` VALUES (1426, 56, 77, 'Luxemburgheză');
INSERT INTO `ff_languages_names` VALUES (1427, 56, 81, 'luxemburčina');
INSERT INTO `ff_languages_names` VALUES (1428, 56, 82, 'luksemburščina');
INSERT INTO `ff_languages_names` VALUES (1429, 56, 86, 'luxemburgiska');
INSERT INTO `ff_languages_names` VALUES (1430, 56, 89, 'Lüksemburgca');
INSERT INTO `ff_languages_names` VALUES (1431, 57, 9, 'лимбургски');
INSERT INTO `ff_languages_names` VALUES (1432, 57, 17, 'limburština');
INSERT INTO `ff_languages_names` VALUES (1433, 57, 20, 'Limburgish');
INSERT INTO `ff_languages_names` VALUES (1434, 57, 21, 'Limburgisch');
INSERT INTO `ff_languages_names` VALUES (1435, 57, 22, 'γλώσσα Λιμβούργου');
INSERT INTO `ff_languages_names` VALUES (1436, 57, 23, 'Limburgan; Limburger; Limburgish');
INSERT INTO `ff_languages_names` VALUES (1437, 57, 25, 'limburgués');
INSERT INTO `ff_languages_names` VALUES (1438, 57, 26, 'limburgi');
INSERT INTO `ff_languages_names` VALUES (1439, 57, 29, 'limburg');
INSERT INTO `ff_languages_names` VALUES (1440, 57, 32, 'limbourgeois');
INSERT INTO `ff_languages_names` VALUES (1441, 57, 40, 'Limburgish');
INSERT INTO `ff_languages_names` VALUES (1442, 57, 42, 'Limburgi');
INSERT INTO `ff_languages_names` VALUES (1443, 57, 45, 'Limburgish');
INSERT INTO `ff_languages_names` VALUES (1444, 57, 1, 'Limburghese');
INSERT INTO `ff_languages_names` VALUES (1445, 57, 59, 'Limburgiečių');
INSERT INTO `ff_languages_names` VALUES (1446, 57, 60, 'Limburgiešu');
INSERT INTO `ff_languages_names` VALUES (1447, 57, 66, 'Limburgan; Limburger; Limburgiż');
INSERT INTO `ff_languages_names` VALUES (1448, 57, 70, 'Limburgs');
INSERT INTO `ff_languages_names` VALUES (1449, 57, 72, 'Limburgisk');
INSERT INTO `ff_languages_names` VALUES (1450, 57, 74, 'limburski');
INSERT INTO `ff_languages_names` VALUES (1451, 57, 75, 'Limburguês');
INSERT INTO `ff_languages_names` VALUES (1452, 57, 77, 'Limburgheză');
INSERT INTO `ff_languages_names` VALUES (1453, 57, 81, 'limburčina');
INSERT INTO `ff_languages_names` VALUES (1454, 57, 82, 'limburščina');
INSERT INTO `ff_languages_names` VALUES (1455, 57, 86, 'limburgiska');
INSERT INTO `ff_languages_names` VALUES (1456, 57, 89, 'Limburgca');
INSERT INTO `ff_languages_names` VALUES (1457, 58, 9, 'лингала');
INSERT INTO `ff_languages_names` VALUES (1458, 58, 17, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1459, 58, 20, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1460, 58, 21, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1461, 58, 22, 'λινγκάλα');
INSERT INTO `ff_languages_names` VALUES (1462, 58, 23, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1463, 58, 25, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1464, 58, 26, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1465, 58, 29, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1466, 58, 32, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1467, 58, 40, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1468, 58, 42, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1469, 58, 45, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1470, 58, 1, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1471, 58, 59, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1472, 58, 60, 'Lingālu');
INSERT INTO `ff_languages_names` VALUES (1473, 58, 66, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1474, 58, 70, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1475, 58, 72, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1476, 58, 74, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1477, 58, 75, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1478, 58, 77, 'Lingala');
INSERT INTO `ff_languages_names` VALUES (1479, 58, 81, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1480, 58, 82, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1481, 58, 86, 'lingala');
INSERT INTO `ff_languages_names` VALUES (1482, 58, 89, 'Lingalaca');
INSERT INTO `ff_languages_names` VALUES (1483, 59, 9, 'литовски');
INSERT INTO `ff_languages_names` VALUES (1484, 59, 17, 'litevština');
INSERT INTO `ff_languages_names` VALUES (1485, 59, 20, 'Litauisk');
INSERT INTO `ff_languages_names` VALUES (1486, 59, 21, 'Litauisch');
INSERT INTO `ff_languages_names` VALUES (1487, 59, 22, 'λιθουανικά');
INSERT INTO `ff_languages_names` VALUES (1488, 59, 23, 'Lithuanian');
INSERT INTO `ff_languages_names` VALUES (1489, 59, 25, 'lituano');
INSERT INTO `ff_languages_names` VALUES (1490, 59, 26, 'leedu');
INSERT INTO `ff_languages_names` VALUES (1491, 59, 29, 'liettua');
INSERT INTO `ff_languages_names` VALUES (1492, 59, 32, 'lituanien');
INSERT INTO `ff_languages_names` VALUES (1493, 59, 40, 'Litavski');
INSERT INTO `ff_languages_names` VALUES (1494, 59, 42, 'Litván');
INSERT INTO `ff_languages_names` VALUES (1495, 59, 45, 'Litháíska');
INSERT INTO `ff_languages_names` VALUES (1496, 59, 1, 'Lituano');
INSERT INTO `ff_languages_names` VALUES (1497, 59, 59, 'Lietuvių');
INSERT INTO `ff_languages_names` VALUES (1498, 59, 60, 'Lietuviešu');
INSERT INTO `ff_languages_names` VALUES (1499, 59, 66, 'Litwan');
INSERT INTO `ff_languages_names` VALUES (1500, 59, 70, 'Litouws');
INSERT INTO `ff_languages_names` VALUES (1501, 59, 72, 'Litauisk');
INSERT INTO `ff_languages_names` VALUES (1502, 59, 74, 'litewski');
INSERT INTO `ff_languages_names` VALUES (1503, 59, 75, 'Lituano');
INSERT INTO `ff_languages_names` VALUES (1504, 59, 77, 'Lituaniană');
INSERT INTO `ff_languages_names` VALUES (1505, 59, 81, 'litovčina');
INSERT INTO `ff_languages_names` VALUES (1506, 59, 82, 'litvanščina');
INSERT INTO `ff_languages_names` VALUES (1507, 59, 86, 'litauiska');
INSERT INTO `ff_languages_names` VALUES (1508, 59, 89, 'Litvanca');
INSERT INTO `ff_languages_names` VALUES (1509, 60, 9, 'латвийски');
INSERT INTO `ff_languages_names` VALUES (1510, 60, 17, 'lotyšština');
INSERT INTO `ff_languages_names` VALUES (1511, 60, 20, 'Lettisk');
INSERT INTO `ff_languages_names` VALUES (1512, 60, 21, 'Lettisch');
INSERT INTO `ff_languages_names` VALUES (1513, 60, 22, 'λεττονικά');
INSERT INTO `ff_languages_names` VALUES (1514, 60, 23, 'Latvian');
INSERT INTO `ff_languages_names` VALUES (1515, 60, 25, 'letón');
INSERT INTO `ff_languages_names` VALUES (1516, 60, 26, 'läti');
INSERT INTO `ff_languages_names` VALUES (1517, 60, 29, 'latvia');
INSERT INTO `ff_languages_names` VALUES (1518, 60, 32, 'letton');
INSERT INTO `ff_languages_names` VALUES (1519, 60, 40, 'Latvijski');
INSERT INTO `ff_languages_names` VALUES (1520, 60, 42, 'Lett');
INSERT INTO `ff_languages_names` VALUES (1521, 60, 45, 'Lettneska');
INSERT INTO `ff_languages_names` VALUES (1522, 60, 1, 'Lettone');
INSERT INTO `ff_languages_names` VALUES (1523, 60, 59, 'Latvių');
INSERT INTO `ff_languages_names` VALUES (1524, 60, 60, 'Latviešu');
INSERT INTO `ff_languages_names` VALUES (1525, 60, 66, 'Latvjan');
INSERT INTO `ff_languages_names` VALUES (1526, 60, 70, 'Lets');
INSERT INTO `ff_languages_names` VALUES (1527, 60, 72, 'Latvisk');
INSERT INTO `ff_languages_names` VALUES (1528, 60, 74, 'łotewski');
INSERT INTO `ff_languages_names` VALUES (1529, 60, 75, 'Letão');
INSERT INTO `ff_languages_names` VALUES (1530, 60, 77, 'Letonă');
INSERT INTO `ff_languages_names` VALUES (1531, 60, 81, 'lotyština');
INSERT INTO `ff_languages_names` VALUES (1532, 60, 82, 'latvijščina');
INSERT INTO `ff_languages_names` VALUES (1533, 60, 86, 'lettiska');
INSERT INTO `ff_languages_names` VALUES (1534, 60, 89, 'Letonca');
INSERT INTO `ff_languages_names` VALUES (1535, 61, 9, 'малгашки');
INSERT INTO `ff_languages_names` VALUES (1536, 61, 17, 'malgaština');
INSERT INTO `ff_languages_names` VALUES (1537, 61, 20, 'Malagasy');
INSERT INTO `ff_languages_names` VALUES (1538, 61, 21, 'Malagassi-Sprache');
INSERT INTO `ff_languages_names` VALUES (1539, 61, 22, 'μαλγασικά');
INSERT INTO `ff_languages_names` VALUES (1540, 61, 23, 'Malagasy');
INSERT INTO `ff_languages_names` VALUES (1541, 61, 25, 'malgache');
INSERT INTO `ff_languages_names` VALUES (1542, 61, 26, 'malagassi');
INSERT INTO `ff_languages_names` VALUES (1543, 61, 29, 'malagassi');
INSERT INTO `ff_languages_names` VALUES (1544, 61, 32, 'malgache');
INSERT INTO `ff_languages_names` VALUES (1545, 61, 40, 'malgaški');
INSERT INTO `ff_languages_names` VALUES (1546, 61, 42, 'Malagasy');
INSERT INTO `ff_languages_names` VALUES (1547, 61, 45, 'Malagasy');
INSERT INTO `ff_languages_names` VALUES (1548, 61, 1, 'Malagasy');
INSERT INTO `ff_languages_names` VALUES (1549, 61, 59, 'Malagasių');
INSERT INTO `ff_languages_names` VALUES (1550, 61, 60, 'Malagasi');
INSERT INTO `ff_languages_names` VALUES (1551, 61, 66, 'Malagażi');
INSERT INTO `ff_languages_names` VALUES (1552, 61, 70, 'Malagassisch');
INSERT INTO `ff_languages_names` VALUES (1553, 61, 72, 'Gassisk');
INSERT INTO `ff_languages_names` VALUES (1554, 61, 74, 'malgaski');
INSERT INTO `ff_languages_names` VALUES (1555, 61, 75, 'Malgache');
INSERT INTO `ff_languages_names` VALUES (1556, 61, 77, 'Malgaşă');
INSERT INTO `ff_languages_names` VALUES (1557, 61, 81, 'malagaština');
INSERT INTO `ff_languages_names` VALUES (1558, 61, 82, 'malgaščina');
INSERT INTO `ff_languages_names` VALUES (1559, 61, 86, 'malagasy');
INSERT INTO `ff_languages_names` VALUES (1560, 61, 89, 'Malagasi');
INSERT INTO `ff_languages_names` VALUES (1561, 62, 9, 'македонски');
INSERT INTO `ff_languages_names` VALUES (1562, 62, 17, 'makedonština');
INSERT INTO `ff_languages_names` VALUES (1563, 62, 20, 'Makedonsk');
INSERT INTO `ff_languages_names` VALUES (1564, 62, 21, 'Makedonisch');
INSERT INTO `ff_languages_names` VALUES (1565, 62, 22, 'σλαβομακεδονικά');
INSERT INTO `ff_languages_names` VALUES (1566, 62, 23, 'Macedonian');
INSERT INTO `ff_languages_names` VALUES (1567, 62, 25, 'macedonio');
INSERT INTO `ff_languages_names` VALUES (1568, 62, 26, 'makedoonia');
INSERT INTO `ff_languages_names` VALUES (1569, 62, 29, 'makedonia');
INSERT INTO `ff_languages_names` VALUES (1570, 62, 32, 'macédonien');
INSERT INTO `ff_languages_names` VALUES (1571, 62, 40, 'makedonski');
INSERT INTO `ff_languages_names` VALUES (1572, 62, 42, 'Macedón');
INSERT INTO `ff_languages_names` VALUES (1573, 62, 45, 'Makedónska');
INSERT INTO `ff_languages_names` VALUES (1574, 62, 1, 'Macedone');
INSERT INTO `ff_languages_names` VALUES (1575, 62, 59, 'Makedonų');
INSERT INTO `ff_languages_names` VALUES (1576, 62, 60, 'Maķedoniešu');
INSERT INTO `ff_languages_names` VALUES (1577, 62, 66, 'Maċedonjan');
INSERT INTO `ff_languages_names` VALUES (1578, 62, 70, 'Macedonisch');
INSERT INTO `ff_languages_names` VALUES (1579, 62, 72, 'Makedonsk');
INSERT INTO `ff_languages_names` VALUES (1580, 62, 74, 'macedoński');
INSERT INTO `ff_languages_names` VALUES (1581, 62, 75, 'Macedónio');
INSERT INTO `ff_languages_names` VALUES (1582, 62, 77, 'Macedoneană');
INSERT INTO `ff_languages_names` VALUES (1583, 62, 81, 'macedónčina');
INSERT INTO `ff_languages_names` VALUES (1584, 62, 82, 'makedonščina');
INSERT INTO `ff_languages_names` VALUES (1585, 62, 86, 'makedonska');
INSERT INTO `ff_languages_names` VALUES (1586, 62, 89, 'Makedonca');
INSERT INTO `ff_languages_names` VALUES (1587, 63, 9, 'монголски');
INSERT INTO `ff_languages_names` VALUES (1588, 63, 17, 'mongolština');
INSERT INTO `ff_languages_names` VALUES (1589, 63, 20, 'Mongolsk');
INSERT INTO `ff_languages_names` VALUES (1590, 63, 21, 'Mongolisch');
INSERT INTO `ff_languages_names` VALUES (1591, 63, 22, 'μογγολικά');
INSERT INTO `ff_languages_names` VALUES (1592, 63, 23, 'Mongolian');
INSERT INTO `ff_languages_names` VALUES (1593, 63, 25, 'mongol');
INSERT INTO `ff_languages_names` VALUES (1594, 63, 26, 'mongoli');
INSERT INTO `ff_languages_names` VALUES (1595, 63, 29, 'mongoli');
INSERT INTO `ff_languages_names` VALUES (1596, 63, 32, 'mongol');
INSERT INTO `ff_languages_names` VALUES (1597, 63, 40, 'mongolski');
INSERT INTO `ff_languages_names` VALUES (1598, 63, 42, 'Mongol');
INSERT INTO `ff_languages_names` VALUES (1599, 63, 45, 'Mongolian');
INSERT INTO `ff_languages_names` VALUES (1600, 63, 1, 'Mongolo');
INSERT INTO `ff_languages_names` VALUES (1601, 63, 59, 'Mongolų');
INSERT INTO `ff_languages_names` VALUES (1602, 63, 60, 'Mongoļu');
INSERT INTO `ff_languages_names` VALUES (1603, 63, 66, 'Mongoljan');
INSERT INTO `ff_languages_names` VALUES (1604, 63, 70, 'Mongools');
INSERT INTO `ff_languages_names` VALUES (1605, 63, 72, 'Mongolsk');
INSERT INTO `ff_languages_names` VALUES (1606, 63, 74, 'mongolski');
INSERT INTO `ff_languages_names` VALUES (1607, 63, 75, 'Mongol');
INSERT INTO `ff_languages_names` VALUES (1608, 63, 77, 'Mongoleză');
INSERT INTO `ff_languages_names` VALUES (1609, 63, 81, 'mongolčina');
INSERT INTO `ff_languages_names` VALUES (1610, 63, 82, 'mongolščina');
INSERT INTO `ff_languages_names` VALUES (1611, 63, 86, 'mongoliska');
INSERT INTO `ff_languages_names` VALUES (1612, 63, 89, 'Moğolca');
INSERT INTO `ff_languages_names` VALUES (1613, 64, 9, 'молдовски');
INSERT INTO `ff_languages_names` VALUES (1614, 64, 17, 'moldavština');
INSERT INTO `ff_languages_names` VALUES (1615, 64, 20, 'Moldovisk');
INSERT INTO `ff_languages_names` VALUES (1616, 64, 21, 'Moldauisch');
INSERT INTO `ff_languages_names` VALUES (1617, 64, 22, 'μολδαβικά');
INSERT INTO `ff_languages_names` VALUES (1618, 64, 23, 'Moldavian');
INSERT INTO `ff_languages_names` VALUES (1619, 64, 25, 'moldavo');
INSERT INTO `ff_languages_names` VALUES (1620, 64, 26, 'moldova');
INSERT INTO `ff_languages_names` VALUES (1621, 64, 29, 'moldavia');
INSERT INTO `ff_languages_names` VALUES (1622, 64, 32, 'moldave');
INSERT INTO `ff_languages_names` VALUES (1623, 64, 40, 'moldavski');
INSERT INTO `ff_languages_names` VALUES (1624, 64, 42, 'Moldáv');
INSERT INTO `ff_languages_names` VALUES (1625, 64, 45, 'Moldavian');
INSERT INTO `ff_languages_names` VALUES (1626, 64, 1, 'Moldavo');
INSERT INTO `ff_languages_names` VALUES (1627, 64, 59, 'Moldavų');
INSERT INTO `ff_languages_names` VALUES (1628, 64, 60, 'Moldāvu');
INSERT INTO `ff_languages_names` VALUES (1629, 64, 66, 'Moldavjan');
INSERT INTO `ff_languages_names` VALUES (1630, 64, 70, 'Moldavisch');
INSERT INTO `ff_languages_names` VALUES (1631, 64, 72, 'Moldovsk');
INSERT INTO `ff_languages_names` VALUES (1632, 64, 74, 'mołdawski');
INSERT INTO `ff_languages_names` VALUES (1633, 64, 75, 'Moldavo');
INSERT INTO `ff_languages_names` VALUES (1634, 64, 77, 'Moldovenească');
INSERT INTO `ff_languages_names` VALUES (1635, 64, 81, 'moldavčina');
INSERT INTO `ff_languages_names` VALUES (1636, 64, 82, 'moldavščina');
INSERT INTO `ff_languages_names` VALUES (1637, 64, 86, 'moldaviska');
INSERT INTO `ff_languages_names` VALUES (1638, 64, 89, 'Moldovca');
INSERT INTO `ff_languages_names` VALUES (1639, 65, 9, 'малайски');
INSERT INTO `ff_languages_names` VALUES (1640, 65, 17, 'malajština');
INSERT INTO `ff_languages_names` VALUES (1641, 65, 20, 'Malajisk');
INSERT INTO `ff_languages_names` VALUES (1642, 65, 21, 'Malaiisch');
INSERT INTO `ff_languages_names` VALUES (1643, 65, 22, 'μαλαισιανά');
INSERT INTO `ff_languages_names` VALUES (1644, 65, 23, 'Malay');
INSERT INTO `ff_languages_names` VALUES (1645, 65, 25, 'malayo');
INSERT INTO `ff_languages_names` VALUES (1646, 65, 26, 'malai');
INSERT INTO `ff_languages_names` VALUES (1647, 65, 29, 'malaiji');
INSERT INTO `ff_languages_names` VALUES (1648, 65, 32, 'malais');
INSERT INTO `ff_languages_names` VALUES (1649, 65, 40, 'malajski');
INSERT INTO `ff_languages_names` VALUES (1650, 65, 42, 'Maláj');
INSERT INTO `ff_languages_names` VALUES (1651, 65, 45, 'Malay');
INSERT INTO `ff_languages_names` VALUES (1652, 65, 1, 'Malay');
INSERT INTO `ff_languages_names` VALUES (1653, 65, 59, 'Malajų');
INSERT INTO `ff_languages_names` VALUES (1654, 65, 60, 'Malajiešu');
INSERT INTO `ff_languages_names` VALUES (1655, 65, 66, 'Malajan');
INSERT INTO `ff_languages_names` VALUES (1656, 65, 70, 'Maleis');
INSERT INTO `ff_languages_names` VALUES (1657, 65, 72, 'Malayisk');
INSERT INTO `ff_languages_names` VALUES (1658, 65, 74, 'malajski');
INSERT INTO `ff_languages_names` VALUES (1659, 65, 75, 'Malaio');
INSERT INTO `ff_languages_names` VALUES (1660, 65, 77, 'Malaeziană');
INSERT INTO `ff_languages_names` VALUES (1661, 65, 81, 'malajčina');
INSERT INTO `ff_languages_names` VALUES (1662, 65, 82, 'malajščina');
INSERT INTO `ff_languages_names` VALUES (1663, 65, 86, 'malajiska');
INSERT INTO `ff_languages_names` VALUES (1664, 65, 89, 'Malezyaca');
INSERT INTO `ff_languages_names` VALUES (1665, 66, 9, 'малтийски');
INSERT INTO `ff_languages_names` VALUES (1666, 66, 17, 'maltština');
INSERT INTO `ff_languages_names` VALUES (1667, 66, 20, 'Maltesisk');
INSERT INTO `ff_languages_names` VALUES (1668, 66, 21, 'Maltesisch');
INSERT INTO `ff_languages_names` VALUES (1669, 66, 22, 'Μαλτεζικά');
INSERT INTO `ff_languages_names` VALUES (1670, 66, 23, 'Maltese');
INSERT INTO `ff_languages_names` VALUES (1671, 66, 25, 'maltés');
INSERT INTO `ff_languages_names` VALUES (1672, 66, 26, 'malta');
INSERT INTO `ff_languages_names` VALUES (1673, 66, 29, 'malta');
INSERT INTO `ff_languages_names` VALUES (1674, 66, 32, 'maltais');
INSERT INTO `ff_languages_names` VALUES (1675, 66, 40, 'Malteški');
INSERT INTO `ff_languages_names` VALUES (1676, 66, 42, 'Máltai');
INSERT INTO `ff_languages_names` VALUES (1677, 66, 45, 'Maltneska');
INSERT INTO `ff_languages_names` VALUES (1678, 66, 1, 'Maltese');
INSERT INTO `ff_languages_names` VALUES (1679, 66, 59, 'Maltiečių');
INSERT INTO `ff_languages_names` VALUES (1680, 66, 60, 'Maltiešu');
INSERT INTO `ff_languages_names` VALUES (1681, 66, 66, 'Malti');
INSERT INTO `ff_languages_names` VALUES (1682, 66, 70, 'Maltees');
INSERT INTO `ff_languages_names` VALUES (1683, 66, 72, 'Maltesisk');
INSERT INTO `ff_languages_names` VALUES (1684, 66, 74, 'maltański');
INSERT INTO `ff_languages_names` VALUES (1685, 66, 75, 'Maltês');
INSERT INTO `ff_languages_names` VALUES (1686, 66, 77, 'Malteză');
INSERT INTO `ff_languages_names` VALUES (1687, 66, 81, 'maltčina');
INSERT INTO `ff_languages_names` VALUES (1688, 66, 82, 'malteščina');
INSERT INTO `ff_languages_names` VALUES (1689, 66, 86, 'maltesiska');
INSERT INTO `ff_languages_names` VALUES (1690, 66, 89, 'Maltaca');
INSERT INTO `ff_languages_names` VALUES (1691, 67, 9, 'бирмански');
INSERT INTO `ff_languages_names` VALUES (1692, 67, 17, 'barmština');
INSERT INTO `ff_languages_names` VALUES (1693, 67, 20, 'Burmesisk');
INSERT INTO `ff_languages_names` VALUES (1694, 67, 21, 'Burmesisch; Birmanisch');
INSERT INTO `ff_languages_names` VALUES (1695, 67, 22, 'βιρμανικά');
INSERT INTO `ff_languages_names` VALUES (1696, 67, 23, 'Burmese');
INSERT INTO `ff_languages_names` VALUES (1697, 67, 25, 'birmano');
INSERT INTO `ff_languages_names` VALUES (1698, 67, 26, 'birma');
INSERT INTO `ff_languages_names` VALUES (1699, 67, 29, 'burma');
INSERT INTO `ff_languages_names` VALUES (1700, 67, 32, 'birman');
INSERT INTO `ff_languages_names` VALUES (1701, 67, 40, 'burmanski');
INSERT INTO `ff_languages_names` VALUES (1702, 67, 42, 'Burméziai');
INSERT INTO `ff_languages_names` VALUES (1703, 67, 45, 'Burmese');
INSERT INTO `ff_languages_names` VALUES (1704, 67, 1, 'Burmese');
INSERT INTO `ff_languages_names` VALUES (1705, 67, 59, 'Birmiečių');
INSERT INTO `ff_languages_names` VALUES (1706, 67, 60, 'Birmiešu');
INSERT INTO `ff_languages_names` VALUES (1707, 67, 66, 'Burmiż');
INSERT INTO `ff_languages_names` VALUES (1708, 67, 70, 'Birmees');
INSERT INTO `ff_languages_names` VALUES (1709, 67, 72, 'Burmesisk');
INSERT INTO `ff_languages_names` VALUES (1710, 67, 74, 'birmański');
INSERT INTO `ff_languages_names` VALUES (1711, 67, 75, 'Birmanês');
INSERT INTO `ff_languages_names` VALUES (1712, 67, 77, 'Birmaneză');
INSERT INTO `ff_languages_names` VALUES (1713, 67, 81, 'barmčina');
INSERT INTO `ff_languages_names` VALUES (1714, 67, 82, 'burmanščina');
INSERT INTO `ff_languages_names` VALUES (1715, 67, 86, 'burmesiska');
INSERT INTO `ff_languages_names` VALUES (1716, 67, 89, 'Burmaca');
INSERT INTO `ff_languages_names` VALUES (1717, 68, 9, 'норвежки (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1718, 68, 17, 'norština (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1719, 68, 20, 'Norsk (Riksmål)');
INSERT INTO `ff_languages_names` VALUES (1720, 68, 21, 'Norwegisch (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1721, 68, 22, 'νορβηγικά (μπόκμωλ)');
INSERT INTO `ff_languages_names` VALUES (1722, 68, 23, 'Norwegian (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1723, 68, 25, 'noruego (bokmal)');
INSERT INTO `ff_languages_names` VALUES (1724, 68, 26, 'norra (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1725, 68, 29, 'norja (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1726, 68, 32, 'norvégien (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1727, 68, 40, 'norveški (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1728, 68, 42, 'Novég (Bokmal)');
INSERT INTO `ff_languages_names` VALUES (1729, 68, 45, 'Norska (Bókmál)');
INSERT INTO `ff_languages_names` VALUES (1730, 68, 1, 'Norvegese (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1731, 68, 59, 'Norvegų (standartinė kalba)');
INSERT INTO `ff_languages_names` VALUES (1732, 68, 60, 'Norvēģu (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1733, 68, 66, 'Norveġiż (Bokmahal)');
INSERT INTO `ff_languages_names` VALUES (1734, 68, 70, 'Noors (Bokmǻl)');
INSERT INTO `ff_languages_names` VALUES (1735, 68, 72, 'Norsk (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1736, 68, 74, 'norweski (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1737, 68, 75, 'Norueguês (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1738, 68, 77, 'Norvegiană');
INSERT INTO `ff_languages_names` VALUES (1739, 68, 81, 'nórčina (Bokmal)');
INSERT INTO `ff_languages_names` VALUES (1740, 68, 82, 'norveščina (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1741, 68, 86, 'norska (bokmål)');
INSERT INTO `ff_languages_names` VALUES (1742, 68, 89, 'Norveççe (Bokmål)');
INSERT INTO `ff_languages_names` VALUES (1743, 69, 9, 'непалски');
INSERT INTO `ff_languages_names` VALUES (1744, 69, 17, 'nepálština');
INSERT INTO `ff_languages_names` VALUES (1745, 69, 20, 'Nepalesisk');
INSERT INTO `ff_languages_names` VALUES (1746, 69, 21, 'Nepali');
INSERT INTO `ff_languages_names` VALUES (1747, 69, 22, 'γλώσσα του Νεπάλ');
INSERT INTO `ff_languages_names` VALUES (1748, 69, 23, 'Nepali');
INSERT INTO `ff_languages_names` VALUES (1749, 69, 25, 'nepalí');
INSERT INTO `ff_languages_names` VALUES (1750, 69, 26, 'nepaali');
INSERT INTO `ff_languages_names` VALUES (1751, 69, 29, 'nepali');
INSERT INTO `ff_languages_names` VALUES (1752, 69, 32, 'népalais');
INSERT INTO `ff_languages_names` VALUES (1753, 69, 40, 'Nepali');
INSERT INTO `ff_languages_names` VALUES (1754, 69, 42, 'Nepáli');
INSERT INTO `ff_languages_names` VALUES (1755, 69, 45, 'Nepali');
INSERT INTO `ff_languages_names` VALUES (1756, 69, 1, 'Nepalese');
INSERT INTO `ff_languages_names` VALUES (1757, 69, 59, 'Nepalų');
INSERT INTO `ff_languages_names` VALUES (1758, 69, 60, 'Nepāļu');
INSERT INTO `ff_languages_names` VALUES (1759, 69, 66, 'Nepaliż');
INSERT INTO `ff_languages_names` VALUES (1760, 69, 70, 'Nepalees');
INSERT INTO `ff_languages_names` VALUES (1761, 69, 72, 'Nepali');
INSERT INTO `ff_languages_names` VALUES (1762, 69, 74, 'nepalski');
INSERT INTO `ff_languages_names` VALUES (1763, 69, 75, 'Nepalês');
INSERT INTO `ff_languages_names` VALUES (1764, 69, 77, 'Nepaleză');
INSERT INTO `ff_languages_names` VALUES (1765, 69, 81, 'nepálčina');
INSERT INTO `ff_languages_names` VALUES (1766, 69, 82, 'nepalščina');
INSERT INTO `ff_languages_names` VALUES (1767, 69, 86, 'nepalesiska');
INSERT INTO `ff_languages_names` VALUES (1768, 69, 89, 'Nepalce');
INSERT INTO `ff_languages_names` VALUES (1769, 70, 9, 'холандски');
INSERT INTO `ff_languages_names` VALUES (1770, 70, 17, 'nizozemština (holandština)');
INSERT INTO `ff_languages_names` VALUES (1771, 70, 20, 'Hollandsk');
INSERT INTO `ff_languages_names` VALUES (1772, 70, 21, 'Niederländisch');
INSERT INTO `ff_languages_names` VALUES (1773, 70, 22, 'ολλανδικά');
INSERT INTO `ff_languages_names` VALUES (1774, 70, 23, 'Dutch');
INSERT INTO `ff_languages_names` VALUES (1775, 70, 25, 'neerlandés');
INSERT INTO `ff_languages_names` VALUES (1776, 70, 26, 'hollandi');
INSERT INTO `ff_languages_names` VALUES (1777, 70, 29, 'hollanti');
INSERT INTO `ff_languages_names` VALUES (1778, 70, 32, 'néerlandais');
INSERT INTO `ff_languages_names` VALUES (1779, 70, 40, 'Nizozemski');
INSERT INTO `ff_languages_names` VALUES (1780, 70, 42, 'Holland');
INSERT INTO `ff_languages_names` VALUES (1781, 70, 45, 'Hollenska');
INSERT INTO `ff_languages_names` VALUES (1782, 70, 1, 'Olandese');
INSERT INTO `ff_languages_names` VALUES (1783, 70, 59, 'Olandų');
INSERT INTO `ff_languages_names` VALUES (1784, 70, 60, 'Nīderlandiešu');
INSERT INTO `ff_languages_names` VALUES (1785, 70, 66, 'Olandiż');
INSERT INTO `ff_languages_names` VALUES (1786, 70, 70, 'Nederlands');
INSERT INTO `ff_languages_names` VALUES (1787, 70, 72, 'Nederlandsk');
INSERT INTO `ff_languages_names` VALUES (1788, 70, 74, 'holenderski');
INSERT INTO `ff_languages_names` VALUES (1789, 70, 75, 'Neerlandês');
INSERT INTO `ff_languages_names` VALUES (1790, 70, 77, 'Olandeză');
INSERT INTO `ff_languages_names` VALUES (1791, 70, 81, 'holandčina');
INSERT INTO `ff_languages_names` VALUES (1792, 70, 82, 'nizozemščina');
INSERT INTO `ff_languages_names` VALUES (1793, 70, 86, 'nederländska');
INSERT INTO `ff_languages_names` VALUES (1794, 70, 89, 'Hollandaca');
INSERT INTO `ff_languages_names` VALUES (1795, 71, 9, 'норвежки (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1796, 71, 17, 'norština (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1797, 71, 20, 'Norsk (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1798, 71, 21, 'Norwegisch (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1799, 71, 22, 'νορβηγικά (νίνορσκ)');
INSERT INTO `ff_languages_names` VALUES (1800, 71, 23, 'Norwegian (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1801, 71, 25, 'noruego (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1802, 71, 26, 'noora (uusnorra)');
INSERT INTO `ff_languages_names` VALUES (1803, 71, 29, 'norja (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1804, 71, 32, 'norvégien (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1805, 71, 40, 'novonorveški (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1806, 71, 42, 'Norvég (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1807, 71, 45, 'Norska (Nýnorska)');
INSERT INTO `ff_languages_names` VALUES (1808, 71, 1, 'Norvegese (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1809, 71, 59, 'Norvegų (naujoji kalba)');
INSERT INTO `ff_languages_names` VALUES (1810, 71, 60, 'Norvēģu (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1811, 71, 66, 'Norveġiż (Ninorsk)');
INSERT INTO `ff_languages_names` VALUES (1812, 71, 70, 'Noors (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1813, 71, 72, 'Norsk (Nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1814, 71, 74, 'norweski (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1815, 71, 75, 'Norueguês (Nynors)');
INSERT INTO `ff_languages_names` VALUES (1816, 71, 77, 'norveški (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1817, 71, 81, 'nórčina (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1818, 71, 82, 'norveščina (nynorsk)');
INSERT INTO `ff_languages_names` VALUES (1819, 71, 86, 'norska (nynorska)');
INSERT INTO `ff_languages_names` VALUES (1820, 71, 89, 'Norveççe (Norsk)');
INSERT INTO `ff_languages_names` VALUES (1821, 72, 9, 'норвежки');
INSERT INTO `ff_languages_names` VALUES (1822, 72, 17, 'norština');
INSERT INTO `ff_languages_names` VALUES (1823, 72, 20, 'Norsk');
INSERT INTO `ff_languages_names` VALUES (1824, 72, 21, 'Norwegisch');
INSERT INTO `ff_languages_names` VALUES (1825, 72, 22, 'νορβηγικά');
INSERT INTO `ff_languages_names` VALUES (1826, 72, 23, 'Norwegian');
INSERT INTO `ff_languages_names` VALUES (1827, 72, 25, 'noruego');
INSERT INTO `ff_languages_names` VALUES (1828, 72, 26, 'norra');
INSERT INTO `ff_languages_names` VALUES (1829, 72, 29, 'norja');
INSERT INTO `ff_languages_names` VALUES (1830, 72, 32, 'norvégien');
INSERT INTO `ff_languages_names` VALUES (1831, 72, 40, 'Norveški');
INSERT INTO `ff_languages_names` VALUES (1832, 72, 42, 'Ónorvég');
INSERT INTO `ff_languages_names` VALUES (1833, 72, 45, 'Norska');
INSERT INTO `ff_languages_names` VALUES (1834, 72, 1, 'Norvegese');
INSERT INTO `ff_languages_names` VALUES (1835, 72, 59, 'Norvegų');
INSERT INTO `ff_languages_names` VALUES (1836, 72, 60, 'Norvēģu');
INSERT INTO `ff_languages_names` VALUES (1837, 72, 66, 'Norveġiż');
INSERT INTO `ff_languages_names` VALUES (1838, 72, 70, 'Noors');
INSERT INTO `ff_languages_names` VALUES (1839, 72, 72, 'Norsk');
INSERT INTO `ff_languages_names` VALUES (1840, 72, 74, 'norweski');
INSERT INTO `ff_languages_names` VALUES (1841, 72, 75, 'Norueguês');
INSERT INTO `ff_languages_names` VALUES (1842, 72, 77, 'Norvegiană');
INSERT INTO `ff_languages_names` VALUES (1843, 72, 81, 'nórčina');
INSERT INTO `ff_languages_names` VALUES (1844, 72, 82, 'norveščina');
INSERT INTO `ff_languages_names` VALUES (1845, 72, 86, 'norska');
INSERT INTO `ff_languages_names` VALUES (1846, 72, 89, 'Norveççe');
INSERT INTO `ff_languages_names` VALUES (1847, 73, 9, 'окситански');
INSERT INTO `ff_languages_names` VALUES (1848, 73, 17, 'okcitánština');
INSERT INTO `ff_languages_names` VALUES (1849, 73, 20, 'Occitansk');
INSERT INTO `ff_languages_names` VALUES (1850, 73, 21, 'Okzitanisch (Andere)');
INSERT INTO `ff_languages_names` VALUES (1851, 73, 22, 'οξιτανικά (μετά το 1500)');
INSERT INTO `ff_languages_names` VALUES (1852, 73, 23, 'Occitan (post 1500); Provençal');
INSERT INTO `ff_languages_names` VALUES (1853, 73, 25, 'occitano (después de 1500); provenzal');
INSERT INTO `ff_languages_names` VALUES (1854, 73, 26, 'provansi; provanssaali');
INSERT INTO `ff_languages_names` VALUES (1855, 73, 29, 'oksitaani');
INSERT INTO `ff_languages_names` VALUES (1856, 73, 32, 'occitan (après 1500); provençal');
INSERT INTO `ff_languages_names` VALUES (1857, 73, 40, 'osetski');
INSERT INTO `ff_languages_names` VALUES (1858, 73, 42, 'Gall (1500 után); Provençal');
INSERT INTO `ff_languages_names` VALUES (1859, 73, 45, 'Occitan');
INSERT INTO `ff_languages_names` VALUES (1860, 73, 1, 'Occitano (dopo il 1500); Provenzale');
INSERT INTO `ff_languages_names` VALUES (1861, 73, 59, 'Oksitanų (po 1500 m.); provansalų');
INSERT INTO `ff_languages_names` VALUES (1862, 73, 60, 'Oksitāņu (pēc 1500.g.); provansāļu');
INSERT INTO `ff_languages_names` VALUES (1863, 73, 66, 'Oċċitan (wara l-1500); Provenzal');
INSERT INTO `ff_languages_names` VALUES (1864, 73, 70, 'Occitaans (na 1500); Provençaals');
INSERT INTO `ff_languages_names` VALUES (1865, 73, 72, 'Oksitansk');
INSERT INTO `ff_languages_names` VALUES (1866, 73, 74, 'oksytański (po 1500); prowansalski');
INSERT INTO `ff_languages_names` VALUES (1867, 73, 75, 'Occitânio (pós-1500); Provençal');
INSERT INTO `ff_languages_names` VALUES (1868, 73, 77, 'Occitană');
INSERT INTO `ff_languages_names` VALUES (1869, 73, 81, 'ocitánčina (po roku 1500); provensalčina');
INSERT INTO `ff_languages_names` VALUES (1870, 73, 82, 'okcitanščina (po 1500); provansalščina');
INSERT INTO `ff_languages_names` VALUES (1871, 73, 86, 'occitanska (efter 1500); provensalska');
INSERT INTO `ff_languages_names` VALUES (1872, 73, 89, 'Oksitanca');
INSERT INTO `ff_languages_names` VALUES (1873, 74, 9, 'полски');
INSERT INTO `ff_languages_names` VALUES (1874, 74, 17, 'polština');
INSERT INTO `ff_languages_names` VALUES (1875, 74, 20, 'Polsk');
INSERT INTO `ff_languages_names` VALUES (1876, 74, 21, 'Polnisch');
INSERT INTO `ff_languages_names` VALUES (1877, 74, 22, 'πολωνικά');
INSERT INTO `ff_languages_names` VALUES (1878, 74, 23, 'Polish');
INSERT INTO `ff_languages_names` VALUES (1879, 74, 25, 'polaco');
INSERT INTO `ff_languages_names` VALUES (1880, 74, 26, 'poola');
INSERT INTO `ff_languages_names` VALUES (1881, 74, 29, 'puola');
INSERT INTO `ff_languages_names` VALUES (1882, 74, 32, 'polonais');
INSERT INTO `ff_languages_names` VALUES (1883, 74, 40, 'Poljski');
INSERT INTO `ff_languages_names` VALUES (1884, 74, 42, 'Lengyel');
INSERT INTO `ff_languages_names` VALUES (1885, 74, 45, 'Pólska');
INSERT INTO `ff_languages_names` VALUES (1886, 74, 1, 'Polacco');
INSERT INTO `ff_languages_names` VALUES (1887, 74, 59, 'Lenkų');
INSERT INTO `ff_languages_names` VALUES (1888, 74, 60, 'Poļu');
INSERT INTO `ff_languages_names` VALUES (1889, 74, 66, 'Pollakk');
INSERT INTO `ff_languages_names` VALUES (1890, 74, 70, 'Pools');
INSERT INTO `ff_languages_names` VALUES (1891, 74, 72, 'Polsk');
INSERT INTO `ff_languages_names` VALUES (1892, 74, 74, 'polski');
INSERT INTO `ff_languages_names` VALUES (1893, 74, 75, 'Polaco');
INSERT INTO `ff_languages_names` VALUES (1894, 74, 77, 'Polonă');
INSERT INTO `ff_languages_names` VALUES (1895, 74, 81, 'poľština');
INSERT INTO `ff_languages_names` VALUES (1896, 74, 82, 'poljščina');
INSERT INTO `ff_languages_names` VALUES (1897, 74, 86, 'polska');
INSERT INTO `ff_languages_names` VALUES (1898, 74, 89, 'Lehçe');
INSERT INTO `ff_languages_names` VALUES (1899, 75, 9, 'португалски');
INSERT INTO `ff_languages_names` VALUES (1900, 75, 17, 'portugalština');
INSERT INTO `ff_languages_names` VALUES (1901, 75, 20, 'Portugisisk');
INSERT INTO `ff_languages_names` VALUES (1902, 75, 21, 'Portugiesisch');
INSERT INTO `ff_languages_names` VALUES (1903, 75, 22, 'πορτογαλικά');
INSERT INTO `ff_languages_names` VALUES (1904, 75, 23, 'Portuguese');
INSERT INTO `ff_languages_names` VALUES (1905, 75, 25, 'portugués');
INSERT INTO `ff_languages_names` VALUES (1906, 75, 26, 'portugali');
INSERT INTO `ff_languages_names` VALUES (1907, 75, 29, 'portugali');
INSERT INTO `ff_languages_names` VALUES (1908, 75, 32, 'portugais');
INSERT INTO `ff_languages_names` VALUES (1909, 75, 40, 'Portugalski');
INSERT INTO `ff_languages_names` VALUES (1910, 75, 42, 'Portugál');
INSERT INTO `ff_languages_names` VALUES (1911, 75, 45, 'Portúgalska');
INSERT INTO `ff_languages_names` VALUES (1912, 75, 1, 'Portoghese');
INSERT INTO `ff_languages_names` VALUES (1913, 75, 59, 'Portugalų');
INSERT INTO `ff_languages_names` VALUES (1914, 75, 60, 'Portugāļu');
INSERT INTO `ff_languages_names` VALUES (1915, 75, 66, 'Portugiż');
INSERT INTO `ff_languages_names` VALUES (1916, 75, 70, 'Portugees');
INSERT INTO `ff_languages_names` VALUES (1917, 75, 72, 'Portugisisk');
INSERT INTO `ff_languages_names` VALUES (1918, 75, 74, 'portugalski');
INSERT INTO `ff_languages_names` VALUES (1919, 75, 75, 'Português');
INSERT INTO `ff_languages_names` VALUES (1920, 75, 77, 'Portugheză');
INSERT INTO `ff_languages_names` VALUES (1921, 75, 81, 'portugalčina');
INSERT INTO `ff_languages_names` VALUES (1922, 75, 82, 'protugalščina');
INSERT INTO `ff_languages_names` VALUES (1923, 75, 86, 'portugisiska');
INSERT INTO `ff_languages_names` VALUES (1924, 75, 89, 'Portekizce');
INSERT INTO `ff_languages_names` VALUES (1925, 76, 9, 'реторомански');
INSERT INTO `ff_languages_names` VALUES (1926, 76, 17, 'rétorománština');
INSERT INTO `ff_languages_names` VALUES (1927, 76, 20, 'Rætoromansk');
INSERT INTO `ff_languages_names` VALUES (1928, 76, 21, 'Rätoromanisch');
INSERT INTO `ff_languages_names` VALUES (1929, 76, 22, 'ρετο-ρωμανικά');
INSERT INTO `ff_languages_names` VALUES (1930, 76, 23, 'Raeto-Romance');
INSERT INTO `ff_languages_names` VALUES (1931, 76, 25, 'reto-románicas');
INSERT INTO `ff_languages_names` VALUES (1932, 76, 26, 'retoromaani');
INSERT INTO `ff_languages_names` VALUES (1933, 76, 29, 'retoromaani');
INSERT INTO `ff_languages_names` VALUES (1934, 76, 32, 'rhéto-roman ; (romanche)');
INSERT INTO `ff_languages_names` VALUES (1935, 76, 40, 'retoromanski');
INSERT INTO `ff_languages_names` VALUES (1936, 76, 42, 'Rétoromán');
INSERT INTO `ff_languages_names` VALUES (1937, 76, 45, 'Raeto-Romance');
INSERT INTO `ff_languages_names` VALUES (1938, 76, 1, 'Romancio, Reto-romanzo');
INSERT INTO `ff_languages_names` VALUES (1939, 76, 59, 'Retoromanų kalbos');
INSERT INTO `ff_languages_names` VALUES (1940, 76, 60, 'Retoromāņu');
INSERT INTO `ff_languages_names` VALUES (1941, 76, 66, 'Reto-Romanz');
INSERT INTO `ff_languages_names` VALUES (1942, 76, 70, 'Retoromaans');
INSERT INTO `ff_languages_names` VALUES (1943, 76, 72, 'Retoromansk');
INSERT INTO `ff_languages_names` VALUES (1944, 76, 74, 'retoromański');
INSERT INTO `ff_languages_names` VALUES (1945, 76, 75, 'Reto-Romano');
INSERT INTO `ff_languages_names` VALUES (1946, 76, 77, 'Reto-Română');
INSERT INTO `ff_languages_names` VALUES (1947, 76, 81, 'rétorománčina');
INSERT INTO `ff_languages_names` VALUES (1948, 76, 82, 'retoromanščina');
INSERT INTO `ff_languages_names` VALUES (1949, 76, 86, 'rätoromanska');
INSERT INTO `ff_languages_names` VALUES (1950, 76, 89, 'Raeto-Romance');
INSERT INTO `ff_languages_names` VALUES (1951, 77, 9, 'румънски');
INSERT INTO `ff_languages_names` VALUES (1952, 77, 17, 'rumunština');
INSERT INTO `ff_languages_names` VALUES (1953, 77, 20, 'Rumænsk');
INSERT INTO `ff_languages_names` VALUES (1954, 77, 21, 'Rumänisch');
INSERT INTO `ff_languages_names` VALUES (1955, 77, 22, 'ρουμανικά');
INSERT INTO `ff_languages_names` VALUES (1956, 77, 23, 'Romanian');
INSERT INTO `ff_languages_names` VALUES (1957, 77, 25, 'rumano');
INSERT INTO `ff_languages_names` VALUES (1958, 77, 26, 'rumeenia');
INSERT INTO `ff_languages_names` VALUES (1959, 77, 29, 'romania');
INSERT INTO `ff_languages_names` VALUES (1960, 77, 32, 'roumain');
INSERT INTO `ff_languages_names` VALUES (1961, 77, 40, 'Rumunjski');
INSERT INTO `ff_languages_names` VALUES (1962, 77, 42, 'Román');
INSERT INTO `ff_languages_names` VALUES (1963, 77, 45, 'Rúmenska');
INSERT INTO `ff_languages_names` VALUES (1964, 77, 1, 'Rumeno');
INSERT INTO `ff_languages_names` VALUES (1965, 77, 59, 'Rumunų');
INSERT INTO `ff_languages_names` VALUES (1966, 77, 60, 'Rumāņu');
INSERT INTO `ff_languages_names` VALUES (1967, 77, 66, 'Rumen');
INSERT INTO `ff_languages_names` VALUES (1968, 77, 70, 'Roemeens');
INSERT INTO `ff_languages_names` VALUES (1969, 77, 72, 'Rumensk');
INSERT INTO `ff_languages_names` VALUES (1970, 77, 74, 'rumuński');
INSERT INTO `ff_languages_names` VALUES (1971, 77, 75, 'Romeno');
INSERT INTO `ff_languages_names` VALUES (1972, 77, 77, 'Română');
INSERT INTO `ff_languages_names` VALUES (1973, 77, 81, 'rumunčina');
INSERT INTO `ff_languages_names` VALUES (1974, 77, 82, 'romunščina');
INSERT INTO `ff_languages_names` VALUES (1975, 77, 86, 'rumänska');
INSERT INTO `ff_languages_names` VALUES (1976, 77, 89, 'Rumence');
INSERT INTO `ff_languages_names` VALUES (1977, 78, 9, 'руски');
INSERT INTO `ff_languages_names` VALUES (1978, 78, 17, 'ruština');
INSERT INTO `ff_languages_names` VALUES (1979, 78, 20, 'Russisk');
INSERT INTO `ff_languages_names` VALUES (1980, 78, 21, 'Russisch');
INSERT INTO `ff_languages_names` VALUES (1981, 78, 22, 'ρωσικά');
INSERT INTO `ff_languages_names` VALUES (1982, 78, 23, 'Russian');
INSERT INTO `ff_languages_names` VALUES (1983, 78, 25, 'ruso');
INSERT INTO `ff_languages_names` VALUES (1984, 78, 26, 'vene');
INSERT INTO `ff_languages_names` VALUES (1985, 78, 29, 'venäjä');
INSERT INTO `ff_languages_names` VALUES (1986, 78, 32, 'russe');
INSERT INTO `ff_languages_names` VALUES (1987, 78, 40, 'ruski');
INSERT INTO `ff_languages_names` VALUES (1988, 78, 42, 'Orosz');
INSERT INTO `ff_languages_names` VALUES (1989, 78, 45, 'Rússneska');
INSERT INTO `ff_languages_names` VALUES (1990, 78, 1, 'Russo');
INSERT INTO `ff_languages_names` VALUES (1991, 78, 59, 'Rusų');
INSERT INTO `ff_languages_names` VALUES (1992, 78, 60, 'Krievu');
INSERT INTO `ff_languages_names` VALUES (1993, 78, 66, 'Russu');
INSERT INTO `ff_languages_names` VALUES (1994, 78, 70, 'Russisch');
INSERT INTO `ff_languages_names` VALUES (1995, 78, 72, 'Russisk');
INSERT INTO `ff_languages_names` VALUES (1996, 78, 74, 'rosyjski');
INSERT INTO `ff_languages_names` VALUES (1997, 78, 75, 'Russo');
INSERT INTO `ff_languages_names` VALUES (1998, 78, 77, 'Rusă');
INSERT INTO `ff_languages_names` VALUES (1999, 78, 81, 'ruština');
INSERT INTO `ff_languages_names` VALUES (2000, 78, 82, 'ruščina');
INSERT INTO `ff_languages_names` VALUES (2001, 78, 86, 'ryska');
INSERT INTO `ff_languages_names` VALUES (2002, 78, 89, 'Rusça');
INSERT INTO `ff_languages_names` VALUES (2003, 79, 9, 'сардински');
INSERT INTO `ff_languages_names` VALUES (2004, 79, 17, 'sardština');
INSERT INTO `ff_languages_names` VALUES (2005, 79, 20, 'Sardinsk');
INSERT INTO `ff_languages_names` VALUES (2006, 79, 21, 'Sardisch');
INSERT INTO `ff_languages_names` VALUES (2007, 79, 22, 'σαρδηνιακά');
INSERT INTO `ff_languages_names` VALUES (2008, 79, 23, 'Sardinian');
INSERT INTO `ff_languages_names` VALUES (2009, 79, 25, 'sardo');
INSERT INTO `ff_languages_names` VALUES (2010, 79, 26, 'sardi');
INSERT INTO `ff_languages_names` VALUES (2011, 79, 29, 'sardi');
INSERT INTO `ff_languages_names` VALUES (2012, 79, 32, 'sarde');
INSERT INTO `ff_languages_names` VALUES (2013, 79, 40, 'sardski');
INSERT INTO `ff_languages_names` VALUES (2014, 79, 42, 'Szardíniai');
INSERT INTO `ff_languages_names` VALUES (2015, 79, 45, 'Sardinian');
INSERT INTO `ff_languages_names` VALUES (2016, 79, 1, 'Sardo');
INSERT INTO `ff_languages_names` VALUES (2017, 79, 59, 'Sardinų');
INSERT INTO `ff_languages_names` VALUES (2018, 79, 60, 'Sardīniešu');
INSERT INTO `ff_languages_names` VALUES (2019, 79, 66, 'Sard');
INSERT INTO `ff_languages_names` VALUES (2020, 79, 70, 'Sardijns');
INSERT INTO `ff_languages_names` VALUES (2021, 79, 72, 'Sardinsk');
INSERT INTO `ff_languages_names` VALUES (2022, 79, 74, 'sardyński');
INSERT INTO `ff_languages_names` VALUES (2023, 79, 75, 'Sardo');
INSERT INTO `ff_languages_names` VALUES (2024, 79, 77, 'Sardiniană');
INSERT INTO `ff_languages_names` VALUES (2025, 79, 81, 'sardínsky jazyk');
INSERT INTO `ff_languages_names` VALUES (2026, 79, 82, 'sardščina');
INSERT INTO `ff_languages_names` VALUES (2027, 79, 86, 'sardiska');
INSERT INTO `ff_languages_names` VALUES (2028, 79, 89, 'Sarduca');
INSERT INTO `ff_languages_names` VALUES (2029, 80, 9, 'северносаамски');
INSERT INTO `ff_languages_names` VALUES (2030, 80, 17, 'sami jazyky, severní');
INSERT INTO `ff_languages_names` VALUES (2031, 80, 20, 'Nordsamisk');
INSERT INTO `ff_languages_names` VALUES (2032, 80, 21, 'Nordsamisch');
INSERT INTO `ff_languages_names` VALUES (2033, 80, 22, 'βορειολαπωνικά');
INSERT INTO `ff_languages_names` VALUES (2034, 80, 23, 'Northern Sami');
INSERT INTO `ff_languages_names` VALUES (2035, 80, 25, 'sami septentrional');
INSERT INTO `ff_languages_names` VALUES (2036, 80, 26, 'põhjasaami');
INSERT INTO `ff_languages_names` VALUES (2037, 80, 29, 'saame, pohjois-');
INSERT INTO `ff_languages_names` VALUES (2038, 80, 32, 'sami du Nord');
INSERT INTO `ff_languages_names` VALUES (2039, 80, 40, 'Northern Sami');
INSERT INTO `ff_languages_names` VALUES (2040, 80, 42, 'Északi sami');
INSERT INTO `ff_languages_names` VALUES (2041, 80, 45, 'en:Northern Sami');
INSERT INTO `ff_languages_names` VALUES (2042, 80, 1, 'Sami settentrionale');
INSERT INTO `ff_languages_names` VALUES (2043, 80, 59, 'Šiaurės samių');
INSERT INTO `ff_languages_names` VALUES (2044, 80, 60, 'Ziemeļsāmu');
INSERT INTO `ff_languages_names` VALUES (2045, 80, 66, 'Sami ta\' Fuq');
INSERT INTO `ff_languages_names` VALUES (2046, 80, 70, 'Noord-Samisch');
INSERT INTO `ff_languages_names` VALUES (2047, 80, 72, 'Nordsamisk');
INSERT INTO `ff_languages_names` VALUES (2048, 80, 74, 'północny sami');
INSERT INTO `ff_languages_names` VALUES (2049, 80, 75, 'Sami Setentrional');
INSERT INTO `ff_languages_names` VALUES (2050, 80, 77, 'Sami');
INSERT INTO `ff_languages_names` VALUES (2051, 80, 81, 'sami (laponské) jazyky, severné');
INSERT INTO `ff_languages_names` VALUES (2052, 80, 82, 'severna samščina');
INSERT INTO `ff_languages_names` VALUES (2053, 80, 86, 'nordsamiska');
INSERT INTO `ff_languages_names` VALUES (2054, 80, 89, 'Kuzey Samice');
INSERT INTO `ff_languages_names` VALUES (2055, 81, 9, 'словашки');
INSERT INTO `ff_languages_names` VALUES (2056, 81, 17, 'slovenština');
INSERT INTO `ff_languages_names` VALUES (2057, 81, 20, 'Slovakisk');
INSERT INTO `ff_languages_names` VALUES (2058, 81, 21, 'Slowakisch');
INSERT INTO `ff_languages_names` VALUES (2059, 81, 22, 'σλοβακικά');
INSERT INTO `ff_languages_names` VALUES (2060, 81, 23, 'Slovak');
INSERT INTO `ff_languages_names` VALUES (2061, 81, 25, 'eslovaco');
INSERT INTO `ff_languages_names` VALUES (2062, 81, 26, 'slovaki');
INSERT INTO `ff_languages_names` VALUES (2063, 81, 29, 'slovakki');
INSERT INTO `ff_languages_names` VALUES (2064, 81, 32, 'slovaque');
INSERT INTO `ff_languages_names` VALUES (2065, 81, 40, 'Slovački');
INSERT INTO `ff_languages_names` VALUES (2066, 81, 42, 'Szlovák');
INSERT INTO `ff_languages_names` VALUES (2067, 81, 45, 'Slovakíska');
INSERT INTO `ff_languages_names` VALUES (2068, 81, 1, 'Slovacco');
INSERT INTO `ff_languages_names` VALUES (2069, 81, 59, 'Slovakų');
INSERT INTO `ff_languages_names` VALUES (2070, 81, 60, 'Slovāku');
INSERT INTO `ff_languages_names` VALUES (2071, 81, 66, 'Slovakk');
INSERT INTO `ff_languages_names` VALUES (2072, 81, 70, 'Slowaaks');
INSERT INTO `ff_languages_names` VALUES (2073, 81, 72, 'Slovakisk');
INSERT INTO `ff_languages_names` VALUES (2074, 81, 74, 'słowacki');
INSERT INTO `ff_languages_names` VALUES (2075, 81, 75, 'Eslovaco');
INSERT INTO `ff_languages_names` VALUES (2076, 81, 77, 'Slovacă');
INSERT INTO `ff_languages_names` VALUES (2077, 81, 81, 'slovenčina');
INSERT INTO `ff_languages_names` VALUES (2078, 81, 82, 'slovaščina');
INSERT INTO `ff_languages_names` VALUES (2079, 81, 86, 'slovakiska');
INSERT INTO `ff_languages_names` VALUES (2080, 81, 89, 'Slovakça');
INSERT INTO `ff_languages_names` VALUES (2081, 82, 9, 'словенски');
INSERT INTO `ff_languages_names` VALUES (2082, 82, 17, 'slovinština');
INSERT INTO `ff_languages_names` VALUES (2083, 82, 20, 'Slovensk');
INSERT INTO `ff_languages_names` VALUES (2084, 82, 21, 'Slowenisch');
INSERT INTO `ff_languages_names` VALUES (2085, 82, 22, 'σλοβενικά');
INSERT INTO `ff_languages_names` VALUES (2086, 82, 23, 'Slovenian');
INSERT INTO `ff_languages_names` VALUES (2087, 82, 25, 'esloveno');
INSERT INTO `ff_languages_names` VALUES (2088, 82, 26, 'sloveeni');
INSERT INTO `ff_languages_names` VALUES (2089, 82, 29, 'sloveeni');
INSERT INTO `ff_languages_names` VALUES (2090, 82, 32, 'slovène');
INSERT INTO `ff_languages_names` VALUES (2091, 82, 40, 'Slovenski');
INSERT INTO `ff_languages_names` VALUES (2092, 82, 42, 'Szlovén');
INSERT INTO `ff_languages_names` VALUES (2093, 82, 45, 'Slóvenska');
INSERT INTO `ff_languages_names` VALUES (2094, 82, 1, 'Sloveno');
INSERT INTO `ff_languages_names` VALUES (2095, 82, 59, 'Slovėnų');
INSERT INTO `ff_languages_names` VALUES (2096, 82, 60, 'Slovēņu');
INSERT INTO `ff_languages_names` VALUES (2097, 82, 66, 'Sloven');
INSERT INTO `ff_languages_names` VALUES (2098, 82, 70, 'Sloveens');
INSERT INTO `ff_languages_names` VALUES (2099, 82, 72, 'Slovensk');
INSERT INTO `ff_languages_names` VALUES (2100, 82, 74, 'słoweński');
INSERT INTO `ff_languages_names` VALUES (2101, 82, 75, 'Eslovénio');
INSERT INTO `ff_languages_names` VALUES (2102, 82, 77, 'Slovenă');
INSERT INTO `ff_languages_names` VALUES (2103, 82, 81, 'slovinčina');
INSERT INTO `ff_languages_names` VALUES (2104, 82, 82, 'slovenščina');
INSERT INTO `ff_languages_names` VALUES (2105, 82, 86, 'slovenska');
INSERT INTO `ff_languages_names` VALUES (2106, 82, 89, 'Slovence');
INSERT INTO `ff_languages_names` VALUES (2107, 83, 9, 'сомалийски');
INSERT INTO `ff_languages_names` VALUES (2108, 83, 17, 'somálština');
INSERT INTO `ff_languages_names` VALUES (2109, 83, 20, 'Somalisk');
INSERT INTO `ff_languages_names` VALUES (2110, 83, 21, 'Somali');
INSERT INTO `ff_languages_names` VALUES (2111, 83, 22, 'σομαλικά');
INSERT INTO `ff_languages_names` VALUES (2112, 83, 23, 'Somali');
INSERT INTO `ff_languages_names` VALUES (2113, 83, 25, 'somalí');
INSERT INTO `ff_languages_names` VALUES (2114, 83, 26, 'somaali');
INSERT INTO `ff_languages_names` VALUES (2115, 83, 29, 'somali');
INSERT INTO `ff_languages_names` VALUES (2116, 83, 32, 'somali');
INSERT INTO `ff_languages_names` VALUES (2117, 83, 40, 'somalski');
INSERT INTO `ff_languages_names` VALUES (2118, 83, 42, 'Szomáli');
INSERT INTO `ff_languages_names` VALUES (2119, 83, 45, 'Somali');
INSERT INTO `ff_languages_names` VALUES (2120, 83, 1, 'Somalo');
INSERT INTO `ff_languages_names` VALUES (2121, 83, 59, 'Somalių');
INSERT INTO `ff_languages_names` VALUES (2122, 83, 60, 'Somāļu');
INSERT INTO `ff_languages_names` VALUES (2123, 83, 66, 'Somali');
INSERT INTO `ff_languages_names` VALUES (2124, 83, 70, 'Somalisch');
INSERT INTO `ff_languages_names` VALUES (2125, 83, 72, 'Somalisk');
INSERT INTO `ff_languages_names` VALUES (2126, 83, 74, 'somalijski');
INSERT INTO `ff_languages_names` VALUES (2127, 83, 75, 'Somali');
INSERT INTO `ff_languages_names` VALUES (2128, 83, 77, 'Somaleză');
INSERT INTO `ff_languages_names` VALUES (2129, 83, 81, 'somálčina');
INSERT INTO `ff_languages_names` VALUES (2130, 83, 82, 'somalščina');
INSERT INTO `ff_languages_names` VALUES (2131, 83, 86, 'somaliska');
INSERT INTO `ff_languages_names` VALUES (2132, 83, 89, 'Somalice');
INSERT INTO `ff_languages_names` VALUES (2133, 84, 9, 'албански');
INSERT INTO `ff_languages_names` VALUES (2134, 84, 17, 'albánština');
INSERT INTO `ff_languages_names` VALUES (2135, 84, 20, 'Albansk');
INSERT INTO `ff_languages_names` VALUES (2136, 84, 21, 'Albanisch');
INSERT INTO `ff_languages_names` VALUES (2137, 84, 22, 'αλβανικά');
INSERT INTO `ff_languages_names` VALUES (2138, 84, 23, 'Albanian');
INSERT INTO `ff_languages_names` VALUES (2139, 84, 25, 'albanés');
INSERT INTO `ff_languages_names` VALUES (2140, 84, 26, 'albaania');
INSERT INTO `ff_languages_names` VALUES (2141, 84, 29, 'albania');
INSERT INTO `ff_languages_names` VALUES (2142, 84, 32, 'albanais');
INSERT INTO `ff_languages_names` VALUES (2143, 84, 40, 'albanski');
INSERT INTO `ff_languages_names` VALUES (2144, 84, 42, 'Albán');
INSERT INTO `ff_languages_names` VALUES (2145, 84, 45, 'Albanska');
INSERT INTO `ff_languages_names` VALUES (2146, 84, 1, 'Albanese');
INSERT INTO `ff_languages_names` VALUES (2147, 84, 59, 'Albanų');
INSERT INTO `ff_languages_names` VALUES (2148, 84, 60, 'Albāņu');
INSERT INTO `ff_languages_names` VALUES (2149, 84, 66, 'Albaniż');
INSERT INTO `ff_languages_names` VALUES (2150, 84, 70, 'Albanees');
INSERT INTO `ff_languages_names` VALUES (2151, 84, 72, 'Albansk');
INSERT INTO `ff_languages_names` VALUES (2152, 84, 74, 'albański');
INSERT INTO `ff_languages_names` VALUES (2153, 84, 75, 'Albanês');
INSERT INTO `ff_languages_names` VALUES (2154, 84, 77, 'Albaneză');
INSERT INTO `ff_languages_names` VALUES (2155, 84, 81, 'albánčina');
INSERT INTO `ff_languages_names` VALUES (2156, 84, 82, 'albanščina');
INSERT INTO `ff_languages_names` VALUES (2157, 84, 86, 'albanska');
INSERT INTO `ff_languages_names` VALUES (2158, 84, 89, 'Arnavutça');
INSERT INTO `ff_languages_names` VALUES (2159, 85, 9, 'сръбски');
INSERT INTO `ff_languages_names` VALUES (2160, 85, 17, 'srbština');
INSERT INTO `ff_languages_names` VALUES (2161, 85, 20, 'Serbisk');
INSERT INTO `ff_languages_names` VALUES (2162, 85, 21, 'Serbisch');
INSERT INTO `ff_languages_names` VALUES (2163, 85, 22, 'σερβικά');
INSERT INTO `ff_languages_names` VALUES (2164, 85, 23, 'Serbian');
INSERT INTO `ff_languages_names` VALUES (2165, 85, 25, 'serbio');
INSERT INTO `ff_languages_names` VALUES (2166, 85, 26, 'serbia');
INSERT INTO `ff_languages_names` VALUES (2167, 85, 29, 'serbia');
INSERT INTO `ff_languages_names` VALUES (2168, 85, 32, 'serbe');
INSERT INTO `ff_languages_names` VALUES (2169, 85, 40, 'srpski');
INSERT INTO `ff_languages_names` VALUES (2170, 85, 42, 'Szerb');
INSERT INTO `ff_languages_names` VALUES (2171, 85, 45, 'Serbneska');
INSERT INTO `ff_languages_names` VALUES (2172, 85, 1, 'Serbo');
INSERT INTO `ff_languages_names` VALUES (2173, 85, 59, 'Serbų');
INSERT INTO `ff_languages_names` VALUES (2174, 85, 60, 'Serbu');
INSERT INTO `ff_languages_names` VALUES (2175, 85, 66, 'Serb');
INSERT INTO `ff_languages_names` VALUES (2176, 85, 70, 'Servisch');
INSERT INTO `ff_languages_names` VALUES (2177, 85, 72, 'Serbisk');
INSERT INTO `ff_languages_names` VALUES (2178, 85, 74, 'serbski');
INSERT INTO `ff_languages_names` VALUES (2179, 85, 75, 'Sérvio');
INSERT INTO `ff_languages_names` VALUES (2180, 85, 77, 'Sârbă');
INSERT INTO `ff_languages_names` VALUES (2181, 85, 81, 'srbčina');
INSERT INTO `ff_languages_names` VALUES (2182, 85, 82, 'srbščina');
INSERT INTO `ff_languages_names` VALUES (2183, 85, 86, 'serbiska');
INSERT INTO `ff_languages_names` VALUES (2184, 85, 89, 'Sırpça');
INSERT INTO `ff_languages_names` VALUES (2185, 86, 9, 'шведски');
INSERT INTO `ff_languages_names` VALUES (2186, 86, 17, 'švédština');
INSERT INTO `ff_languages_names` VALUES (2187, 86, 20, 'Svensk');
INSERT INTO `ff_languages_names` VALUES (2188, 86, 21, 'Schwedisch');
INSERT INTO `ff_languages_names` VALUES (2189, 86, 22, 'Σουηδικά');
INSERT INTO `ff_languages_names` VALUES (2190, 86, 23, 'Swedish');
INSERT INTO `ff_languages_names` VALUES (2191, 86, 25, 'Sueco');
INSERT INTO `ff_languages_names` VALUES (2192, 86, 26, 'rootsi');
INSERT INTO `ff_languages_names` VALUES (2193, 86, 29, 'ruotsi');
INSERT INTO `ff_languages_names` VALUES (2194, 86, 32, 'Suédois');
INSERT INTO `ff_languages_names` VALUES (2195, 86, 40, 'Švedski');
INSERT INTO `ff_languages_names` VALUES (2196, 86, 42, 'svéd');
INSERT INTO `ff_languages_names` VALUES (2197, 86, 45, 'Sænska');
INSERT INTO `ff_languages_names` VALUES (2198, 86, 1, 'Svedese');
INSERT INTO `ff_languages_names` VALUES (2199, 86, 59, 'Švedų');
INSERT INTO `ff_languages_names` VALUES (2200, 86, 60, 'Zviedru');
INSERT INTO `ff_languages_names` VALUES (2201, 86, 66, 'Svediż');
INSERT INTO `ff_languages_names` VALUES (2202, 86, 70, 'Zweeds');
INSERT INTO `ff_languages_names` VALUES (2203, 86, 72, 'Svensk');
INSERT INTO `ff_languages_names` VALUES (2204, 86, 74, 'szwedzki');
INSERT INTO `ff_languages_names` VALUES (2205, 86, 75, 'Sueco');
INSERT INTO `ff_languages_names` VALUES (2206, 86, 77, 'Suedeză');
INSERT INTO `ff_languages_names` VALUES (2207, 86, 81, 'švédčina');
INSERT INTO `ff_languages_names` VALUES (2208, 86, 82, 'švedščina');
INSERT INTO `ff_languages_names` VALUES (2209, 86, 86, 'Svenska');
INSERT INTO `ff_languages_names` VALUES (2210, 86, 89, 'İsveççe');
INSERT INTO `ff_languages_names` VALUES (2211, 87, 9, 'суахили');
INSERT INTO `ff_languages_names` VALUES (2212, 87, 17, 'svahilština');
INSERT INTO `ff_languages_names` VALUES (2213, 87, 20, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2214, 87, 21, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2215, 87, 22, 'σουαχίλι');
INSERT INTO `ff_languages_names` VALUES (2216, 87, 23, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2217, 87, 25, 'swahili');
INSERT INTO `ff_languages_names` VALUES (2218, 87, 26, 'suahiili');
INSERT INTO `ff_languages_names` VALUES (2219, 87, 29, 'swahili');
INSERT INTO `ff_languages_names` VALUES (2220, 87, 32, 'swahili');
INSERT INTO `ff_languages_names` VALUES (2221, 87, 40, 'svahili');
INSERT INTO `ff_languages_names` VALUES (2222, 87, 42, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2223, 87, 45, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2224, 87, 1, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2225, 87, 59, 'Svahilių');
INSERT INTO `ff_languages_names` VALUES (2226, 87, 60, 'Svahilu');
INSERT INTO `ff_languages_names` VALUES (2227, 87, 66, 'Swaħili');
INSERT INTO `ff_languages_names` VALUES (2228, 87, 70, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2229, 87, 72, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2230, 87, 74, 'suahili');
INSERT INTO `ff_languages_names` VALUES (2231, 87, 75, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2232, 87, 77, 'Swahili');
INSERT INTO `ff_languages_names` VALUES (2233, 87, 81, 'svahilčina');
INSERT INTO `ff_languages_names` VALUES (2234, 87, 82, 'svahili');
INSERT INTO `ff_languages_names` VALUES (2235, 87, 86, 'swahili');
INSERT INTO `ff_languages_names` VALUES (2236, 87, 89, 'swahilice');
INSERT INTO `ff_languages_names` VALUES (2237, 88, 9, 'туркменски');
INSERT INTO `ff_languages_names` VALUES (2238, 88, 17, 'turkmenština');
INSERT INTO `ff_languages_names` VALUES (2239, 88, 20, 'Turkmensk');
INSERT INTO `ff_languages_names` VALUES (2240, 88, 21, 'Turkmenisch');
INSERT INTO `ff_languages_names` VALUES (2241, 88, 22, 'τουρκμενικά');
INSERT INTO `ff_languages_names` VALUES (2242, 88, 23, 'Turkmen');
INSERT INTO `ff_languages_names` VALUES (2243, 88, 25, 'turcomano');
INSERT INTO `ff_languages_names` VALUES (2244, 88, 26, 'turkmeeni; türkmeeni');
INSERT INTO `ff_languages_names` VALUES (2245, 88, 29, 'turkmeeni');
INSERT INTO `ff_languages_names` VALUES (2246, 88, 32, 'turkmène');
INSERT INTO `ff_languages_names` VALUES (2247, 88, 40, 'turkmenski');
INSERT INTO `ff_languages_names` VALUES (2248, 88, 42, 'Türkmén');
INSERT INTO `ff_languages_names` VALUES (2249, 88, 45, 'Turkmen');
INSERT INTO `ff_languages_names` VALUES (2250, 88, 1, 'Turcmeno');
INSERT INTO `ff_languages_names` VALUES (2251, 88, 59, 'Turkmėnų');
INSERT INTO `ff_languages_names` VALUES (2252, 88, 60, 'Turkmēņu');
INSERT INTO `ff_languages_names` VALUES (2253, 88, 66, 'Turkmen');
INSERT INTO `ff_languages_names` VALUES (2254, 88, 70, 'Turkmeens');
INSERT INTO `ff_languages_names` VALUES (2255, 88, 72, 'Turkmensk');
INSERT INTO `ff_languages_names` VALUES (2256, 88, 74, 'turkmeński');
INSERT INTO `ff_languages_names` VALUES (2257, 88, 75, 'Turquemeno');
INSERT INTO `ff_languages_names` VALUES (2258, 88, 77, 'Turmenă');
INSERT INTO `ff_languages_names` VALUES (2259, 88, 81, 'turkménčina');
INSERT INTO `ff_languages_names` VALUES (2260, 88, 82, 'turkmenščina');
INSERT INTO `ff_languages_names` VALUES (2261, 88, 86, 'turkmeniska');
INSERT INTO `ff_languages_names` VALUES (2262, 88, 89, 'Türkmence');
INSERT INTO `ff_languages_names` VALUES (2263, 89, 9, 'турски');
INSERT INTO `ff_languages_names` VALUES (2264, 89, 17, 'turečtina');
INSERT INTO `ff_languages_names` VALUES (2265, 89, 20, 'Tyrkisk');
INSERT INTO `ff_languages_names` VALUES (2266, 89, 21, 'Türkisch');
INSERT INTO `ff_languages_names` VALUES (2267, 89, 22, 'Tουρκικά');
INSERT INTO `ff_languages_names` VALUES (2268, 89, 23, 'Turkish');
INSERT INTO `ff_languages_names` VALUES (2269, 89, 25, 'turco');
INSERT INTO `ff_languages_names` VALUES (2270, 89, 26, 'türgi');
INSERT INTO `ff_languages_names` VALUES (2271, 89, 29, 'turkki');
INSERT INTO `ff_languages_names` VALUES (2272, 89, 32, 'turc');
INSERT INTO `ff_languages_names` VALUES (2273, 89, 40, 'Turski');
INSERT INTO `ff_languages_names` VALUES (2274, 89, 42, 'Török');
INSERT INTO `ff_languages_names` VALUES (2275, 89, 45, 'Tyrkneska');
INSERT INTO `ff_languages_names` VALUES (2276, 89, 1, 'Turco');
INSERT INTO `ff_languages_names` VALUES (2277, 89, 59, 'Turkų');
INSERT INTO `ff_languages_names` VALUES (2278, 89, 60, 'Turku');
INSERT INTO `ff_languages_names` VALUES (2279, 89, 66, 'Tork');
INSERT INTO `ff_languages_names` VALUES (2280, 89, 70, 'Turks');
INSERT INTO `ff_languages_names` VALUES (2281, 89, 72, 'Tyrkisk');
INSERT INTO `ff_languages_names` VALUES (2282, 89, 74, 'turecki');
INSERT INTO `ff_languages_names` VALUES (2283, 89, 75, 'Turco');
INSERT INTO `ff_languages_names` VALUES (2284, 89, 77, 'Turcă');
INSERT INTO `ff_languages_names` VALUES (2285, 89, 81, 'turečtina');
INSERT INTO `ff_languages_names` VALUES (2286, 89, 82, 'turščina');
INSERT INTO `ff_languages_names` VALUES (2287, 89, 86, 'turkiska');
INSERT INTO `ff_languages_names` VALUES (2288, 89, 89, 'Türkçe');
INSERT INTO `ff_languages_names` VALUES (2289, 90, 9, 'таитянски');
INSERT INTO `ff_languages_names` VALUES (2290, 90, 17, 'tahitština');
INSERT INTO `ff_languages_names` VALUES (2291, 90, 20, 'Tahitiansk');
INSERT INTO `ff_languages_names` VALUES (2292, 90, 21, 'Tahitisch');
INSERT INTO `ff_languages_names` VALUES (2293, 90, 22, 'γλώσσα Ταϊτής');
INSERT INTO `ff_languages_names` VALUES (2294, 90, 23, 'Tahitian');
INSERT INTO `ff_languages_names` VALUES (2295, 90, 25, 'tahitiano');
INSERT INTO `ff_languages_names` VALUES (2296, 90, 26, 'tahiti');
INSERT INTO `ff_languages_names` VALUES (2297, 90, 29, 'tahiti');
INSERT INTO `ff_languages_names` VALUES (2298, 90, 32, 'tahitien');
INSERT INTO `ff_languages_names` VALUES (2299, 90, 40, 'tahićanski');
INSERT INTO `ff_languages_names` VALUES (2300, 90, 42, 'Tahiti');
INSERT INTO `ff_languages_names` VALUES (2301, 90, 45, 'Tahitian');
INSERT INTO `ff_languages_names` VALUES (2302, 90, 1, 'Tahitian');
INSERT INTO `ff_languages_names` VALUES (2303, 90, 59, 'Taičio');
INSERT INTO `ff_languages_names` VALUES (2304, 90, 60, 'Taiti');
INSERT INTO `ff_languages_names` VALUES (2305, 90, 66, 'Taħitjan');
INSERT INTO `ff_languages_names` VALUES (2306, 90, 70, 'Tahitisch');
INSERT INTO `ff_languages_names` VALUES (2307, 90, 72, 'Tahitisk');
INSERT INTO `ff_languages_names` VALUES (2308, 90, 74, 'tahitański');
INSERT INTO `ff_languages_names` VALUES (2309, 90, 75, 'Taitiano');
INSERT INTO `ff_languages_names` VALUES (2310, 90, 77, 'Tahitiană');
INSERT INTO `ff_languages_names` VALUES (2311, 90, 81, 'tahitčina');
INSERT INTO `ff_languages_names` VALUES (2312, 90, 82, 'tahitijščina');
INSERT INTO `ff_languages_names` VALUES (2313, 90, 86, 'tahitiska');
INSERT INTO `ff_languages_names` VALUES (2314, 90, 89, 'Tahitice');
INSERT INTO `ff_languages_names` VALUES (2315, 91, 9, 'украински');
INSERT INTO `ff_languages_names` VALUES (2316, 91, 17, 'ukrajinština');
INSERT INTO `ff_languages_names` VALUES (2317, 91, 20, 'Ukrainsk');
INSERT INTO `ff_languages_names` VALUES (2318, 91, 21, 'Ukrainisch');
INSERT INTO `ff_languages_names` VALUES (2319, 91, 22, 'ουκρανικά');
INSERT INTO `ff_languages_names` VALUES (2320, 91, 23, 'Ukrainian');
INSERT INTO `ff_languages_names` VALUES (2321, 91, 25, 'ucraniano');
INSERT INTO `ff_languages_names` VALUES (2322, 91, 26, 'ukraina');
INSERT INTO `ff_languages_names` VALUES (2323, 91, 29, 'ukraina');
INSERT INTO `ff_languages_names` VALUES (2324, 91, 32, 'ukrainien');
INSERT INTO `ff_languages_names` VALUES (2325, 91, 40, 'ukrajinski');
INSERT INTO `ff_languages_names` VALUES (2326, 91, 42, 'Ukrán');
INSERT INTO `ff_languages_names` VALUES (2327, 91, 45, 'Úkraínska');
INSERT INTO `ff_languages_names` VALUES (2328, 91, 1, 'Ucraino');
INSERT INTO `ff_languages_names` VALUES (2329, 91, 59, 'Ukrainiečių');
INSERT INTO `ff_languages_names` VALUES (2330, 91, 60, 'Ukraiņu');
INSERT INTO `ff_languages_names` VALUES (2331, 91, 66, 'Ukrain');
INSERT INTO `ff_languages_names` VALUES (2332, 91, 70, 'Oekraïens');
INSERT INTO `ff_languages_names` VALUES (2333, 91, 72, 'Ukrainsk');
INSERT INTO `ff_languages_names` VALUES (2334, 91, 74, 'ukraiński');
INSERT INTO `ff_languages_names` VALUES (2335, 91, 75, 'Ucraniano');
INSERT INTO `ff_languages_names` VALUES (2336, 91, 77, 'Ucrainiană');
INSERT INTO `ff_languages_names` VALUES (2337, 91, 81, 'ukrajinčina');
INSERT INTO `ff_languages_names` VALUES (2338, 91, 82, 'ukrajinščina');
INSERT INTO `ff_languages_names` VALUES (2339, 91, 86, 'ukrainska');
INSERT INTO `ff_languages_names` VALUES (2340, 91, 89, 'Ukraynaca');
INSERT INTO `ff_languages_names` VALUES (2341, 92, 9, 'урду');
INSERT INTO `ff_languages_names` VALUES (2342, 92, 17, 'urdština');
INSERT INTO `ff_languages_names` VALUES (2343, 92, 20, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2344, 92, 21, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2345, 92, 22, 'ούρντου');
INSERT INTO `ff_languages_names` VALUES (2346, 92, 23, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2347, 92, 25, 'urdu');
INSERT INTO `ff_languages_names` VALUES (2348, 92, 26, 'urdu');
INSERT INTO `ff_languages_names` VALUES (2349, 92, 29, 'urdu');
INSERT INTO `ff_languages_names` VALUES (2350, 92, 32, 'ourdou');
INSERT INTO `ff_languages_names` VALUES (2351, 92, 40, 'urdski');
INSERT INTO `ff_languages_names` VALUES (2352, 92, 42, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2353, 92, 45, 'en:Urdu');
INSERT INTO `ff_languages_names` VALUES (2354, 92, 1, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2355, 92, 59, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2356, 92, 60, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2357, 92, 66, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2358, 92, 70, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2359, 92, 72, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2360, 92, 74, 'urdu');
INSERT INTO `ff_languages_names` VALUES (2361, 92, 75, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2362, 92, 77, 'Urdu');
INSERT INTO `ff_languages_names` VALUES (2363, 92, 81, 'urdčina');
INSERT INTO `ff_languages_names` VALUES (2364, 92, 82, 'urdujščina');
INSERT INTO `ff_languages_names` VALUES (2365, 92, 86, 'urdu');
INSERT INTO `ff_languages_names` VALUES (2366, 92, 89, 'Urduca');
INSERT INTO `ff_languages_names` VALUES (2367, 93, 9, 'узбекски');
INSERT INTO `ff_languages_names` VALUES (2368, 93, 17, 'uzbečtina');
INSERT INTO `ff_languages_names` VALUES (2369, 93, 20, 'Usbekisk');
INSERT INTO `ff_languages_names` VALUES (2370, 93, 21, 'Usbekisch');
INSERT INTO `ff_languages_names` VALUES (2371, 93, 22, 'ουζμπέκ');
INSERT INTO `ff_languages_names` VALUES (2372, 93, 23, 'Uzbek');
INSERT INTO `ff_languages_names` VALUES (2373, 93, 25, 'uzbeko');
INSERT INTO `ff_languages_names` VALUES (2374, 93, 26, 'usbeki');
INSERT INTO `ff_languages_names` VALUES (2375, 93, 29, 'uzbekki');
INSERT INTO `ff_languages_names` VALUES (2376, 93, 32, 'ouszbek');
INSERT INTO `ff_languages_names` VALUES (2377, 93, 40, 'Uzbek');
INSERT INTO `ff_languages_names` VALUES (2378, 93, 42, 'Üzbég');
INSERT INTO `ff_languages_names` VALUES (2379, 93, 45, 'Uzbek');
INSERT INTO `ff_languages_names` VALUES (2380, 93, 1, 'Usbeco; usbeko, uzbeco, usbecco');
INSERT INTO `ff_languages_names` VALUES (2381, 93, 59, 'Uzbekų');
INSERT INTO `ff_languages_names` VALUES (2382, 93, 60, 'Uzbeku');
INSERT INTO `ff_languages_names` VALUES (2383, 93, 66, 'Użbek');
INSERT INTO `ff_languages_names` VALUES (2384, 93, 70, 'Oezbeeks');
INSERT INTO `ff_languages_names` VALUES (2385, 93, 72, 'Usbekisk');
INSERT INTO `ff_languages_names` VALUES (2386, 93, 74, 'uzbecki');
INSERT INTO `ff_languages_names` VALUES (2387, 93, 75, 'Uzbeque');
INSERT INTO `ff_languages_names` VALUES (2388, 93, 77, 'Uzbecă');
INSERT INTO `ff_languages_names` VALUES (2389, 93, 81, 'uzbečtina');
INSERT INTO `ff_languages_names` VALUES (2390, 93, 82, 'uzbeščina');
INSERT INTO `ff_languages_names` VALUES (2391, 93, 86, 'uzbekiska');
INSERT INTO `ff_languages_names` VALUES (2392, 93, 89, 'Özbekçe');
INSERT INTO `ff_languages_names` VALUES (2393, 94, 9, 'виетнамски');
INSERT INTO `ff_languages_names` VALUES (2394, 94, 17, 'vietnamština');
INSERT INTO `ff_languages_names` VALUES (2395, 94, 20, 'Vietnamesisk');
INSERT INTO `ff_languages_names` VALUES (2396, 94, 21, 'Vietnamesisch');
INSERT INTO `ff_languages_names` VALUES (2397, 94, 22, 'βιετναμεζικά');
INSERT INTO `ff_languages_names` VALUES (2398, 94, 23, 'Vietnamese');
INSERT INTO `ff_languages_names` VALUES (2399, 94, 25, 'vietnamita');
INSERT INTO `ff_languages_names` VALUES (2400, 94, 26, 'vietnami');
INSERT INTO `ff_languages_names` VALUES (2401, 94, 29, 'vietnam');
INSERT INTO `ff_languages_names` VALUES (2402, 94, 32, 'vietnamien');
INSERT INTO `ff_languages_names` VALUES (2403, 94, 40, 'vijetnamski');
INSERT INTO `ff_languages_names` VALUES (2404, 94, 42, 'Vietnámi');
INSERT INTO `ff_languages_names` VALUES (2405, 94, 45, 'Víetnamska');
INSERT INTO `ff_languages_names` VALUES (2406, 94, 1, 'Vietnamita');
INSERT INTO `ff_languages_names` VALUES (2407, 94, 59, 'Vietnamiečių');
INSERT INTO `ff_languages_names` VALUES (2408, 94, 60, 'Vjetnamiešu');
INSERT INTO `ff_languages_names` VALUES (2409, 94, 66, 'Vjetnamiż');
INSERT INTO `ff_languages_names` VALUES (2410, 94, 70, 'Vietnamees');
INSERT INTO `ff_languages_names` VALUES (2411, 94, 72, 'Vietnamesisk');
INSERT INTO `ff_languages_names` VALUES (2412, 94, 74, 'wietnamski');
INSERT INTO `ff_languages_names` VALUES (2413, 94, 75, 'Vietnamita');
INSERT INTO `ff_languages_names` VALUES (2414, 94, 77, 'Vietnameză');
INSERT INTO `ff_languages_names` VALUES (2415, 94, 81, 'vietnamčina');
INSERT INTO `ff_languages_names` VALUES (2416, 94, 82, 'vietnamščina');
INSERT INTO `ff_languages_names` VALUES (2417, 94, 86, 'vietnamesiska');
INSERT INTO `ff_languages_names` VALUES (2418, 94, 89, 'Vietnamca');
INSERT INTO `ff_languages_names` VALUES (2419, 95, 9, 'волапюк');
INSERT INTO `ff_languages_names` VALUES (2420, 95, 17, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2421, 95, 20, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2422, 95, 21, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2423, 95, 22, 'βόλαπουκ');
INSERT INTO `ff_languages_names` VALUES (2424, 95, 23, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2425, 95, 25, 'volapuk');
INSERT INTO `ff_languages_names` VALUES (2426, 95, 26, 'volapüki');
INSERT INTO `ff_languages_names` VALUES (2427, 95, 29, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2428, 95, 32, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2429, 95, 40, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2430, 95, 42, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2431, 95, 45, 'en:Volapük');
INSERT INTO `ff_languages_names` VALUES (2432, 95, 1, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2433, 95, 59, 'Volapiukas');
INSERT INTO `ff_languages_names` VALUES (2434, 95, 60, 'Vola');
INSERT INTO `ff_languages_names` VALUES (2435, 95, 66, 'Volapuk');
INSERT INTO `ff_languages_names` VALUES (2436, 95, 70, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2437, 95, 72, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2438, 95, 74, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2439, 95, 75, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2440, 95, 77, 'Volapük');
INSERT INTO `ff_languages_names` VALUES (2441, 95, 81, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2442, 95, 82, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2443, 95, 86, 'volapük');
INSERT INTO `ff_languages_names` VALUES (2444, 95, 89, 'Volapükçe');
INSERT INTO `ff_languages_names` VALUES (2445, 96, 9, 'идиш');
INSERT INTO `ff_languages_names` VALUES (2446, 96, 17, 'jidiš');
INSERT INTO `ff_languages_names` VALUES (2447, 96, 20, 'Jiddisch');
INSERT INTO `ff_languages_names` VALUES (2448, 96, 21, 'Jiddisch');
INSERT INTO `ff_languages_names` VALUES (2449, 96, 22, 'Γίντις');
INSERT INTO `ff_languages_names` VALUES (2450, 96, 23, 'Yiddish');
INSERT INTO `ff_languages_names` VALUES (2451, 96, 25, 'yídish');
INSERT INTO `ff_languages_names` VALUES (2452, 96, 26, 'jidiši');
INSERT INTO `ff_languages_names` VALUES (2453, 96, 29, 'jiddiš');
INSERT INTO `ff_languages_names` VALUES (2454, 96, 32, 'yiddish');
INSERT INTO `ff_languages_names` VALUES (2455, 96, 40, 'jidiš');
INSERT INTO `ff_languages_names` VALUES (2456, 96, 42, 'Jiddis');
INSERT INTO `ff_languages_names` VALUES (2457, 96, 45, 'Yiddish');
INSERT INTO `ff_languages_names` VALUES (2458, 96, 1, 'Yiddish');
INSERT INTO `ff_languages_names` VALUES (2459, 96, 59, 'Jidiš');
INSERT INTO `ff_languages_names` VALUES (2460, 96, 60, 'Jidišs');
INSERT INTO `ff_languages_names` VALUES (2461, 96, 66, 'Jiddix');
INSERT INTO `ff_languages_names` VALUES (2462, 96, 70, 'Jiddisch');
INSERT INTO `ff_languages_names` VALUES (2463, 96, 72, 'Jiddisk');
INSERT INTO `ff_languages_names` VALUES (2464, 96, 74, 'jidysz');
INSERT INTO `ff_languages_names` VALUES (2465, 96, 75, 'Yiddish');
INSERT INTO `ff_languages_names` VALUES (2466, 96, 77, 'Idiş');
INSERT INTO `ff_languages_names` VALUES (2467, 96, 81, 'jidiš');
INSERT INTO `ff_languages_names` VALUES (2468, 96, 82, 'jidiš');
INSERT INTO `ff_languages_names` VALUES (2469, 96, 86, 'jiddish');
INSERT INTO `ff_languages_names` VALUES (2470, 96, 89, 'Yidce');
INSERT INTO `ff_languages_names` VALUES (2471, 97, 9, 'китайски');
INSERT INTO `ff_languages_names` VALUES (2472, 97, 17, 'čínština');
INSERT INTO `ff_languages_names` VALUES (2473, 97, 20, 'Kinesisk');
INSERT INTO `ff_languages_names` VALUES (2474, 97, 21, 'Chinesisch');
INSERT INTO `ff_languages_names` VALUES (2475, 97, 22, 'κινεζικά');
INSERT INTO `ff_languages_names` VALUES (2476, 97, 23, 'Chinese');
INSERT INTO `ff_languages_names` VALUES (2477, 97, 25, 'chino');
INSERT INTO `ff_languages_names` VALUES (2478, 97, 26, 'hiina');
INSERT INTO `ff_languages_names` VALUES (2479, 97, 29, 'kiina');
INSERT INTO `ff_languages_names` VALUES (2480, 97, 32, 'chinois');
INSERT INTO `ff_languages_names` VALUES (2481, 97, 40, 'kineski');
INSERT INTO `ff_languages_names` VALUES (2482, 97, 42, 'Kinai');
INSERT INTO `ff_languages_names` VALUES (2483, 97, 45, 'Kínverska');
INSERT INTO `ff_languages_names` VALUES (2484, 97, 1, 'Cinese');
INSERT INTO `ff_languages_names` VALUES (2485, 97, 59, 'Kinų');
INSERT INTO `ff_languages_names` VALUES (2486, 97, 60, 'Ķīniešu');
INSERT INTO `ff_languages_names` VALUES (2487, 97, 66, 'Ċiniż');
INSERT INTO `ff_languages_names` VALUES (2488, 97, 70, 'Chinees');
INSERT INTO `ff_languages_names` VALUES (2489, 97, 72, 'Kinesisk');
INSERT INTO `ff_languages_names` VALUES (2490, 97, 74, 'chiński');
INSERT INTO `ff_languages_names` VALUES (2491, 97, 75, 'Chinês');
INSERT INTO `ff_languages_names` VALUES (2492, 97, 77, 'Chineză');
INSERT INTO `ff_languages_names` VALUES (2493, 97, 81, 'čínština');
INSERT INTO `ff_languages_names` VALUES (2494, 97, 82, 'kitajščina');
INSERT INTO `ff_languages_names` VALUES (2495, 97, 86, 'kinesiska');
INSERT INTO `ff_languages_names` VALUES (2496, 97, 89, 'Çince');

-- ----------------------------
-- Table structure for indicatori_indicatore
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_indicatore`;
CREATE TABLE `indicatori_indicatore`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `descrizione` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `istruzioni` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `formula_calcolo_risultato` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `formula_calcolo_raggiungimento` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_indicatore_cdr_cruscotto_anno
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_indicatore_cdr_cruscotto_anno`;
CREATE TABLE `indicatori_indicatore_cdr_cruscotto_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_indicatore` int(11) NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_obiettivo_indicatore
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_obiettivo_indicatore`;
CREATE TABLE `indicatori_obiettivo_indicatore`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_obiettivo` int(11) NULL DEFAULT NULL,
  `ID_indicatore` int(11) NULL DEFAULT NULL,
  `valore_target` float(11, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_parametro
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_parametro`;
CREATE TABLE `indicatori_parametro`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `ID_tipo_parametro` int(11) NULL DEFAULT NULL,
  `anno_introduzione` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `anno_termine` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_parametro_indicatore
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_parametro_indicatore`;
CREATE TABLE `indicatori_parametro_indicatore`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_parametro` int(11) NULL DEFAULT NULL,
  `ID_indicatore` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_periodo_cruscotto
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_periodo_cruscotto`;
CREATE TABLE `indicatori_periodo_cruscotto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_riferimento_inizio` date NOT NULL,
  `data_riferimento_fine` date NOT NULL,
  `ordinamento_anno` int(11) NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_valore_parametro_indicatore_rendicontazione
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_valore_parametro_indicatore_rendicontazione`;
CREATE TABLE `indicatori_valore_parametro_indicatore_rendicontazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_rendicontazione` int(11) NULL DEFAULT NULL,
  `ID_parametro_indicatore` int(11) NULL DEFAULT NULL,
  `valore` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_valore_parametro_rilevato
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_valore_parametro_rilevato`;
CREATE TABLE `indicatori_valore_parametro_rilevato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_parametro` int(11) NOT NULL,
  `ID_periodo_rendicontazione` int(11) NULL DEFAULT NULL,
  `ID_periodo_cruscotto` int(11) NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `valore` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `modificabile` tinyint(1) NULL DEFAULT NULL,
  `data_riferimento` datetime(0) NULL DEFAULT NULL,
  `data_importazione` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_valore_target
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_valore_target`;
CREATE TABLE `indicatori_valore_target`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_indicatore` int(11) NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `valore_target` float(11, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for indicatori_valore_target_obiettivo_cdr
-- ----------------------------
DROP TABLE IF EXISTS `indicatori_valore_target_obiettivo_cdr`;
CREATE TABLE `indicatori_valore_target_obiettivo_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_obiettivo_indicatore` int(11) NOT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `valore_target` float(11, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_categoria
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_categoria`;
CREATE TABLE `investimenti_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_categoria_registro_cespiti
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_categoria_registro_cespiti`;
CREATE TABLE `investimenti_categoria_registro_cespiti`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_categoria_uoc_competente_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_categoria_uoc_competente_anno`;
CREATE TABLE `investimenti_categoria_uoc_competente_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_categoria` int(11) NULL DEFAULT NULL,
  `anno_inizio` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_cdr_abilitato_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_cdr_abilitato_anno`;
CREATE TABLE `investimenti_cdr_abilitato_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `anno_inizio` int(11) NULL DEFAULT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_cdr_bilancio_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_cdr_bilancio_anno`;
CREATE TABLE `investimenti_cdr_bilancio_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `anno_inizio` int(11) NULL DEFAULT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_dipartimento_amministrativo_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_dipartimento_amministrativo_anno`;
CREATE TABLE `investimenti_dipartimento_amministrativo_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `anno_inizio` int(11) NULL DEFAULT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_direzione_riferimento_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_direzione_riferimento_anno`;
CREATE TABLE `investimenti_direzione_riferimento_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `anno_inizio` int(11) NULL DEFAULT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_fonte_finanziamento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_fonte_finanziamento`;
CREATE TABLE `investimenti_fonte_finanziamento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `budget_anno` float(11, 0) NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_investimento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_investimento`;
CREATE TABLE `investimenti_investimento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr_creazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  `data_creazione` datetime(0) NULL DEFAULT NULL,
  `richiesta_codice_cdc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_nuova` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_matricola_bene_da_sostituire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_ID_categoria` int(11) NULL DEFAULT NULL,
  `richiesta_descrizione_bene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_quantita` int(11) NULL DEFAULT NULL,
  `richiesta_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_motivazioni_supporto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_eventuali_costi_aggiuntivi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_costo_stimato` float(11, 2) NULL DEFAULT NULL,
  `richiesta_ID_priorita` int(11) NULL DEFAULT NULL,
  `richiesta_tempi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_ubicazione_bene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_data_chiusura` datetime(0) NULL DEFAULT NULL,
  `approvazione_ID_parere_direzione_riferimento` int(11) NULL DEFAULT NULL,
  `approvazione_note_parere_direzione_riferimento` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `approvazione_ID_priorita_direzione_riferimento` int(11) NULL DEFAULT NULL,
  `approvazione_ID_tempi_stimati_direzione_riferimento` int(11) NULL DEFAULT NULL,
  `approvazione_data` datetime(0) NULL DEFAULT NULL,
  `approvazione_data_scarto_direzione_riferimento` datetime(0) NULL DEFAULT NULL,
  `istruttoria_ID_categoria_uoc_competente_anno` int(11) NULL DEFAULT NULL,
  `istruttoria_data_avvio` datetime(0) NULL DEFAULT NULL,
  `istruttoria_costo_presunto` float(11, 2) NULL DEFAULT NULL,
  `istruttoria_modalita_acquisizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `istruttoria_ID_tempi_stimati_uoc_competente` int(11) NULL DEFAULT NULL,
  `istruttoria_anno_soddisfacimento` int(11) NULL DEFAULT NULL,
  `istruttoria_ID_fonte_finanziamento_proposta` int(11) NULL DEFAULT NULL,
  `istruttoria_ID_categoria_registro_cespiti_proposta` int(11) NULL DEFAULT NULL,
  `istruttoria_non_coerente_piano_investimenti` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `istruttoria_data_chiusura_uoc_competente` datetime(0) NULL DEFAULT NULL,
  `istruttoria_data_scarto_uoc_competente` datetime(0) NULL DEFAULT NULL,
  `verifica_copertura_ID_registro_cespiti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `verifica_copertura_ID_fonte_finanziamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `verifica_copertura_data_fine` datetime(0) NULL DEFAULT NULL,
  `proposta_piano_investimenti_data` datetime(0) NULL DEFAULT NULL,
  `dg_ID_parere` int(11) NULL DEFAULT NULL,
  `dg_ID_priorita` int(11) NULL DEFAULT NULL,
  `dg_ID_tempi` int(11) NULL DEFAULT NULL,
  `dg_data_validazione_piano_investimenti` datetime(0) NULL DEFAULT NULL,
  `dg_data_scarto_piano_investimenti` datetime(0) NULL DEFAULT NULL,
  `monitoraggio_importo_definitivo` decimal(11, 0) NULL DEFAULT NULL,
  `monitoraggio_data` datetime(0) NULL DEFAULT NULL,
  `monitoraggio_provvedimento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `monitoraggio_fatture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `monitoraggio_fornitore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `monitoraggio_ID_fonte_finanziamento` int(11) NULL DEFAULT NULL,
  `monitoraggio_note` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `monitoraggio_data_chiusura` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_linee_guida_anno
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_linee_guida_anno`;
CREATE TABLE `investimenti_linee_guida_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for investimenti_parere_dg
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_parere_dg`;
CREATE TABLE `investimenti_parere_dg`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `esito` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_parere_dg
-- ----------------------------
INSERT INTO `investimenti_parere_dg` VALUES (1, 'Si', '1');
INSERT INTO `investimenti_parere_dg` VALUES (2, 'No', NULL);
INSERT INTO `investimenti_parere_dg` VALUES (3, 'Altro', NULL);

-- ----------------------------
-- Table structure for investimenti_parere_direzione_riferimento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_parere_direzione_riferimento`;
CREATE TABLE `investimenti_parere_direzione_riferimento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `esito` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_parere_direzione_riferimento
-- ----------------------------
INSERT INTO `investimenti_parere_direzione_riferimento` VALUES (1, 'Si', '1');
INSERT INTO `investimenti_parere_direzione_riferimento` VALUES (2, 'No', NULL);
INSERT INTO `investimenti_parere_direzione_riferimento` VALUES (3, 'Altro', NULL);

-- ----------------------------
-- Table structure for investimenti_priorita_dg
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_priorita_dg`;
CREATE TABLE `investimenti_priorita_dg`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_priorita_dg
-- ----------------------------
INSERT INTO `investimenti_priorita_dg` VALUES (1, 'Alta');
INSERT INTO `investimenti_priorita_dg` VALUES (2, 'Media');
INSERT INTO `investimenti_priorita_dg` VALUES (3, 'Bassa');

-- ----------------------------
-- Table structure for investimenti_priorita_direzione_riferimento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_priorita_direzione_riferimento`;
CREATE TABLE `investimenti_priorita_direzione_riferimento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_priorita_direzione_riferimento
-- ----------------------------
INSERT INTO `investimenti_priorita_direzione_riferimento` VALUES (1, 'Alta');
INSERT INTO `investimenti_priorita_direzione_riferimento` VALUES (2, 'Media');
INSERT INTO `investimenti_priorita_direzione_riferimento` VALUES (3, 'Bassa');

-- ----------------------------
-- Table structure for investimenti_priorita_intervento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_priorita_intervento`;
CREATE TABLE `investimenti_priorita_intervento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_priorita_intervento
-- ----------------------------
INSERT INTO `investimenti_priorita_intervento` VALUES (1, 'Alta');
INSERT INTO `investimenti_priorita_intervento` VALUES (2, 'Media');
INSERT INTO `investimenti_priorita_intervento` VALUES (3, 'Bassa');

-- ----------------------------
-- Table structure for investimenti_tempi_dg
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_tempi_dg`;
CREATE TABLE `investimenti_tempi_dg`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_tempi_dg
-- ----------------------------
INSERT INTO `investimenti_tempi_dg` VALUES (1, '1° semestre');
INSERT INTO `investimenti_tempi_dg` VALUES (2, '2° semestre');

-- ----------------------------
-- Table structure for investimenti_tempi_direzione_riferimento
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_tempi_direzione_riferimento`;
CREATE TABLE `investimenti_tempi_direzione_riferimento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_tempi_direzione_riferimento
-- ----------------------------
INSERT INTO `investimenti_tempi_direzione_riferimento` VALUES (1, '1° semestre');
INSERT INTO `investimenti_tempi_direzione_riferimento` VALUES (2, '2° semestre');

-- ----------------------------
-- Table structure for investimenti_tempi_uoc_competente
-- ----------------------------
DROP TABLE IF EXISTS `investimenti_tempi_uoc_competente`;
CREATE TABLE `investimenti_tempi_uoc_competente`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of investimenti_tempi_uoc_competente
-- ----------------------------
INSERT INTO `investimenti_tempi_uoc_competente` VALUES (1, '1° semestre');
INSERT INTO `investimenti_tempi_uoc_competente` VALUES (2, '2° semestre');

-- ----------------------------
-- Table structure for log_record_operation
-- ----------------------------
DROP TABLE IF EXISTS `log_record_operation`;
CREATE TABLE `log_record_operation`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime(0) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `src_table` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_record_ID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_accettazione
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_accettazione`;
CREATE TABLE `obiettivi_accettazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_personale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `note_dipendente` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `data_accettazione_dipendente` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_area
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_area`;
CREATE TABLE `obiettivi_area`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_area_risultato
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_area_risultato`;
CREATE TABLE `obiettivi_area_risultato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_obiettivo
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_obiettivo`;
CREATE TABLE `obiettivi_obiettivo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anno_budget` int(11) NOT NULL,
  `titolo` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `codice_incr_anno` int(11) NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `indicatori` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `formula_calcolo_raggiungimento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_origine` int(11) NOT NULL,
  `ID_tipo` int(11) NOT NULL,
  `ID_area_risultato` int(11) NOT NULL,
  `ID_area` int(11) NOT NULL,
  `data_ultima_modifica` datetime(0) NULL DEFAULT NULL,
  `data_eliminazione` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_obiettivo_cdr
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_obiettivo_cdr`;
CREATE TABLE `obiettivi_obiettivo_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_obiettivo` int(11) NOT NULL,
  `ID_tipo_piano_cdr` int(11) NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codice_cdr_coreferenza` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `peso` int(11) NULL DEFAULT NULL,
  `azioni` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ID_parere_azioni` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_azioni` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `data_chiusura_modifiche` date NULL DEFAULT NULL,
  `data_ultima_modifica` datetime(0) NULL DEFAULT NULL,
  `data_eliminazione` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `chiave`(`ID_obiettivo`, `ID_tipo_piano_cdr`, `codice_cdr`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_obiettivo_cdr_personale
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_obiettivo_cdr_personale`;
CREATE TABLE `obiettivi_obiettivo_cdr_personale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_obiettivo_cdr` int(11) NOT NULL,
  `matricola_personale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peso` int(11) NULL DEFAULT NULL,
  `data_ultima_modifica` datetime(0) NULL DEFAULT NULL,
  `data_accettazione` datetime(0) NULL DEFAULT NULL,
  `data_eliminazione` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `chiave`(`ID_obiettivo_cdr`, `matricola_personale`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_origine
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_origine`;
CREATE TABLE `obiettivi_origine`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_parere_azioni
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_parere_azioni`;
CREATE TABLE `obiettivi_parere_azioni`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_periodo_rendicontazione
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_periodo_rendicontazione`;
CREATE TABLE `obiettivi_periodo_rendicontazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_riferimento_inizio` date NOT NULL,
  `data_riferimento_fine` date NOT NULL,
  `ordinamento_anno` int(11) NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `data_termine_responsabile` date NULL DEFAULT NULL,
  `allegati` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_rendicontazione
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_rendicontazione`;
CREATE TABLE `obiettivi_rendicontazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo_rendicontazione` int(11) NOT NULL,
  `ID_obiettivo_cdr` int(11) NOT NULL,
  `azioni` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `provvedimenti` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `criticita` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `misurazione_indicatori` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `raggiungibile` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `richiesta_revisione` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `perc_raggiungimento` float(11, 2) NULL DEFAULT NULL,
  `perc_nucleo` int(11) NULL DEFAULT NULL,
  `note_nucleo` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_ultima_modifica_referente` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_rendicontazione_allegato
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_rendicontazione_allegato`;
CREATE TABLE `obiettivi_rendicontazione_allegato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `rendicontazione_id` int(11) NOT NULL DEFAULT 0,
  `allegato_id` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(0) NULL DEFAULT NULL,
  `updatedAt` datetime(0) NULL DEFAULT NULL,
  `deletedAt` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_tipo
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_tipo`;
CREATE TABLE `obiettivi_tipo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for obiettivi_valutazione_personale
-- ----------------------------
DROP TABLE IF EXISTS `obiettivi_valutazione_personale`;
CREATE TABLE `obiettivi_valutazione_personale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo_rendicontazione` int(11) NOT NULL,
  `ID_obiettivo_cdr_personale` int(11) NOT NULL,
  `perc_raggiungimento` int(11) NULL DEFAULT NULL,
  `time_ultimo_aggiornamento` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for personale
-- ----------------------------
DROP TABLE IF EXISTS `personale`;
CREATE TABLE `personale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cognome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  UNIQUE INDEX `matricola`(`matricola`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for piano_cdr
-- ----------------------------
DROP TABLE IF EXISTS `piano_cdr`;
CREATE TABLE `piano_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `data_definizione` date NOT NULL,
  `data_introduzione` date NOT NULL,
  `ID_tipo_piano_cdr` int(1) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_UNIQUE`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_direzione_riferimento_anno
-- ----------------------------
DROP TABLE IF EXISTS `progetti_direzione_riferimento_anno`;
CREATE TABLE `progetti_direzione_riferimento_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_anno_budget` int(11) NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_libreria_territorio_applicazione
-- ----------------------------
DROP TABLE IF EXISTS `progetti_libreria_territorio_applicazione`;
CREATE TABLE `progetti_libreria_territorio_applicazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione_territorio_applicazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_libreria_tipo_progetto
-- ----------------------------
DROP TABLE IF EXISTS `progetti_libreria_tipo_progetto`;
CREATE TABLE `progetti_libreria_tipo_progetto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_tipo_progetto` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione_tipo_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_libreria_tipologia_monitoraggio
-- ----------------------------
DROP TABLE IF EXISTS `progetti_libreria_tipologia_monitoraggio`;
CREATE TABLE `progetti_libreria_tipologia_monitoraggio`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione_tipologia_monitoraggio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_monitoraggio
-- ----------------------------
DROP TABLE IF EXISTS `progetti_monitoraggio`;
CREATE TABLE `progetti_monitoraggio`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_progetto` int(11) NULL DEFAULT NULL,
  `numero_monitoraggio` int(11) NULL DEFAULT NULL,
  `ID_tipologia_monitoraggio` int(11) NULL DEFAULT NULL,
  `descrizione_fase` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `costi_sostenuti` float NULL DEFAULT NULL,
  `descrizione_utilizzo_risorse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_rispetto_risorse_previste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_rispetto_tempistiche` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_replicabilita_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_monitoraggio_indicatore
-- ----------------------------
DROP TABLE IF EXISTS `progetti_monitoraggio_indicatore`;
CREATE TABLE `progetti_monitoraggio_indicatore`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_monitoraggio` int(11) NULL DEFAULT NULL,
  `ID_indicatore` int(11) NULL DEFAULT NULL,
  `valore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_progetto
-- ----------------------------
DROP TABLE IF EXISTS `progetti_progetto`;
CREATE TABLE `progetti_progetto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_utente_creazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `codice_cdr_proponente` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `data_creazione` datetime(0) NULL DEFAULT NULL,
  `titolo_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricola_responsabile_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_tipo_progetto` int(11) NULL DEFAULT NULL,
  `ID_file` int(11) NULL DEFAULT NULL,
  `finanziato` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0',
  `capofila` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `team_progetto` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `partner_esterni` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tema_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `modalita_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `target_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `setting` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `obiettivo_generale_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `analisi_contesto_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_territorio_applicazione` int(11) NULL DEFAULT NULL,
  `metodi_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `risultati_attesi_progetto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cambiamenti_altri_enti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `frequenza_monitoraggio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `metodo_monitoraggio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `budget` float NULL DEFAULT NULL,
  `risorse_gia_disponibili` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0',
  `descrizione_risorse_aggiuntive` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `importo_risorse_aggiuntive` float NULL DEFAULT NULL,
  `ricadute_altri_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `costi_indotti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `importo_totale_costi_indotti` float NULL DEFAULT NULL,
  `materiali` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `importo_materiali` float NULL DEFAULT NULL,
  `spazi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `risorse_professionali_coinvolte` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `importo_risorse_professionali_coinvolte` float NULL DEFAULT NULL,
  `altro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `importo_altro` float NULL DEFAULT NULL,
  `oracle_erp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `data_inizio_progetto` date NULL DEFAULT NULL,
  `data_fine_progetto` date NULL DEFAULT NULL,
  `data_approvazione` datetime(0) NULL DEFAULT NULL,
  `matricola_responsabile_riferimento_approvazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stato` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `numero_revisione` int(11) NULL DEFAULT 0,
  `validazione_finale` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `note_validazione_finale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `data_validazione_finale` datetime(0) NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_progetto_fase_tempo_realizzazione
-- ----------------------------
DROP TABLE IF EXISTS `progetti_progetto_fase_tempo_realizzazione`;
CREATE TABLE `progetti_progetto_fase_tempo_realizzazione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_progetto` int(11) NULL DEFAULT NULL,
  `data_inizio_fase` datetime(0) NULL DEFAULT NULL,
  `data_fine_fase` datetime(0) NULL DEFAULT NULL,
  `descrizione_fase` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_progetto_finanziamento
-- ----------------------------
DROP TABLE IF EXISTS `progetti_progetto_finanziamento`;
CREATE TABLE `progetti_progetto_finanziamento`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_progetto` int(11) NULL DEFAULT NULL,
  `importo` float NULL DEFAULT NULL,
  `origine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `atto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cofinanziamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_progetto_indicatore
-- ----------------------------
DROP TABLE IF EXISTS `progetti_progetto_indicatore`;
CREATE TABLE `progetti_progetto_indicatore`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_progetto` int(11) NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `valore_atteso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for progetti_progetto_partner_interni
-- ----------------------------
DROP TABLE IF EXISTS `progetti_progetto_partner_interni`;
CREATE TABLE `progetti_progetto_partner_interni`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_progetto` int(11) NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `extend` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `time_modifica` datetime(0) NULL DEFAULT NULL,
  `record_attivo` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for qualifica_interna
-- ----------------------------
DROP TABLE IF EXISTS `qualifica_interna`;
CREATE TABLE `qualifica_interna`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dirigente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_ruolo` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for rapporto_lavoro
-- ----------------------------
DROP TABLE IF EXISTS `rapporto_lavoro`;
CREATE TABLE `rapporto_lavoro`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `part_time` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for responsabile_cdr
-- ----------------------------
DROP TABLE IF EXISTS `responsabile_cdr`;
CREATE TABLE `responsabile_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `matricola_responsabile` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `data_inizio` date NULL DEFAULT NULL,
  `data_fine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for riesame_direzione_campo
-- ----------------------------
DROP TABLE IF EXISTS `riesame_direzione_campo`;
CREATE TABLE `riesame_direzione_campo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_tipo_campo` int(11) NULL DEFAULT NULL,
  `ordinamento` int(11) NULL DEFAULT NULL,
  `ID_sezione` int(11) NULL DEFAULT NULL,
  `anno_introduzione` int(11) NULL DEFAULT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for riesame_direzione_introduzione
-- ----------------------------
DROP TABLE IF EXISTS `riesame_direzione_introduzione`;
CREATE TABLE `riesame_direzione_introduzione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `testo` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `anno_termine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for riesame_direzione_riesame
-- ----------------------------
DROP TABLE IF EXISTS `riesame_direzione_riesame`;
CREATE TABLE `riesame_direzione_riesame`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `data_chiusura` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for riesame_direzione_sezione
-- ----------------------------
DROP TABLE IF EXISTS `riesame_direzione_sezione`;
CREATE TABLE `riesame_direzione_sezione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for riesame_direzione_valore_campo
-- ----------------------------
DROP TABLE IF EXISTS `riesame_direzione_valore_campo`;
CREATE TABLE `riesame_direzione_valore_campo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_riesame` int(11) NULL DEFAULT NULL,
  `ID_campo` int(11) NULL DEFAULT NULL,
  `valore` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ruolo
-- ----------------------------
DROP TABLE IF EXISTS `ruolo`;
CREATE TABLE `ruolo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for strategia_anno
-- ----------------------------
DROP TABLE IF EXISTS `strategia_anno`;
CREATE TABLE `strategia_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anno_budget` int(11) NOT NULL,
  `data_chiusura_definizione_strategia` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for strategia_cdr_programmazione_strategica
-- ----------------------------
DROP TABLE IF EXISTS `strategia_cdr_programmazione_strategica`;
CREATE TABLE `strategia_cdr_programmazione_strategica`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anno_inizio` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anno_fine` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for strategia_descrizione_introduttiva
-- ----------------------------
DROP TABLE IF EXISTS `strategia_descrizione_introduttiva`;
CREATE TABLE `strategia_descrizione_introduttiva`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for strategia_prospettiva
-- ----------------------------
DROP TABLE IF EXISTS `strategia_prospettiva`;
CREATE TABLE `strategia_prospettiva`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_termine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for strategia_strategia
-- ----------------------------
DROP TABLE IF EXISTS `strategia_strategia`;
CREATE TABLE `strategia_strategia`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_prospettiva` int(11) NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `codice_cdr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_ultima_modifica` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `fk_strategia_prospettiva1`(`ID_prospettiva`) USING BTREE,
  INDEX `fk_strategia_periodo_inserimento_dati1`(`ID_anno_budget`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for support_countries
-- ----------------------------
DROP TABLE IF EXISTS `support_countries`;
CREATE TABLE `support_countries`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 291 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of support_countries
-- ----------------------------
INSERT INTO `support_countries` VALUES (1, 'Albania', 'AL');
INSERT INTO `support_countries` VALUES (2, 'Algeria', 'DZ');
INSERT INTO `support_countries` VALUES (3, 'Andorra', 'AD');
INSERT INTO `support_countries` VALUES (4, 'Angola', 'AN7');
INSERT INTO `support_countries` VALUES (5, 'Anguilla', 'AI');
INSERT INTO `support_countries` VALUES (6, 'Antigua e Barbuda', 'AG');
INSERT INTO `support_countries` VALUES (7, 'Antille Olandesi', 'AN');
INSERT INTO `support_countries` VALUES (8, 'Arabia Saudita', 'SA');
INSERT INTO `support_countries` VALUES (9, 'Argentina', 'AR');
INSERT INTO `support_countries` VALUES (10, 'Armenia', 'AM');
INSERT INTO `support_countries` VALUES (11, 'Aruba', 'AW');
INSERT INTO `support_countries` VALUES (12, 'Australia', 'AU');
INSERT INTO `support_countries` VALUES (13, 'Austria', 'AT');
INSERT INTO `support_countries` VALUES (14, 'Azerbaigian', 'AZ');
INSERT INTO `support_countries` VALUES (15, 'Bahamas', 'BS');
INSERT INTO `support_countries` VALUES (16, 'Bahrain', 'BH');
INSERT INTO `support_countries` VALUES (17, 'Bangladesh', 'BD');
INSERT INTO `support_countries` VALUES (18, 'Barbados', 'BB');
INSERT INTO `support_countries` VALUES (19, 'Barbuda (Antigua)', 'AG1');
INSERT INTO `support_countries` VALUES (20, 'Belgio', 'BE');
INSERT INTO `support_countries` VALUES (21, 'Belize', 'BZ');
INSERT INTO `support_countries` VALUES (22, 'Benin', 'BJ');
INSERT INTO `support_countries` VALUES (23, 'Bermuda', 'BM');
INSERT INTO `support_countries` VALUES (24, 'Bielorussia', 'BY');
INSERT INTO `support_countries` VALUES (25, 'Bolivia', 'BO');
INSERT INTO `support_countries` VALUES (26, 'Bonaire (Antille Olandesi)', 'AN1');
INSERT INTO `support_countries` VALUES (27, 'Bosnia-Herzegovina', 'BA');
INSERT INTO `support_countries` VALUES (28, 'Botswana', 'BW');
INSERT INTO `support_countries` VALUES (29, 'Brasile', 'BR');
INSERT INTO `support_countries` VALUES (30, 'Brunei', 'BN');
INSERT INTO `support_countries` VALUES (31, 'Bulgaria', 'BG');
INSERT INTO `support_countries` VALUES (32, 'Burkina Faso', 'BF');
INSERT INTO `support_countries` VALUES (33, 'Burundi', 'BI');
INSERT INTO `support_countries` VALUES (34, 'Cambogia', 'KH');
INSERT INTO `support_countries` VALUES (35, 'Camerun', 'CM');
INSERT INTO `support_countries` VALUES (36, 'Canada', 'CA');
INSERT INTO `support_countries` VALUES (37, 'Ceca, Repubblica', 'CZ');
INSERT INTO `support_countries` VALUES (38, 'Centrafricana, Repubblica', 'CF');
INSERT INTO `support_countries` VALUES (39, 'Ceuta', 'XCE');
INSERT INTO `support_countries` VALUES (40, 'Ciad', 'TD');
INSERT INTO `support_countries` VALUES (41, 'Cile', 'CL');
INSERT INTO `support_countries` VALUES (42, 'Cina, Repubblica Popolare', 'CN');
INSERT INTO `support_countries` VALUES (43, 'Cipro', 'CY');
INSERT INTO `support_countries` VALUES (44, 'Cisgiordania e Gaza', 'XGC');
INSERT INTO `support_countries` VALUES (45, 'Colombia', 'CO');
INSERT INTO `support_countries` VALUES (46, 'Congo (Brazzaville)', 'CG1');
INSERT INTO `support_countries` VALUES (47, 'Congo, Repubblica Dem.', 'CG');
INSERT INTO `support_countries` VALUES (48, 'Corea del Sud', 'KR');
INSERT INTO `support_countries` VALUES (49, 'Costa d\'Avorio', 'CI');
INSERT INTO `support_countries` VALUES (50, 'Costa Rica', 'CR');
INSERT INTO `support_countries` VALUES (51, 'Croazia', 'HR');
INSERT INTO `support_countries` VALUES (52, 'Curaçao (Antille Olandesi)', 'AN2');
INSERT INTO `support_countries` VALUES (53, 'Danimarca', 'DK');
INSERT INTO `support_countries` VALUES (54, 'Dominica', 'DM');
INSERT INTO `support_countries` VALUES (55, 'Dominicana, Repubblica', 'DO');
INSERT INTO `support_countries` VALUES (56, 'Ecuador', 'EC');
INSERT INTO `support_countries` VALUES (57, 'Egitto', 'EG');
INSERT INTO `support_countries` VALUES (58, 'El Salvador', 'SV');
INSERT INTO `support_countries` VALUES (59, 'Emirati Arabi Uniti', 'AE');
INSERT INTO `support_countries` VALUES (60, 'Eritrea', 'ER');
INSERT INTO `support_countries` VALUES (61, 'Estonia', 'EE');
INSERT INTO `support_countries` VALUES (62, 'Etiopia', 'ET');
INSERT INTO `support_countries` VALUES (63, 'Federazione Yugoslava', 'YU');
INSERT INTO `support_countries` VALUES (64, 'Figi', 'FJ');
INSERT INTO `support_countries` VALUES (65, 'Filippine', 'PH');
INSERT INTO `support_countries` VALUES (66, 'Finlandia', 'FI');
INSERT INTO `support_countries` VALUES (67, 'Francia', 'FR');
INSERT INTO `support_countries` VALUES (68, 'Gabon', 'GA');
INSERT INTO `support_countries` VALUES (69, 'Gambia', 'GM');
INSERT INTO `support_countries` VALUES (70, 'Gaza e Cisgiordania', 'XCG');
INSERT INTO `support_countries` VALUES (71, 'Georgia', 'GE');
INSERT INTO `support_countries` VALUES (72, 'Germania', 'DE');
INSERT INTO `support_countries` VALUES (73, 'Ghana', 'GH');
INSERT INTO `support_countries` VALUES (74, 'Giamaica', 'JM');
INSERT INTO `support_countries` VALUES (75, 'Giappone', 'JP');
INSERT INTO `support_countries` VALUES (76, 'Gibilterra', 'GI');
INSERT INTO `support_countries` VALUES (77, 'Gibuti', 'DJ');
INSERT INTO `support_countries` VALUES (78, 'Giordania', 'JO');
INSERT INTO `support_countries` VALUES (79, 'Grecia', 'GR');
INSERT INTO `support_countries` VALUES (80, 'Grenada', 'GD');
INSERT INTO `support_countries` VALUES (81, 'Groenlandia', 'GL');
INSERT INTO `support_countries` VALUES (82, 'Guadalupa', 'GP');
INSERT INTO `support_countries` VALUES (83, 'Guam', 'GU');
INSERT INTO `support_countries` VALUES (84, 'Guatemala', 'GT');
INSERT INTO `support_countries` VALUES (85, 'Guinea', 'GN');
INSERT INTO `support_countries` VALUES (86, 'Guinea Equatoriale', 'GQ');
INSERT INTO `support_countries` VALUES (87, 'Guinea-Bissau', 'GW');
INSERT INTO `support_countries` VALUES (88, 'Guyana', 'GY');
INSERT INTO `support_countries` VALUES (89, 'Guyana Francese', 'GF');
INSERT INTO `support_countries` VALUES (90, 'Haiti', 'HT');
INSERT INTO `support_countries` VALUES (91, 'Honduras', 'HN');
INSERT INTO `support_countries` VALUES (92, 'Hong Kong', 'HK');
INSERT INTO `support_countries` VALUES (93, 'India', 'IN');
INSERT INTO `support_countries` VALUES (94, 'Indonesia', 'ID');
INSERT INTO `support_countries` VALUES (95, 'Irlanda, Repubblica', 'IE');
INSERT INTO `support_countries` VALUES (96, 'Islanda', 'IS');
INSERT INTO `support_countries` VALUES (97, 'Isola Union (St Vincente Grenadines)', 'VC');
INSERT INTO `support_countries` VALUES (98, 'Isola Wake', 'XIW');
INSERT INTO `support_countries` VALUES (99, 'Isole Canarie', 'XIC');
INSERT INTO `support_countries` VALUES (100, 'Isole Capo Verde', 'CV');
INSERT INTO `support_countries` VALUES (101, 'Isole Cayman', 'KY');
INSERT INTO `support_countries` VALUES (102, 'Isole Cook', 'CK');
INSERT INTO `support_countries` VALUES (103, 'Isole Faroe', 'FO');
INSERT INTO `support_countries` VALUES (104, 'Isole Marianne del Nord', 'MP');
INSERT INTO `support_countries` VALUES (105, 'Isole Marshall', 'MH');
INSERT INTO `support_countries` VALUES (106, 'Isole Salomone', 'SB');
INSERT INTO `support_countries` VALUES (107, 'Isole Turks e Caicos', 'TC');
INSERT INTO `support_countries` VALUES (108, 'Isole Vergini Britanniche', 'VG');
INSERT INTO `support_countries` VALUES (109, 'Isole Vergini Statunitensi', 'VI');
INSERT INTO `support_countries` VALUES (110, 'Isole Wallis e Futuna', 'WF');
INSERT INTO `support_countries` VALUES (111, 'Israele', 'IL');
INSERT INTO `support_countries` VALUES (112, 'Italia', 'IT');
INSERT INTO `support_countries` VALUES (113, 'Kazakistan', 'KZ');
INSERT INTO `support_countries` VALUES (114, 'Kenya', 'KE');
INSERT INTO `support_countries` VALUES (115, 'Kiribati', 'KI');
INSERT INTO `support_countries` VALUES (116, 'Kosrae (Stati Federali Micronesia)', 'FM1');
INSERT INTO `support_countries` VALUES (117, 'Kuwait', 'KW');
INSERT INTO `support_countries` VALUES (118, 'Kyrgyzstan', 'KG');
INSERT INTO `support_countries` VALUES (119, 'Laos', 'LA');
INSERT INTO `support_countries` VALUES (120, 'Lesotho', 'LS');
INSERT INTO `support_countries` VALUES (121, 'Lettonia', 'LV');
INSERT INTO `support_countries` VALUES (122, 'Libano', 'LB');
INSERT INTO `support_countries` VALUES (123, 'Liberia', 'LR');
INSERT INTO `support_countries` VALUES (124, 'Liechtenstein', 'LI');
INSERT INTO `support_countries` VALUES (125, 'Lituania', 'LT');
INSERT INTO `support_countries` VALUES (126, 'Lussemburgo', 'LU');
INSERT INTO `support_countries` VALUES (127, 'Macau', 'MO');
INSERT INTO `support_countries` VALUES (128, 'Macedonia', 'MK');
INSERT INTO `support_countries` VALUES (129, 'Madagascar', 'MG');
INSERT INTO `support_countries` VALUES (130, 'Madera,  Isola di', 'XMI');
INSERT INTO `support_countries` VALUES (131, 'Malawi', 'MW');
INSERT INTO `support_countries` VALUES (132, 'Maldive', 'MV');
INSERT INTO `support_countries` VALUES (133, 'Malesia', 'MY');
INSERT INTO `support_countries` VALUES (134, 'Mali', 'ML');
INSERT INTO `support_countries` VALUES (135, 'Malta', 'MT');
INSERT INTO `support_countries` VALUES (136, 'Marocco', 'MA');
INSERT INTO `support_countries` VALUES (137, 'Martinica', 'MQ');
INSERT INTO `support_countries` VALUES (138, 'Mauritania', 'MR');
INSERT INTO `support_countries` VALUES (139, 'Mauritius', 'MU');
INSERT INTO `support_countries` VALUES (140, 'Melilla', 'XME');
INSERT INTO `support_countries` VALUES (141, 'Messico', 'MX');
INSERT INTO `support_countries` VALUES (142, 'Micronesia (Stati Federali della)', 'FM2');
INSERT INTO `support_countries` VALUES (143, 'Moldavia', 'MD');
INSERT INTO `support_countries` VALUES (144, 'Monaco (Principato di)', 'MC');
INSERT INTO `support_countries` VALUES (145, 'Mongolia', 'MO1');
INSERT INTO `support_countries` VALUES (146, 'Montserrat', 'MS');
INSERT INTO `support_countries` VALUES (147, 'Mozambico', 'MZ');
INSERT INTO `support_countries` VALUES (148, 'Myanmar', 'MM');
INSERT INTO `support_countries` VALUES (149, 'Namibia', 'NA');
INSERT INTO `support_countries` VALUES (150, 'Nepal', 'NP');
INSERT INTO `support_countries` VALUES (151, 'Nevis (St.Kitts - Nevis)', 'KN1');
INSERT INTO `support_countries` VALUES (152, 'Nicaragua', 'NI');
INSERT INTO `support_countries` VALUES (153, 'Niger', 'NE');
INSERT INTO `support_countries` VALUES (154, 'Nigeria', 'NG');
INSERT INTO `support_countries` VALUES (155, 'Norvegia', 'NO');
INSERT INTO `support_countries` VALUES (156, 'Nuova Caledonia', 'NC');
INSERT INTO `support_countries` VALUES (157, 'Nuova Zelanda', 'NZ');
INSERT INTO `support_countries` VALUES (158, 'Olanda (Paesi Bassi)', 'NL');
INSERT INTO `support_countries` VALUES (159, 'Olandesi, Antille', 'AN3');
INSERT INTO `support_countries` VALUES (160, 'Oman', 'OM');
INSERT INTO `support_countries` VALUES (161, 'Pakistan', 'PK');
INSERT INTO `support_countries` VALUES (162, 'Palau', 'PW');
INSERT INTO `support_countries` VALUES (163, 'Panama', 'PA');
INSERT INTO `support_countries` VALUES (164, 'Papua Nuova Guinea', 'PG');
INSERT INTO `support_countries` VALUES (165, 'Paraguay', 'PY');
INSERT INTO `support_countries` VALUES (166, 'Per&#249;', 'PE');
INSERT INTO `support_countries` VALUES (167, 'Polinesia Francese', 'PF');
INSERT INTO `support_countries` VALUES (168, 'Polonia', 'PL');
INSERT INTO `support_countries` VALUES (169, 'Ponape (Stati Federali Micronesia)', 'FM3');
INSERT INTO `support_countries` VALUES (170, 'Portogallo', 'PT');
INSERT INTO `support_countries` VALUES (171, 'Puerto Rico (Portorico)', 'PR');
INSERT INTO `support_countries` VALUES (172, 'Qatar', 'QA');
INSERT INTO `support_countries` VALUES (173, 'Regno Unito - Galles', 'GBW');
INSERT INTO `support_countries` VALUES (174, 'Regno Unito - Inghilterra', 'GBE');
INSERT INTO `support_countries` VALUES (175, 'Regno Unito - Irlanda del Nord', 'GBI');
INSERT INTO `support_countries` VALUES (176, 'Regno Unito - Scozia', 'GBS');
INSERT INTO `support_countries` VALUES (177, 'R&#233;union', 'RE');
INSERT INTO `support_countries` VALUES (178, 'Romania', 'RO');
INSERT INTO `support_countries` VALUES (179, 'Rota (Isole Marianne del Nord)', 'MP2');
INSERT INTO `support_countries` VALUES (180, 'Ruanda', 'RW');
INSERT INTO `support_countries` VALUES (181, 'Russia', 'RU');
INSERT INTO `support_countries` VALUES (182, 'Saba (Antille Olandesi)', 'AN4');
INSERT INTO `support_countries` VALUES (183, 'Saipan (Isole Marianne del Nord)', 'MP1');
INSERT INTO `support_countries` VALUES (184, 'Samoa Americane', 'AS');
INSERT INTO `support_countries` VALUES (185, 'Samoa Occidentale', 'AS1');
INSERT INTO `support_countries` VALUES (186, 'Senegal', 'SN');
INSERT INTO `support_countries` VALUES (187, 'Seychelles', 'SC');
INSERT INTO `support_countries` VALUES (188, 'Sierra Leone', 'SL');
INSERT INTO `support_countries` VALUES (189, 'Singapore', 'SG');
INSERT INTO `support_countries` VALUES (190, 'Siria', 'SY');
INSERT INTO `support_countries` VALUES (191, 'Slovacca, Repubblica', 'SK');
INSERT INTO `support_countries` VALUES (192, 'Slovenia', 'SI');
INSERT INTO `support_countries` VALUES (193, 'Spagna', 'ES');
INSERT INTO `support_countries` VALUES (194, 'Sri Lanka', 'LK');
INSERT INTO `support_countries` VALUES (195, 'St. Barth&#233;lemy', 'XSB');
INSERT INTO `support_countries` VALUES (196, 'St. Christopher (St. Kitts-Nevis)', 'KN2');
INSERT INTO `support_countries` VALUES (197, 'St. Croix (Isole Vergini Statunitensi)', 'VI1');
INSERT INTO `support_countries` VALUES (198, 'St. Eustatius (Antille Olandesi)', 'AN5');
INSERT INTO `support_countries` VALUES (199, 'St. John (Isole Vergini Statunitensi)', 'VI2');
INSERT INTO `support_countries` VALUES (200, 'St. Kitts (St. Kitts-Nevis)', 'KN3');
INSERT INTO `support_countries` VALUES (201, 'St. Lucia', 'LC');
INSERT INTO `support_countries` VALUES (202, 'St. Maarten (Antille Olandesi)', 'AN6');
INSERT INTO `support_countries` VALUES (203, 'St. Martin (Guadalupa)', 'GP1');
INSERT INTO `support_countries` VALUES (204, 'St. Thomas (Isole Vergini Statunitensi)', 'VI3');
INSERT INTO `support_countries` VALUES (205, 'St. Vincent e le Grenadine', 'VC1');
INSERT INTO `support_countries` VALUES (206, 'Sud Africa', 'ZA');
INSERT INTO `support_countries` VALUES (207, 'Suriname', 'SR');
INSERT INTO `support_countries` VALUES (208, 'Svezia', 'SE');
INSERT INTO `support_countries` VALUES (209, 'Svizzera', 'CH');
INSERT INTO `support_countries` VALUES (210, 'Swaziland', 'SZ');
INSERT INTO `support_countries` VALUES (211, 'Tahiti', 'HT1');
INSERT INTO `support_countries` VALUES (212, 'Tailandia', 'TH');
INSERT INTO `support_countries` VALUES (213, 'Taiwan', 'TW');
INSERT INTO `support_countries` VALUES (214, 'Tajikistan', 'TJ');
INSERT INTO `support_countries` VALUES (215, 'Tanzania', 'TZ');
INSERT INTO `support_countries` VALUES (216, 'Tinian (Isole Marianne del Nord)', 'MP3');
INSERT INTO `support_countries` VALUES (217, 'Togo', 'TG');
INSERT INTO `support_countries` VALUES (218, 'Tonga', 'TO');
INSERT INTO `support_countries` VALUES (219, 'Tortola (Isole Vergini Britanniche)', 'VG1');
INSERT INTO `support_countries` VALUES (220, 'Trinidad e Tobago', 'TT');
INSERT INTO `support_countries` VALUES (221, 'Truk (Stati Federali Micronesia)', 'FM4');
INSERT INTO `support_countries` VALUES (222, 'Tunisia', 'TN');
INSERT INTO `support_countries` VALUES (223, 'Turchia', 'TR');
INSERT INTO `support_countries` VALUES (224, 'Turkmenistan', 'TM');
INSERT INTO `support_countries` VALUES (225, 'Tuvalu', 'TV');
INSERT INTO `support_countries` VALUES (226, 'Ucraina', 'UA');
INSERT INTO `support_countries` VALUES (227, 'Uganda', 'UG');
INSERT INTO `support_countries` VALUES (228, 'Ungheria', 'HU');
INSERT INTO `support_countries` VALUES (229, 'Uruguay', 'UY');
INSERT INTO `support_countries` VALUES (230, 'USA - Alabama', 'USAL');
INSERT INTO `support_countries` VALUES (231, 'USA - Alaska', 'USAK');
INSERT INTO `support_countries` VALUES (232, 'USA - altri stati', 'US');
INSERT INTO `support_countries` VALUES (233, 'USA - Arizona', 'USAZ');
INSERT INTO `support_countries` VALUES (234, 'USA - Arkansas', 'USAR');
INSERT INTO `support_countries` VALUES (235, 'USA - California', 'USCA');
INSERT INTO `support_countries` VALUES (236, 'USA - Colorado', 'USCO');
INSERT INTO `support_countries` VALUES (237, 'USA - Conneticut', 'USCT');
INSERT INTO `support_countries` VALUES (238, 'USA - Delaware', 'USDE');
INSERT INTO `support_countries` VALUES (239, 'USA - Dist. of Columbia', 'USDC');
INSERT INTO `support_countries` VALUES (240, 'USA - Florida', 'USFL');
INSERT INTO `support_countries` VALUES (241, 'USA - Georgia', 'USGA');
INSERT INTO `support_countries` VALUES (242, 'USA - Hawaii', 'USHI');
INSERT INTO `support_countries` VALUES (243, 'USA - Idaho', 'USID');
INSERT INTO `support_countries` VALUES (244, 'USA - Illinois', 'USIL');
INSERT INTO `support_countries` VALUES (245, 'USA - Indiana', 'USIN');
INSERT INTO `support_countries` VALUES (246, 'USA - Iowa', 'USIA');
INSERT INTO `support_countries` VALUES (247, 'USA - Kansas', 'USKS');
INSERT INTO `support_countries` VALUES (248, 'USA - Kentucky', 'USKY');
INSERT INTO `support_countries` VALUES (249, 'USA - Louisiana', 'USLA');
INSERT INTO `support_countries` VALUES (250, 'USA - Maine', 'USME');
INSERT INTO `support_countries` VALUES (251, 'USA - Maryland', 'USMD');
INSERT INTO `support_countries` VALUES (252, 'USA - Massachusetts', 'USMA');
INSERT INTO `support_countries` VALUES (253, 'USA - Michigan', 'USMI');
INSERT INTO `support_countries` VALUES (254, 'USA - Minnesota', 'USMN');
INSERT INTO `support_countries` VALUES (255, 'USA - Mississippi', 'USMS');
INSERT INTO `support_countries` VALUES (256, 'USA - Missouri', 'USMO');
INSERT INTO `support_countries` VALUES (257, 'USA - Montana', 'USMT');
INSERT INTO `support_countries` VALUES (258, 'USA - Nebraska', 'USNE');
INSERT INTO `support_countries` VALUES (259, 'USA - Nevada', 'USNV');
INSERT INTO `support_countries` VALUES (260, 'USA - New Hampshire', 'USNH');
INSERT INTO `support_countries` VALUES (261, 'USA - New Jersey', 'USNJ');
INSERT INTO `support_countries` VALUES (262, 'USA - New Mexico', 'USNM');
INSERT INTO `support_countries` VALUES (263, 'USA - New York', 'USNY');
INSERT INTO `support_countries` VALUES (264, 'USA - North Carolina', 'USNC');
INSERT INTO `support_countries` VALUES (265, 'USA - North Dakota', 'USND');
INSERT INTO `support_countries` VALUES (266, 'USA - Ohio', 'USOH');
INSERT INTO `support_countries` VALUES (267, 'USA - Oklahoma', 'USOK');
INSERT INTO `support_countries` VALUES (268, 'USA - Oregon', 'USOR');
INSERT INTO `support_countries` VALUES (269, 'USA - Pennsylvania', 'USPA');
INSERT INTO `support_countries` VALUES (270, 'USA - Rhode Island', 'USRI');
INSERT INTO `support_countries` VALUES (271, 'USA - South Carolina', 'USSC');
INSERT INTO `support_countries` VALUES (272, 'USA - South Dakota', 'USSD');
INSERT INTO `support_countries` VALUES (273, 'USA - Tennessee', 'USTN');
INSERT INTO `support_countries` VALUES (274, 'USA - Texas', 'USTX');
INSERT INTO `support_countries` VALUES (275, 'USA - Utah', 'USUT');
INSERT INTO `support_countries` VALUES (276, 'USA - Vermont', 'USVT');
INSERT INTO `support_countries` VALUES (277, 'USA - Virginia', 'USVA');
INSERT INTO `support_countries` VALUES (278, 'USA - Washington', 'USWA');
INSERT INTO `support_countries` VALUES (279, 'USA - West Virginia', 'USWV');
INSERT INTO `support_countries` VALUES (280, 'USA - Wisconsin', 'USWI');
INSERT INTO `support_countries` VALUES (281, 'USA - Wyoming', 'USWY');
INSERT INTO `support_countries` VALUES (282, 'Uzbekistan', 'UZ');
INSERT INTO `support_countries` VALUES (283, 'Vanuatu', 'VU');
INSERT INTO `support_countries` VALUES (284, 'Venezuela', 'VE');
INSERT INTO `support_countries` VALUES (285, 'Vietnam', 'VN');
INSERT INTO `support_countries` VALUES (286, 'Virgin Gorda (Isole Vergini Britanniche)', 'VG2');
INSERT INTO `support_countries` VALUES (287, 'Yap (Stati Federali Micronesia)', 'FM5');
INSERT INTO `support_countries` VALUES (288, 'Yemen, Repubblica dello', 'YE');
INSERT INTO `support_countries` VALUES (289, 'Zambia', 'ZM');
INSERT INTO `support_countries` VALUES (290, 'Zimbabwe', 'ZW');

-- ----------------------------
-- Table structure for support_province
-- ----------------------------
DROP TABLE IF EXISTS `support_province`;
CREATE TABLE `support_province`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RegionID` int(11) NOT NULL,
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CarAbbreviation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `RegionID`(`RegionID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 111 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of support_province
-- ----------------------------
INSERT INTO `support_province` VALUES (1, 1, 'Torino', 'TO', 'torino');
INSERT INTO `support_province` VALUES (2, 1, 'Vercelli', 'VC', 'vercelli');
INSERT INTO `support_province` VALUES (3, 1, 'Novara', 'NO', 'novara');
INSERT INTO `support_province` VALUES (4, 1, 'Cuneo', 'CN', 'cuneo');
INSERT INTO `support_province` VALUES (5, 1, 'Asti', 'AT', 'asti');
INSERT INTO `support_province` VALUES (6, 1, 'Alessandria', 'AL', 'alessandria');
INSERT INTO `support_province` VALUES (7, 2, 'Aosta', 'AO', 'aosta');
INSERT INTO `support_province` VALUES (8, 7, 'Imperia', 'IM', 'imperia');
INSERT INTO `support_province` VALUES (9, 7, 'Savona', 'SV', 'savona');
INSERT INTO `support_province` VALUES (10, 7, 'Genova', 'GE', 'genova');
INSERT INTO `support_province` VALUES (11, 7, 'La Spezia', 'SP', 'la-spezia');
INSERT INTO `support_province` VALUES (12, 3, 'Varese', 'VA', 'varese');
INSERT INTO `support_province` VALUES (13, 3, 'Como', 'CO', 'como');
INSERT INTO `support_province` VALUES (14, 3, 'Sondrio', 'SO', 'sondrio');
INSERT INTO `support_province` VALUES (15, 3, 'Milano', 'MI', 'milano');
INSERT INTO `support_province` VALUES (16, 3, 'Bergamo', 'BG', 'bergamo');
INSERT INTO `support_province` VALUES (17, 3, 'Brescia', 'BS', 'brescia');
INSERT INTO `support_province` VALUES (18, 3, 'Pavia', 'PV', 'pavia');
INSERT INTO `support_province` VALUES (19, 3, 'Cremona', 'CR', 'cremona');
INSERT INTO `support_province` VALUES (20, 3, 'Mantova', 'MN', 'mantova');
INSERT INTO `support_province` VALUES (21, 4, 'Bolzano', 'BZ', 'bolzano');
INSERT INTO `support_province` VALUES (22, 4, 'Trento', 'TN', 'trento');
INSERT INTO `support_province` VALUES (23, 5, 'Verona', 'VR', 'verona');
INSERT INTO `support_province` VALUES (24, 5, 'Vicenza', 'VI', 'vicenza');
INSERT INTO `support_province` VALUES (25, 5, 'Belluno', 'BL', 'belluno');
INSERT INTO `support_province` VALUES (26, 5, 'Treviso', 'TV', 'treviso');
INSERT INTO `support_province` VALUES (27, 5, 'Venezia', 'VE', 'venezia');
INSERT INTO `support_province` VALUES (28, 5, 'Padova', 'PD', 'padova');
INSERT INTO `support_province` VALUES (29, 5, 'Rovigo', 'RO', 'rovigo');
INSERT INTO `support_province` VALUES (30, 6, 'Udine', 'UD', 'udine');
INSERT INTO `support_province` VALUES (31, 6, 'Gorizia', 'GO', 'gorizia');
INSERT INTO `support_province` VALUES (32, 6, 'Trieste', 'TS', 'trieste');
INSERT INTO `support_province` VALUES (33, 8, 'Piacenza', 'PC', 'piacenza');
INSERT INTO `support_province` VALUES (34, 8, 'Parma', 'PR', 'parma');
INSERT INTO `support_province` VALUES (35, 8, 'Reggio Emilia', 'RE', 'reggio-emilia');
INSERT INTO `support_province` VALUES (36, 8, 'Modena', 'MO', 'modena');
INSERT INTO `support_province` VALUES (37, 8, 'Bologna', 'BO', 'bologna');
INSERT INTO `support_province` VALUES (38, 8, 'Ferrara', 'FE', 'ferrara');
INSERT INTO `support_province` VALUES (39, 8, 'Ravenna', 'RA', 'ravenna');
INSERT INTO `support_province` VALUES (40, 8, 'Forlì Cesena', 'FC', 'forli-cesena');
INSERT INTO `support_province` VALUES (41, 11, 'Pesaro Urbino', 'PU', 'pesaro-urbino');
INSERT INTO `support_province` VALUES (42, 11, 'Ancona', 'AN', 'ancona');
INSERT INTO `support_province` VALUES (43, 11, 'Macerata', 'MC', 'macerata');
INSERT INTO `support_province` VALUES (44, 11, 'Ascoli Piceno', 'AP', 'ascoli-piceno');
INSERT INTO `support_province` VALUES (45, 9, 'Massa Carrara', 'MS', 'massa-carrara');
INSERT INTO `support_province` VALUES (46, 9, 'Lucca', 'LU', 'lucca');
INSERT INTO `support_province` VALUES (47, 9, 'Pistoia', 'PT', 'pistoia');
INSERT INTO `support_province` VALUES (48, 9, 'Firenze', 'FI', 'firenze');
INSERT INTO `support_province` VALUES (49, 9, 'Livorno', 'LI', 'livorno');
INSERT INTO `support_province` VALUES (50, 9, 'Pisa', 'PI', 'pisa');
INSERT INTO `support_province` VALUES (51, 9, 'Arezzo', 'AR', 'arezzo');
INSERT INTO `support_province` VALUES (52, 9, 'Siena', 'SI', 'siena');
INSERT INTO `support_province` VALUES (53, 9, 'Grosseto', 'GR', 'grosseto');
INSERT INTO `support_province` VALUES (54, 10, 'Perugia', 'PG', 'perugia');
INSERT INTO `support_province` VALUES (55, 10, 'Terni', 'TR', 'terni');
INSERT INTO `support_province` VALUES (56, 12, 'Viterbo', 'VT', 'viterbo');
INSERT INTO `support_province` VALUES (57, 12, 'Rieti', 'RI', 'rieti');
INSERT INTO `support_province` VALUES (58, 12, 'Roma', 'RM', 'roma');
INSERT INTO `support_province` VALUES (59, 12, 'Latina', 'LT', 'latina');
INSERT INTO `support_province` VALUES (60, 12, 'Frosinone', 'FR', 'frosinone');
INSERT INTO `support_province` VALUES (61, 15, 'Caserta', 'CE', 'caserta');
INSERT INTO `support_province` VALUES (62, 15, 'Benevento', 'BN', 'benevento');
INSERT INTO `support_province` VALUES (63, 15, 'Napoli', 'NA', 'napoli');
INSERT INTO `support_province` VALUES (64, 15, 'Avellino', 'AV', 'avellino');
INSERT INTO `support_province` VALUES (65, 15, 'Salerno', 'SA', 'salerno');
INSERT INTO `support_province` VALUES (66, 13, 'L\'Aquila', 'AQ', 'l-aquila');
INSERT INTO `support_province` VALUES (67, 13, 'Teramo', 'TE', 'teramo');
INSERT INTO `support_province` VALUES (68, 13, 'Pescara', 'PE', 'pescara');
INSERT INTO `support_province` VALUES (69, 13, 'Chieti', 'CH', 'chieti');
INSERT INTO `support_province` VALUES (70, 14, 'Campobasso', 'CB', 'campobasso');
INSERT INTO `support_province` VALUES (71, 16, 'Foggia', 'FG', 'foggia');
INSERT INTO `support_province` VALUES (72, 16, 'Bari', 'BA', 'bari');
INSERT INTO `support_province` VALUES (73, 16, 'Taranto', 'TA', 'taranto');
INSERT INTO `support_province` VALUES (74, 16, 'Brindisi', 'BR', 'brindisi');
INSERT INTO `support_province` VALUES (75, 16, 'Lecce', 'LE', 'lecce');
INSERT INTO `support_province` VALUES (76, 17, 'Potenza', 'PZ', 'potenza');
INSERT INTO `support_province` VALUES (77, 17, 'Matera', 'MT', 'matera');
INSERT INTO `support_province` VALUES (78, 18, 'Cosenza', 'CS', 'cosenza');
INSERT INTO `support_province` VALUES (79, 18, 'Catanzaro', 'CZ', 'catanzaro');
INSERT INTO `support_province` VALUES (80, 18, 'Reggio di Calabria', 'RC', 'reggio-di-calabria');
INSERT INTO `support_province` VALUES (81, 19, 'Trapani', 'TP', 'trapani');
INSERT INTO `support_province` VALUES (82, 19, 'Palermo', 'PA', 'palermo');
INSERT INTO `support_province` VALUES (83, 19, 'Messina', 'ME', 'messina');
INSERT INTO `support_province` VALUES (84, 19, 'Agrigento', 'AG', 'agrigento');
INSERT INTO `support_province` VALUES (85, 19, 'Caltanissetta', 'CL', 'caltanissetta');
INSERT INTO `support_province` VALUES (86, 19, 'Enna', 'EN', 'enna');
INSERT INTO `support_province` VALUES (87, 19, 'Catania', 'CT', 'catania');
INSERT INTO `support_province` VALUES (88, 19, 'Ragusa', 'RG', 'ragusa');
INSERT INTO `support_province` VALUES (89, 19, 'Siracusa', 'SR', 'siracusa');
INSERT INTO `support_province` VALUES (90, 20, 'Sassari', 'SS', 'sassari');
INSERT INTO `support_province` VALUES (91, 20, 'Nuoro', 'NU', 'nuoro');
INSERT INTO `support_province` VALUES (92, 20, 'Cagliari', 'CA', 'cagliari');
INSERT INTO `support_province` VALUES (93, 6, 'Pordenone', 'PN', 'pordenone');
INSERT INTO `support_province` VALUES (94, 14, 'Isernia', 'IS', 'isernia');
INSERT INTO `support_province` VALUES (95, 20, 'Oristano', 'OR', 'oristano');
INSERT INTO `support_province` VALUES (96, 1, 'Biella', 'BI', 'biella');
INSERT INTO `support_province` VALUES (97, 3, 'Lecco', 'LC', 'lecco');
INSERT INTO `support_province` VALUES (98, 3, 'Lodi', 'LO', 'lodi');
INSERT INTO `support_province` VALUES (99, 8, 'Rimini', 'RN', 'rimini');
INSERT INTO `support_province` VALUES (100, 9, 'Prato', 'PO', 'prato');
INSERT INTO `support_province` VALUES (101, 18, 'Crotone', 'KR', 'crotone');
INSERT INTO `support_province` VALUES (102, 18, 'Vibo Valentia', 'VV', 'vibo-valentia');
INSERT INTO `support_province` VALUES (103, 1, 'Verbano Cusio Ossola', 'VB', 'verbano-cusio-ossola');
INSERT INTO `support_province` VALUES (104, 20, 'Olbia Tempio', 'OT', 'olbia-tempio');
INSERT INTO `support_province` VALUES (105, 20, 'Ogliastra', 'OG', 'ogliastra');
INSERT INTO `support_province` VALUES (106, 20, 'Medio Campidano', 'VS', 'medio-campidano');
INSERT INTO `support_province` VALUES (107, 20, 'Carbonia Iglesias', 'CI', 'carbonia-iglesias');
INSERT INTO `support_province` VALUES (108, 3, 'Monza e della Brianza', 'MB', 'monza-e-della-brianza');
INSERT INTO `support_province` VALUES (109, 11, 'Fermo', 'FM', 'fermo');
INSERT INTO `support_province` VALUES (110, 16, 'Barletta Andria Trani', 'BT', 'barletta-andria-trani');

-- ----------------------------
-- Table structure for support_regioni
-- ----------------------------
DROP TABLE IF EXISTS `support_regioni`;
CREATE TABLE `support_regioni`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of support_regioni
-- ----------------------------
INSERT INTO `support_regioni` VALUES (1, 'Piemonte', 'piemonte');
INSERT INTO `support_regioni` VALUES (2, 'Valle d\'Aosta', 'valle-d-aosta');
INSERT INTO `support_regioni` VALUES (3, 'Lombardia', 'lombardia');
INSERT INTO `support_regioni` VALUES (4, 'Trentino Alto Adige', 'trentino-alto-adige');
INSERT INTO `support_regioni` VALUES (5, 'Veneto', 'veneto');
INSERT INTO `support_regioni` VALUES (6, 'Friuli Venezia Giulia', 'friuli-venezia-giulia');
INSERT INTO `support_regioni` VALUES (7, 'Liguria', 'liguria');
INSERT INTO `support_regioni` VALUES (8, 'Emilia Romagna', 'emilia-romagna');
INSERT INTO `support_regioni` VALUES (9, 'Toscana', 'toscana');
INSERT INTO `support_regioni` VALUES (10, 'Umbria', 'umbria');
INSERT INTO `support_regioni` VALUES (11, 'Marche', 'marche');
INSERT INTO `support_regioni` VALUES (12, 'Lazio', 'lazio');
INSERT INTO `support_regioni` VALUES (13, 'Abruzzo', 'abruzzo');
INSERT INTO `support_regioni` VALUES (14, 'Molise', 'molise');
INSERT INTO `support_regioni` VALUES (15, 'Campania', 'campania');
INSERT INTO `support_regioni` VALUES (16, 'Puglia', 'puglia');
INSERT INTO `support_regioni` VALUES (17, 'Basilicata', 'basilicata');
INSERT INTO `support_regioni` VALUES (18, 'Calabria', 'calabria');
INSERT INTO `support_regioni` VALUES (19, 'Sicilia', 'sicilia');
INSERT INTO `support_regioni` VALUES (20, 'Sardegna', 'sardegna');

-- ----------------------------
-- Table structure for tipo_cdr
-- ----------------------------
DROP TABLE IF EXISTS `tipo_cdr`;
CREATE TABLE `tipo_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID_UNIQUE`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tipo_cdr_padre
-- ----------------------------
DROP TABLE IF EXISTS `tipo_cdr_padre`;
CREATE TABLE `tipo_cdr_padre`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_tipo_cdr` int(11) NOT NULL,
  `ID_tipo_cdr_padre` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tipo_contratto
-- ----------------------------
DROP TABLE IF EXISTS `tipo_contratto`;
CREATE TABLE `tipo_contratto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tipo_piano_cdr
-- ----------------------------
DROP TABLE IF EXISTS `tipo_piano_cdr`;
CREATE TABLE `tipo_piano_cdr`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priorita` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_ambito
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_ambito`;
CREATE TABLE `valutazioni_ambito`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_inizio` int(11) NOT NULL,
  `anno_fine` int(11) NULL DEFAULT NULL,
  `ID_sezione` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_ambito_categoria_anno
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_ambito_categoria_anno`;
CREATE TABLE `valutazioni_ambito_categoria_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_ambito` int(11) NOT NULL,
  `ID_categoria` int(11) NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `peso` int(11) NULL DEFAULT NULL,
  `metodo_valutazione` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `key`(`ID_ambito`, `ID_categoria`, `ID_anno_budget`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_ambito_precalcolato
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_ambito_precalcolato`;
CREATE TABLE `valutazioni_ambito_precalcolato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_ambito` int(11) NOT NULL,
  `ID_valutazione` int(11) NOT NULL,
  `valore` decimal(10, 2) NULL DEFAULT NULL,
  `time_aggiornamento` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_area_item
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_area_item`;
CREATE TABLE `valutazioni_area_item`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_ambito` int(11) NOT NULL,
  `ordine_visualizzazione` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_categoria
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_categoria`;
CREATE TABLE `valutazioni_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `abbreviazione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dirigenza` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `formula_appartenenza_personale` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `anno_inizio` int(11) NOT NULL,
  `anno_fine` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_cdr_cruscotto
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_cdr_cruscotto`;
CREATE TABLE `valutazioni_cdr_cruscotto`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice_cdr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anno_inizio` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anno_fine` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_fascia_punteggio
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_fascia_punteggio`;
CREATE TABLE `valutazioni_fascia_punteggio`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `min` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `colore` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_item
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_item`;
CREATE TABLE `valutazioni_item`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peso` int(11) NOT NULL,
  `anno_introduzione` int(11) NOT NULL,
  `anno_esclusione` int(11) NULL DEFAULT NULL,
  `ID_area_item` int(11) NOT NULL,
  `ordine_visualizzazione` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_item_categoria
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_item_categoria`;
CREATE TABLE `valutazioni_item_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_item` int(11) NOT NULL,
  `ID_categoria` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_periodo
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_periodo`;
CREATE TABLE `valutazioni_periodo`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_anno_budget` int(11) NOT NULL,
  `inibizione_visualizzazione_totali` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `inibizione_visualizzazione_ambiti_totali` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `inibizione_visualizzazione_data_colloquio` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `data_apertura_compilazione` date NULL DEFAULT NULL,
  `data_chiusura_autovalutazione` date NULL DEFAULT NULL,
  `data_chiusura_valutatore` date NULL DEFAULT NULL,
  `data_chiusura_valutato` date NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_periodo_categoria
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_periodo_categoria`;
CREATE TABLE `valutazioni_periodo_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo` int(11) NOT NULL,
  `ID_categoria` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_periodo_categoria_ambito
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_periodo_categoria_ambito`;
CREATE TABLE `valutazioni_periodo_categoria_ambito`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_periodo_categoria` int(11) NOT NULL,
  `ID_ambito` int(11) NOT NULL,
  `autovalutazione_attiva` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `inibizione_visualizzazione_punteggi` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `key`(`ID_periodo_categoria`, `ID_ambito`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_punteggio_item
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_punteggio_item`;
CREATE TABLE `valutazioni_punteggio_item`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `punteggio` float NOT NULL,
  `descrizione` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ID_item` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_regola_categoria
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_regola_categoria`;
CREATE TABLE `valutazioni_regola_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_categoria` int(11) NULL DEFAULT NULL,
  `ID_attributo` int(11) NULL DEFAULT NULL,
  `valore` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_sezione
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_sezione`;
CREATE TABLE `valutazioni_sezione`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_sezione_peso_anno
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_sezione_peso_anno`;
CREATE TABLE `valutazioni_sezione_peso_anno`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_anno_budget` int(11) NOT NULL,
  `ID_sezione` int(11) NOT NULL,
  `ID_categoria` int(11) NOT NULL,
  `peso` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE,
  INDEX `key`(`ID_sezione`, `ID_anno_budget`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_totale
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_totale`;
CREATE TABLE `valutazioni_totale`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `anno_inizio` int(11) NOT NULL,
  `anno_fine` int(11) NULL DEFAULT NULL,
  `ordine_visualizzazione` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_totale_ambito
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_totale_ambito`;
CREATE TABLE `valutazioni_totale_ambito`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_totale` int(11) NOT NULL,
  `ID_ambito` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_totale_categoria
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_totale_categoria`;
CREATE TABLE `valutazioni_totale_categoria`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_totale` int(11) NOT NULL,
  `ID_categoria` int(11) NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE INDEX `ID`(`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for valutazioni_totale_precalcolato
-- ----------------------------
DROP TABLE IF EXISTS `valutazioni_totale_precalcolato`;
CREATE TABLE `valutazioni_totale_precalcolato`  (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_totale` int(11) NOT NULL,
  `ID_valutazione` int(11) NOT NULL,
  `valore` decimal(10, 2) NULL DEFAULT NULL,
  `time_aggiornamento` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
