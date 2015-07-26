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

class TableColumn extends OTable {

    public $index = 0;
    public $name = "";
    public $Table_id = 0;
    public $type = "";
    public $length = "";
    public $default_value = "";
    public $attributes = "";
    public $null = 0;
    public $auto_increment = 0;
    public static $mysql_table_name = "TableColumn";

    public function get_Table() {
        return Table::from_mysql_id($this->Table_id);
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("TableColumn");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($id);

        $index = $xml->createElement("index", htmlspecialchars($this->index, ENT_QUOTES, "UTF-8"));
        $element->appendChild($index);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);
        
        $table_id = $xml->createElement("Table_id", htmlspecialchars($this->Table_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($table_id);

        $type = $xml->createElement("type", htmlspecialchars($this->type, ENT_QUOTES, "UTF-8"));
        $element->appendChild($type);

        $length = $xml->createElement("length", htmlspecialchars($this->length, ENT_QUOTES, "UTF-8"));
        $element->appendChild($length);

        $default_value = $xml->createElement("default_value", htmlspecialchars($this->default_value, ENT_QUOTES, "UTF-8"));
        $element->appendChild($default_value);

        $attributes = $xml->createElement("attributes", htmlspecialchars($this->attributes, ENT_QUOTES, "UTF-8"));
        $element->appendChild($attributes);

        $null = $xml->createElement("null", htmlspecialchars($this->null, ENT_QUOTES, "UTF-8"));
        $element->appendChild($null);

        $auto_increment = $xml->createElement("auto_increment", htmlspecialchars($this->auto_increment, ENT_QUOTES, "UTF-8"));
        $element->appendChild($auto_increment);
        
        return $element;
    }

    public static function get_column_definition($type, $length, $attributes, $null, $auto_increment, $default_value) {
        $result = mysql_real_escape_string($type);
        if ($type == "HTML")
            $result = "text";
        if (trim($length) != "") {
            $result.="(" . $length . ") ";
        }
        if (trim($attributes) != "")
            $result.=" " . mysql_real_escape_string($attributes);
        if ($null == 1)
            $result.=" NULL";
        else
            $result.=" NOT NULL";
        if ($auto_increment == 1)
            $result.=" AUTO_INCREMENT";
        if (trim($default_value) != "")
            $result.=" DEFAULT " . mysql_real_escape_string($default_value);
        return $result;
    }

    public static function validate_columns_name() {
        $sql = sprintf("SELECT * FROM `TableColumn`");
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            set_time_limit(0);
            $table = Table::from_mysql_id($r['Table_id']);
            if ($table == null)
                continue;
            if (!$table->has_table()) {
                $table->mysql_delete();
                continue;
            }

            $table_name = $table->get_table_name();

            $type = TableColumn::get_column_definition($r['type'], $r['length'], $r['attributes'], $r['null'], $r['auto_increment'], $r['default_value']);

            $old_name = $r['name'];
            $new_name = Table::format_column_name($old_name);
            if ($old_name != $new_name) {
                $sql2 = sprintf("ALTER TABLE `%s` CHANGE `%s` `%s` %s;", $table_name, $old_name, $new_name, $type);
                $i = 1;
                while (!mysql_query($sql2)) {
                    $new_name = "col" . $i;
                    $sql2 = sprintf("ALTER TABLE `%s` CHANGE `%s` `%s` %s;", $table_name, $old_name, $new_name, $type);
                    $i++;
                }

                $sql2 = sprintf("UPDATE `TableColumn` SET `name`='%s' WHERE `id`='%d'", $new_name, $r['id']);
                if (!mysql_query($sql2)) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TableColumn`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TableColumn` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `name` text NOT NULL,
            `Table_id` bigint(20) NOT NULL,
            `type` text NOT NULL,
            `length` text NOT NULL,
            `default_value` text NOT NULL,
            `attributes` text NOT NULL,
            `null` tinyint(1) NOT NULL,
            `auto_increment` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>