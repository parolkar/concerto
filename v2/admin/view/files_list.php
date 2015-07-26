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

if (!isset($obj)) {
    $oid = 0;
    if (isset($_POST['oid']) && $_POST['oid'] != 0)
        $oid = $_POST['oid'];
    if (isset($_POST['temp_id']))
        $temp_id = $_POST['temp_id'];
    if (isset($_POST['class_name']))
        $class_name = $_POST['class_name'];

    $obj = $class_name::from_mysql_id($oid);
    if ($obj == null)
        $obj = new $class_name();
}

$files = $obj->get_files();
if ($oid == 0)
    $files = File::from_property(array(
                "temp_id" => $temp_id
            ));
foreach ($files as $file) {
    ?>
    <tr>
        <td class="ui-widget-content ui-corner-all"><?= $file->id ?></td>
        <td class="noWrap ui-widget-content ui-corner-all"><?= $file->file_name ?></td>
        <td class="noWrap ui-widget-content ui-corner-all"><?= $file->get_stored_as() ?></td>
        <td class="noWrap ui-widget-content ui-corner-all"><?= $file->get_formated_size() ?></td>
        <td class="ui-widget-content ui-corner-all"><?= $file->md5 ?></td>
        <td class="noWrap ui-widget-content ui-corner-all">
            <a href="<?= $file->get_external_source() ?>" target="_blank"><button class="btnPreview"></button></a>
            <button class="btnDelete" onclick="<?= $class_name ?>.uiDeleteFile(<?= $file->id ?>)"></button>
        </td>
    </tr>
    <?php
}
?>
<tr><td class="ui-widget-content ui-corner-all" colspan="6">
        <?php if ($class_name::$max_files_num != -1) { ?>
            Files: <b>(<?= count($files) ?>/<?= $class_name::$max_files_num ?>)</b>. If file limit is reached, new file upload will be cancelled.
        <?php
        } else {
            ?>
            Files: <b><?= count($files) ?></b>. Unlimited file upload.
        <?php } ?>
    </td></tr>