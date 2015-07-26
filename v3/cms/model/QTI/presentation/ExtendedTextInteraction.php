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

class ExtendedTextInteraction extends ABlockStringInteraction {

    //attributes
    public $maxStrings = null;
    public $expectedLines = null;
    public static $name = "extendedTextInteraction";
    public static $possible_attributes = array(
        "maxStrings",
        "expectedLines"
    );
    public static $required_attributes = array();
    public static $possible_children = array();
    public static $required_children = array();

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_HTML_code() {
        $code = "";
        if ($this->prompt != null)
            $code.=$this->prompt->get_HTML_code();

        $count = 1;
        $expectedLines = "";
        $value = ($this->placeholderText != null ? sprintf("value='%s'", $this->placeholderText) : "");
        if ($this->expectedLines != null)
            $expectedLines = "rows='" . $this->expectedLines . "'";
        if ($this->maxStrings != null)
            $count = $this->maxStrings;
        for ($i = 0; $i < $count; $i++) {
            $code.=sprintf("<textarea class='QTIextendedTextInteraction' %s name='%s'>%s</textarea>", $expectedLines, $this->responseIdentifier, $value);
        }
        return $code;
    }

}

?>