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

$table = array();

foreach ($obj->get_TableIndexes() as $index) {
    $columns = "";
    foreach ($index->get_TableIndexColumns() as $col) {
        $tc = TableColumn::from_mysql_id($col->TableColumn_id);
        if ($tc != null) {
            if ($columns != "")
                $columns.=",";
            $columns.=$tc->name;
        }
    }
    $row = array("id" => $index->id, "type" => $index->type, "columns" => $columns);
    array_push($table, $row);
}

echo json_encode($table);
?>