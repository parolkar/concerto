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

$list = $_GET['class_name']::get_list();
$cols = $_GET['class_name']::get_list_columns();

$table = array();

foreach ($list as $obj) {
    $row = array();
    $i = 0;
    foreach ($cols as $col) {
        $row[$col["property"]] = htmlspecialchars_decode($obj->get_list_column_value($i));
        if ($col["type"] == "number")
            $row[$col["property"]]+=0;
        $i++;
    }
    array_push($table, $row);
}

echo json_encode($table);
?>