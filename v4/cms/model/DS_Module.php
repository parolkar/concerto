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

class DS_Module extends ODataSet {

    public static $mysql_table_name = "DS_Module";

    public function get_name() {
        switch ($this->id) {
            case 1: return Language::string(167);
            case 2: return Language::string(85);
            case 3: return Language::string(89);
            case 6: return Language::string(88);
            case 8: return Language::string(458);
        }
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`DS_Module` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
            " , $db);
        if (!mysql_query($sql))
            return false;

        $sql = sprintf("
            INSERT INTO `%s`.`DS_Module` (`id`, `name`, `value`, `position`) VALUES
            (1, 'HTML templates', 'Template', 1),
            (2, 'tables', 'Table', 2),
            (3, 'users', 'User', 3),
            (6, 'tests', 'Test', 6),
            (8, 'QTI assessment item', 'QTIAssessmentItem', 8);
            ", $db);
        return mysql_query($sql);
    }

}

?>