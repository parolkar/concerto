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

$user = User::from_property(array("login" => $_POST['login']), false);
if ($user == null) {
    echo json_encode(array("result" => -1));
    exit();
}

$link = Ini::$path_external . "cms/?pruid=" . $user->id . "&pruh=" . $user->calculate_password_recovery_hash();

User::mail_utf8($user->email, "no-reply@concerto.e-psychometrics.com", Language::string(428), sprintf(Language::string(429), $link));
echo json_encode(array("result" => 0));
?>