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
?>

<div class="divSectionSummary">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(6) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span id="spanExpandDetail_<?= $_POST['counter'] ?>" class="spanExpandDetail spanIcon ui-icon ui-icon-folder-<?= $_POST['detail'] == 1 ? "open" : "collapsed" ?> tooltip" title="<?= Language::string(390) ?>" onclick="Test.uiToggleDetails(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(6) ?></td>
            <td class="tdSectionColumnAction"></td>
            <td class="tdSectionColumnIcon <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnEnd <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnIcon"></td>
            <td class="tdSectionColumnButton"><button class="btnAddSection noWrap" onclick="Test.uiAddLogicSection(0,<?= $_POST['counter'] ?>)"><?= Language::string(619) ?></button></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?= $_POST['detail'] == 1 || $_POST['oid'] == 0  ? "" : "notVisible" ?>">
    <table class="fullWidth">
        <tr>
            <td style="width:50%;" valign="top" align="center">
                <fieldset class="ui-widget-content">
                    <legend><b><?= Language::string(113) ?></b></legend>
                    <div>
                        <table>
                            <tr>
                                <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(245) ?>"></span></td>
                                <td>CONCERTO_TEST_ID</td>
                            </tr>
                            <tr>
                                <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(246) ?>"></span></td>
                                <td>CONCERTO_TEST_SESSION_ID</td>
                            </tr>
                            <tr>
                                <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(629) ?>"></span></td>
                                <td>USER_IP</td>
                            </tr>
                        </table>
                    </div>
                    <div class="notVisible">
                        <input class="inputReturnVar" type="hidden" value="CONCERTO_TEST_ID" />
                        <input class="inputReturnVar" type="hidden" value="CONCERTO_TEST_SESSION_ID" />
                        <input class="inputReturnVar" type="hidden" value="USER_IP" />
                    </div>
                </fieldset>
            </td>
        </tr>
    </table>
</div>