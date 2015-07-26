<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Ini {
    const SETTING_VERSION="version";
    const SETTING_EXTERNAL_PATH="external_path";
    const SETTING_RSCRIPT_PATH="rscript_path";
    const SETTING_IS_RSTUDIO_ON="is_rstudio_on";

    private static $error_reporting = false;
    public static $version = "";
    public static $internal_path = "";
    public static $external_path = "";
    public static $rscript_path = "";
    public static $temp_path = "";
    public static $main_methods_r_path = "";
    public static $is_rstudio_on = false;
    public static $rstudio_url = "";
    public static $dictionary_path = "";
    public static $internal_media_path = "";
    public static $external_media_path = "";

    function __construct() {
        if (self::$error_reporting)
            error_reporting(E_ALL);
        else
            error_reporting(0);

        if (!$this->initialize_db_connection())
            die("Error initializing DB connection!");
        
        self::load_static_settings();

        $this->load_classes();

        $this->initiate_session();

        $this->change_directory_rights();
    }

    private function initiate_session() {
        session_start();
        date_default_timezone_set('Europe/London');
        if (!isset($_SESSION['tpc_logged_login']))
            $_SESSION['tpc_logged_login'] = null;
        if (!isset($_SESSION['tpc_logged_md5_password']))
            $_SESSION['tpc_logged_md5_password'] = null;

        if (!isset($_GET['lng'])) {
            if (!isset($_SESSION['lng']))
                $_SESSION['lng'] = "en";
        }
        else
            $_SESSION['lng'] = $_GET['lng'];

        Language::load_dictionary();
    }

    private static function load_static_settings() {
        self::$version = self::get_setting(self::SETTING_VERSION);
        self::$internal_path = str_replace("model", "", __DIR__);
        self::$external_path = self::get_setting(self::SETTING_EXTERNAL_PATH);
        self::$rscript_path = self::get_setting(self::SETTING_RSCRIPT_PATH);
        self::$temp_path = self::$internal_path . "temp/";
        self::$main_methods_r_path = self::$internal_path . "R/library/mainmethods.R";
        self::$is_rstudio_on = self::get_setting(self::SETTING_IS_RSTUDIO_ON) == 1;
        self::$rstudio_url = "http://" . $_SERVER['HTTP_HOST'] . ":8787";
        self::$dictionary_path = self::$internal_path . "model/dictionary.xml";
        self::$internal_media_path = self::$internal_path . "media/";
        self::$external_media_path = self::$external_path . "media/";
    }

    private static function change_directory_rights() {
        chmod(Ini::$temp_path, 0777);
        chmod(Ini::$internal_media_path, 0777);
    }

    private function initialize_db_connection() {
        include str_replace("model", "", __DIR__) . "SETTINGS.php";
        $h = mysql_connect($db_host . ($db_port != "" ? ":" . $db_port : ""), $db_user, $db_password);
        if (!$h)
            return false;
        mysql_set_charset('utf8', $h);
        if (mysql_select_db($db_name, $h))
            return true;
        else
            return false;
    }

    public static function install_db() {
        //tables creation
        $sql = "CREATE TABLE IF NOT EXISTS `Item` (
			  `id` bigint(20) NOT NULL auto_increment,
			  `HTML` text NOT NULL,
			  `name` varchar(20) NOT NULL,
			  `timer` int(11) NOT NULL,
                          `default_Button_id` bigint(20) NOT NULL,
			  `time_updated` timestamp NULL default NULL,
			  `time_created` timestamp NOT NULL default '0000-00-00 00:00:00',
			  `hash` text NOT NULL,
                          `Sharing_id` int(11) NOT NULL,
                          `Owner_id` bigint(20) NOT NULL,
			  PRIMARY KEY  (`id`),
                          UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `ItemButton` (
                          `id` bigint(20) NOT NULL auto_increment,
                          `Item_id` bigint(20) NOT NULL,
                          `name` text NOT NULL,
                          `function` text NOT NULL,
                          PRIMARY KEY  (`id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `r_out` (
			  `ID` int(11) NOT NULL auto_increment,
			  `Session_ID` bigint(20) default '0',
			  `Variable` varchar(45) default NULL,
			  `Value` text,
			  PRIMARY KEY  (`ID`),
			  UNIQUE KEY `unique` (`Session_ID`,`Variable`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `Session` (
			  `id` bigint(20) NOT NULL auto_increment,
			  `time_created` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `User` (
			  `id` bigint(20) NOT NULL auto_increment,
			  `login` varchar(20) NOT NULL,
			  `md5_password` text NOT NULL,
                          `firstname` text NOT NULL,
                          `lastname` text NOT NULL,
                          `email` text NOT NULL,
                          `phone` text NOT NULL,
                          `Group_id` bigint(20) NOT NULL,
                          `last_action_time` int(11) NOT NULL,
                          `Owner_id` bigint(20) NOT NULL,
                          `Sharing_id` int(11) NOT NULL,
                          `superadmin` tinyint(1) NOT NULL,
			  PRIMARY KEY  (`id`),
                          UNIQUE KEY `login` (`login`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `Setting` (
                          `id` int(11) NOT NULL auto_increment,
			  `name` text NOT NULL,
			  `value` text NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `Group` (
			  `id` bigint(20) NOT NULL auto_increment,
			  `name` varchar(20) NOT NULL,
                          `Sharing_id` int(11) NOT NULL,
                          `Owner_id` bigint(11) NOT NULL, 
			  PRIMARY KEY  (`id`),
                          UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `DS_Sharing` (
			  `id` int(11) NOT NULL auto_increment,
			  `name` text NOT NULL,
                          `value` text NOT NULL,
                          `position` int(11) NOT NULL,
			  PRIMARY KEY  (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        mysql_query($sql);
        
        //patch
        //v2.0.2
        $sql = sprintf("ALTER TABLE  `Group` CHANGE  `name`  `name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE `Group` DROP INDEX name");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE  `Group` ADD UNIQUE (`name`)");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE  `Item` CHANGE  `name`  `name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE `Item` DROP INDEX name");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE  `User` CHANGE  `login`  `login` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE `User` DROP INDEX login");
        mysql_query($sql);
        
        $sql = sprintf("ALTER TABLE  `User` ADD UNIQUE (`login`)");
        mysql_query($sql);
        
        //rows insertion
        //Setting
        $sql = sprintf("SELECT * FROM `Setting` WHERE `name`='version'");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `Setting` SET `name`='version', `value`='2.0.5'");
            mysql_query($sql);
        }
        else self::set_setting(self::SETTING_VERSION,"2.0.5");

        $sql = sprintf("SELECT * FROM `Setting` WHERE `name`='rscript_path'");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `Setting` SET `name`='rscript_path', `value`=''");
            mysql_query($sql);
        }

        $sql = sprintf("SELECT * FROM `Setting` WHERE `name`='external_path'");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `Setting` SET `name`='external_path', `value`=''");
            mysql_query($sql);
        }
        
        $sql = sprintf("SELECT * FROM `Setting` WHERE `name`='is_rstudio_on'");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `Setting` SET `name`='is_rstudio_on', `value`='0'");
            mysql_query($sql);
        }

        //DS_Sharing
        $sql = sprintf("SELECT * FROM `DS_Sharing` WHERE `name`='%s'", "private");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `DS_Sharing` SET `name`='%s', `value`='%s'", "private", 1);
            mysql_query($sql);
        }
        $sql = sprintf("SELECT * FROM `DS_Sharing` WHERE `name`='%s'", "group");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `DS_Sharing` SET `name`='%s', `value`='%s'", "group", 2);
            mysql_query($sql);
        }
        $sql = sprintf("SELECT * FROM `DS_Sharing` WHERE `name`='%s'", "public");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) {
            $sql = sprintf("INSERT INTO `DS_Sharing` SET `name`='%s', `value`='%s'", "public", 3);
            mysql_query($sql);
        }
    }
    
    public static function install_files()
    {
        //patch
        //v2.0.2
        $filename = Ini::$internal_path."setup.php";
        if(file_exists($filename)) unlink($filename);
        
        $filename = Ini::$internal_path."admin/query/save_setting.php";
        if(file_exists($filename)) unlink($filename);
        
        $filename = Ini::$internal_path."admin/query/save_superadmin.php";
        if(file_exists($filename)) unlink($filename);
    }

    private function load_classes() {
        require_once self::$internal_path . "model/OTable.php";
        require_once self::$internal_path . "model/OModule.php";
        require_once self::$internal_path . "model/Group.php";
        require_once self::$internal_path . "model/User.php";
        require_once self::$internal_path . "model/ODataSet.php";
        require_once self::$internal_path . "model/DS_Sharing.php";

        require_once self::$internal_path . 'model/Language.php';
        require_once self::$internal_path . 'model/ItemButton.php';
        require_once self::$internal_path . 'model/Item.php';
    }

    public static function get_setting($name) {
        $sql = sprintf("SELECT `value` FROM `Setting` WHERE `name`='%s'", $name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
            return $r[0];
        return null;
    }

    public static function set_setting($name, $value) {
        $sql = sprintf("UPDATE `Setting` SET `value`='%s' WHERE `name`='%s'", $value, $name);
        mysql_query($sql);
    }

}

?>