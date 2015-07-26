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

class CustomSection extends OModule {

    public $name = "";
    public $description = "";
    public $code = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "CustomSection";

    public function __construct($params = array()) {
        $this->name = Language::string(68);
        parent::__construct($params);
    }

    public function mysql_delete() {
        $this->delete_object_links(CustomSectionVariable::get_mysql_table());
        $this->mysql_delete_object();
    }

    public function get_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id));
    }

    public function get_parameter_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id, "type" => 0));
    }

    public function get_return_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id, "type" => 1));
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        if ($this->id != 0) {
            $this->delete_object_links(CustomSectionVariable::get_mysql_table());
            $i = 0;
        }

        if (array_key_exists("parameters", $post)) {
            foreach ($post["parameters"] as $param) {
                $p = json_decode($param);
                $var = new CustomSectionVariable();
                $var->description = $p->description;
                $var->name = $p->name;
                $var->index = $i;
                $var->type = 0;
                $var->CustomSection_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }
        if (array_key_exists("returns", $post)) {
            foreach ($post["returns"] as $ret) {
                $r = json_decode($ret);
                $var = new CustomSectionVariable();
                $var->description = $r->description;
                $var->name = $r->name;
                $var->index = $i;
                $var->type = 1;
                $var->CustomSection_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }

        $obj = static::from_mysql_id($lid);
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }

        return $lid;
    }

    public function export() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $export->appendChild($obj);

        return $xml->saveXML();
    }

    public function import_XML($xml) {
        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return -5;
        }

        $elements = $xpath->query("/export/CustomSection");
        foreach ($elements as $element) {
            $this->xml_hash = $element->getAttribute("xml_hash");
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                    case "code": $this->code = $child->nodeValue;
                        break;
                }
            }
            $lid = $this->mysql_save();

            $elements = $xpath->query("./CustomSectionVariables/CustomSectionVariable",$element);
            foreach ($elements as $element) {
                $obj = new CustomSectionVariable();
                $obj->CustomSection_id = $lid;
                $children = $element->childNodes;
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "name": $obj->name = $child->nodeValue;
                            break;
                        case "description": $obj->description = $child->nodeValue;
                            break;
                        case "index": $obj->index = $child->nodeValue;
                            break;
                        case "type": $obj->type = $child->nodeValue;
                            break;
                    }
                }
                $obj->mysql_save();
            }
        }

        return $lid;
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("CustomSection");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $code = $xml->createElement("code", htmlspecialchars($this->code, ENT_QUOTES, "UTF-8"));
        $element->appendChild($code);

        $csv = $xml->createElement("CustomSectionVariables");
        $element->appendChild($csv);

        $elems = $this->get_CustomSectionVariables();
        foreach ($elems as $elem) {
            $e = $elem->to_XML();
            $e = $xml->importNode($e, true);

            $csv->appendChild($e);
        }

        return $element;
    }

    public function get_description() {
        return Template::strip_html($this->description);
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `CustomSection`;"))
                return false;
        }
        $sql = "
            CREATE TABLE `CustomSection` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `description` text NOT NULL,
            `code` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>