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

class TestTemplate extends OTable {

    public $Test_id = 0;
    public $TestSection_id = 0;
    public $Template_id = 0;
    public $HTML = "";
    public $effect_show = "none";
    public $effect_hide = "none";
    public $effect_show_options = "";
    public $effect_hide_options = "";
    
    public static $mysql_table_name = "TestTemplate";

    public function to_XML() {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $element = $xml->createElement("TestTemplate");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($id);

        $Test_id = $xml->createElement("Test_id", htmlspecialchars($this->Test_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($Test_id);

        $TestSection_id = $xml->createElement("TestSection_id", htmlspecialchars($this->TestSection_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($TestSection_id);

        $Template_id = $xml->createElement("Template_id", htmlspecialchars($this->Template_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($Template_id);

        $html = $xml->createElement("HTML", htmlspecialchars($this->HTML, ENT_QUOTES, "UTF-8"));
        $element->appendChild($html);
        
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

    public static function repopulate_table() {
        $sql = "DELETE FROM `TestTemplate`";
        mysql_query($sql);
        $sql = "SELECT * FROM `TestSection`";
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            set_time_limit(0);
            if ($r['TestSectionType_id'] == DS_TestSectionType::LOAD_HTML_TEMPLATE) {
                $ts = TestSection::from_mysql_id($r['id']);
                $vals = $ts->get_values();
                $template = Template::from_mysql_id($vals[0]);
                if ($template != null) {
                    $html = Template::output_html($template->HTML, $vals, $template->get_outputs(), $template->get_inserts());

                    $test_template = new TestTemplate();
                    $test_template->Test_id = $r['Test_id'];
                    $test_template->TestSection_id = $r['id'];
                    $test_template->Template_id = $vals[0];
                    $test_template->HTML = $html;
                    $test_template->effect_show = $template->effect_show;
                    $test_template->effect_hide = $template->effect_hide;
                    $test_template->effect_show_options = $template->effect_show_options;
                    $test_template->effect_hide_options = $template->effect_hide_options;
                    $test_template->mysql_save();
                }
            }
        }
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestTemplate`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestTemplate` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `TestSection_id` bigint(20) NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `HTML` text NOT NULL,
            `effect_show` text NOT NULL,
            `effect_hide` text NOT NULL,
            `effect_show_options` text NOT NULL,
            `effect_hide_options` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>