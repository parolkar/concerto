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
if (!Ini::$public_registration) {
    $logged_user = User::get_logged_user();
    if ($logged_user == null) {
        echo json_encode(array("result" => -1));
        exit();
    }
}

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);

if ($_POST['class_name']::$is_master_table)
    $db = "`" . Ini::$db_master_name . "`.";
else
    $db = "";

$sql = sprintf("SELECT * FROM %s`%s` WHERE ( ", $db, $_POST['class_name']);
$i = 0;
foreach ($_POST['fields'] as $field) {
    if ($i > 0)
        $sql.="OR ";
    $f = json_decode($field);
    $sql.=sprintf("`%s`='%s' ", mysql_real_escape_string($f->name), mysql_real_escape_string(trim($f->value)));
    $i++;
    if (trim($f->value) == "") {
        echo json_encode(array("result" => 1));
        exit();
    }
}
if ($i > 0)
    $sql.=") ";
else
    $sql.="TRUE) ";

if ($obj != null) {
    $sql.=sprintf("AND `id`!='%d'", $obj->id);
}

$z = mysql_query($sql);
$count = mysql_num_rows($z);

echo json_encode(array("result" => $count == 0 ? 0 : 1));
?>