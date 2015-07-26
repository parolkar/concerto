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

if (!isset($ini)) {
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) {
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}

$vals = array();
if (array_key_exists('value', $_POST)) {
    $vals = $_POST['value'];
}
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0) {
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}
?>  

<div class="divSectionSummary sortableHandle">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(10) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span id="spanExpandDetail_<?= $_POST['counter'] ?>" class="spanExpandDetail spanIcon ui-icon ui-icon-folder-<?= $_POST['detail'] == 1 ? "open" : "collapsed" ?> tooltip" title="<?= Language::string(390) ?>" onclick="Test.uiToggleDetails(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(10) ?></td>
            <td class="tdSectionColumnAction">
                <table class="fullWidth">
                    <tr>
                        <td><?= Language::string(122) ?>:</td>
                        <td>
                            <select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>" onchange="Test.uiRefreshSectionContent(<?= $_POST['type'] ?>, <?= $_POST['counter'] ?>, Test.getSectionValues(Test.sectionDivToObject($('#divSection_<?= $_POST['counter'] ?>'))))">
                                <option value="0" <?= $vals[0] == 0 ? "selected" : "" ?>><?= Language::string(395) ?></option>
                                <option value="1" <?= $vals[0] == 1 ? "selected" : "" ?>><?= Language::string(396) ?></option>
                            </select>
                        </td>
                        <td class="noWrap">, <?= Language::string(397) ?>:</td>
                        <td class="fullWidth">
                            <input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?> fullWidth ui-state-focus" value="<?= htmlspecialchars($vals[1], ENT_QUOTES) ?>" onchange="Test.uiSetVarNameChanged($(this))" />
                        </td>
                    </tr>
                </table>
            </td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-newwin tooltip" title="<?= Language::string(511) ?>" onclick="Test.duplicateSection(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnIcon <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnEnd <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Test.uiRemoveSection(<?= $_POST['counter'] ?>)" title="<?= Language::string(59) ?>"></span></td>
            <td class="tdSectionColumnButton"><button class="btnAddSection noWrap" onclick="Test.uiAddLogicSection(0,<?= $_POST['counter'] ?>)"><?= Language::string(619) ?></button></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?= $_POST['detail'] == 1 || $_POST['oid'] == 0 ? "" : "notVisible" ?>">
    <table class="fullWidth">
        <tr>
            <td>
                <b><?= Language::string(398) ?></b>:
            </td>
            <td>
                <select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>">
                    <option value="!=" <?= $vals[2] == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
                    <option value="==" <?= $vals[2] == "==" ? "selected" : "" ?>><?= Language::string(222) ?></option>
                    <option value=">" <?= $vals[2] == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
                    <option value=">=" <?= $vals[2] == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
                    <option value="<" <?= $vals[2] == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
                    <option value="<=" <?= $vals[2] == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
                </select> 
            </td>
            <td class="fullWidth">
                <input type="text" class="fullWidth ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[3], ENT_QUOTES) ?>" />
            </td>
        </tr>
    </table>
    <table class="fullWidth <?= $vals[0] == 1 ? "notVisible" : "" ?>">
        <tr>
            <td class="noWrap">
                <b><?= Language::string(400) ?></b>:
            </td>
            <td style="width:50%;">
                <input type="text" class="fullWidth ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[4], ENT_QUOTES) ?>" />
            </td>
            <td class="noWrap">, <b><?= Language::string(399) ?></b>:</td>
            <td style="width:50%;">
                <input type="text" class="fullWidth ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[5], ENT_QUOTES) ?>" />
            </td>
        </tr>
    </table>
</div>