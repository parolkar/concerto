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
    public $open = 0;
    public $loader_Template_id = 0;
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Test";

    public function __construct($params = array()) {
        $this->name = Language::string(76);
        parent::__construct($params);
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        $sections = array();
        if (isset($post['sections']))
            $sections = json_decode($post["sections"], true);

        if ($this->id != 0) {
            $this->delete_sections();
            $this->delete_templates();

            $this->delete_object_links(TestVariable::get_mysql_table());
            $i = 0;
        } else {
            $found = false;
            foreach ($sections as $section) {
                if ($section['counter'] == 1) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $start_section = new TestSection();
                $start_section->TestSectionType_id = DS_TestSectionType::START;
                $start_section->Test_id = $lid;
                $start_section->counter = 1;
                $start_section->mysql_save();
            }

            $found = false;
            foreach ($sections as $section) {
                if ($section['counter'] == 2) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $end_section = new TestSection();
                $end_section->TestSectionType_id = DS_TestSectionType::END;
                $end_section->Test_id = $lid;
                $end_section->counter = 2;
                $end_section->mysql_save();
            }
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

        foreach ($sections as $section) {
            $s = new TestSection();
            $s->counter = $section['counter'];
            $s->TestSectionType_id = $section['type'];
            $s->end = $section['end'];
            $s->Test_id = $lid;

            $s->parent_counter = $section['parent'];

            $slid = $s->mysql_save();

            $vals = $section['value'];

            foreach ($vals as $k => $v) {
                $index = substr($k, 1);
                $value = $v;

                $sv = new TestSectionValue();
                $sv->TestSection_id = $slid;
                $sv->index = $index;
                $sv->value = $value;
                $sv->mysql_save();
            }

            if ($s->TestSectionType_id == DS_TestSectionType::LOAD_HTML_TEMPLATE) {
                $ts = TestSection::from_mysql_id($slid);
                $vals = $ts->get_values();
                $template = Template::from_mysql_id($vals[0]);
                if ($template != null) {
                    $html = Template::output_html($template->HTML, $vals, $template->get_outputs(), $template->get_inserts());

                    $test_template = new TestTemplate();
                    $test_template->Test_id = $lid;
                    $test_template->TestSection_id = $slid;
                    $test_template->Template_id = $vals[0];
                    $test_template->HTML = $html;
                    $test_template->mysql_save();
                }
            }
        }

        $sql = sprintf("DELETE FROM `%s` WHERE `Test_id`=%d", TestProtectedVariable::get_mysql_table(), $lid);
        mysql_query($sql);
        if (array_key_exists("protected", $post)) {
            foreach ($post['protected'] as $var) {
                $var = json_decode($var);
                $s = new TestProtectedVariable();
                $s->name = $var->name;
                $s->Test_id = $lid;
                $slid = $s->mysql_save();
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
        foreach ($values as $val) {
            $v = json_decode($val);
            foreach ($params as $param) {
                if ($param->name == $v->name) {
                    array_push($result, $val);
                    break;
                }
            }
        }
        return $result;
    }

    public function get_loader_Template() {
        return Template::from_mysql_id($this->loader_Template_id);
    }

    public function mysql_delete() {
        $this->delete_sections();
        $this->delete_sessions();
        $this->delete_templates();
        $this->delete_object_links(TestProtectedVariable::get_mysql_table());
        $this->delete_object_links(TestVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function delete_sections() {
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $section) {
            $section->mysql_delete();
        }
    }

    public function delete_templates() {
        $templates = TestTemplate::from_property(array("Test_id" => $this->id));
        foreach ($templates as $template) {
            $template->mysql_delete();
        }
    }

    public function delete_sessions() {
        $sessions = TestSession::from_property(array("Test_id" => $this->id));
        foreach ($sessions as $session) {
            $session->remove();
        }
    }

    public function get_max_counter() {
        $max = 0;
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $section) {
            $max = max(array($max, $section->counter));
        }
        return $max;
    }

    public function get_starting_counter() {
        return $this->get_TestSection()->counter;
    }

    public function get_TestSection($counter = null) {
        $section = null;
        if ($counter == null)
            $section = TestSection::from_property(array("Test_id" => $this->id), false);
        else
            $section = TestSection::from_property(array("Test_id" => $this->id, "counter" => $counter), false);
        return $section;
    }

    public function get_TestSections_RFunction_declaration() {
        $code = "";
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $s) {
            $code.=$s->get_RFunction();
        }
        return $code;
    }

    public function export($xml = null, $sub_test = false, $main_test = null) {
        if ($xml == null) {
            $xml = new DOMDocument('1.0', 'UTF-8');

            $export = $xml->createElement("export");
            $export->setAttribute("version", Ini::$version);
            $xml->appendChild($export);
            $xpath = new DOMXPath($xml);
        } else {
            $xpath = new DOMXPath($xml);
            $export = $xpath->query("/export");
            $export = $export->item(0);
        }

        //append subobjects of test
        $tests_ids = array();
        array_push($tests_ids, $this->id);
        $templates_ids = array();
        $custom_sections_ids = array();
        $tables_ids = array();
        $qtiai_ids = array();

        $loader = $this->get_loader_Template();
        if ($loader != null) {
            if (!in_array($loader->id, $templates_ids)) {
                $template = $loader;
                if ($template != null) {
                    $present_templates = $xpath->query("/export/Template");
                    $exists = false;
                    foreach ($present_templates as $obj) {
                        if ($template->xml_hash == $obj->getAttribute("xml_hash")) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {

                        $element = $template->to_XML();
                        $obj = $xml->importNode($element, true);
                        $export->appendChild($obj);
                        array_push($templates_ids, $loader->id);
                    }
                }
            }
        }

        $sql = sprintf("SELECT 
            `TestSection`.`id`,`TestSectionValue`.`value`,`TestSection`.`TestSectionType_id` 
            FROM `TestSection` 
            LEFT JOIN `TestSectionValue` ON `TestSectionValue`.`TestSection_id`=`TestSection`.`id`
            WHERE 
            (`TestSection`.`TestSectionType_id`=13 AND `TestSectionValue`.`index`=0 OR
            `TestSection`.`TestSectionType_id`=2 AND `TestSectionValue`.`index`=0 OR
            `TestSection`.`TestSectionType_id`=9 AND `TestSectionValue`.`index`=0 OR
            `TestSection`.`TestSectionType_id`=11 AND `TestSectionValue`.`index`=0 OR
            `TestSection`.`TestSectionType_id`=8 AND `TestSectionValue`.`index`=3 OR
            `TestSection`.`TestSectionType_id`=5 AND `TestSectionValue`.`index`=5) AND `TestSection`.`Test_id`=%d ORDER BY `TestSection`.`TestSectionType_id` ASC", $this->id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            switch ($r[2]) {
                //templates
                case 2: {
                        if (!in_array($r[1], $templates_ids)) {
                            $template = Template::from_mysql_id($r[1]);
                            if ($template != null) {
                                $present_templates = $xpath->query("/export/Template");
                                $exists = false;
                                foreach ($present_templates as $obj) {
                                    if ($template->xml_hash == $obj->getAttribute("xml_hash")) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if ($exists)
                                    break;

                                $element = $template->to_XML();
                                $obj = $xml->importNode($element, true);
                                $export->appendChild($obj);
                                array_push($templates_ids, $r[1]);
                            }
                        }
                        break;
                    }
                //QTIAssessmentItem
                case 13: {
                        if (!in_array($r[1], $qtiai_ids)) {
                            $qtiai = QTIAssessmentItem::from_mysql_id($r[1]);
                            if ($qtiai != null) {
                                $present_qtiai = $xpath->query("/export/QTIAssessmentItem");
                                $exists = false;
                                foreach ($present_qtiai as $obj) {
                                    if ($qtiai->xml_hash == $obj->getAttribute("xml_hash")) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if ($exists)
                                    break;

                                $element = $qtiai->to_XML();
                                $obj = $xml->importNode($element, true);
                                $export->appendChild($obj);
                                array_push($qtiai_ids, $r[1]);
                            }
                        }
                        break;
                    }
                //custom sections
                case 9: {
                        if (!in_array($r[1], $custom_sections_ids)) {
                            $custom_section = CustomSection::from_mysql_id($r[1]);
                            if ($custom_section != null) {
                                $present_custom_sections = $xpath->query("/export/CustomSection");
                                $exists = false;
                                foreach ($present_custom_sections as $obj) {
                                    if ($custom_section->xml_hash == $obj->getAttribute("xml_hash")) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if ($exists)
                                    break;

                                $element = $custom_section->to_XML();
                                $obj = $xml->importNode($element, true);
                                $export->appendChild($obj);
                                array_push($custom_sections_ids, $r[1]);
                            }
                        }
                        break;
                    }
                //tables
                case 8: {
                        if (!in_array($r[1], $tables_ids)) {
                            $table = Table::from_mysql_id($r[1]);
                            if ($table != null) {
                                $present_tables = $xpath->query("/export/Table");
                                $exists = false;
                                foreach ($present_tables as $obj) {
                                    if ($table->xml_hash == $obj->getAttribute("xml_hash")) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if ($exists)
                                    break;

                                $element = $table->to_XML();
                                $obj = $xml->importNode($element, true);
                                $export->appendChild($obj);
                                array_push($tables_ids, $r[1]);
                            }
                        }
                        break;
                    }
                case 5: {
                        $sql2 = sprintf("SELECT * FROM `TestSectionValue` WHERE `TestSection_id`=%d AND `index`=2 AND `value`=0", $r[0]);
                        $z2 = mysql_query($sql2);
                        while ($r2 = mysql_fetch_array($z2)) {
                            if (!in_array($r[1], $tables_ids)) {
                                $table = Table::from_mysql_id($r[1]);
                                if ($table != null) {
                                    $present_tables = $xpath->query("/export/Table");
                                    $exists = false;
                                    foreach ($present_tables as $obj) {
                                        if ($table->xml_hash == $obj->getAttribute("xml_hash")) {
                                            $exists = true;
                                            break;
                                        }
                                    }
                                    if ($exists)
                                        break;

                                    $element = $table->to_XML();
                                    $obj = $xml->importNode($element, true);
                                    $export->appendChild($obj);
                                    array_push($tables_ids, $r[1]);
                                }
                            }
                        }
                        break;
                    }
                //tests
                case 11: {
                        if (!in_array($r[1], $tests_ids)) {
                            $test = Test::from_mysql_id($r[1]);
                            if ($test != null) {
                                if ($main_test != null && $main_test->id == $test->id)
                                    break;
                                $present_tests = $xpath->query("/export/Test");
                                $exists = false;
                                foreach ($present_tests as $obj) {
                                    if ($test->xml_hash == $obj->getAttribute("xml_hash")) {
                                        $exists = true;
                                        break;
                                    }
                                }
                                if ($exists)
                                    break;

                                $xml = $test->export($xml, true, ($main_test != null ? $main_test : $this));
                                $element = $test->to_XML();
                                $obj = $xml->importNode($element, true);
                                $export->appendChild($obj);
                                array_push($tests_ids, $r[1]);
                            }
                        }
                        break;
                    }
            }
        }

        if (!$sub_test) {
            $element = $this->to_XML();
            $obj = $xml->importNode($element, true);
            $export->appendChild($obj);
        }

        return ($sub_test ? $xml : $xml->saveXML());
    }

    public function import_XML($xml, $compare = null) {
        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return -5;
        }

        if ($compare == null) {
            $compare = array(
                "Template" => array(),
                "Table" => array(),
                "CustomSection" => array(),
                "Test" => array(),
                "QTIAssessmentItem" => array()
            );
        }

        //link templates
        $logged_user = User::get_logged_user();
        $elements = $xpath->query("/export/Template");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["Template"][$id] = Template::find_xml_hash($hash);
            if ($compare["Template"][$id] == 0) {
                $obj = new Template();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(Template::convert_to_XML_document($element));
                $compare["Template"][$id] = $lid;
            }
        }

        //link QTI assessment items
        $logged_user = User::get_logged_user();
        $elements = $xpath->query("/export/QTIAssessmentItem");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["QTIAssessmentItem"][$id] = QTIAssessmentItem::find_xml_hash($hash);
            if ($compare["QTIAssessmentItem"][$id] == 0) {
                $obj = new QTIAssessmentItem();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(QTIAssessmentItem::convert_to_XML_document($element));
                $compare["QTIAssessmentItem"][$id] = $lid;
            }
        }

        //link tables
        $elements = $xpath->query("/export/Table");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["Table"][$id] = Table::find_xml_hash($hash);
            if ($compare["Table"][$id] == 0) {
                $obj = new Table();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(Table::convert_to_XML_document($element));
                $compare["Table"][$id] = $lid;
            }
        }

        //link custom sections
        $elements = $xpath->query("/export/CustomSection");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["CustomSection"][$id] = CustomSection::find_xml_hash($hash);
            if ($compare["CustomSection"][$id] == 0) {
                $obj = new CustomSection();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(CustomSection::convert_to_XML_document($element));
                $compare["CustomSection"][$id] = $lid;
            }
        }

        //link tests
        $elements = $xpath->query("/export/Test");
        for ($i = 0; $i < $elements->length - 1; $i++) {
            $element = $elements->item($i);
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            if (!isset($compare["Test"][$id]))
                $compare["Test"][$id] = 0;
            if ($compare["Test"][$id] == 0) {
                $obj = new Test();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(CustomSection::convert_to_XML_document($element), $compare);
                $compare["Test"][$id] = $lid;
            }
        }

        $elements = $xpath->query("/export/Test");
        $element = $elements->item($elements->length - 1);
        $this->xml_hash = $element->getAttribute("xml_hash");
        $element_id = $element->getAttribute("id");
        if (isset($compare["Test"][$element_id]) && $compare["Test"][$element_id] != 0)
            return $compare["Test"][$element_id];
        $children = $element->childNodes;
        foreach ($children as $child) {
            switch ($child->nodeName) {
                case "name": $this->name = $child->nodeValue;
                    break;
                case "description": $this->description = $child->nodeValue;
                    break;
                case "open": $this->open = $child->nodeValue;
                    break;
                case "loader_Template_id": $this->loader_Template_id = ($child->nodeValue == 0 ? 0 : $compare["Template"][$child->nodeValue]);
                    break;
            }
        }

        $this->id = $this->mysql_save();

        $post = array();
        $post["sections"] = array();

        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestSections/TestSection");
        foreach ($elements as $element) {
            $test_section = array();
            $test_section["value"] = array();

            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "end": $test_section["end"] = $child->nodeValue;
                        break;
                    case "counter": $test_section["counter"] = $child->nodeValue;
                        break;
                    case "TestSectionType_id": $test_section["type"] = $child->nodeValue;
                        break;
                    case "parent_counter": $test_section["parent"] = $child->nodeValue;
                        break;
                    case "TestSectionValues": {
                            $ts_child_list = $child->childNodes;
                            foreach ($ts_child_list as $ts_child) {
                                $index = -1;
                                $value = "";

                                $tsv_vars = $ts_child->childNodes;
                                foreach ($tsv_vars as $tsv_child) {
                                    switch ($tsv_child->nodeName) {
                                        case "index": $index = $tsv_child->nodeValue;
                                            break;
                                        case "value": $value = $tsv_child->nodeValue;
                                            break;
                                    }
                                }
                                if ($index != -1)
                                    $test_section["value"]["v" . $index] = $value;
                            }
                            break;
                        }
                }
            }

            switch ($test_section["type"]) {
                case 2: {
                        if ($test_section["value"]["v0"] == 0)
                            break;
                        $value = 0;
                        if (isset($compare["Template"][$test_section["value"]["v0"]]))
                            $value = $compare["Template"][$test_section["value"]["v0"]];
                        $test_section["value"]["v0"] = $value;
                        break;
                    }
                case 13: {
                        if ($test_section["value"]["v0"] == 0)
                            break;
                        $value = 0;
                        if (isset($compare["QTIAssessmentItem"][$test_section["value"]["v0"]]))
                            $value = $compare["QTIAssessmentItem"][$test_section["value"]["v0"]];
                        $test_section["value"]["v0"] = $value;
                        break;
                    }
                case 11: {
                        if ($test_section["value"]["v0"] == 0)
                            break;
                        $value = 0;
                        if (isset($compare["Test"][$test_section["value"]["v0"]]))
                            $value = $compare["Test"][$test_section["value"]["v0"]];
                        $test_section["value"]["v0"] = $value;
                        break;
                    }
                case 9: {
                        if ($test_section["value"]["v0"] == 0)
                            break;
                        $value = 0;
                        if (isset($compare["CustomSection"][$test_section["value"]["v0"]]))
                            $value = $compare["CustomSection"][$test_section["value"]["v0"]];
                        $test_section["value"]["v0"] = $value;
                        break;
                    }
                case 8: {
                        if ($test_section["value"]["v3"] == 0)
                            break;
                        $value = 0;
                        if (isset($compare["Table"][$test_section["value"]["v3"]]))
                            $value = $compare["Table"][$test_section["value"]["v3"]];
                        $test_section["value"]["v3"] = $value;
                        break;
                    }
                case 5: {
                        if ($test_section["value"]["v5"] == 0)
                            break;
                        if ($test_section["value"]["v2"] == 0) {
                            $value = 0;
                            if (isset($compare["Table"][$test_section["value"]["v5"]]))
                                $value = $compare["Table"][$test_section["value"]["v5"]];
                            $test_section["value"]["v5"] = $value;
                        }
                        break;
                    }
            }

            if (count($test_section["value"]) == 0)
                $test_section['value'] = "{}";

            array_push($post["sections"], $test_section);
        }

        $post['sections'] = json_encode($post['sections']);

        $post["parameters"] = array();
        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestVariables/TestVariable");
        foreach ($elements as $element) {
            $tv = array();
            $tv["Test_id"] = $element_id;
            $children = $element->childNodes;
            $correct = true;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "index": $tv["index"] = $child->nodeValue;
                        break;
                    case "name": $tv["name"] = $child->nodeValue;
                        break;
                    case "description": $tv["description"] = $child->nodeValue;
                        break;
                    case "type": {
                            $tv["type"] = $child->nodeValue;
                            if ($tv["type"] != 0)
                                $correct = false;
                            break;
                        }
                }
            }
            if ($correct) {
                $tv = json_encode($tv);
                array_push($post['parameters'], $tv);
            }
        }

        $post["returns"] = array();
        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestVariables/TestVariable");
        foreach ($elements as $element) {
            $tv = array();
            $tv["Test_id"] = $element_id;
            $children = $element->childNodes;
            $correct = true;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "index": $tv["index"] = $child->nodeValue;
                        break;
                    case "name": $tv["name"] = $child->nodeValue;
                        break;
                    case "description": $tv["description"] = $child->nodeValue;
                        break;
                    case "type": {
                            $tv["type"] = $child->nodeValue;
                            if ($tv["type"] != 1)
                                $correct = false;
                            break;
                        }
                }
            }
            if ($correct) {
                $tv = json_encode($tv);
                array_push($post['returns'], $tv);
            }
        }

        $post["protected"] = array();
        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestProtectedVariables/TestProtectedVariable");
        foreach ($elements as $element) {
            $tpv = array();
            $tpv["Test_id"] = $element_id;
            $children = $element->childNodes;
            $correct = true;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $tpv["name"] = $child->nodeValue;
                        break;
                }
            }
            $tpv = json_encode($tpv);
            array_push($post['protected'], $tpv);
        }

        return $this->mysql_save_from_post($post);
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

        $open = $xml->createElement("open", htmlspecialchars($this->open, ENT_QUOTES, "UTF-8"));
        $element->appendChild($open);

        $loader_Template_id = $xml->createElement("loader_Template_id", htmlspecialchars($this->loader_Template_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($loader_Template_id);

        $sections = $xml->createElement("TestSections");
        $element->appendChild($sections);

        $ts = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($ts as $s) {
            $elem = $s->to_XML();
            $elem = $xml->importNode($elem, true);
            $sections->appendChild($elem);
        }

        $test_protected_variables = $xml->createElement("TestProtectedVariables");
        $element->appendChild($test_protected_variables);

        $tpv = $this->get_TestProtectedVariables();
        foreach ($tpv as $var) {
            $elem = $var->to_XML();
            $elem = $xml->importNode($elem, true);
            $test_protected_variables->appendChild($elem);
        }

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

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `Test`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Test` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `open` tinyint(1) NOT NULL,
            `session_count` bigint(20) NOT NULL,
            `loader_Template_id` bigint(20) NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public static function get_list_columns() {
        $cols = parent::get_list_columns();

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

    public function get_TestProtectedVariables_name() {
        $result = array();

        $tpv = TestProtectedVariable::from_property(array("Test_id" => $this->id));
        foreach ($tpv as $v) {
            array_push($result, $v->name);
        }
        return $result;
    }

    public function get_TestProtectedVariables() {
        $tpv = TestProtectedVariable::from_property(array("Test_id" => $this->id));
        return $tpv;
    }

    public function get_description() {
        return Template::strip_html($this->description);
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