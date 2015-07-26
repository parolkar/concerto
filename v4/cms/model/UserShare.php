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

class UserShare extends OTable {

    public $UserWorkspace_id = 0;
    public $invitee_id = 0;
    public static $mysql_table_name = "UserShare";
    public static $is_master_table = true;

    public function get_UserWorkspace() {
        return UserWorkspace::from_mysql_id($this->UserWorkspace_id);
    }

    public function get_invitee() {
        return User::from_mysql_id($this->invitee_id);
    }

    public function mysql_save() {
        $prev = UserShare::from_mysql_id($this->id);
        if ($this->id != 0 && $prev != null) {
            if ($this->UserWorkspace_id != $prev->UserWorkspace_id ||
                    $this->invitee_id != $prev->invitee_id) {
                $shares = UserShare::from_property(array("UserWorkspace_id" => $prev->UserWorkspace_id, "invitee_id" => $prev->invitee_id));
                $ws = $prev->get_UserWorkspace();
                if ($ws != null && count($shares) <= 1) {
                    $ws->revoke_privileges_db_user($prev->invitee_id);
                }
            }
        }

        $ws = UserWorkspace::from_mysql_id($this->UserWorkspace_id);
        if ($ws != null) {
            $ws->grant_privileges_db_user($this->invitee_id);
        }

        parent::mysql_save();
    }

    public function mysql_delete() {
        $ws = $this->get_UserWorkspace();

        $shares = UserShare::from_property(array("UserWorkspace_id" => $this->UserWorkspace_id, "invitee_id" => $this->invitee_id));

        if ($ws != null && count($shares) <= 1) {
            $ws->revoke_privileges_db_user($this->invitee_id);
        }

        parent::mysql_delete();
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`UserShare` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `UserWorkspace_id` bigint(20) NOT NULL,
            `invitee_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        return mysql_query($sql);
    }

}

?>