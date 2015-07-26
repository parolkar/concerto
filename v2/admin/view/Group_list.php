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
$list_caption = Language::string(106);
$empty_caption = Language::string(107);
//////////

$sql = $user->mysql_list_rights_filter($class_name, "`" . $class_name . "`.`name` ASC");
$num_rows = mysql_num_rows(mysql_query($sql));
?>
<script>
    $(function(){
        Methods.iniIconButtons();
<?php if ($num_rows > 0) { ?>
            Methods.iniListTableExtensions("<?= $class_name ?>",true,true,[0,1,2,3],<?=$class_name?>.listLength);
<?php } ?>
    })
</script>

<div class="fullWidth" align="center"><button class="btnNew" onclick='<?=$class_name?>.uiEdit(0)' /></div>

<?php if ($num_rows > 0)
    include Ini::$internal_path . 'admin/view/inc/list_filter.inc.php'; ?>
<table class="fullWidth listTable ui-widget-content ui-corner-all" id="table<?= $class_name ?>List">
    <caption class="ui-widget-header ui-corner-all"><?= $list_caption ?></caption>
    <thead>
        <tr>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(49); ?></th>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(50); ?></th>
            <th class="ui-widget-header ui-corner-all" style="display:none;"><?= Language::string(108); ?></th>
            <th class="ui-widget-header ui-corner-all" style="display:none;"><?= Language::string(105); ?></th>
            <th class="noWrap ui-widget-header ui-corner-all"><?= Language::string(100); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
$z = mysql_query($sql);
while ($r = mysql_fetch_array($z)) {
    $obj = $class_name::from_mysql_id($r[0]);
    $owner = $obj->get_owner();
    $share = $obj->get_sharing();
    ?>
            <tr id="row<?= $class_name . $obj->id ?>" class="row<?= $class_name ?>">
                <td class="noWrap ui-widget-content ui-corner-all"><?= $obj->id ?></td>
                <td class="noWrap fullWidth ui-widget-content ui-corner-all"><?= $obj->name ?></td>
                <td class="noWrap fullWidth ui-widget-content ui-corner-all" style="display:none;"><?= ($owner != null ? $owner->get_full_name() : "&lt;" . Language::string(95) . "&gt;") ?></td>
                <td class="noWrap fullWidth ui-widget-content ui-corner-all" style="display:none;"><?= ($share != null ? $share->name : "&lt;" . Language::string(95) . "&gt;") ?></td>
                <td class="noWrap">
                    <button class="btnEdit" onclick="<?= $class_name ?>.uiEdit(<?= $obj->id ?>)"></button>
                    <button class="btnDelete" onclick="<?= $class_name ?>.uiDelete(<?= $obj->id ?>)"></button>
                </td>
            </tr>
<?php } ?> 
    </tbody>
</table>
        <?php if ($num_rows == 0) { ?>
    <div class="fullWidth ui-widget-content ui-corner-all" align="center"><?= $empty_caption ?></div>
    <?php
} else
    include Ini::$internal_path . 'admin/view/inc/list_pager.inc.php'; ?>