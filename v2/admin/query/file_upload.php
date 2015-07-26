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

$file = $_FILES['file'];

$f = new File();
$f->temp_id = $_POST['temp_id'];
$f->file_name = $file['name'];
$f->Module_id = $_POST['Module_id'];
$f->object_id = $_POST['object_id'];
$f->temp_name = $file['tmp_name'];
$f->file_size = $file['size'];

$code = 0;
$can_upload = true;
$module = DS_Module::from_mysql_id($_POST['Module_id']);
$class_name = $module->value;

$max_num = $class_name::$max_files_num;
$obj = $class_name::from_mysql_id($_POST['object_id']);

if ($obj == null)
    $files = File::from_property(array(
                "temp_id" => $_POST['temp_id']
            ));
else
    $files = $obj->get_files();
$cur_num = count($files);
if ($cur_num >= $max_num && $max_num != -1)
    $can_upload = false;

if ($can_upload)
    $f->mysql_save();
else
    $code = -1;

echo '{"name":"' . $file['name'] . '","type":"' . $file['type'] . '","size":"' . $file['size'] . '", "code":"' . $code . '"}';
?>