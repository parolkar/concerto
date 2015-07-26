<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class OTable {

    public $id = 0;

    function __construct($params=array()) {
        $vars = get_object_vars($this);
        foreach ($vars as $k => $v) {
            if (isset($params[$k]))
                $this->$k = $params[$k];
        }
    }

    public static function from_mysql_result($r) {
        return new static($r);
    }

    public function mysql_save_from_post($post) {
        foreach ($post as $pk => $pv) {
            foreach (get_object_vars($this) as $ok => $ov) {
                if ($pk == $ok)
                    $this->$ok = $pv;
            }
        }

        return $this->mysql_save();
    }

    public function mysql_save() {
        $exclude = array("id");
        if ($this->id == 0)
            $sql = "INSERT INTO ";
        else
            $sql = "UPDATE ";
        $sql.=sprintf("`%s` SET ", self::get_mysql_table());

        $i = 0;
        foreach (get_object_vars($this) as $k => $v) {
            if (in_array($k, $exclude))
                continue;
            if ($i > 0)
                $sql.=", ";
            $sql.=sprintf("`%s`='%s' ", $k, mysql_real_escape_string($v));
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

    protected function clear_object_links($table, $field=null) {
        if ($field == null)
            $field = static::get_mysql_table() . "_id";
        $sql = sprintf("UPDATE `%s` SET `%s`=0 WHERE `%s`='%d'", $table, $field, $field, $this->id);
        mysql_query($sql);
    }

    protected function delete_object_links($table, $field=null) {
        if ($field == null)
            $field = static::get_mysql_table() . "_id";
        $sql = sprintf("DELETE FROM `%s` WHERE `%s`='%d'", $table, $field, $this->id);
        mysql_query($sql);
    }

    protected function mysql_delete_object() {
        $sql = sprintf("DELETE FROM `%s` WHERE `id`='%d'", static::get_mysql_table(), $this->id);
        mysql_query($sql);
    }

    public static function from_mysql_id($id) {
        $sql = sprintf("SELECT * FROM `%s` WHERE `id`='%d'", static::get_mysql_table(), $id);
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

    public static function from_property($pairs, $is_array=true) {
        $result = array();

        $where = "";
        foreach ($pairs as $k => $v) {
            if ($where != "")
                $where.=" AND ";
            $where.=sprintf("`%s`='%s'", $k, $v);
        }

        $sql = sprintf("SELECT * FROM `%s` WHERE %s", static::get_mysql_table(), $where);

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

    public static function mysql_delete_obsolete($link_field="object_id") {
        $sql = sprintf("SELECT * FROM `%s` WHERE `creation_time`<=%d AND `%s`=0", static::get_mysql_table(), time() - 60 * 60 * 24, $link_field);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            $obj = static::from_mysql_result($r);
            $obj->mysql_delete();
        }
    }

    public static function mysql_delete_temporary($temp_id, $link_field="object_id") {
        $collection = static::from_property(array(
                    "temp_id" => $temp_id,
                    $link_field => 0
                ));
        foreach ($collection as $obj)
            $obj->mysql_delete();
    }

    public function to_DOMElement($xml_document) {
        $element = $xml_document->createElement(static::get_mysql_table());
        foreach (get_object_vars($this) as $ok => $ov) {
            $element->setAttribute($ok, $ov);
        }
        return $element;
    }

    public static function escape($subject, $line_breaks=false) {
        $subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8", false);
        if ($line_breaks) {
            $subject = str_replace('\n', '', $subject);
            $subject = str_replace('\r', '', $subject);
        }
        return $subject;
    }

}

?>