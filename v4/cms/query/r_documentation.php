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

$sql = "SELECT `".Ini::$db_master_name."`.`".RDoc::get_mysql_table()."`.`HTML`
    FROM `".Ini::$db_master_name."`.`".RDoc::get_mysql_table()."`
    LEFT JOIN `".Ini::$db_master_name."`.`".RDocFunction::get_mysql_table()."` ON `".Ini::$db_master_name."`.`".RDocFunction::get_mysql_table()."`.`RDoc_id` = `".Ini::$db_master_name."`.`".RDoc::get_mysql_table()."`.`id`
    WHERE `".Ini::$db_master_name."`.`".RDocFunction::get_mysql_table()."`.`name` = '".$_POST['func']."'";
$z=mysql_query($sql);
while($r=mysql_fetch_array($z)){
    echo json_encode(array("html"=>$r[0]));
    break;
}
?>