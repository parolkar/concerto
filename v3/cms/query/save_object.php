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

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null)
{
    echo json_encode(array("result" => -1));
    exit();
}

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);
if ($obj == null)
{
    if (!$logged_user->is_module_writeable($_POST['class_name']))
    {
        echo json_encode(array("result" => -2));
        exit();
    }

    $obj = new $_POST['class_name']();
    $vars = get_object_vars($obj);
    $is_ownable = false;
    foreach ($vars as $k => $v)
    {
        if ($k == "Owner_id")
        {
            $is_ownable = true;
            break;
        }
    }
    if ($is_ownable) $obj->Owner_id = $logged_user->id;
    if (isset($_POST['Sharing_id'])) $obj->Sharing_id = $_POST['Sharing_id'];
}
else
{
    if (!$logged_user->is_object_editable($obj))
    {
        echo json_encode(array("result" => -2));
        exit();
    }
}

$response = $obj->mysql_save_from_post($_POST);
if(is_numeric($response)) {
    $oid = $response;
    $result = 0;
    echo json_encode(array("result" => $result, "oid" => $oid));
    exit();
}
else {
    echo $response;
}
?>