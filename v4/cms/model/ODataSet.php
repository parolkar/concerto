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

class ODataSet extends OTable {

    public $name = "";
    public $value = "";
    public $position = 0;
    public static $is_master_table = true;

    public static function get_all($sort = "`position` ASC") {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $res = array();
        $sql = sprintf("SELECT * FROM %s`%s` ORDER BY %s", $db, static::get_mysql_table(), $sort);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($res, static::from_mysql_result($r));
        }
        return $res;
    }

    public static function from_value($value) {
        return self::from_property(array("value" => $value), false);
    }

}

?>