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

class File extends OTable {

    public $temp_id = "";
    public $creation_time = -1;
    public $Module_id = 0;
    public $object_id = 0;
    public $temp_name = "";
    public $file_name = "";
    public $file_size = 0;
    public $md5 = "";
    public static $mysql_table_name = "File";

    function __construct($params=array()) {
        parent::__construct($params);

        if ($this->creation_time == -1)
            $this->creation_time = time();
    }

    public function get_formated_size() {
        $sizes = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        if ($this->file_size == 0) {
            return('n/a');
        } else {
            return (round($this->file_size / pow(1024, ($i = floor(log($this->file_size, 1024)))), 2) . $sizes[$i]);
        }
    }

    public function get_extenstion() {
        $pi = pathinfo($this->file_name);
        return $pi['extension'];
    }

    public function get_stored_as() {
        return md5($this->id) . "." . $this->get_extenstion();
    }

    public function get_source() {
        return Setting::get_setting(Setting::SETTING_MEDIA_DIR) . $this->get_stored_as();
    }

    public function get_external_source() {
        return Setting::get_setting(Setting::SETTING_EXT_MEDIA_DIR) . $this->get_stored_as();
    }

    public function mysql_delete() {
        if (file_exists($this->get_source()))
            unlink($this->get_source());
        $this->mysql_delete_object();
    }

    public function mysql_save() {
        self::mysql_delete_obsolete();

        $this->id = parent::mysql_save();

        if (file_exists($this->temp_name)) {
            move_uploaded_file($this->temp_name, $this->get_source());
            $this->md5 = $this->get_calculated_md5();
            $this->mysql_save();
        }

        return $this->id;
    }

    public function get_calculated_md5() {
        return self::calculate_md5($this->get_source());
    }

    public function get_calculated_file_size() {
        return self::calculate_file_size($this->get_source());
    }

    public static function calculate_md5($path) {
        $fh = fopen($path, "r");
        $content = fread($fh, filesize($path));
        fclose($fh);
        return md5($content);
    }

    public static function calculate_file_size($path) {
        return filesize($path);
    }

    public function to_DOMElement($xml_document) {
        $element = parent::to_DOMElement($xml_document);
        $element->setAttribute("source", $this->get_external_source());
        $element->setAttribute("stored_as", $this->get_stored_as());
        return $element;
    }

}

?>