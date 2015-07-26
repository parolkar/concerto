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

class TestSessionLog extends OTable {

    const TEST_SESSION_LOG_TYPE_R = 0;
    const TEST_SESSION_LOG_TYPE_JS = 1;

    public $UserWorkspace_id = 0;
    public $TestSession_id = 0;
    public $Test_id = 0;
    public $hash = "";
    public $Template_id = 0;
    public $Template_UserWorkspace_id = 0;
    public $status = 0;
    public $message = "";
    public $type = 0;
    public $IP = "";
    public $browser = "";
    public static $mysql_table_name = "TestSessionLog";
    
    public function get_UserWorkspace(){
        return UserWorkspace::from_mysql_id($this->UserWorkspace_id);
    }
    
    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`TestSessionLog` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
            `IP` text NOT NULL,
            `browser` text NOT NULL,
            `message` text NOT NULL,
            `UserWorkspace_id` bigint(20) NOT NULL,
            `TestSession_id` bigint(20) NOT NULL,
            `hash` text NOT NULL,
            `Test_id` bigint(20) NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `Template_UserWorkspace_id` bigint(20) NOT NULL,
            `status` tinyint(4) NOT NULL,
            `type` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ", $db);
        return mysql_query($sql);
    }

}

?>