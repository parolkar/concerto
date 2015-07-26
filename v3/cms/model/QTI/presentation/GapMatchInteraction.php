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

class GapMatchInteraction extends ABlockInteraction {

    //attributes
    public $shuffle = "false";
    //children
    public $gapChoice = array();
    public static $name = "gapMatchInteraction";
    public static $possible_attributes = array(
        "shuffle"
    );
    public static $required_attributes = array();
    public static $possible_children = array(
        "*"
    );
    public static $required_children = array(
        "gapChoice"
    );

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_gaps() {
        $result = array();
        $xpath = new DOMXPath($this->node->ownerDocument);
        $xpath->registerNamespace("qti", "http://www.imsglobal.org/xsd/imsqti_v2p0");
        $search = $xpath->query("//qti:gap");
        foreach ($search as $elem) {
            $obj = new Gap($elem, $this);
            $obj->validate(null, $this->TestSection_id);
            array_push($result, $obj);
        }
        return $result;
    }

    public function get_HTML_code() {
        $choices = array();
        if ($this->shuffle == "false")
            $choices = $this->gapChoice;
        else {
            $temp = array();
            foreach ($this->gapChoice as $choice) {
                if ($choice->fixed == "false")
                    array_push($temp, $choice);
            }
            for ($i = 0; $i < count($this->gapChoice); $i++) {
                if ($this->gapChoice[$i]->fixed == "true")
                    array_push($choices, $this->gapChoice[$i]);
                else {
                    $index = rand(0, count($temp) - 1);
                    array_push($choices, $temp[$index]);
                    unset($temp[$index]);
                    $temp = array_values($temp);
                }
            }
        }

        $code = "";
        if ($this->prompt != null)
            $code.=$this->prompt->get_HTML_code();
        foreach ($this->node->childNodes as $child) {
            if ($child->nodeName != "prompt" && $child->nodeName != "gapText" && $child->nodeName != "gapImg") {
                $code.=$this->node->ownerDocument->saveXML($child);
            }
        }
        $code.="<br/><br/>";
        $code.="<table class='QTItable'><tr><td class='QTItableColumnHeader'></td>";
        foreach ($choices as $choice) {
            $code.=sprintf("<td class='%s QTItableColumnHeader'>%s</td>", "choiceContent_" . $choice->identifier, $choice->get_HTML_code());
        }
        $code.="</tr>";
        foreach ($this->get_gaps() as $gap) {
            $code.=sprintf("<tr><td class='QTItableRowHeader'>%s</td>", $gap->identifier);
            foreach ($choices as $choice) {
                if ($gap->matchGroup != null && $gap->matchGroup != "" && !in_array($choice->identifier, explode(" ", $gap->matchGroup))) {
                    $code.="<td class='QTItableCell'></td>";
                    continue;
                }
                if ($choice->matchGroup != null && $choice->matchGroup != "" && !in_array($gap->identifier, explode(" ", $choice->matchGroup))) {
                    $code.="<td class='QTItableCell'></td>";
                    continue;
                }

                $value = $choice->identifier . " " . $gap->identifier;
                $code.=sprintf("<td class='QTItableCell' valign='middle' align='center'><input type='checkbox' vi='%s' hi='%s' hmm='%s' class='QTIgapMatchInteractionCheckbox' name='%s' value='%s' onclick='QTI.gapMatchInteractionCheck(%s,\"%s\",this)' /></td>", $gap->identifier, $choice->identifier, $choice->matchMax, $this->responseIdentifier, $value, $this->TestSection_id, $this->responseIdentifier);
            }
            $code.="</tr>";
        }
        $code.="</table>";
        return $code;
    }

}

?>