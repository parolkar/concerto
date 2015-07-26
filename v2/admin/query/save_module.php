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

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);
if ($obj == null) {
    $obj = new $_POST['class_name']();
    $vars = get_object_vars($obj);
    $is_ownable = false;
    foreach ($vars as $k => $v) {
        if ($k == "Owner_id") {
            $is_ownable = true;
            break;
        }
    }
    if ($is_ownable)
        $obj->Owner_id = $user->id;
    if (isset($_POST['Sharing_id']))
        $obj->Sharing_id = $_POST['Sharing_id'];
}

$_POST['oid'] = $obj->mysql_save_from_post($_POST);

echo json_encode(array("oid" => $_POST['oid']));
?>