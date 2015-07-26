<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class ItemButton extends OTable {

    public $Item_id;
    public $name;
    public $function;
    public static $mysql_table_name = "ItemButton";

    public static function delete_rout_variable($session_id, $variable_name) {
        $sql = sprintf("DELETE FROM `r_out` WHERE `Session_ID`='%d' AND `Variable`='%s'", $session_id, mysql_real_escape_string($variable_name));
        mysql_query($sql);
    }

    public static function insert_rout_variable($session_id, $variable_name, $variable_value) {
        $sql = sprintf("INSERT INTO `r_out` SET 
			`Session_ID`='%d', 
			`Variable`='%s',
			`Value`='%s'", $session_id, mysql_real_escape_string($variable_name), mysql_real_escape_string($variable_value));
        mysql_query($sql);
        return mysql_insert_id();
    }

}

?>