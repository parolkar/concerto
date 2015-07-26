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

class DS_UserInstitutionType extends ODataSet
{
    public static $mysql_table_name = "DS_UserInstitutionType";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(416);
            case 2: return Language::string(417);
            case 3: return Language::string(418);
        }
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `DS_UserInstitutionType`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_UserInstitutionType` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_UserInstitutionType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'academic', '1', 1),
            (2, 'commercial', '2', 2),
            (3, 'other ( please specify )', '3', 3);
            ";
        return mysql_query($sql);
    }

}

?>