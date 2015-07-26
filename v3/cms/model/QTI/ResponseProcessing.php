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

class ResponseProcessing extends OQTIElement {

    //attributes
    public $template = null;
    public $templateLocation = null;
    //children
    public $responseRule = array();
    public static $name = "responseProcessing";
    public static $possible_attributes = array(
        "template",
        "templateLocation",
        "xmlns",
        "xmlns:xsi",
        "xsi:schemaLocation"
    );
    public static $required_attributes = array();
    public static $possible_children = array(
        "responseRule"
    );
    public static $required_children = array();

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function validate($map = null, $TestSection_id = 0) {
        $result = parent::validate($map, $TestSection_id);
        if (json_decode($result)->result != 0 || $this->template == null)
            return $result;

        @$xml = file_get_contents($this->template);
        if (!$xml)
            return $result;

        $doc = new DOMDocument("1.0", "UTF-8");
        @$doc->loadXML($xml);
        $node = $doc->documentElement;
        $node = $this->node->ownerDocument->importNode($node, true);
        $this->parent->node->replaceChild($node, $this->node);

        $this->node = $node;

        return parent::validate($map, $TestSection_id);
    }

}

?>