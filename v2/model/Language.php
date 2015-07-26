<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Language
{
    public static $xml;

    public static function string($id)
    {
        $lang = "en";
        if (isset($_SESSION['lng'])) $lang = $_SESSION['lng'];
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $string = $xpath->query("/root/strings/string[@id='$id']/$lang");
        foreach ($string as $s) return $s->nodeValue;
    }
    
    public static function languages()
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $lngs = $xpath->query("/root/languages/language");
        return $lngs;
    }

    public static function load_dictionary()
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load(Ini::$dictionary_path);
        self::$xml = $doc->saveXML();
    }

    public static function load_js_dictionary($client = false)
    {
        echo'
            <script>
                ';
        if (!$client)
        {
            echo'
                User.captionIncorrectLogin="' . Language::string(80) . '";
                User.captionDelete="' . Language::string(81) . '";
                User.captionPasswordsMismatch="' . Language::string(82) . '";
                Group.captionDelete="' . Language::string(109) . '";
                Item.captionDelete="' . Language::string(114) . '";
                Item.captionImportHTMLConfirm="' . Language::string(10) . '";
                    ';
        }
        echo'   
                Methods.captionBtnEdit="' . Language::string(43) . '";
                Methods.captionBtnDelete="' . Language::string(42) . '";
                Methods.captionBtnSave="' . Language::string(41) . '";
                Methods.captionBtnNew="' . Language::string(40) . '";
                Methods.captionBtnPreview="' . Language::string(115) . '";
                Methods.captionBtnCancel="' . Language::string(75) . '";
                Methods.captionBtnLogout="' . Language::string(116) . '";
                Methods.captionBtnImportHTML="' . Language::string(39) . '";
                Methods.captionBtnInfoRFunction="' . Language::string(12) . '";
                Methods.captionBtnInfoItemName="' . Language::string(14) . '";
                Methods.captionBtnInfoItemTemplate="' . Language::string(15) . '";
                Methods.captionBtnInfoItemHash="' . Language::string(16) . '";
                Methods.captionBtnInfoItemTimer="' . Language::string(17) . '";
                Methods.captionBtnInfoSubmitButtons="' . Language::string(18) . '";
                Methods.captionBtnInfoItemTable="' . Language::string(19) . '";
                Methods.captionBtnInfoSentVariables="' . Language::string(23) . '";
                Methods.captionBtnInfoAcceptedVariables="' . Language::string(24) . '";
                Methods.captionBtnInfoItemDefaultButton="' . Language::string(13) . '";
                Methods.captionBtnDebug="' . Language::string(119) . '";
                Methods.captionBtnSessionVariables="' . Language::string(120) . '";
                Methods.captionBtnRVariables="' . Language::string(121) . '";
                Methods.captionBtnHomepage="' . Language::string(122) . '";
                Methods.captionBtnGoogleGroup="' . Language::string(123) . '";
                Methods.captionBtnBuiltInFunctionsDoc="' . Language::string(124) . '";
                Methods.captionBtnItemsSessionVariables="' . Language::string(130) . '";
                Methods.captionRequiredFields="' . Language::string(131) . '";
                Methods.captionNotSaved="' . Language::string(132) . '";
                Methods.captionSaved="' . Language::string(133) . '";
                Methods.captionBtnRun="' . Language::string(134) . '";
                Methods.captionBtnExecute="' . Language::string(135) . '";
                Methods.captionDeleteFileConfirmation="'.Language::string(136).'";
                Methods.captionDefaultAlertTitle="'.Language::string(137).'";
                Methods.captionDefaultConfirmationTitle="'.Language::string(138).'";
            </script>
            ';
    }

}

?>
