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
$class_name = "Group";
$edit_caption = Language::string(103);
$new_caption = Language::string(104);
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
        <td class="noWrap">*<?= Language::string(50) ?>:</td>
        <td class="fullWidth"><input type="text" id="form<?= $class_name ?>InputName" name="form<?= $class_name ?>InputName" value="<?= $obj->name ?>" class="fullWidth" /></td>
    </tr>
    <tr style="display:none;">
        <td class="noWrap"><?= Language::string(105) ?>:</td>
        <td class="fullWidth">
            <select id="form<?= $class_name ?>SelectSharing" class="fullWidth">
<?php foreach (DS_Sharing::get_all() as $share) { ?>
                    <option value="<?= $share->id ?>" <?= ($share->id == $obj->Sharing_id ? "selected" : "") ?>><?= $share->name ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
<?= $buttons ?>
        </td>
    </tr>
</table>