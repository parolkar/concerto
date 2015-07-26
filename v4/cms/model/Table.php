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

    public function is_duplicate_table_name($name) {
        if ($this->id == 0 || $this->name != $name) {
            $sql = sprintf("SHOW TABLES LIKE '%s'", $name);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) > 0)
                return true;
        }
        return false;
    }

    public static function create_new_mysql_table($name) {
        $sql = sprintf("CREATE TABLE  `%s` (
            `id` bigint(20) NOT NULL auto_increment,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", mysql_real_escape_string($name));
        return mysql_query($sql);
    }

    public function rename_mysql_table($name) {
        $sql = sprintf("RENAME TABLE  `%s` TO  `%s` ;", mysql_real_escape_string($this->name), mysql_real_escape_string($name));
        return mysql_query($sql);
    }

    public function get_columns() {
        $result = array();
        $sql = sprintf("SHOW COLUMNS IN `%s`", $this->name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($result, TableColumn::from_mysql_result($r));
        }
        return $result;
    }

    public static function has_id($table_name) {
        $table = new Table();
        $table->name = $table_name;
        foreach ($table->get_columns() as $column) {
            if ($column->is_id())
                return true;
            else
                return false;
        }
        return false;
    }

    public function get_indexes() {
        return TableIndex::from_mysql_table($this->name);
    }

    public function mysql_save_from_post($post) {
        $is_new = $this->id == 0;

        if ($is_new) {
            if (!Table::create_new_mysql_table($post['name']))
                return json_encode(array("result" => -6, "message" => mysql_error()));
        } else {
            if ($this->name != $post['name']) {
                if (!$this->rename_mysql_table($post['name']))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        $lid = parent::mysql_save_from_post($post);
        $obj = Table::from_mysql_id($lid);

        if (array_key_exists("deleteData", $post)) {
            if ($post["deleteData"] == "*") {
                $sql = sprintf("DELETE FROM `%s`", mysql_real_escape_string($obj->name));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            } else {
                $rows = json_decode($post["deleteData"]);
                foreach ($rows as $row) {
                    $sql = sprintf("DELETE FROM `%s` WHERE id='%s'", mysql_real_escape_string($obj->name), mysql_real_escape_string($row));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                }
            }
        }

        if (array_key_exists("deleteIndexes", $post)) {
            $indexes = json_decode($post["deleteIndexes"]);
            foreach ($indexes as $index) {
                $sql = sprintf("DROP INDEX `%s` ON `%s`", mysql_real_escape_string($index), mysql_real_escape_string($obj->name));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        if (array_key_exists("deleteColumns", $post)) {
            $columns = json_decode($post["deleteColumns"]);
            foreach ($columns as $column) {
                $sql = sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", mysql_real_escape_string($obj->name), mysql_real_escape_string($column));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        if (array_key_exists("updateColumns", $post)) {
            $columns = json_decode($post["updateColumns"]);
            foreach ($columns as $column) {
                $col = TableColumn::from_ui($column);
                if ($column->id != "") {
                    $sql = sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s", mysql_real_escape_string($obj->name), mysql_real_escape_string($column->id), mysql_real_escape_string($column->name), $col->get_definition());
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                } else {
                    $sql = sprintf("ALTER TABLE `%s` ADD COLUMN `%s` %s", mysql_real_escape_string($obj->name), mysql_real_escape_string($column->name), $col->get_definition());
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                }
            }
        }

        if (array_key_exists("updateIndexes", $post)) {
            $indexes = json_decode($post["updateIndexes"]);
            foreach ($indexes as $index) {
                $ind = TableIndex::from_ui($index);
                if ($index->id != "") {
                    $sql = sprintf("ALTER TABLE `%s` DROP INDEX `%s`", mysql_real_escape_string($obj->name), mysql_real_escape_string($index->id));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                }
                $sql = sprintf("ALTER TABLE `%s` ADD %s", mysql_real_escape_string($obj->name), $ind->get_definition());
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        $columns = $this->get_columns();

        if (array_key_exists("updateData", $post)) {
            $rows = json_decode($post["updateData"], true);
            foreach ($rows as $row) {
                $set = "";
                foreach ($row as $k => $v) {
                    if ($k == "id")
                        continue;
                    if ($set != "")
                        $set.=",";
                    if ($v == "") {
                        $nullable = true;
                        foreach ($columns as $col) {
                            if ($col->name == $k) {
                                $nullable = $col->null;
                            }
                        }
                        if (!$nullable)
                            $set.="`" . mysql_real_escape_string($k) . "`=''";
                        else
                            $set.="`" . mysql_real_escape_string($k) . "`=NULL";
                    } else {
                        $set.="`" . mysql_real_escape_string($k) . "`='" . mysql_real_escape_string($v) . "'";
                    }
                }

                if ($row["id"] != null) {
                    $sql = sprintf("UPDATE `%s` SET %s WHERE `id`=%s", mysql_real_escape_string($obj->name), $set, mysql_real_escape_string($row["id"]));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                } else {
                    $sql = sprintf("INSERT INTO `%s` SET %s", mysql_real_escape_string($obj->name), $set);
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error() . " " . $sql));
                }
            }
        }

//hash
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }

        return $lid;
    }

    public function mysql_delete_Table() {
        $sql = "DROP TABLE IF EXISTS `" . $this->name . "`;";
        return mysql_query($sql);
    }

    public function import_from_mysql($table) {
        if (!Table::has_id($table)) {
            return -7;
        }

        if (!$this->mysql_delete_Table()) {
            return json_encode(array("result" => -6, "message" => mysql_error()));
        }

        $sql = sprintf("CREATE TABLE `%s` LIKE `%s`", $this->name, $table);
        if (!mysql_query($sql)) {
            return json_encode(array("result" => -6, "message" => mysql_error()));
        }
        $sql = sprintf("INSERT INTO `%s` SELECT * FROM `%s`", $this->name, $table);
        if (!mysql_query($sql)) {
            return json_encode(array("result" => -6, "message" => mysql_error()));
        }

        $this->rename_restricted_columns();

        return 0;
    }

    public function import_from_csv($path, $delimeter = ",", $enclosure = '"', $header = false, $id = true) {
        if (!$this->mysql_delete_Table()) {
            echo json_encode(array("result" => -6, mysql_error()));
        }

        if (!$this->create_new_mysql_table($this->name)) {
            echo json_encode(array("result" => -6, mysql_error()));
        }

        $row = 1;
        $column_names = array();

        if (!$id)
            array_push($column_names, "id");

        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, $delimeter, $enclosure)) !== FALSE) {
                if ($row == 1) {
                    for ($i = $id ? 1 : 2; $i <= count($data); $i++) {
                        $column_name = "c" . $i;
                        if ($header)
                            $column_name = Table::format_column_name($data[$i - 1]);
                        if (trim($column_name) == "")
                            continue;
                        array_push($column_names, $column_name);
                        $sql = sprintf("ALTER TABLE `%s` ADD `%s`  TEXT NOT NULL", $this->name, $column_name);
                        if (!mysql_query($sql))
                            return json_encode(array("result" => -6, "message" => mysql_error()));
                    }
                    if ($header) {
                        $row++;
                        continue;
                    }
                }

                $sql = sprintf("INSERT INTO `%s` SET ", $this->name);
                for ($i = 1; $i <= count($column_names); $i++) {
                    if ($i > 1)
                        $sql.=", ";
                    $sql.=sprintf("`%s`='%s'", $column_names[$i - 1], mysql_real_escape_string(utf8_encode($data[$i - 1])));
                }
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
                $row++;
            }
        }

        $this->rename_restricted_columns();

        return 0;
    }

    public function rename_restricted_columns() {
        $sql = sprintf("SHOW COLUMNS FROM `%s`", $this->name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            $tc = TableColumn::from_mysql_result($r);
            if (in_array($tc->name, TableColumn::$restricted_names)) {

                $i = 1;
                $sql = "";
                do {
                    $new_name = $tc->name . "_" . $i;
                    $sql = sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s", $this->name, $tc->name, $new_name, $tc->get_definition());
                    $i++;
                } while (!mysql_query($sql));
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
        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return json_encode(array("result" => -5));
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

            $dumps = $xpath->query("./TableStructureDump", $element);
            foreach ($dumps as $dump) {

                $sql = htmlspecialchars_decode(trim($dump->nodeValue), ENT_QUOTES);
                $lines = explode(";\n", $sql);

                foreach ($lines as $line) {
                    if (!mysql_query(htmlspecialchars_decode($line))) {
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                    }
                }
            }

            $dumps = $xpath->query("./TableDataDump", $element);
            foreach ($dumps as $dump) {

                $sql = htmlspecialchars_decode(trim($dump->nodeValue), ENT_QUOTES);
                $lines = explode(";\n", $sql);

                foreach ($lines as $line) {
                    if ($line == "")
                        continue;
                    if (!mysql_query(htmlspecialchars_decode($line))) {
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                    }
                }
            }

            $last_result = $this->mysql_save();
        }
        return $last_result;
    }

    public function dump_structure() {
        include Ini::$path_internal . "SETTINGS.php";
        $db_host = Ini::$db_host;
        $db_port = Ini::$db_port;
        $db_user = $db_master_user;
        $db_password = $db_master_password;
        $db_name = User::get_current_db();
        $db_table = $this->name;
        return `mysqldump --opt --host=$db_host --port=$db_port --user=$db_user --password="$db_password" --no-data --compact $db_name $db_table | grep -v '^\/\*![0-4][0-9]\{4\}.*\/;$'`;
    }

    public function dump_data() {
        include Ini::$path_internal . "SETTINGS.php";
        $db_host = Ini::$db_host;
        $db_port = Ini::$db_port;
        $db_user = $db_master_user;
        $db_password = $db_master_password;
        $db_name = User::get_current_db();
        $db_table = $this->name;

        $count = 0;
        $sql = sprintf("SELECT COUNT(*) FROM `%s`", $db_table);
        $z = mysql_query($sql);
        if ($r = mysql_fetch_row($z)) {
            $count = $r[0];
        }
        if ($count == 0)
            return "";
        else
            return `mysqldump --opt --host=$db_host --port=$db_port --user=$db_user --password="$db_password" --no-create-info --compact $db_name $db_table | grep -v '^\/\*![0-4][0-9]\{4\}.*\/;$'`;
    }

    public function calculate_xml_hash() {
        $xml = $this->to_XML(false);
        $xml->removeAttribute("id");
        $xml->removeAttribute("xml_hash");
        return md5($xml->ownerDocument->saveXML($xml));
    }

    public function to_XML($with_data = true) {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("Table");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $dump_structure = $xml->createElement("TableStructureDump", htmlspecialchars($this->dump_structure(), ENT_QUOTES, "UTF-8"));
        $element->appendChild($dump_structure);

        if ($with_data) {
            $dump_data = $xml->createElement("TableDataDump", htmlspecialchars($this->dump_data(), ENT_QUOTES, "UTF-8"));
            $element->appendChild($dump_data);
        }
        return $element;
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`Table` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` varchar(50) NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        if (!mysql_query($sql))
            return false;
        $sql = sprintf("ALTER TABLE  `%s`.`Table` ADD UNIQUE ( `name` )", $db);
        return mysql_query($sql);
    }

}

?>