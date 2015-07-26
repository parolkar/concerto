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

if ($readable) {
    ?>

    <script>
        $(function(){
            $( "#div<?= $class_name ?>RadioMenu" ).buttonset(); 
        });
    </script>

    <div align="center" id="div<?= $class_name ?>RadioMenu">
        <input type="radio" id="radio<?= $class_name ?>List" name="radio<?= $class_name ?>" checked="checked" onclick="<?= $class_name ?>.uiShowList();" />
        <label for="radio<?= $class_name ?>List"><?= Language::string(337) ?></label>
        <?php
        if ($writeable) {
            ?>
            <input type="radio" id="radio<?= $class_name ?>Form" name="radio<?= $class_name ?>" disabled="disabled" onclick="<?= $class_name ?>.uiShowForm();" />
            <label for="radio<?= $class_name ?>Form"><?= Language::string(338) ?> <?= Language::string(73) ?></label>
            <?php
        }
        ?>
    </div>

    <div align="center">
        <?php if ($logged_user->is_module_accesible($class_name)) { ?>
            <div align="center" id="div<?= $class_name ?>List" class="table fullWidth">
                <?php include Ini::$path_internal . 'cms/view/includes/list.inc.php'; ?>
            </div>
        <?php } ?>

        <?php if ($logged_user->is_module_writeable($class_name)) { ?>
            <div align="center" id="div<?= $class_name ?>Form" class="table fullWidth" style="display:none;">
                <?php include Ini::$path_internal . 'cms/view/' . $class_name . '_form.php'; ?>
            </div>

            <div id="div<?= $class_name ?>DialogImport" class="notVisible">
                <fieldset class="padding ui-widget-content ui-corner-all margin">
                    <legend>
                        <table>
                            <tr>
                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(267) ?>"></span></td>
                                <td class=""><b><?= Language::string(86) ?>:</b></td>
                            </tr>
                        </table>
                    </legend>
                    <input id="file<?= $class_name ?>Import" type="file" name="files[]" class="fullWidth ui-widget-content ui-corner-all" />
                </fieldset>
            </div>

            <div id="div<?= $class_name ?>DialogDescription" class="notVisible">
                <fieldset class="padding ui-widget-content ui-corner-all margin">
                    <legend>
                        <table>
                            <tr>
                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(254) ?>"></span></td>
                                <td class=""><b><?= Language::string(97) ?>:</b></td>
                            </tr>
                        </table>
                    </legend>
                    <textarea id="dialog<?= $class_name ?>TextareaDescription" name="dialog<?= $class_name ?>TextareaDescription" class="fullWidth ui-widget-content ui-corner-all">
                    </textarea>
                </fieldset>
            </div>

        <?php } ?>
    </div>
    <?php
}
?>