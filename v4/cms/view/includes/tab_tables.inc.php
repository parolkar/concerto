<?php
/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

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
if ($logged_user == null)
    die(Language::string(81));

$class_name = "Table";
$class_label = Language::string(85);

include Ini::$path_internal . "cms/view/includes/tab.inc.php";
?>

<div id="divTableDialogExportCSV" class="notVisible">
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(506) ?>"></span></td>
                    <td class=""><b><?= Language::string(504) ?></b></td>
                </tr>
            </table>
        </legend>
        <table>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(330) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(332) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVExportHeader" type="checkbox" class="ui-widget-content ui-corner-all" value="1" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(325) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(326) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVExportDelimeter" type="text" maxlength="1" class="fullWidth ui-widget-content ui-corner-all" value="," />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(327) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= htmlspecialchars(Language::string(328)) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVExportEnclosure" type="text" maxlength="1" class="fullWidth ui-widget-content ui-corner-all" value='"' />
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>

<div id="divTableDialogImportCSV" class="notVisible">
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(505) ?>"></span></td>
                    <td class=""><b><?= Language::string(504) ?></b></td>
                </tr>
            </table>
        </legend>
        <table>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(640) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(641) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVImportID" type="checkbox" class="ui-widget-content ui-corner-all" value="1" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(330) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(331) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVImportHeader" type="checkbox" class="ui-widget-content ui-corner-all" value="1" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(325) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(326) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVImportDelimeter" type="text" maxlength="1" class="fullWidth ui-widget-content ui-corner-all" value="," />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(327) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= htmlspecialchars(Language::string(328)) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="inputTableCSVImportEnclosure" type="text" maxlength="1" class="fullWidth ui-widget-content ui-corner-all" value='"' />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(86) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(256) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="fileTableCSVImport" type="file" name="files[]" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>

<div id="div<?= $class_name ?>DialogImportMySQL" class="notVisible">
</div>

<div id="div<?= $class_name ?>DialogHTML" class="notVisible">
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(259) ?>"></span></td>
                    <td class=""><b><?= Language::string(18) ?>:</b></td>
                </tr>
            </table>
        </legend>
        <textarea id="form<?= $class_name ?>TextareaHTML" name="form<?= $class_name ?>TextareaHTML" class="fullWidth ui-widget-content ui-corner-all">
                                                                                                                        
        </textarea>
    </fieldset>
</div>