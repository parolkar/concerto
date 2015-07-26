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

$obj = User::from_mysql_id($_GET['oid']);
if ($obj == null) {
    echo json_encode(array());
    exit();
}

$shares = array();

foreach ($obj->get_workspaces() as $ws) {
    foreach ($ws->get_shares() as $share) {
        $invitee = User::from_mysql_id($share->invitee_id);
        if ($invitee == null)
            continue;
        $row = array("id" => $share->id, "invitee_id" => $invitee->id, "name" => $invitee->get_full_name(), "institution" => $invitee->institution_name, "workspace_id" => $share->UserWorkspace_id, "workspace_name" => $ws->name);
        array_push($shares, $row);
    }
}

echo json_encode($shares);
?>