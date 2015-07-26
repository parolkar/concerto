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

class OTable {

    public $id = 0;
    public $created = 0;
    public $updated = 0;
    public static $is_master_table = false;

    function __construct($params = array()) {
        $vars = get_object_vars($this);
        foreach ($vars as $k => $v) {
            if (isset($params[$k]))
                $this->$k = $params[$k];
        }
    }

    public function calculate_xml_hash() {
        $xml = $this->to_XML();
        $xml->removeAttribute("id");
        $xml->removeAttribute("xml_hash");
        return md5($xml->ownerDocument->saveXML($xml));
    }

    public static function find_xml_hash($hash) {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $sql = sprintf("SELECT * FROM %s`%s` ORDER BY `id`", $db, static::get_mysql_table());
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            $obj = static::from_mysql_id($r[0]);
            if ($obj->xml_hash == $hash)
                return $r[0];
        }
        return 0;
    }

    public function import($path) {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $content = file_get_contents($path);
        if (!$content)
            return json_encode(array("result" => -4));
        if (!@$xml->loadXML(gzuncompress($content)))
            return json_encode(array("result" => -4));

        return $this->import_XML($xml);
    }

    public static function convert_to_XML_document($node) {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $obj = $xml->importNode($node, true);
        $export->appendChild($obj);
        return $xml;
    }

    public static function from_mysql_result($r) {
        return new static($r);
    }

    public function mysql_save_from_post($post) {
        foreach ($post as $pk => $pv) {
            if (is_string($pv))
                $pv = trim($pv);
            foreach (get_object_vars($this) as $ok => $ov) {
                if ($pk == $ok)
                    $this->$ok = $pv;
            }
        }

        return $this->mysql_save();
    }

    public function mysql_save() {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $exclude = array("id", "updated");
        if ($this->id == 0) {
            $this->created = date("Y-m-d H:i:s");
            $sql = "INSERT INTO ";
        }
        else
            $sql = "UPDATE ";
        $sql.=sprintf("%s`%s` SET ", $db, self::get_mysql_table());

        $i = 0;
        foreach (get_object_vars($this) as $k => $v) {
            if (is_string($v))
                $v = mysql_real_escape_string($v);
            if (in_array($k, $exclude))
                continue;
            if ($i > 0)
                $sql.=", ";
            $sql.=sprintf("`%s`='%s' ", $k, $v);
            $i++;
        }

        if ($this->id != 0)
            $sql.=sprintf("WHERE `id`='%d'", $this->id);
        mysql_query($sql);
        if ($this->id != 0)
            return $this->id;
        else
            return mysql_insert_id();
    }

    protected function clear_object_links($table, $field = null) {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        if ($field == null)
            $field = static::get_mysql_table() . "_id";
        $sql = sprintf("UPDATE %s`%s` SET `%s`=0 WHERE `%s`='%d'", $db, $table, $field, $field, $this->id);
        mysql_query($sql);
    }

    protected function delete_object_links($table, $field = null) {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        if ($field == null)
            $field = static::get_mysql_table() . "_id";
        $sql = sprintf("DELETE FROM %s`%s` WHERE `%s`='%d'", $db, $table, $field, $this->id);
        mysql_query($sql);
    }

    protected function mysql_delete_object() {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $sql = sprintf("DELETE FROM %s`%s` WHERE `id`='%d'", $db, static::get_mysql_table(), $this->id);
        mysql_query($sql);
    }

    public static function from_mysql_id($id) {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $sql = sprintf("SELECT * FROM %s`%s` WHERE `id`='%d'", $db, static::get_mysql_table(), $id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return static::from_mysql_result($r);
        }
        return null;
    }

    public static function get_mysql_table() {
        return static::$mysql_table_name;
    }

    public function mysql_delete() {
        $this->mysql_delete_object();
    }

    public static function from_property($pairs, $is_array = true, $order = "`id` ASC") {
        if (static::$is_master_table)
            $db = "`" . Ini::$db_master_name . "`.";
        else
            $db = "";

        $result = array();

        $where = "";
        foreach ($pairs as $k => $v) {
            if ($where != "")
                $where.=" AND ";
            $where.=sprintf("`%s`='%s'", $k, $v);
        }

        if ($where != "")
            $where = "WHERE " . $where;

        $sql = sprintf("SELECT * FROM %s`%s` %s ORDER BY " . $order, $db, static::get_mysql_table(), $where);

        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            if (!$is_array)
                return static::from_mysql_result($r);
            else
                array_push($result, static::from_mysql_result($r));
        }
        if (!$is_array)
            return null;
        else
            return $result;
    }

    public function to_DOMElement($xml_document) {
        $element = $xml_document->createElement(static::get_mysql_table());
        foreach (get_object_vars($this) as $ok => $ov) {
            $element->setAttribute($ok, $ov);
        }
        return $element;
    }

    public static function escape($subject, $line_breaks = false) {
        $subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8", false);
        if ($line_breaks) {
            $subject = str_replace('\n', '', $subject);
            $subject = str_replace('\r', '', $subject);
        }
        return $subject;
    }

    public static function get_list() {
        return static::from_property(array());
    }

}

?>