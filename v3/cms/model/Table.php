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

class Table extends OModule {

    public $name = "";
    public $description = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Table";

    public function __construct($params = array()) {
        $this->name = Language::string(74);
        parent::__construct($params);
    }

    public function get_table_name() {
        return self::get_table_prefix() . "_" . $this->id;
    }

    public static function get_table_prefix() {
        return "c3tbl";
    }

    public function mysql_delete() {
        $this->mysql_delete_Table();

        parent::mysql_delete();
    }

    private static $auto_increment_row_comparer_field = "";

    private static function auto_increment_row_comparer($a, $b) {
        $a = json_decode($a);
        $b = json_decode($b);

        $field = self::$auto_increment_row_comparer_field;
        if ($a->$field >= $b->$field && ($a->$field == null || $a->$field == ""))
            return 1;
        else
            return -1;
    }

    public function mysql_save_from_post($post) {
        $simulation = false;
        if (array_key_exists("save_simulation", $post) && $post['save_simulation'] == 1)
            $simulation = true;

        $lid = parent::mysql_save_from_post($post);

        if (!$simulation) {
            $this->mysql_delete_TableColumn();
        }

        if (array_key_exists("cols", $post)) {
            if ($simulation)
                $table_name = "`" . self::get_table_prefix() . "_temp_" . $lid . "`";
            else
                $table_name = "`" . self::get_table_prefix() . "_" . $lid . "`";

//table
            if (!$simulation) {
                $sql = "DROP TABLE IF EXISTS " . $table_name . ";";
                mysql_query($sql);
            }

            $sql = "CREATE " . ($simulation ? "TEMPORARY" : "") . " TABLE  " . $table_name . " (";
            $i = 0;
            $timestamp = false;
            foreach ($post['cols'] as $col_json) {
                $col = json_decode($col_json);
                if ($i > 0)
                    $sql.=",";
                if (!$timestamp && $col->type == "timestamp") {
                    $timestamp = true;
                    $col->defaultValue = "CURRENT_TIMESTAMP";
                    $col->attributes = "on update current_timestamp";
                    $post['cols'][$i] = json_encode($col);
                }
                $sql.="`" . $col->name . "` " . TableColumn::get_column_definition($col->type, $col->lengthValues, $col->attributes, $col->nullable, 0, $col->defaultValue);
                $i++;
            }
            $sql.=") ENGINE = INNODB DEFAULT CHARSET=utf8;";
            if (!mysql_query($sql)) {
                $message = mysql_error();
                return json_encode(array("result" => -6, "message" => $message));
            }

//indexes
            if (array_key_exists("indexes", $post)) {
                foreach ($post['indexes'] as $index_json) {
                    $index = json_decode($index_json);
                    $columns = explode(",", $index->columns);
                    $cols = "";
                    foreach ($columns as $column) {
                        if ($cols != "")
                            $cols.=",";
                        $cols.="`" . $column . "`";
                    }

                    $sql = sprintf("ALTER TABLE %s ADD %s(%s)", $table_name, $index->type, $cols);
                    if (!mysql_query($sql)) {
                        $message = mysql_error();
                        return json_encode(array("result" => -6, "message" => $message));
                    }
                }
            }

//auto increment
            $auto_increment = false;
            foreach ($post['cols'] as $col_json) {
                $col = json_decode($col_json);

                if ($col->auto_increment == 1) {
                    $auto_increment = true;
                    self::$auto_increment_row_comparer_field = $col->name;
                    $sql = sprintf("ALTER TABLE %s CHANGE `%s` `%s` %s", $table_name, $col->name, $col->name, TableColumn::get_column_definition($col->type, $col->lengthValues, $col->attributes, $col->nullable, $col->auto_increment, $col->defaultValue));
                    if (!mysql_query($sql)) {
                        $message = mysql_error();
                        return json_encode(array("result" => -6, "message" => $message));
                    }
                }
            }

//TableColumn
            $sql = "START TRANSACTION";
            mysql_query($sql);

            $sql = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`type`, `length`,`attributes`, `null`, `auto_increment`, `default_value`) VALUES ", TableColumn::get_mysql_table());
            $i = 0;
            foreach ($post['cols'] as $col_json) {
                $col = json_decode($col_json);

                if ($i > 0)
                    $sql.=",";
                $sql.="(";
                $sql.= ($i + 1) . ",'" . mysql_real_escape_string($col->name) . "'," . $lid . ",'" . mysql_real_escape_string($col->type) . "', '" . mysql_real_escape_string($col->lengthValues) . "', '" . mysql_real_escape_string($col->attributes) . "', " . mysql_real_escape_string($col->nullable) . "," . mysql_real_escape_string($col->auto_increment) . ",'" . mysql_real_escape_string($col->defaultValue) . "'";
                $sql.=")";
                $i++;
            }
            if (!mysql_query($sql)) {
                $message = mysql_error();
                $sql = "ROLLBACK";
                mysql_query($sql);
                return json_encode(array("result" => -6, "message" => $message));
            }

//TableIndex
            if (array_key_exists("indexes", $post)) {
                foreach ($post['indexes'] as $index_json) {
                    $index = json_decode($index_json);
                    $columns = explode(",", $index->columns);

                    $ti = new TableIndex();
                    $ti->Table_id = $lid;
                    $ti->type = $index->type;
                    $ti_lid = $ti->mysql_save();

                    $i = 0;
                    foreach ($columns as $col) {
                        $tc = TableColumn::from_property(array("Table_id" => $lid, "name" => $col), false);
                        $tic = new TableIndexColumn();
                        $tic->index = $i;
                        $tic->TableIndex_id = $ti_lid;
                        if ($tc != null)
                            $tic->TableColumn_id = $tc->id;
                        $tic->mysql_save();
                        $i++;
                    }
                }
            }

//data
            if (array_key_exists("rows", $post) && $post['rows'] != null && is_array($post['rows'])) {

                if ($auto_increment) {
                    usort($post['rows'], "self::auto_increment_row_comparer");
                }

                $sql = "INSERT INTO " . $table_name . " (";
                $i = 0;
                foreach ($post['cols'] as $col_json) {
                    if ($i > 0)
                        $sql.=",";
                    $col = json_decode($col_json);
                    $sql.="`" . $col->name . "`";
                    $i++;
                }
                $sql.=") VALUES ";

                for ($a = 0; $a < count($post['rows']); $a++) {
                    $row = json_decode($post['rows'][$a]);
                    if ($a > 0)
                        $sql.=",";
                    $sql.="(";
                    $i = 0;
                    foreach ($post['cols'] as $col_json) {
                        $col = json_decode($col_json);
                        $col_name = $col->name;
                        if ($row->$col_name == "" && $col->nullable == 1)
                            $row->$col_name = null;
                        if ($i > 0)
                            $sql.=",";
                        if ($row->$col_name !== null)
                            $sql.="'" . mysql_real_escape_string($row->$col_name) . "'";
                        else
                            $sql.="NULL";
                        $i++;
                    }
                    $sql.=")";
                }
                if (!mysql_query($sql)) {
                    $message = mysql_error();
                    $sql = "ROLLBACK";
                    mysql_query($sql);
                    return json_encode(array("result" => -6, "message" => $message));
                }
            }
        }
        if (!$simulation) {
            $sql = "COMMIT";
            mysql_query($sql);
        } else {
            $sql = "ROLLBACK";
            mysql_query($sql);
        }

//hash
        $obj = static::from_mysql_id($lid);
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }

        return $lid;
    }

    public function has_table() {
        $table_name = self::get_table_prefix() . "_" . $this->id;
        $sql = "SHOW TABLES LIKE '" . $table_name . "'";
        $z = mysql_query($sql);
        if (mysql_num_rows($z) > 0)
            return true;
        return false;
    }

    public function mysql_delete_Table() {
        $this->mysql_delete_TableColumn();

        $table_name = "`" . $this->get_table_name() . "`";
        $sql = "DROP TABLE IF EXISTS " . $table_name . ";";
        mysql_query($sql);
    }

    public function mysql_delete_TableColumn() {
        $this->delete_object_links(TableColumn::get_mysql_table());
        $this->mysql_delete_TableIndex();
    }

    public function mysql_delete_TableIndex() {
        foreach ($this->get_TableIndexes() as $index) {
            $index->mysql_delete();
        }
    }

    public function get_TableColumns() {
        return TableColumn::from_property(array("Table_id" => $this->id));
    }

    public function get_TableIndexes() {
        return TableIndex::from_property(array("Table_id" => $this->id));
    }

    public function import_from_mysql($table) {
        $this->mysql_delete_Table();

        $columns = array();
        $sql = sprintf("SHOW COLUMNS FROM `%s`", $table);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($columns, $r['Field']);
        }

        $sql = sprintf("SELECT * FROM `%s`", $table);
        $z = mysql_query($sql);
        $i = 0;
        while ($r = mysql_fetch_array($z)) {
            if ($i == 0) {
                $sql = "CREATE TABLE  " . $this->get_table_name() . " (";
                $j = 0;
                foreach ($columns as $col) {
                    if ($j > 0)
                        $sql.=",";
                    $sql.="`" . Table::format_column_name($col) . "`  TEXT NOT NULL";

                    $sql2 = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`type`) VALUES (%d,'%s',%d,%s)", TableColumn::get_mysql_table(), ($j + 1), Table::format_column_name($col), $this->id, "text");
                    mysql_query($sql2);

                    $j++;
                }
                $sql.=") ENGINE = INNODB DEFAULT CHARSET=utf8;";
                mysql_query($sql);
            }
            $cols = "";
            $vals = "";
            $j = 0;
            foreach ($columns as $col) {
                if ($j > 0) {
                    $cols.=",";
                    $vals.=",";
                }
                $cols.="`" . Table::format_column_name($col) . "`";
                $vals.="'" . $r[$col] . "'";
                $j++;
            }
            $sql = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->get_table_name(), $cols, $vals);
            mysql_query($sql);
            $i++;
        }
        return 0;
    }

    public function import_from_csv($path, $delimeter = ",", $enclosure = '"', $header = false) {
        $this->mysql_delete_Table();

        $row = 1;
        $column_names = array();

        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, $delimeter, $enclosure)) !== FALSE) {
                if ($row == 1) {
                    $sql = "CREATE TABLE  " . $this->get_table_name() . " (";
                    for ($i = 1; $i <= count($data); $i++) {
                        $column_name = "c" . $i;
                        if ($header)
                            $column_name = Table::format_column_name($data[$i - 1]);
                        if (trim($column_name) == "")
                            continue;
                        array_push($column_names, $column_name);
                        if ($i > 1)
                            $sql.=",";
                        $sql.="`" . $column_name . "`  TEXT NOT NULL";

                        $sql2 = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`type`) VALUES (%d,'%s',%d,'%s')", TableColumn::get_mysql_table(), $i, $column_name, $this->id, "text");
                        if (!mysql_query($sql2))
                            return -4;
                    }
                    $sql.=") ENGINE = INNODB DEFAULT CHARSET=utf8;";
                    if (!mysql_query($sql))
                        return -4;
                    if ($header) {
                        $row++;
                        continue;
                    }
                }

                $sql = sprintf("INSERT INTO `%s` SET ", $this->get_table_name());
                for ($i = 1; $i <= count($column_names); $i++) {
                    if ($i > 1)
                        $sql.=", ";
                    $sql.=sprintf("`%s`='%s'", $column_names[$i - 1], mysql_real_escape_string($data[$i - 1]));
                }
                if (!mysql_query($sql))
                    return -4;
                $row++;
            }
        }
        return 0;
    }

    public static function format_column_name($name) {
        $name = preg_replace("/[^A-Z^a-z^0-9^_]/i", "", $name);
        $name = preg_replace("/^([^A-Z^a-z]{1,})*/i", "", $name);
        $name = preg_replace("/([^A-Z^a-z^0-9]{1,})$/i", "", $name);
        return $name;
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

        $last_result = 0;
        $elements = $xpath->query("/export/Table");
        foreach ($elements as $element) {
            $this->xml_hash = $element->getAttribute("xml_hash");
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                }
            }

            $post['cols'] = array();
            $elements_tc = $xpath->query("./TableColumns/TableColumn", $element);
            foreach ($elements_tc as $element_tc) {
                $children = $element_tc->childNodes;
                $col = array("oid" => 0);
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "name": $col["name"] = $child->nodeValue;
                            break;
                        case "type": $col["type"] = $child->nodeValue;
                            break;
                        case "length": $col["lengthValues"] = $child->nodeValue;
                            break;
                        case "default_value": $col["defaultValue"] = $child->nodeValue;
                            break;
                        case "attributes": $col["attributes"] = $child->nodeValue;
                            break;
                        case "null": $col["nullable"] = $child->nodeValue;
                            break;
                        case "auto_increment": $col["auto_increment"] = $child->nodeValue;
                            break;
                    }
                }
                array_push($post['cols'], json_encode($col));
            }

            $post['indexes'] = array();
            $elements_ti = $xpath->query("./TableIndexes/TableIndex", $element);
            foreach ($elements_ti as $element_ti) {
                $children = $element_ti->childNodes;
                $index = array("oid" => 0);
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "type": $index["type"] = $child->nodeValue;
                            break;
                        case "columns": $index["columns"] = $child->nodeValue;
                            break;
                    }
                }
                array_push($post['indexes'], json_encode($index));
            }

            $post['rows'] = array();
            $elements_r = $xpath->query("./rows/row", $element);
            foreach ($elements_r as $element_r) {
                $children = $element_r->childNodes;
                $row = array();
                foreach ($children as $child) {
                    $row[$child->nodeName] = $child->nodeValue;
                }
                array_push($post['rows'], json_encode($row));
            }

            $last_result = $this->mysql_save_from_post($post);
        }
        return $last_result;
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("Table");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $columns = $xml->createElement("TableColumns");
        $element->appendChild($columns);

        $cols = $this->get_TableColumns();
        foreach ($cols as $col) {
            $elem = $col->to_XML();
            $elem = $xml->importNode($elem, true);
            $columns->appendChild($elem);
        }

        $indexes = $xml->createElement("TableIndexes");
        $element->appendChild($indexes);

        $indx = $this->get_TableIndexes();
        foreach ($indx as $index) {
            $elem = $index->to_XML();
            $elem = $xml->importNode($elem, true);
            $indexes->appendChild($elem);
        }

        $rows = $xml->createElement("rows");
        $element->appendChild($rows);

        if ($this->has_table()) {
            $sql = sprintf("SELECT * FROM `%s`", $this->get_table_name());
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z)) {
                $row = $xml->createElement("row");

                foreach ($cols as $col) {
                    $cell = $xml->createElement($col->name, htmlspecialchars($r[$col->name], ENT_QUOTES, "UTF-8"));
                    $row->appendChild($cell);
                }

                $rows->appendChild($row);
            }
        }
        return $element;
    }

    public function get_description() {
        return Template::strip_html($this->description);
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `Table`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Table` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>