<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University

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

class UserWorkspace extends OTable {

    public $owner_id = 0;
    public $name = "";
    public $db_login = "";
    public $db_password = "";
    public $db_name = "";
    public $main = 0;
    public static $mysql_table_name = "UserWorkspace";
    public static $is_master_table = true;

    public function get_owner() {
        return User::from_mysql_id($this->owner_id);
    }

    public function get_shares() {
        return UserShare::from_property(array("UserWorkspace_id" => $this->id));
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`UserWorkspace` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `owner_id` bigint(20) NOT NULL,
            `db_login` text NOT NULL,
            `db_password` text NOT NULL,
            `db_name` text NOT NULL,
            `name` text NOT NULL,
            `main` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        return mysql_query($sql);
    }

    public function mysql_save() {
        $is_new = $this->id == 0;
        $lid = parent::mysql_save();
        $this->id = $lid;
        if ($is_new)
            $this->create_db_user();
        $this->grant_privileges_db_user($this->owner_id);
    }

    public function remove_shares() {
        foreach (UserShare::from_property(array("UserWorkspace_id" => $this->id)) as $share) {
            $share->mysql_delete();
        }
    }

    public function get_formatted_name() {
        return $this->id . ". " . $this->name;
    }

    public function mysql_delete() {
        $this->remove_shares();

        $sql = sprintf("DROP DATABASE `%s`", $this->db_name);
        mysql_query($sql);

        $sql = sprintf("DROP USER '%s'@'localhost'", $this->db_login);
        mysql_query($sql);

        parent::mysql_delete();
    }

    public function grant_privileges_db_user($user_id) {
        $wid = User::from_mysql_id($user_id)->get_main_UserWorkspace()->id;
        $user = Ini::$db_users_name_prefix . $wid;
        $db_name = Ini::$db_users_db_name_prefix . $this->id;

        $sql = sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost'", $db_name, $user);
        mysql_query($sql);
    }
    
    public function revoke_privileges_db_user($user_id){
        $wid = User::from_mysql_id($user_id)->get_main_UserWorkspace()->id;
        $user = Ini::$db_users_name_prefix . $wid;
        $db_name = Ini::$db_users_db_name_prefix . $this->id;

        $sql = sprintf("REVOKE ALL PRIVILEGES ON `%s`.* FROM '%s'@'localhost'", $db_name, $user);
        mysql_query($sql);
    }

    public function create_db_user() {
        $user = Ini::$db_users_name_prefix . $this->id;
        $password = User::generate_password();
        $db_name = Ini::$db_users_db_name_prefix . $this->id;
        $sql = sprintf("CREATE USER '%s'@'localhost' IDENTIFIED BY '%s';", $user, $password);
        mysql_query($sql);
        $this->db_login = $user;
        $this->db_password = $password;
        $this->db_name = $db_name;
        parent::mysql_save();

        $sql = sprintf("CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", $db_name);
        mysql_query($sql);

        $sql = sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost'", $db_name, $user);
        mysql_query($sql);

        Ini::create_db_structure();
    }

}

?>