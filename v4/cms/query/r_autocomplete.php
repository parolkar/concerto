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

$result = array();
$result['functions'] = array();
$sql = "SELECT 
    `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`name`, 
        `" . Ini::$db_master_name . "`.`" . RDocLibrary::get_mysql_table() . "`.`name`, 
            `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`id` 
    FROM `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "` 
    LEFT JOIN `" . Ini::$db_master_name . "`.`" . RDocLibrary::get_mysql_table() . "` ON `" . Ini::$db_master_name . "`.`" . RDocLibrary::get_mysql_table() . "`.`id` = `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`RDocLibrary_id`
    WHERE `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`name` LIKE '" . $_POST['string'] . "%' 
    ORDER BY `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`name` ASC";
$z = mysql_query($sql);
while ($r = mysql_fetch_array($z)) {
    array_push($result['functions'], array("id" => $r[2], "name" => $r[0], "pack" => $r[1]));
}

echo json_encode($result);
?>