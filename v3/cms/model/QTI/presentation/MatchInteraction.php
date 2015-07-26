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

class MatchInteraction extends ABlockInteraction {

    //attributes
    public $shuffle = "false";
    public $maxAssociations = 1;
    //children
    public $simpleMatchSet = array();
    public static $name = "matchInteraction";
    public static $possible_attributes = array(
        "shuffle",
        "maxAssociations"
    );
    public static $required_attributes = array();
    public static $possible_children = array(
        "simpleMatchSet"
    );
    public static $required_children = array(
        "simpleMatchSet"
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
        $sms1 = array();
        $sms2 = array();
        if ($this->shuffle == "false") {
            $sms1 = $this->simpleMatchSet[0]->simpleAssociableChoice;
            $sms2 = $this->simpleMatchSet[1]->simpleAssociableChoice;
        } else {
            $temp1 = array();
            $temp2 = array();
            foreach ($this->simpleMatchSet[0]->simpleAssociableChoice as $choice) {
                if ($choice->fixed == "false")
                    array_push($temp1, $choice);
            }
            foreach ($this->simpleMatchSet[1]->simpleAssociableChoice as $choice) {
                if ($choice->fixed == "false")
                    array_push($temp2, $choice);
            }
            for ($i = 0; $i < count($this->simpleMatchSet[0]->simpleAssociableChoice); $i++) {
                if ($this->simpleMatchSet[0]->simpleAssociableChoice[$i]->fixed == "true")
                    array_push($sms1, $this->simpleMatchSet[0]->simpleAssociableChoice[$i]);
                else {
                    $index = rand(0, count($temp1) - 1);
                    array_push($sms1, $temp1[$index]);
                    unset($temp1[$index]);
                    $temp1 = array_values($temp1);
                }
            }
            for ($i = 0; $i < count($this->simpleMatchSet[1]->simpleAssociableChoice); $i++) {
                if ($this->simpleMatchSet[1]->simpleAssociableChoice[$i]->fixed == "true")
                    array_push($sms2, $this->simpleMatchSet[1]->simpleAssociableChoice[$i]);
                else {
                    $index = rand(0, count($temp2) - 1);
                    array_push($sms2, $temp2[$index]);
                    unset($temp2[$index]);
                    $temp2 = array_values($temp2);
                }
            }
        }
        $code.="<script>
            </script>";
        $code.= $this->prompt != null ? $this->prompt->get_HTML_code() : "";
        $code.="<table class='QTItable'><tr><td class='QTItableColumnHeader'></td>";
        foreach ($sms2 as $choice) {
            $code.=sprintf("<td class='QTItableColumnHeader'>%s</td>", $choice->get_contents());
        }
        $code.="</tr>";
        foreach ($sms1 as $choiceV) {
            $code.="<tr>";
            $code.=sprintf("<td class='QTItableRowHeader'>%s</td>", $choiceV->get_contents());
            foreach ($sms2 as $choiceH) {
                if ($choiceV->matchGroup != null && $choiceV->matchGroup != "" && !in_array($choiceH->identifier, explode(" ", $choiceV->matchGroup))) {
                    $code.="<td class='QTItableCell'></td>";
                    continue;
                }
                if ($choiceH->matchGroup != null && $choiceH->matchGroup != "" && !in_array($choiceV->identifier, explode(" ", $choiceH->matchGroup))) {
                    $code.="<td class='QTItableCell'></td>";
                    continue;
                }
                $value = $choiceV->identifier . " " . $choiceH->identifier;
                $check = sprintf("<input class='QTImatchInteractionCheckbox' vi='%s' hi='%s' vmm='%s' hmm='%s' type='checkbox' value='%s' name='%s' onclick='QTI.matchInteractionCheck(%s,\"%s\",this,%s)' />", $choiceV->identifier, $choiceH->identifier, $choiceV->matchMax, $choiceH->matchMax, $value, $this->responseIdentifier, $this->TestSection_id, $this->responseIdentifier, $this->maxAssociations);
                $code.=sprintf("<td class='QTItableCell' align='center' valign='middle'>%s</td>", $check);
            }
            $code.="</tr>";
        }
        $code.="</table>";
        return $code;
    }

}

?>