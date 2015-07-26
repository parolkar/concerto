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

class TestProtectedVariable extends OTable
{
    public $Test_id = 0;
    public $name = "";
    public static $mysql_table_name = "TestProtectedVariable";

    public function get_Test()
    {
        return Test::from_mysql_id($this->Test_id);
    }
    
    public function to_XML()
    {
        $xml = new DOMDocument('1.0',"UTF-8");

        $element = $xml->createElement("TestProtectedVariable");
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES,"UTF-8"));
        $element->appendChild($name);

        return $element;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TestProtectedVariable`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestProtectedVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `name` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ";
        return mysql_query($sql);
    }

}

?>