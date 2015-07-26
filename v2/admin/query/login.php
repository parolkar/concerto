<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!isset($ini))
{
    require_once '../../model/Ini.php';
    $ini = new Ini();
}

$user = User::log_in($_POST['login'], $_POST['password']);
if ($user != null)
{
    echo json_encode(array("success" => 1, "id" => $user->id, "login" => $user->login, "md5password" => $user->md5_password));
}
else echo json_encode(array("success" => 0))
    
?>