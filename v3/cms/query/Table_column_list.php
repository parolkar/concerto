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
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) {
    echo json_encode(array());
    exit();
}

$obj = Table::from_mysql_id($_GET['oid']);
if ($obj == null || !$logged_user->is_object_readable($obj)) {
    echo json_encode(array());
    exit();
}

$sql = sprintf("SELECT * FROM `TableColumn` WHERE `Table_id`='%d'", $_GET['oid']);

$table = array();

$z = mysql_query($sql);
while ($r = mysql_fetch_array($z)) {
    $obj = TableColumn::from_mysql_id($r["id"]);
    $row = array("id" => $obj->id, "name" => $obj->name, "type" => $obj->type, "lengthValues" => $obj->length, "defaultValue" => $obj->default_value, "attributes" => $obj->attributes, "nullable" => $obj->null, "auto_increment" => $obj->auto_increment);
    array_push($table, $row);
}

echo json_encode($table);
?>