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

class AnyN extends AExpression {

    //attributes
    public $min = "";
    public $max = "";
    //children
    public $expression = array();
    public static $name = "anyN";
    public static $possible_attributes = array(
        "min",
        "max"
    );
    public static $required_attributes = array(
        "min",
        "max"
    );
    public static $possible_children = array(
        "expression"
    );
    public static $required_children = array(
        "expression"
    );

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_R_code() {
        $vector.="c(";
        $i = 0;
        foreach ($this->expression as $exp) {
            if ($i > 0)
                $vector.=",";
            $vector.=$exp->get_R_code();
            $i++;
        }
        $vector.=")";
        return "if(table(" . $vector . ")['TRUE']>=" . $this->min . " && table(" . $vector . ")['TRUE']<=" . $this->max . ") TRUE else if(length(" . $vector . ")-" . $this->min . "<table(" . $vector . ")['FALSE'] || " . $this->max . "<table(" . $vector . ")['TRUE']) FALSE else NULL";
    }

    public function get_cardinality(){
        return "single";
    }
    
    public function get_baseType(){
        return "boolean";
    }
}

?>