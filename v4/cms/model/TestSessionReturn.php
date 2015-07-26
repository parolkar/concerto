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

class TestSessionReturn extends OTable {

    public $TestSession_id = 0;
    public $name = "";
    public $value = "";
    public static $mysql_table_name = "TestSessionReturn";

    public function get_TestSession() {
        return TestSession::from_mysql_id($this->TestSession_id);
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`TestSessionReturn` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
            `TestSession_id` bigint(20) NOT NULL,
            `name` varchar(50) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `TestSession_id` (`TestSession_id`,`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ", $db);
        return mysql_query($sql);
    }

}

?>