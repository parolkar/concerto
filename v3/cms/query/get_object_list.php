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
    echo json_encode(array());
    exit();
}

$sql = $logged_user->mysql_list_rights_filter($_GET['class_name'], "`" . $_GET['class_name']. "`.`id` ASC");

$cols = $_GET['class_name']::get_list_columns();

$table = array();

$z=mysql_query($sql);
while($r=mysql_fetch_array($z))
{
    $obj = $_GET['class_name']::from_mysql_id($r[0]);
    $row = array();
    $i=0;
    foreach($cols as $col)
    {
        $row[$col["property"]]=htmlspecialchars_decode($obj->get_list_column_value($i));
        if($col["type"]=="number") $row[$col["property"]]+=0;
        $i++;
    }
    $editable = $logged_user->is_object_editable($obj);
    $row["editable"]=$logged_user->is_object_editable($obj)?1:0;
    array_push($table, $row);
}

echo json_encode($table);
?>