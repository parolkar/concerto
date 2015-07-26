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

class Setting extends OTable
{
    public static $mysql_table_name = "Setting";

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `Setting`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Setting` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` TEXT NOT NULL ,
            `value` TEXT NOT NULL
            ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;
            ";
        if (!mysql_query($sql)) return false;
        $sql = "
            INSERT INTO `Setting` (`name`, `value`) VALUES ('version','" . Ini::$version . "');
            ";
        return mysql_query($sql);
    }

    public static function get_setting($name)
    {
        $sql = sprintf("SELECT `value` FROM `Setting` WHERE `name`='%s'", $name);
        $z = @mysql_query($sql);
        if (!$z) return null;
        while ($r = mysql_fetch_array($z)) return $r[0];
        return null;
    }

    public static function set_setting($name, $value)
    {
        $sql = sprintf("UPDATE `Setting` SET `value`='%s' WHERE `name`='%s'", $value, $name);
        if (!mysql_query($sql)) return false;
        return true;
    }

}

?>
