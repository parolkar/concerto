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

class Test extends OModule {

    public $name = "unnamed test";
    public $description = "";
    public $session_count = 0;
    public $type = 0;
    public $code = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Test";

    public function __construct($params = array()) {
        $this->name = Language::string(76);
        parent::__construct($params);
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        if ($this->id != 0) {
            $this->delete_object_links(TestVariable::get_mysql_table());
            $i = 0;
        }

        $i = 0;
        if (array_key_exists("parameters", $post)) {
            foreach ($post["parameters"] as $param) {
                $p = json_decode($param);
                $var = new TestVariable();
                $var->description = $p->description;
                $var->name = $p->name;
                $var->index = $i;
                $var->type = 0;
                $var->Test_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }
        if (array_key_exists("returns", $post)) {
            foreach ($post["returns"] as $ret) {
                $r = json_decode($ret);
                $var = new TestVariable();
                $var->description = $r->description;
                $var->name = $r->name;
                $var->index = $i;
                $var->type = 1;
                $var->Test_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }

        if (array_key_exists("deleteLogs", $post)) {
            if ($post["deleteLogs"] == "*") {
                $sql = sprintf("DELETE FROM `%s` WHERE `Test_id`=%s", TestSessionLog::get_mysql_table(), $lid);
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            } else {
                $logs = json_decode($post["deleteLogs"]);
                foreach ($logs as $log) {
                    $log = TestSessionLog::from_mysql_id($log);
                    if ($log != null) {
                        $log->mysql_delete();
                    }
                }
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

    public function verified_input_values($values) {
        $result = array();
        $params = $this->get_parameter_TestVariables();
        if (is_array($values)) {
            foreach ($values as $vk => $vv) {
                foreach ($params as $param) {
                    if ($param->name == $vk) {
                        $result[$vk] = $vv;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    public function mysql_delete() {
        $this->delete_sessions();
        $this->delete_object_links(TestVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function delete_sessions() {
        $sessions = TestSession::from_property(array("Test_id" => $this->id));
        foreach ($sessions as $session) {
            $session->remove();
        }
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
        $elements = $xpath->query("/export/Test");
        foreach ($elements as $element) {
            $element_id = $element->getAttribute("id");
            $this->xml_hash = $element->getAttribute("xml_hash");
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                    case "type": $this->type = $child->nodeValue;
                        break;
                    case "code": $this->code = $child->nodeValue;
                        break;
                }
            }
            $last_result = $this->mysql_save();

            $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestVariables/TestVariable");
            foreach ($elements as $element) {
                $tv = new TestVariable();
                $tv->Test_id = $last_result;
                $children = $element->childNodes;
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "index": $tv->index = $child->nodeValue;
                            break;
                        case "name": $tv->name = $child->nodeValue;
                            break;
                        case "description": $tv->description = $child->nodeValue;
                            break;
                        case "type": $tv->type = $child->nodeValue;
                            break;
                    }
                }
                $tv->mysql_save();
            }
        }

        return $last_result;
    }

    public function to_XML() {
        $xml = new DOMDocument();

        $element = $xml->createElement("Test");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $type = $xml->createElement("type", htmlspecialchars($this->type, ENT_QUOTES, "UTF-8"));
        $element->appendChild($type);

        $code = $xml->createElement("code", htmlspecialchars($this->code, ENT_QUOTES, "UTF-8"));
        $element->appendChild($code);

        $test_variables = $xml->createElement("TestVariables");
        $element->appendChild($test_variables);

        $tv = $this->get_TestVariables();
        foreach ($tv as $var) {
            $elem = $var->to_XML();
            $elem = $xml->importNode($elem, true);
            $test_variables->appendChild($elem);
        }

        return $element;
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`Test` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` varchar(50) NOT NULL,
            `type` tinyint(1) NOT NULL,
            `session_count` bigint(20) NOT NULL,
            `code` longtext NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        if (!mysql_query($sql))
            return false;
        $sql = sprintf("ALTER TABLE  `%s`.`Test` ADD UNIQUE ( `name` )", $db);
        return mysql_query($sql);
    }

    public static function get_list_columns() {
        $cols = parent::get_list_columns();

        array_push($cols, array(
            "name" => Language::string(488),
            "property" => "format_open",
            "searchable" => true,
            "sortable" => true,
            "type" => "string",
            "groupable" => true,
            "width" => 120,
            "show" => true
        ));

        array_push($cols, array(
            "name" => Language::string(335),
            "property" => "session_count",
            "searchable" => true,
            "sortable" => true,
            "type" => "number",
            "groupable" => false,
            "width" => 120,
            "show" => true
        ));

        return $cols;
    }

    public function get_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id));
    }

    public function get_parameter_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id, "type" => 0));
    }

    public function get_return_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id, "type" => 1));
    }

}

?>