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

//////////
$class_name = "Table";
$edit_caption = Language::string(117);
$new_caption = Language::string(118);
//////////

$oid = 0;
if (isset($_POST['oid']) && $_POST['oid'] != 0)
    $oid = $_POST['oid'];

$btn_cancel = "<button class='btnCancel' onclick='" . $class_name . ".uiEdit(0)'>" . Language::string(23) . "</button>";
$btn_delete = "<button class='btnDelete ui-state-error' onclick='" . $class_name . ".uiDelete($oid)'>" . Language::string(94) . "</button>";
$btn_save = "<button class='btnSave ui-state-highlight' onclick='" . $class_name . ".uiSave()'>" . Language::string(95) . "</button>";
$btn_save_new = "<button class='btnSaveNew' onclick='" . $class_name . ".uiSave(null,true)'>" . Language::string(510) . "</button>";

$caption = "";
$buttons = "";
if ($oid > 0) {
    $oid = $_POST['oid'];
    $obj = $class_name::from_mysql_id($oid);

    $caption = $edit_caption . " #" . $oid;
    $buttons = $btn_cancel . $btn_save . $btn_save_new . $btn_delete;
} else {
    $obj = new $class_name();
    $caption = $new_caption;
    $buttons = "";
}

if ($oid != 0) {
    ?>
    <script>
        $(function(){
            Methods.iniIconButton(".btnGoToTop","arrow-1-n");
            Methods.iniIconButton(".btnCancel", "cancel");
            Methods.iniIconButton(".btnSave", "disk");
            Methods.iniIconButton(".btnSaveNew", "disk");
            Methods.iniIconButton(".btnDelete", "trash");
            Methods.iniIconButton(".btnTableStructureImportTable", "arrowthickstop-1-s");
            Methods.iniIconButton(".btnTableStructureImportCSV", "arrowthickstop-1-s");
            Methods.iniIconButton(".btnTableStructureExportCSV", "arrowthickstop-1-n");
    <?php
    if ($class_name::$exportable && $oid > 0) {
        ?>
                    Methods.iniIconButton(".btnExport", "arrowthickstop-1-n");
                    Methods.iniIconButton(".btnUpload", "gear");        
        <?php
    }
    ?>
            Methods.iniTooltips();
            Methods.iniDescriptionTooltips();
        });
    </script>

    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend class="">
            <table>
                <tr>
                    <td><b><?= $caption ?></b></td>
                    <?php
                    if ($oid != -1) {
                        ?>
                        <td>
                            <span class="spanIcon tooltipDescription ui-icon ui-icon-document-b" onclick="<?= $class_name ?>.uiEditDescription($(this).next())" title="<?= Language::string(107) ?>"></span>
                            <textarea id="form<?= $class_name ?>TextareaDescription" name="form<?= $class_name ?>TextareaDescription" class="notVisible"><?= $obj->description ?></textarea>
                        </td>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </legend>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">*^ <?= Language::string(70) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(119) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputName" value="<?= $obj->name ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="clear: left;" />
    </fieldset>

    <?php
    if ($oid != -1) {
        include Ini::$path_internal . 'cms/view/Table_structure.php';
        include Ini::$path_internal . 'cms/view/Table_indexes.php';
        include Ini::$path_internal . 'cms/view/Table_data.php';
    }

    if ($oid != -1) {
        ?>
        <div id="div<?= $class_name ?>Dialog" class="notVisible">
            <fieldset class="padding ui-widget-content ui-corner-all margin">
                <legend>
                    <table>
                        <tr>
                            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(508) ?>"></span></td>
                            <td class=""><b><?= Language::string(507) ?></b></td>
                        </tr>
                    </table>
                </legend>
                <table>
                    <tr>
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(70) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(257) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <input type="text" id="form<?= $class_name ?>InputColumnName" value="" class="fullWidth ui-widget-content ui-corner-all" />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(122) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(258) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <select id="form<?= $class_name ?>SelectColumnType" class="<?=User::view_class(true)?> fullWidth ui-widget-content ui-corner-all form<?= $class_name ?>SelectColumnType sctSimple" onchange="$('.form<?= $class_name ?>SelectColumnType').val($(this).val());">
                                    <option value="double"><?= Language::string(636) ?></option>
                                    <option value="text" <?= User::is_simple_view() ? "selected" : "" ?>><?= Language::string(637) ?></option>
                                </select>
                                
                                <select id="form<?= $class_name ?>SelectColumnType" class="<?=User::view_class()?> fullWidth ui-widget-content ui-corner-all form<?= $class_name ?>SelectColumnType sctAdvanced" onchange="$('.form<?= $class_name ?>SelectColumnType').val($(this).val());">
                                    <optgroup label="<?= Language::string(581) ?>">
                                        <option value="tinyint">tinyint</option>
                                        <option value="smallint">smallint</option>
                                        <option value="mediumint">mediumint</option>
                                        <option value="int">int</option>
                                        <option value="bigint">bigint</option>
                                        <option value="decimal">decimal</option>
                                        <option value="float">float</option>
                                        <option value="double">double</option>
                                        <option value="real">real</option>
                                        <option value="bit">bit</option>
                                        <option value="boolean">boolean</option>
                                        <option value="serial">serial</option>
                                    </optgroup>
                                    <optgroup label="<?= Language::string(583) ?>">
                                        <option value="char">char</option>
                                        <option value="varchar">varchar</option>
                                        <option value="tinytext">tinytext - HTML</option>
                                        <option value="text" <?= !User::is_simple_view() ? "selected" : "" ?>>text - HTML</option>
                                        <option value="mediumtext">mediumtext - HTML</option>
                                        <option value="longtext">longtext - HTML</option>
                                        <option value="binary">binary</option>
                                        <option value="varbinary">varbinary</option>
                                        <option value="tinyblob">tinyblob</option>
                                        <option value="mediumblob">mediumblob</option>
                                        <option value="blob">blob</option>
                                        <option value="longblob">longblob</option>
                                        <option value="enum">enum</option>
                                        <option value="set">set</option>
                                    </optgroup>
                                    <optgroup label="<?= Language::string(582) ?>">
                                        <option value="date">date</option>
                                        <option value="datetime">datetime</option>
                                        <option value="timestamp">timestamp</option>
                                        <option value="time">time</option>
                                        <option value="year">year</option>
                                    </optgroup>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr class="<?= User::view_class() ?>">
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(585) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(586) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <input type="text" id="form<?= $class_name ?>InputColumnLength" value="" class="fullWidth ui-widget-content ui-corner-all" />
                            </div>
                        </td>
                    </tr>
                    <tr class="<?= User::view_class() ?>">
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(538) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(587) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <input type="text" id="form<?= $class_name ?>InputColumnDefault" value="" class="fullWidth ui-widget-content ui-corner-all" />
                            </div>
                        </td>
                    </tr>
                    <tr class="<?= User::view_class() ?>">
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(588) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(589) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <select id="form<?= $class_name ?>SelectColumnAttributes" class="fullWidth ui-widget-content ui-corner-all">
                                    <option value="">&lt;<?= Language::string(73) ?>&gt;</option>
                                    <option value="binary">binary</option>
                                    <option value="unsigned">unsigned</option>
                                    <option value="unsigned zerofill">unsigned zerofill</option>
                                    <option value="on update current_timestamp">on update current_timestamp</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr class="<?= User::view_class() ?>">
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(590) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(591) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <input type="checkbox" id="form<?= $class_name ?>CheckboxColumnNull" value="1" class="ui-widget-content ui-corner-all" />
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>

        <div id="div<?= $class_name ?>IndexDialog" class="notVisible">
            <fieldset class="padding ui-widget-content ui-corner-all margin">
                <legend>
                    <table>
                        <tr>
                            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(607) ?>"></span></td>
                            <td class=""><b><?= Language::string(606) ?></b></td>
                        </tr>
                    </table>
                </legend>
                <table>
                    <tr>
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(122) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(608) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin">
                                <select id="form<?= $class_name ?>SelectIndexType" class="fullWidth ui-widget-content ui-corner-all">
                                    <option value="unique">unique</option>
                                    <option value="index">index</option>

                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(602) ?>:</td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(609) ?>"></span></td>
                        <td class="fullWidth">
                            <div class="horizontalMargin" id="div<?= $class_name ?>IndexableColumns">

                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
        <?php
    }
    if ($oid != -1) {
        ?>
        <div class="divFormFloatingBar" align="right">
            <button class="btnGoToTop" onclick="location.href='#'"><?= Language::string(442) ?></button>
            <?= $btn_cancel ?>
            <?= $btn_delete ?>
            <?= $btn_save ?>
            <?= $btn_save_new ?>
            <?php
            if ($class_name::$exportable && $oid > 0) {
                ?>
                <button class="btnExport" onclick="<?= $class_name ?>.uiExport(<?= $oid ?>)"><?= Language::string(443) ?></button>
                <button class="btnUpload" onclick="<?= $class_name ?>.uiUpload(<?= $oid ?>)"><?= Language::string(383) ?></button>
                <?php
            }
            ?>
            <button class="btnTableStructureImportTable" onclick="Table.uiImportTable()"><?= Language::string(125) ?></button>
            <button class="btnTableStructureImportCSV" onclick="Table.uiImportCSV()"><?= Language::string(126) ?></button>
            <button class="btnTableStructureExportCSV" onclick="Table.uiExportCSV()"><?= Language::string(127) ?></button>
        </div>
        <?php
    }
} else {
    ?>
    <div class="padding margin ui-state-error " align="center"><?= Language::string(123) ?></div>
    <?php
}
?>
