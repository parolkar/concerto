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

class CustomSectionVariable extends OTable
{
    public $CustomSection_id = 0;
    public $index = 0;
    public $name = "";
    public $description = "";
    public $type = 0;
    public static $mysql_table_name = "CustomSectionVariable";

    public function get_CustomSection()
    {
        return CustomSection::from_mysql_id($this->CustomSection_id);
    }

    public function to_XML()
    {
        $xml = new DOMDocument('1.0',"UTF-8");

        $element = $xml->createElement("CustomSectionVariable");
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES,"UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES,"UTF-8"));
        $element->appendChild($description);

        $index = $xml->createElement("index", htmlspecialchars($this->index, ENT_QUOTES,"UTF-8"));
        $element->appendChild($index);

        $type = $xml->createElement("type", htmlspecialchars($this->type, ENT_QUOTES,"UTF-8"));
        $element->appendChild($type);

        return $element;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `CustomSectionVariable`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `CustomSectionVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `CustomSection_id` bigint(20) NOT NULL,
            `name` text NOT NULL,
            `description` text NOT NULL,
            `type` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>