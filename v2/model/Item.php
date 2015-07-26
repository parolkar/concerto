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

class Item extends OModule {

    public $name = "";
    public $timer = 0;
    public $hash = "";
    public $HTML = "";
    public $default_Button_id = 0;
    public static $mysql_table_name = "Item";

    public function get_ItemButtons() {
        $buttons = array();
        $sql = sprintf("SELECT * FROM `%s` WHERE `Item_id`='%d'", ItemButton::get_mysql_table(), $this->id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
            array_push($buttons, ItemButton::from_mysql_result($r));
        return $buttons;
    }

    public function calculate_hash() {
        return md5("c1o2n3c4e5r6t7o8r9o0c1k2s" . $this->id);
    }

    public function get_Button($name) {
        return ItemButton::from_property(array("name" => $name, "Item_id" => $this->id), false);
        return null;
    }

    public function get_default_Button() {
        return ItemButton::from_mysql_id($this->default_Button_id);
    }

    public function mysql_delete() {
        $this->mysql_delete_object();
        $this->delete_object_links(ItemButton::get_mysql_table());
    }

    public static function from_hash($hash) {
        return Item::from_property(array("hash" => $hash), false);
    }

    public static function from_name($name) {
        return Item::from_property(array("name" => $name), false);
    }

    public function mysql_save_from_post($post) {
        foreach ($post as $pk => $pv) {
            foreach (get_object_vars($this) as $ok => $ov) {
                if ($pk == $ok)
                    $this->$ok = $pv;
            }
        }

        $lid = $this->mysql_save();
        $this->id = $lid;

        if (isset($post['buttons'])) {
            foreach ($this->get_ItemButtons() as $button) {
                if (!in_array($button->name, $post['buttons']))
                    $button->mysql_delete();
            }

            for ($i = 0; $i < count($post['buttons']); $i++) {
                $name = $post['buttons'][$i];
                $function = $post['functions'][$i];

                if ($name == "")
                    continue;
                $button = $this->get_Button($name);
                if ($button == null) {
                    $button = new ItemButton();
                    $button->id = 0;
                }

                $button->function = $function;
                $button->Item_id = $lid;
                $button->name = $name;
                $button->mysql_save();
            }
        }

        $default_btn = ItemButton::from_property(array("name" => $post["default_btn_name"], "Item_id" => $post["oid"]), false);
        if ($default_btn != null)
            $this->default_Button_id = $default_btn->id;
        else
            $this->default_Button_id = 0;
        $this->hash = $this->calculate_hash();
        $this->mysql_save();

        return $lid;
    }

}

?>