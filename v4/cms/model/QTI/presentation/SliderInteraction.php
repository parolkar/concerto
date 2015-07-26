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

class SliderInteraction extends ABlockInteraction {

    //attributes
    public $lowerBound = "";
    public $upperBound = "";
    public $step = null;
    public $stepLabel = "false";
    public $orientation = null;
    public $reverse = null;
    public static $name = "sliderInteraction";
    public static $possible_attributes = array(
        "lowerBound",
        "upperBound",
        "step",
        "stepLabel",
        "orientation",
        "reverse"
    );
    public static $required_attributes = array(
        "lowerBound",
        "upperBound"
    );
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
        $code = sprintf("<script>$(function(){ QTI.initializeSliderInteraction('%s',%s,%s,%s,'%s'); });</script>", $this->parent->id, $this->lowerBound, $this->upperBound, ($this->step != null ? $this->step : "null"), ($this->orientation != null ? $this->orientation : "horizontal"));
        if ($this->prompt != null)
            $code.=$this->prompt->get_HTML_code();
        $code.="<div class='QTIsliderInteraction'/>";
        $code.=sprintf("<input type='hidden' class='QTIsliderInteractionInput' name='%s' value='0' />", $this->responseIdentifier);
        return $code;
    }

}

?>