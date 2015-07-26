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

class AssociateInteraction extends ABlockInteraction {

//attributes
    public $shuffle = "false";
    public $maxAssociations = 1;
//children
    public $simpleAssociableChoice = array();
    public static $name = "associateInteraction";
    public static $possible_attributes = array(
        "shuffle",
        "maxAssociations"
    );
    public static $required_attributes = array();
    public static $possible_children = array(
        "simpleAssociableChoice"
    );
    public static $required_children = array(
        "simpleAssociableChoice"
    );

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
        $choices = array();
        if ($this->shuffle == "false")
            $choices = $this->simpleAssociableChoice;
        else {
            $temp = array();
            foreach ($this->simpleAssociableChoice as $choice) {
                if ($choice->fixed == "false")
                    array_push($temp, $choice);
            }
            for ($i = 0; $i < count($this->simpleAssociableChoice); $i++) {
                if ($this->simpleAssociableChoice[$i]->fixed == "true")
                    array_push($choices, $this->simpleAssociableChoice[$i]);
                else {
                    $index = rand(0, count($temp) - 1);
                    array_push($choices, $temp[$index]);
                    unset($temp[$index]);
                    $temp = array_values($temp);
                }
            }
        }
        $code.="<script>
            $(function(){
                QTI.initializeAssociateInteraction(".$this->TestSection_id.",'".$this->responseIdentifier."'," . $this->maxAssociations . ");
            });
            </script>";
        $code.="<div class='QTIdisplayTable QTIdraggableOptionsContainer'>";
        foreach ($choices as $choice) {
            $code.=$choice->get_HTML_code();
        }
        $code.="</div>";
        $code.="<div align='center'><table><tr>";
        $code.="<td class='QTIassociateAction QTIasssociateAddAssociationDropable' align='center'>+</td>";
        $code.="<td class='QTIassociateAction QTIasssociateRemoveAssociationDropable' align='center'>-</td>";
        $code.="</tr></table></div>";
        $code.="<div class='QTIassociationsContainer'></div>";
        return $code;
    }

}

?>