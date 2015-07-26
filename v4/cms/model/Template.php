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

class Template extends OModule {

    public $name = "";
    public $HTML = "";
    public $head = "";
    public $description = "";
    public $effect_show = "none";
    public $effect_show_options = "";
    public $effect_hide = "none";
    public $effect_hide_options = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Template";

    public function __construct($params = array()) {
        $this->name = Language::string(75);
        parent::__construct($params);
    }

    public function get_inserts() {
        $inserts = array();
        $html = $this->HTML;
        while (strpos($html, "{{") !== false) {
            $html = substr($html, strpos($html, "{{") + 2);
            if (strpos($html, "}}") !== false) {
                $name = substr($html, 0, strpos($html, "}}"));
                if ($name == "TIME_LEFT")
                    continue;
                if (!in_array($name, $inserts))
                    array_push($inserts, $name);
            }
        }
        return $inserts;
    }

    public static function get_insert_reference($name, $vals) {
        for ($i = 3; $i < 3 + $vals[1] * 2; $i = $i + 2) {
            if (isset($vals[$i])) {
                if ($vals[$i] == $name) {
                    if (isset($vals[$i + 1]))
                        return $vals[$i + 1];
                    else
                        break;
                }
            } else
                break;
        }
        return $name;
    }

    public static function get_return_reference($name, $vals) {
        for ($i = 3 + $vals[1] * 2; $i < 3 + $vals[1] * 2 + $vals[2] * 2; $i = $i + 2) {
            if (isset($vals[$i])) {
                if ($vals[$i] == $name) {
                    if (isset($vals[$i + 1]))
                        return $vals[$i + 1];
                    else
                        break;
                }
            } else
                break;
        }
        return $name;
    }

    public static function output_html($html, $vals, $outputs, $inserts = null) {
        if (trim($html) == "")
            return "";
        if ($inserts != null) {
            foreach ($inserts as $insert) {
                $ref = Template::get_insert_reference($insert, $vals);
                if ($ref != $insert) {
                    $html = str_replace("{{" . $insert . "}}", "{{" . $ref . "}}", $html);
                }
            }
        }
        $html = str_get_html($html);
        foreach ($outputs as $out) {
            $elems = $html->find("[name='" . $out["name"] . "']");
            $reference = null;
            foreach ($elems as $elem) {
                if ($reference == null) {
                    $reference = Template::get_return_reference($out["name"], $vals);
                }
                $elem->setAttribute("name", $reference);
            }
        }
        return $html->save();
    }

    public function get_outputs() {
        $names = array();
        $outputs = array();
        $html_string = $this->HTML;
        if (empty($html_string))
            $html_string = "<p></p>";
        $html = str_get_html($html_string);
        foreach ($html->find('input[type="text"], input[type="hidden"], input[type="password"], input[type="checkbox"], input[type="radio"]') as $element) {
            if (!in_array($element->name, $names)) {
                array_push($outputs, array("name" => $element->name, "type" => $element->type));
                array_push($names, $element->name);
            }
        }

        foreach ($html->find('textarea, select') as $element) {
            if (!in_array($element->name, $names)) {
                array_push($outputs, array("name" => $element->name, "type" => $element->tag));
                array_push($names, $element->name);
            }
        }
        return $outputs;
    }

    public function export() {
        $xml = new DOMDocument('1.0', "UTF-8");
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $export->appendChild($obj);

        return trim($xml->saveXML());
    }

    public function import_XML($xml) {
        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return json_encode(array("result" => -5));
        }

        $last_result = 0;
        $elements = $xpath->query("/export/Template");
        foreach ($elements as $element) {
            $this->xml_hash = $element->getAttribute("xml_hash");
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                    case "head": $this->head = $child->nodeValue;
                        break;
                    case "HTML": $this->HTML = $child->nodeValue;
                        break;
                    case "effect_show": $this->effect_show = $child->nodeValue;
                        break;
                    case "effect_show_options": $this->effect_show_options = $child->nodeValue;
                        break;
                    case "effect_hide": $this->effect_hide = $child->nodeValue;
                        break;
                    case "effect_hide_options": $this->effect_hide_options = $child->nodeValue;
                        break;
                }
            }
            $last_result = $this->mysql_save();
        }
        return $last_result;
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        $obj = static::from_mysql_id($lid);
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }
        return $lid;
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $element = $xml->createElement("Template");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $HTML = $xml->createElement("HTML", htmlspecialchars($this->HTML, ENT_QUOTES, "UTF-8"));
        $element->appendChild($HTML);

        $head = $xml->createElement("head", htmlspecialchars($this->head, ENT_QUOTES, "UTF-8"));
        $element->appendChild($head);

        $effect_show = $xml->createElement("effect_show", htmlspecialchars($this->effect_show, ENT_QUOTES, "UTF-8"));
        $element->appendChild($effect_show);

        $effect_hide = $xml->createElement("effect_hide", htmlspecialchars($this->effect_hide, ENT_QUOTES, "UTF-8"));
        $element->appendChild($effect_hide);

        $effect_show_options = $xml->createElement("effect_show_options", htmlspecialchars($this->effect_show_options, ENT_QUOTES, "UTF-8"));
        $element->appendChild($effect_show_options);

        $effect_hide_options = $xml->createElement("effect_hide_options", htmlspecialchars($this->effect_hide_options, ENT_QUOTES, "UTF-8"));
        $element->appendChild($effect_hide_options);

        return $element;
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`Template` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` varchar(50) NOT NULL,
            `head` text NOT NULL,
            `HTML` text NOT NULL,
            `effect_show` text NOT NULL,
            `effect_show_options` text NOT NULL,
            `effect_hide` text NOT NULL,
            `effect_hide_options` text NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        if (!mysql_query($sql))
            return false;
        $sql = sprintf("ALTER TABLE  `%s`.`Template` ADD UNIQUE ( `name` )", $db);
        return mysql_query($sql);
    }

    public static function strip_html($html, $scripts = true, $styles = true) {
        $obj = new simple_html_dom();
        $obj->load($html);
        if ($styles) {
            $elems = $obj->find("style");
            foreach ($elems as $elem) {
                $elem->outertext = "";
            }
            $elems = $obj->find("link");
            foreach ($elems as $elem) {
                $elem->outertext = "";
            }
        }
        if ($scripts) {
            $elems = $obj->find("script");
            foreach ($elems as $elem) {
                $elem->outertext = "";
            }
        }
        return $obj->save();
    }

    public function get_preview_HTML() {
        return Template::strip_html($this->HTML);
    }

}

?>