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

class SimpleChoice extends AChoice {

    public static $name = "simpleChoice";
    public static $possible_attributes = array();
    public static $required_attributes = array();
    public static $possible_children = array(
        "*"
    );
    public static $required_children = array();

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_HTML_code() {
        $label = $this->get_contents();
        $class_name = get_class($this->parent);
        if ($class_name::$name == "choiceInteraction") {
            $interaction = "";
            if ($this->parent->maxChoices == "1") {
                $interaction = sprintf("<input type='radio' name='%s' value='%s' />", $this->parent->responseIdentifier, $this->identifier);
            } else {
                $onclick = "";
                if ($this->parent->maxChoices != "0")
                    $onclick = sprintf("onclick='QTI.maxChoicesCheck(\"%s\",this,\"%s\",%s)'", $this->parent->id, $this->parent->responseIdentifier, $this->parent->maxChoices);
                $interaction = sprintf("<input type='checkbox' name='%s' value='%s' %s />", $this->parent->responseIdentifier, $this->identifier, $onclick);
            }
            return $interaction;
        }
        if ($class_name::$name == "orderInteraction") {
            $class = "";
            $class .= ($this->parent->orientation == null || $this->parent->orientation == 'horizontal' ? 'QTIhorizontalOrderedElement' : 'QTIverticalOrderedElement');
            return sprintf("<div class='%s'>%s<input type='hidden' name='%s' value='%s' /></div>", $class, $label, $this->parent->responseIdentifier, $this->identifier);
        }
    }

}

?>