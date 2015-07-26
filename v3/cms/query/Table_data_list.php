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

$cols = array();
$i = 0;
echo"[";
if ($obj->has_table()) {
    $cols = TableColumn::from_property(array("Table_id" => $obj->id));

    $sql = sprintf("SELECT * FROM `%s`", $obj->get_table_name());
    $z = mysql_query($sql);
    while ($r = mysql_fetch_array($z)) {
        if ($i > 0)
            echo ",";
        $row = array();
        foreach ($cols as $col) {
            $row[$col->name] = $r[$col->name];
        }
        echo json_encode($row);
        $i++;
    }
}
echo"]";
?>