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

class RDocFunction extends OTable {

    public static $mysql_table_name = "RDocFunction";
    public static $is_master_table = true;
    
    public $RDocLibrary_id = 0;
    public $name = "";
    public $RDoc_id = 0;
    
    public function get_RDoc(){
        return RDoc::from_mysql_id($this->RDoc_id);
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`RDocFunction` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `RDocLibrary_id` BIGINT(20) NOT NULL ,
            `name` TEXT NOT NULL,
            `RDoc_id` BIGINT(20) NOT NULL
            ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;
            ", $db);
        return mysql_query($sql);
    }

}

?>
