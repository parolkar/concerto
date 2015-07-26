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

class InlineChoiceInteraction extends AInlineInteraction {

    //attributes
    public $shuffle = "false";
    //children
    public $inlineChoice = array();
    public static $name = "inlineChoiceInteraction";
    public static $possible_attributes = array(
        "shuffle"
    );
    public static $required_attributes = array();
    public static $possible_children = array(
        "inlineChoice"
    );
    public static $required_children = array(
        "inlineChoice"
    );

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_HTML_code() {
        $choices = array();
        if ($this->shuffle == "false")
            $choices = $this->inlineChoice;
        else {
            $temp = array();
            foreach ($this->inlineChoice as $choice) {
                if ($choice->fixed == "false")
                    array_push($temp, $choice);
            }
            for ($i = 0; $i < count($this->inlineChoice); $i++) {
                if ($this->inlineChoice[$i]->fixed == "true")
                    array_push($choices, $this->inlineChoice[$i]);
                else {
                    $index = rand(0, count($temp) - 1);
                    array_push($choices, $temp[$index]);
                    unset($temp[$index]);
                    $temp = array_values($temp);
                }
            }
        }

        $code = sprintf("<select class='QTIinlineChoiceInteraction' name='%s'>", $this->responseIdentifier);
        foreach ($choices as $choice) {
            $code.=$choice->get_HTML_code();
        }
        $code.="</select>";
        return $code;
    }

}

?>