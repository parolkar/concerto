<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class Setup {

    public static function get_db_update_steps_count() {
        return self::update_db(true);
    }

    public static function create_db() {
        return self::update_db(false, false, true);
    }

    public static function update_db_recalculate_hash() {
        return self::update_db(false, true);
    }

    public static function php_version_check() {
        $v = phpversion();
        $nums = explode(".", $v);
        if ($nums[0] < 5)
            return json_encode(array("result" => 1, "param" => $v));
        if ($nums[0] == 5 && $nums[1] < 3)
            return json_encode(array("result" => 1, "param" => $v));
        if ($nums[0] == 5 && $nums[1] >= 3)
            return json_encode(array("result" => 0, "param" => $v));
        if ($nums[0] > 5)
            return json_encode(array("result" => 0, "param" => $v));
    }

    public static function php_safe_mode_check() {
        return json_encode(array("result" => !ini_get("safe_mode") ? 0 : 1, "param" => ini_get("safe_mode")));
    }

    public static function php_magic_quotes_check() {
        return json_encode(array("result" => !ini_get("magic_quotes_gpc") ? 0 : 1, "param" => ini_get("magic_quotes_gpc")));
    }

    public static function php_short_open_tag_check() {
        return json_encode(array("result" => ini_get("short_open_tag") ? 0 : 1, "param" => ini_get("magic_quotes_gpc")));
    }

    public static function php_exe_path_check() {
        require '../Ini.php';
        $ini = new Ini();
        return self::file_paths_check(Ini::$path_php_exe);
    }

    public static function R_exe_path_check() {
        require '../Ini.php';
        $ini = new Ini();
        return self::file_paths_check(Ini::$path_r_exe);
    }

    public static function file_paths_check($path) {
        if (file_exists($path) && is_file($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function directory_paths_check($path) {
        if (file_exists($path) && is_dir($path))
            return true;
        else
            return false;
    }

    public static function directory_writable_check($path) {
        if (self::directory_paths_check($path) && is_writable($path))
            return true;
        else
            return false;
    }

    public static function media_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_internal_media))
            return json_encode(array("result" => 0, "param" => Ini::$path_internal_media));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_internal_media));
    }

    public static function socks_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_unix_sock_dir))
            return json_encode(array("result" => 0, "param" => Ini::$path_unix_sock_dir));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_unix_sock_dir));
    }

    public static function data_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_data))
            return json_encode(array("result" => 0, "param" => Ini::$path_data));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_data));
    }

    public static function files_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/js/lib/fileupload/php/files";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function thumbnails_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/js/lib/fileupload/php/thumbnails";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function cache_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function rscript_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e 1+1', $array, $return);
        return json_encode(array("result" => $return, "param" => Ini::$path_r_script));
    }

    public static function r_version_check() {
        $version = Setup::get_r_version();
        $elems = explode(".", $version);
        if ($elems[0] > 2)
            return json_encode(array("result" => 0, "param" => $version));
        if ($elems[0] == 2) {
            if ($elems[1] >= 15)
                return json_encode(array("result" => 0, "param" => $version));
        }
        return json_encode(array("result" => 1, "param" => $version));
    }

    public static function get_r_version() {
        require '../Ini.php';
        $ini = new Ini();
        $output = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e version', $output, $return);
        $version = str_replace(" ", "", str_replace("major", "", $output[6])) . "." . str_replace(" ", "", str_replace("minor", "", $output[7]));
        return $version;
    }

    public static function mysql_connection_check() {
        include'../SETTINGS.php';
        if (@mysql_connect($db_host . ":" . $db_port, $db_master_user, $db_master_password))
            return json_encode(array("result" => 0, "param" => "Host: <b>$db_host</b>, Port: <b>$db_port</b>, Login: <b>$db_master_user</b>"));
        else
            return json_encode(array("result" => 1, "param" => "Host: <b>$db_host</b>, Port: <b>$db_port</b>, Login: <b>$db_master_user</b>"));;
    }

    public static function mysql_select_db_check() {
        include'../SETTINGS.php';
        Setup::mysql_connection_check();
        if (@mysql_select_db($db_master_name))
            return json_encode(array("result" => 0, "param" => $db_master_name));
        else
            return json_encode(array("result" => 1, "param" => $db_master_name));
    }

    public static function r_package_check($package) {
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(' . $package . ')"', $array, $return);
        return ($return == 0);
    }

    public static function concerto_r_package_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(concerto)"', $array, $return);
        return json_encode(array("result" => $return, "param" => "concerto"));
    }

    public static function does_patch_apply($patch_version, $previous_version) {
        $patch_elems = explode(".", $patch_version);
        $previous_elems = explode(".", $previous_version);

        if ($previous_elems[0] < $patch_elems[0])
            return true;
        if ($previous_elems[0] == $patch_elems[0] && $previous_elems[1] < $patch_elems[1])
            return true;
        if ($previous_elems[0] == $patch_elems[0] && $previous_elems[1] == $patch_elems[1] && $previous_elems[2] < $patch_elems[2])
            return true;
        if ($previous_elems[0] == $patch_elems[0] && $previous_elems[1] == $patch_elems[1] && $previous_elems[2] == $patch_elems[2] && $previous_elems[3] < $patch_elems[3])
            return true;
        return false;
    }

    public static function update_db($simulate = false, $only_recalculate_hash = false, $only_create_db = false) {
        require '../Ini.php';
        $ini = new Ini();

        if ($only_create_db) {
            return Ini::create_db_structure();
        }

        if ($only_recalculate_hash) {
            foreach (User::get_all_db() as $db) {
                mysql_select_db($db);
                OModule::calculate_all_xml_hashes();
            }
            return json_encode(array("result" => 0));
        }

        $versions_to_update = array();

        $previous_version = Setting::get_setting("version");

        if ($previous_version == null)
            $previous_version = Ini::$version;

        $recalculate_hash = false;

        if (Setup::does_patch_apply("4.0.0.beta2", $previous_version)) {
            if ($simulate) {
                array_push($versions_to_update, "4.0.0.beta2");
            } else {

                //RDoc.HTML should be larger
                $sql = "ALTER TABLE `" . Ini::$db_master_name . "`.`RDoc` CHANGE `HTML` `HTML` mediumtext NOT NULL;";
                if (!mysql_query($sql))
                    return json_encode(array("result" => 1, "param" => $sql));

                //names should be unique
                $tables = array("Test", "Template", "Table", "QTIAssessmentItem");
                foreach (User::get_all_db() as $db) {
                    foreach ($tables as $table) {
                        $sql = sprintf("SELECT `name` , COUNT(  `name` ) AS `c`
                            FROM  `%s`.`%s` 
                            GROUP BY `name` 
                            HAVING `c`>1", $db, $table);
                        $z = mysql_query($sql);
                        if (!$z)
                            return json_encode(array("result" => 1, "param" => $sql));
                        while ($r = mysql_fetch_array($z)) {
                            $sql = sprintf("UPDATE `%s`.`%s` SET `name`=CONCAT(`name`,'_',`id`) WHERE `name`='%s'", $db, $table, mysql_real_escape_string($r[0]));
                            if (!mysql_query($sql))
                                return json_encode(array("result" => 1, "param" => $sql));
                        }

                        //change object name column to unique
                        $sql = sprintf("ALTER TABLE `%s`.`%s` CHANGE  `name`  `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL", $db, $table);
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));

                        $sql = sprintf("ALTER TABLE  `%s`.`%s` ADD UNIQUE ( `name` )", $db, $table);
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    //add TestSession new columns
                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='effect_show'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `effect_show` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='effect_hide'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `effect_hide` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='effect_show_options'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `effect_show_options` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='effect_hide_options'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `effect_hide_options` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_HTML'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_HTML` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_head'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_head` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_effect_show'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_effect_show` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_effect_hide'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_effect_hide` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_effect_show_options'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_effect_show_options` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_effect_hide_options'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_effect_hide_options` TEXT NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_Template_id'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_Template_id` bigint(20) NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_UserWorkspace_id'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `loader_UserWorkspace_id` bigint(20) NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }

                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='loader_Template_id'", $db, Test::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) > 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` DROP COLUMN `loader_Template_id`", $db, Test::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }
                }

                Setting::set_setting("version", "4.0.0.beta2");
                return json_encode(array("result" => 0, "param" => "4.0.0.beta2"));
            }
        }

        if (Setup::does_patch_apply("4.0.0.beta4", $previous_version)) {
            if ($simulate) {
                array_push($versions_to_update, "4.0.0.beta4");
            } else {

                foreach (User::get_all_db() as $db) {
                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='open'", $db, Test::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) > 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` CHANGE `open` `type` TINYINT(1) NOT NULL", $db, Test::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }
                }

                Setting::set_setting("version", "4.0.0.beta4");
                return json_encode(array("result" => 0, "param" => "4.0.0.beta4"));
            }
        }
        
        if (Setup::does_patch_apply("4.0.0.beta9", $previous_version)) {
            if ($simulate) {
                array_push($versions_to_update, "4.0.0.beta9");
            } else {

                foreach (User::get_all_db() as $db) {
                    $sql = sprintf("SHOW COLUMNS FROM `%s`.`%s` WHERE `Field`='resume_declined'", $db, TestSession::get_mysql_table());
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) == 0) {
                        $sql = sprintf("ALTER TABLE `%s`.`%s` ADD  `resume_declined` tinyint(1) NOT NULL", $db, TestSession::get_mysql_table());
                        if (!mysql_query($sql))
                            return json_encode(array("result" => 1, "param" => $sql));
                    }
                }

                Setting::set_setting("version", "4.0.0.beta9");
                return json_encode(array("result" => 0, "param" => "4.0.0.beta9"));
            }
        }

        if ($simulate)
            return json_encode(array("versions" => $versions_to_update, "recalculate_hash" => $recalculate_hash, "create_db" => Ini::create_db_structure(true)));
        return json_encode(array("result" => 2));
    }

}

echo Setup::$_POST['check']();
?>