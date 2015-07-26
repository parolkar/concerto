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

<script>
    $(function(){
        Methods.iniIconButton("#btnStartDebug", "play");
        Methods.iniIconButton("#btnStopDebug", "stop");
    });
</script>

<fieldset class="padding ui-widget-content ui-corner-all" style="margin:0px;">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(622) ?>"></span></td>
                <td class=""><b><?= Language::string(621) ?></b></td>
            </tr>
        </table>
    </legend>
    <table> 
        <tr>
            <td id="tdTestDebugStatus" style="width:100%;" class="ui-state-highlight">
                <?= Language::string(661) ?>
            </td>
            <td>
                <button id="btnStopDebug" style="font-size:smaller; white-space: nowrap;" disabled="disabled" onclick="Test.uiStopDebug()"><?= Language::string(662) ?></button>
            </td>
            <td>
                <button id="btnStartDebug" style="font-size: smaller; white-space: nowrap;" onclick="Test.uiStartDebug('<?= Ini::$path_external ?>',<?= User::get_current_UserWorkspace()->id ?>)"><?= Language::string(323) ?></button>
            </td>
        </tr>
    </table>
    <div id="divTestSessionStateContent">
    </div>
</fieldset>