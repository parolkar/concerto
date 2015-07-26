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
    echo json_encode(array("result" => -1));
    exit();
}

$uf = UserFunction::from_property(array("User_id"=>$logged_user->id,"RDocFunction_id"=>$_POST['oid']),false);

if ($uf != null) {
    echo json_encode(array("result" => 1));
    exit();
}

$nuf = new UserFunction();
$nuf->RDocFunction_id = $_POST['oid'];
$nuf->User_id = $logged_user->id;
$nuf->mysql_save();

echo json_encode(array("result" => 0));
?>