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

class TestSectionValue extends OTable {

    public $TestSection_id = 0;
    public $index = 0;
    public $value = "";
    public static $mysql_table_name = "TestSectionValue";

    public function get_TestSection() {
        return TestSection::from_mysql_id($this->TestSection_id);
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $tsv = $xml->createElement("TestSectionValue");
        $xml->appendChild($tsv);

        $index = $xml->createElement("index", $this->index);
        $tsv->appendChild($index);

        $value = $xml->createElement("value", htmlspecialchars($this->value, ENT_QUOTES, "UTF-8"));
        $tsv->appendChild($value);

        return $tsv;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSectionValue`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSectionValue` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSection_id` bigint(20) NOT NULL,
            `index` int(11) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }
}

?>