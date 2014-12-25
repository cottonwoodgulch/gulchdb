# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.5.38)
# Database: trek
# Generation Time: 2014-12-25 06:44:06 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table address_associations
# ------------------------------------------------------------

CREATE TABLE `address_associations` (
  `address_association_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `address_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`address_association_id`),
  KEY `contact_id` (`contact_id`),
  KEY `address_id` (`address_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Associate addresses with one or more contacts each';



# Dump of table address_types
# ------------------------------------------------------------

CREATE TABLE `address_types` (
  `address_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `address_type` varchar(50) NOT NULL DEFAULT '',
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  `show_custom` tinyint(4) NOT NULL DEFAULT '0',
  `custom_caption` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`address_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Types of addresses and relative importance';



# Dump of table addresses
# ------------------------------------------------------------

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `address_type_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `street_address_1` varchar(250) NOT NULL DEFAULT '',
  `street_address_2` varchar(250) DEFAULT NULL,
  `city` varchar(250) NOT NULL DEFAULT '',
  `state` varchar(250) NOT NULL DEFAULT '',
  `country` varchar(250) NOT NULL DEFAULT 'United States',
  `postal_code` varchar(20) NOT NULL DEFAULT '',
  `custom` varchar(255) DEFAULT NULL,
  `start` date DEFAULT NULL,
  `end` date DEFAULT NULL,
  `recurring` enum('yes','no') NOT NULL DEFAULT 'no',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`address_id`),
  KEY `address_type_id` (`address_type_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table bookmarks
# ------------------------------------------------------------

CREATE TABLE `bookmarks` (
  `bookmark_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT '',
  `sql` longtext NOT NULL,
  `display` longtext NOT NULL,
  PRIMARY KEY (`bookmark_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table contact_types
# ------------------------------------------------------------

CREATE TABLE `contact_types` (
  `contact_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_type` varchar(50) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  `show_title` tinyint(4) NOT NULL DEFAULT '1',
  `title_caption` varchar(50) NOT NULL DEFAULT 'Title',
  `show_primary_name` tinyint(4) NOT NULL DEFAULT '1',
  `primary_name_caption` varchar(50) NOT NULL DEFAULT 'Last Name',
  `show_first_name` tinyint(4) NOT NULL DEFAULT '1',
  `first_name_caption` varchar(50) NOT NULL DEFAULT 'First Name',
  `show_middle_name` tinyint(4) NOT NULL DEFAULT '1',
  `middle_name_caption` varchar(50) NOT NULL DEFAULT 'Middle Name',
  `show_degree` tinyint(4) NOT NULL DEFAULT '1',
  `degree_caption` varchar(50) NOT NULL DEFAULT 'Degree',
  `show_nickname` tinyint(4) NOT NULL DEFAULT '1',
  `nickname_caption` varchar(50) NOT NULL DEFAULT 'Nickname',
  `show_birth_date` tinyint(4) NOT NULL DEFAULT '1',
  `birth_date_caption` varchar(50) NOT NULL DEFAULT 'Birth Date',
  `show_gender` tinyint(4) NOT NULL DEFAULT '1',
  `gender_caption` varchar(50) NOT NULL DEFAULT 'Gender',
  `show_deceased` tinyint(4) NOT NULL DEFAULT '1',
  `deceased_caption` varchar(50) NOT NULL DEFAULT 'Deceased',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`contact_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Specify which fields are shown (and what their captions are)';



# Dump of table contacts
# ------------------------------------------------------------

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_type_id` int(11) NOT NULL DEFAULT '0',
  `title_id` int(11) DEFAULT NULL,
  `primary_name` varchar(250) NOT NULL DEFAULT '',
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `degree_id` int(11) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `deceased` tinyint(4) NOT NULL DEFAULT '0',
  `mailing_preference` int(11) NOT NULL DEFAULT '1',
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `contact_type_id` (`contact_type_id`),
  KEY `title_id` (`title_id`),
  KEY `degree_id` (`degree_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0;



# Dump of table degrees
# ------------------------------------------------------------

CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL AUTO_INCREMENT,
  `degree` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`degree_id`),
  UNIQUE KEY `degree` (`degree`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table donation_associations
# ------------------------------------------------------------

CREATE TABLE `donation_associations` (
  `donation_assocation_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `donation_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`donation_assocation_id`),
  KEY `contact_id` (`contact_id`),
  KEY `donation_id` (`donation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Associate each donation with one or more contacts';



# Dump of table donations
# ------------------------------------------------------------

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `amount` double DEFAULT NULL,
  `check_number` varchar(50) DEFAULT NULL,
  `check_id` int(11) DEFAULT NULL,
  `fund_id` int(11) NOT NULL DEFAULT '0',
  `anonymous` tinyint(4) NOT NULL DEFAULT '0',
  `share_count` double DEFAULT NULL,
  `share_value` double DEFAULT NULL,
  `share_company` varchar(250) DEFAULT NULL,
  `purpose` varchar(250) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`donation_id`),
  KEY `donor_id` (`donor_id`),
  KEY `check_id` (`check_id`),
  KEY `fund_id` (`fund_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table email_associations
# ------------------------------------------------------------

CREATE TABLE `email_associations` (
  `email_association_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `email_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_association_id`),
  KEY `contact_id` (`contact_id`),
  KEY `email_id` (`email_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table email_types
# ------------------------------------------------------------

CREATE TABLE `email_types` (
  `email_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type` varchar(50) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Types of emails and their relative importance';



# Dump of table emails
# ------------------------------------------------------------

CREATE TABLE `emails` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(250) NOT NULL DEFAULT '',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`email_id`),
  KEY `email_type_id` (`email_type_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table families
# ------------------------------------------------------------

CREATE TABLE `families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table family_associations
# ------------------------------------------------------------

CREATE TABLE `family_associations` (
  `family_association_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`family_association_id`),
  UNIQUE KEY `family_id` (`family_id`,`contact_id`),
  CONSTRAINT `family_associations_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table funds
# ------------------------------------------------------------

CREATE TABLE `funds` (
  `fund_id` int(11) NOT NULL AUTO_INCREMENT,
  `fund` varchar(250) NOT NULL DEFAULT '',
  `code` varchar(10) DEFAULT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fund_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table groups
# ------------------------------------------------------------

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(250) NOT NULL DEFAULT '',
  `short_name` varchar(50) NOT NULL DEFAULT '',
  `excluded` enum('0','1') NOT NULL DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `program_name` (`group`,`short_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table mailing_preferences
# ------------------------------------------------------------

CREATE TABLE `mailing_preferences` (
  `mailing_preference_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mailing_preference` text NOT NULL,
  `rank` int(11) DEFAULT NULL,
  PRIMARY KEY (`mailing_preference_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table newtrek
# ------------------------------------------------------------

CREATE TABLE `newtrek` (
  `qb_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `is_new` tinyint(1) DEFAULT NULL,
  `is_phone` tinyint(1) DEFAULT NULL,
  `is_email` tinyint(1) DEFAULT NULL,
  `fname` varchar(64) DEFAULT NULL,
  `mname` varchar(64) DEFAULT NULL,
  `lname` varchar(64) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `dob` varchar(16) DEFAULT NULL,
  `gender` varchar(16) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `cell` varchar(32) DEFAULT NULL,
  `phone_format` tinyint(4) DEFAULT NULL,
  `addr1` varchar(128) DEFAULT NULL,
  `addr2` varchar(128) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `state` varchar(32) DEFAULT NULL,
  `zip` varchar(8) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `trekgroup` varchar(64) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`qb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table notes
# ------------------------------------------------------------

CREATE TABLE `notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `phone_id` int(11) DEFAULT NULL,
  `email_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `donation_id` int(11) DEFAULT NULL,
  `check_id` int(11) DEFAULT NULL,
  `form_id` int(11) DEFAULT NULL,
  `form_received_id` int(11) DEFAULT NULL,
  `fund_id` int(11) DEFAULT NULL,
  `relationship_id` int(11) DEFAULT NULL,
  `url_id` int(11) DEFAULT NULL,
  `note` longtext NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`note_id`),
  KEY `contact_id` (`contact_id`),
  KEY `address_id` (`address_id`),
  KEY `phone_id` (`phone_id`),
  KEY `email_id` (`email_id`),
  KEY `group_id` (`group_id`),
  KEY `payment_id` (`payment_id`),
  KEY `donation_id` (`donation_id`),
  KEY `check_id` (`check_id`),
  KEY `form_id` (`form_id`),
  KEY `form_received_id` (`form_received_id`),
  KEY `fund_id` (`fund_id`),
  KEY `relationship_id` (`relationship_id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table payment_types
# ------------------------------------------------------------

CREATE TABLE `payment_types` (
  `payment_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(250) NOT NULL DEFAULT '',
  `code` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`payment_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table payments
# ------------------------------------------------------------

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type_id` int(11) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `tuition_id` int(11) NOT NULL DEFAULT '0',
  `amount` double DEFAULT NULL,
  `check_number` int(11) DEFAULT NULL,
  `check_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`payment_id`),
  KEY `payment_type_id` (`payment_type_id`),
  KEY `contact_id` (`contact_id`),
  KEY `tuition_id` (`tuition_id`),
  KEY `check_id` (`check_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table phone_associations
# ------------------------------------------------------------

CREATE TABLE `phone_associations` (
  `phone_association_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `phone_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`phone_association_id`),
  KEY `contact_id` (`contact_id`),
  KEY `phone_id` (`phone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Associate phone numbers with one or more contacts each';



# Dump of table phone_types
# ------------------------------------------------------------

CREATE TABLE `phone_types` (
  `phone_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_type` varchar(50) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`phone_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Types of phone numbers and their relative importance';



# Dump of table phones
# ------------------------------------------------------------

CREATE TABLE `phones` (
  `phone_id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_type_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `number` char(50) NOT NULL DEFAULT '0',
  `formatted` tinyint(4) NOT NULL DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`phone_id`),
  KEY `phone_type_id` (`phone_type_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table phprbac_permissions
# ------------------------------------------------------------

CREATE TABLE `phprbac_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Lft` int(11) NOT NULL,
  `Rght` int(11) NOT NULL,
  `Title` char(64) COLLATE utf8_bin NOT NULL,
  `Description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`),
  KEY `Lft` (`Lft`),
  KEY `Rght` (`Rght`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table phprbac_rolepermissions
# ------------------------------------------------------------

CREATE TABLE `phprbac_rolepermissions` (
  `RoleID` int(11) NOT NULL,
  `PermissionID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY (`RoleID`,`PermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table phprbac_roles
# ------------------------------------------------------------

CREATE TABLE `phprbac_roles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Lft` int(11) NOT NULL,
  `Rght` int(11) NOT NULL,
  `Title` varchar(128) COLLATE utf8_bin NOT NULL,
  `Description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`),
  KEY `Lft` (`Lft`),
  KEY `Rght` (`Rght`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table phprbac_userroles
# ------------------------------------------------------------

CREATE TABLE `phprbac_userroles` (
  `UserID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY (`UserID`,`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table region
# ------------------------------------------------------------

CREATE TABLE `region` (
  `region` varchar(32) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `zip` char(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table relationship_types
# ------------------------------------------------------------

CREATE TABLE `relationship_types` (
  `relationship_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `relationship_type` varchar(250) NOT NULL DEFAULT '',
  `Male` varchar(50) DEFAULT NULL,
  `Female` varchar(50) DEFAULT NULL,
  `inverse_relationship_id` int(11) NOT NULL DEFAULT '0',
  `excluded` enum('0','1') NOT NULL DEFAULT '0',
  `rank` smallint(6) NOT NULL DEFAULT '100',
  PRIMARY KEY (`relationship_type_id`),
  KEY `inverse_relationship_id` (`inverse_relationship_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table relationships
# ------------------------------------------------------------

CREATE TABLE `relationships` (
  `relationship_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `relationship_type_id` int(11) NOT NULL DEFAULT '0',
  `relative_id` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`relationship_id`),
  KEY `contact_id` (`contact_id`),
  KEY `relationship_type_id` (`relationship_type_id`),
  KEY `relative_id` (`relative_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL DEFAULT '',
  `is_staff` tinyint(4) NOT NULL DEFAULT '1',
  `rank` smallint(6) NOT NULL DEFAULT '100',
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Types of roles within a roster';



# Dump of table roster_memberships
# ------------------------------------------------------------

CREATE TABLE `roster_memberships` (
  `roster_membership_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `roster_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) DEFAULT NULL,
  `tuition_type_id` int(11) DEFAULT '2',
  `balance` double NOT NULL DEFAULT '0',
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`roster_membership_id`),
  KEY `contact_id` (`contact_id`),
  KEY `roster_id` (`roster_id`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Associate contacts with particular rosters';



# Dump of table rosters
# ------------------------------------------------------------

CREATE TABLE `rosters` (
  `roster_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `year` int(50) NOT NULL DEFAULT '0',
  `log` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`roster_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0 COMMENT='Individual rosters for each program';



# Dump of table titles
# ------------------------------------------------------------

CREATE TABLE `titles` (
  `title_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL DEFAULT '',
  `deprecated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`title_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Courtesy titles (Professor, Dr., Mr., Ms., etc.)';



# Dump of table tuition_types
# ------------------------------------------------------------

CREATE TABLE `tuition_types` (
  `tuition_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `tuition_type` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`tuition_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table tuitions
# ------------------------------------------------------------

CREATE TABLE `tuitions` (
  `tuition_id` int(11) NOT NULL AUTO_INCREMENT,
  `tuition_type_id` int(11) NOT NULL DEFAULT '0',
  `roster_id` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `due_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`tuition_id`),
  KEY `tuition_type_id` (`tuition_type_id`),
  KEY `roster_id` (`roster_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table url_associations
# ------------------------------------------------------------

CREATE TABLE `url_associations` (
  `url_association_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `url_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`url_association_id`),
  KEY `contact_id` (`contact_id`),
  KEY `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table url_types
# ------------------------------------------------------------

CREATE TABLE `url_types` (
  `url_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `url_type` varchar(50) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`url_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table urls
# ------------------------------------------------------------

CREATE TABLE `urls` (
  `url_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `url_type_id` int(11) NOT NULL DEFAULT '0',
  `url` varchar(250) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`url_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table zip_codes
# ------------------------------------------------------------

CREATE TABLE `zip_codes` (
  `zip` varchar(5) NOT NULL DEFAULT '0',
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `city` varchar(26) NOT NULL DEFAULT '',
  `state` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`zip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='http://www.census.gov/tiger/tms/gazetteer/zips.txt';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
