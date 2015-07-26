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

if (!$logged_user->is_module_writeable($class_name))
    die(Language::string(81));
if (!$logged_user->is_object_editable($obj))
    die(Language::string(81));
?>

<script>
    $(function(){
        Table.uiIniHTMLTooltips();
        
        Table.uiIniDataGrid();
    });
</script>

<!--grid magic starts here-->

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(503) ?>"></span></td>
                <td class=""><b><?= Language::string(351) ?></b></td>
            </tr>
        </table>
    </legend>
    <div id="div<?= $class_name ?>GridDataContainerExpandable">
        <div id="div<?= $class_name ?>DataStructureEmptyCaption" class="ui-state-error margin" style="display:none;"><?= Language::string(352) ?></div>
        <div id="div<?= $class_name ?>GridDataContainer" align="left" class="margin">
        </div>
    </div>
</fieldset>