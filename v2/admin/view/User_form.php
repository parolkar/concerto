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

if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
}
$user = User::get_logged_user();
if ($user == null)
    die(Language::string(85));

//////////
$class_name = "User";
$edit_caption = Language::string(86);
$new_caption = Language::string(87);
//////////

$oid = 0;
if (isset($_POST['oid']) && $_POST['oid'] != 0)
    $oid = $_POST['oid'];
if (isset($_POST['temp_id']))
    $temp_id = $_POST['temp_id'];
else {
    $temp_id = $class_name . "_" . session_id() . "_" . time() . "_" . $user->id . "_" . rand(0, 100);
    echo "<script>" . $class_name . ".tempID='$temp_id';</script>";
}

$btn_new = "<button class='btnNew' onclick='" . $class_name . ".uiEdit(0)'></button>";
$btn_delete = "<button class='btnDelete' onclick='" . $class_name . ".uiDelete($oid)'></button>";
$btn_save = "<button class='btnSave' onclick='" . $class_name . ".uiSave()'></button>";

$caption = "";
$buttons = "";
if ($oid != 0) {
    $oid = $_POST['oid'];
    $obj = $class_name::from_mysql_id($oid);

    $caption = $edit_caption . " #" . $oid;
    $buttons = $btn_save . $btn_delete;
} else {
    $obj = new $class_name();
    $caption = $new_caption;
    $buttons = $btn_save;
}
?>
<script>
    $(function(){
        Methods.iniIconButtons();
    });
</script>

<div class="ui-widget-header ui-corner-all fullWidth"><h4><?= $caption ?></h4></div>
<table class="fullWidth ui-widget-content ui-corner-all formTable ui-state-focus">
    <tr>
        <td class="noWrap">*<?= Language::string(76) ?>:</td>
        <td class="fullWidth"><input type="text" name="form<?= $class_name ?>InputLogin" id="form<?= $class_name ?>InputLogin" value="<?= $obj->login ?>" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?=$oid==0?"*":""?><input type="checkbox" id="form<?= $class_name ?>CheckboxPassword" title="<?= Language::string(88) ?>" <?=$oid==0?"checked style='display:none;'":""?> /><?= Language::string(77) ?>:</td>
        <td class="fullWidth"><input type="password" name="form<?= $class_name ?>InputPassword" id="form<?= $class_name ?>InputPassword" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?=$oid==0?"*":""?><?= Language::string(89) ?>:</td>
        <td class="fullWidth"><input type="password" name="form<?= $class_name ?>InputPasswordConf" id="form<?= $class_name ?>InputPasswordConf" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?= Language::string(90) ?>:</td>
        <td class="fullWidth"><input type="text" name="form<?= $class_name ?>InputFirstname" id="form<?= $class_name ?>InputFirstname" value="<?= $obj->firstname ?>" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?= Language::string(91) ?>:</td>
        <td class="fullWidth"><input type="text" name="form<?= $class_name ?>InputLastname" id="form<?= $class_name ?>InputLastname" value="<?= $obj->lastname ?>" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?= Language::string(92) ?>:</td>
        <td class="fullWidth"><input type="text" name="form<?= $class_name ?>InputEmail" id="form<?= $class_name ?>InputEmail" value="<?= $obj->email ?>" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?= Language::string(93) ?>:</td>
        <td class="fullWidth"><input type="text" name="form<?= $class_name ?>InputPhone" id="form<?= $class_name ?>InputPhone" value="<?= $obj->phone ?>" class="fullWidth" /></td>
    </tr>
    <tr>
        <td class="noWrap"><?= Language::string(94) ?>:</td>
        <td class="fullWidth">
            <select id="form<?= $class_name ?>SelectGroup" class="fullWidth">
                <option value="0" <?= (!$obj->has_group() ? "selected" : "") ?>>&lt;<?= Language::string(95) ?>&gt;</option>
<?php
$sql = $user->mysql_list_rights_filter("Group", "`name` ASC");
$z = mysql_query($sql);
while ($r = mysql_fetch_array($z)) {
    $group = Group::from_mysql_id($r[0]);
    ?>
                    <option value="<?= $group->id ?>" <?= ($obj->Group_id == $group->id ? "selected" : "") ?>><?= $group->name ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td class="noWrap"><?= Language::string(96) ?>:</td>
        <td class="fullWidth"><input type="checkbox" id="form<?= $class_name ?>CheckboxSuperadmin" <?= $obj->is_superadmin() ? "checked" : "" ?> /></td>
    </tr>
    <tr <?=($oid!=0?"style='display:none;'":"")?>>
        <td class="noWrap"><?= Language::string(142) ?>:</td>
        <td class="fullWidth"><input type="checkbox" id="form<?= $class_name ?>CheckboxSendCredentials" onchange="this.checked?$('#row<?=$class_name?>Welcome').css('display',''):$('#row<?=$class_name?>Welcome').css('display','none')" /></td>
    </tr>
    <tr id="row<?=$class_name?>Welcome" style="display:none;">
        <td class="noWrap"><?= Language::string(143) ?>:</td>
        <td class="fullWidth"><textarea name="form<?= $class_name ?>TextareaWelcome" id="form<?= $class_name ?>TextareaWelcome" class="fullWidth" style="height:150px;"></textarea></td>
    </tr>

    <tr>
        <td colspan="2" align="center">
<?= $buttons ?>
        </td>
    </tr>
</table>