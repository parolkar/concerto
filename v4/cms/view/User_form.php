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
$class_name = "User";
$edit_caption = Language::string(170);
$new_caption = Language::string(171);
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
    <?php
    if ($class_name::$exportable && $oid > 0) {
        ?>
                    Methods.iniIconButton(".btnExport", "arrowthickstop-1-n");
                    Methods.iniIconButton(".btnUpload", "gear");        
        <?php
    }
    ?>
            Methods.iniTooltips();
        });
    </script>

    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend class=""><b><?= $caption ?></b></legend>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">*^ <?= Language::string(173) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(178) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputLogin" value="<?= $obj->login ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel"><input class="tooltip" type="checkbox" id="form<?= $class_name ?>CheckboxPassword" title="<?= Language::string(180) ?>" <?= $oid == -1 ? "checked style='display:none;'" : "" ?> /><?= Language::string(179) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(181) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="password" id="form<?= $class_name ?>InputPassword" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>

                </tr>
            </table>
        </div>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel"><?= Language::string(182) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(183) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="password" id="form<?= $class_name ?>InputPasswordConf" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">* <?= Language::string(184) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(186) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputFirstname" value="<?= $obj->firstname ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">* <?= Language::string(185) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(187) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputLastname" value="<?= $obj->lastname ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>    

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">* <?= Language::string(419) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(420) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <select id="form<?= $class_name ?>SelectInstitutionType" class="fullWidth ui-widget-content ui-corner-all">
                                <?php foreach (DS_UserInstitutionType::get_all() as $it) {
                                    ?>
                                    <option value="<?= $it->id ?>" <?= ($it->id == $obj->UserInstitutionType_id ? "selected" : "") ?>><?= $it->get_name() ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
            </table>
        </div>    

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">* <?= Language::string(421) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(422) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputInstitutionName" value="<?= $obj->institution_name ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>    

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel">* <?= Language::string(174) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(188) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputEmail" value="<?= $obj->email ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>    

        <div class="divFormElement">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel"><?= Language::string(189) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(190) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input type="text" id="form<?= $class_name ?>InputPhone" value="<?= $obj->phone ?>" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>       

        <?php
        if ($logged_user->superuser) {
            ?>
            <div class="divFormElement">
                <table class="fullWidth">
                    <tr>
                        <td class="noWrap tdFormLabel"><?= Language::string(623) ?>:</td>
                        <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(624) ?>"></span></td>
                        <td>
                            <div class="divFormControl">
                                <input type="checkbox" id="form<?= $class_name ?>CheckboxSuperuser" <?= $obj->superuser == 1 ? "checked" : "" ?> class="ui-widget-content ui-corner-all" />
                            </div>
                        </td>
                    </tr>
                </table>
            </div>       
            <?php
        }
        ?>

        <div style="clear: left;" />
    </fieldset>

    <?php
    if ($oid != -1) {
        ?>
        <div id="div<?= $class_name ?>ShareDialog" class="notVisible">

        </div>

        <div id="div<?= $class_name ?>WorkspaceDialog" class="notVisible">
            <fieldset class="padding ui-widget-content ui-corner-all margin">
                <legend>
                    <table>
                        <tr>
                            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(672) ?>"></span></td>
                            <td class=""><b><?= Language::string(70) ?></b></td>
                        </tr>
                    </table>
                </legend>
                <div class="horizontalMargin">
                    <input type="text" id = "input<?= $class_name ?>NameWorkspaceDialog" class = "fullWidth ui-widget-content ui-corner-all" />
                </div>

            </fieldset>
        </div>

        <?php
        include Ini::$path_internal . "cms/view/User_workspace.php";
        include Ini::$path_internal . "cms/view/User_share.php";
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
        </div>
        <?php
    }
} else {
    ?>
    <div class="padding margin ui-state-error " align="center"><?= Language::string(123) ?></div>
    <?php
}
?>