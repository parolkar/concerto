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

class Language {

    public static $dictionary;
    public static $xml;

    public static function string($id) {
        //return str_replace('"', "&quot;", str_replace("'", "&#039;", self::$dictionary[$id]));
        $string = isset(self::$dictionary[$id]) && trim(self::$dictionary[$id]) !== "" ? addcslashes(self::$dictionary[$id], '"') : "[string:$id]";
        return $string;
        //return addcslashes(self::$dictionary[$id],'"');
    }

    public static function languages() {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $lngs = $xpath->query("/root/languages/language");
        return $lngs;
    }

    public static function load_dictionary() {
        self::$dictionary = array();

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load(Ini::$path_internal . "/cms/dictionary/dictionary.xml");
        self::$xml = $doc->saveXML();
        $lang = "en";
        if (isset($_SESSION['lng']))
            $lang = $_SESSION['lng'];
        $xpath = new DOMXPath($doc);
        $string = $xpath->query("/root/strings/string");
        foreach ($string as $s) {
            foreach ($s->childNodes as $child) {
                if ($child->nodeName == $lang)
                    self::$dictionary[$s->getAttribute("id")] = $child->nodeValue;
            }
        }
    }

    public static function get_kendo_culture() {
        $lang = "en";
        if (isset($_SESSION['lng']))
            $lang = $_SESSION['lng'];

        switch ($lang) {
            case "en": {
                    return "en-GB";
                }
            case "pl": {
                    return "pl-PL";
                }
            case "cn": {
                    return "zh-CHS";
                }
            case "de": {
                    return "de-DE";
                }
            case "fr": {
                    return "fr-FR";
                }
            case "br": {
                    return "pt-BR";
                }
        }
        return "en-GB";
    }

    public static function load_js_dictionary($client = false) {
        echo"<script>";

        $lang = "en";
        if (isset($_SESSION['lng']))
            $lang = $_SESSION['lng'];
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $ids = $xpath->query("/root/strings/string[@js='1']");
        foreach ($ids as $id) {
            $id = $id->getAttribute("id");
            echo"dictionary['s" . $id . "']=\"" . str_replace("\n", "", self::string($id)) . "\"
                ";
        }
        echo"</script>";
    }

}

?>
