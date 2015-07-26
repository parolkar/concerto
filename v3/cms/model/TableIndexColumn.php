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

class TableIndexColumn extends OTable {
    
    public $index = 0;
    public $TableIndex_id = 0;
    public $TableColumn_id = 0;
    
    public static $mysql_table_name = "TableIndexColumn";

    public function get_TableIndex() {
        return TableIndex::from_mysql_id($this->TableIndex_id);
    }
    
    public function get_TableColumn(){
        return TableColumn::from_mysql_id($this->TableColumn_id);
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("TableIndexColumn");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($id);

        $index = $xml->createElement("index", htmlspecialchars($this->index, ENT_QUOTES, "UTF-8"));
        $element->appendChild($index);

        $table_index_id = $xml->createElement("TableIndex_id", htmlspecialchars($this->TableIndex_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($table_index_id);
        
        $table_column_id = $xml->createElement("TableColumn_id", htmlspecialchars($this->TableColumn_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($table_column_id);

        return $element;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TableIndexColumn`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TableIndexColumn` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int NOT NULL,
            `TableIndex_id` bigint(20) NOT NULL,
            `TableColumn_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>