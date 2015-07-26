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

if (!isset($ini)) {
    require_once '../model/Ini.php';
    $ini = new Ini();
}

if (isset($_POST['hash']))
    $item = Item::from_hash($_POST['hash']);
else
    $item = null;

$sid = User::initialize_session();

$variables = array();

foreach ($_POST as $k => $v) {
    ItemButton::insert_rout_variable($sid, mysql_real_escape_string($k), mysql_real_escape_string($v));
    $variables[$k] = $v;
}

ItemButton::insert_rout_variable($sid, 'template_id', mysql_real_escape_string($item->id));
$variables["template_id"] = $item->id;

ItemButton::insert_rout_variable($sid, 'SessionID', $sid);
$variables['SessionID'] = $sid;

ItemButton::insert_rout_variable($sid, 'IP', $_SERVER['REMOTE_ADDR']);
$variables['IP'] = $_SERVER['REMOTE_ADDR'];

ItemButton::insert_rout_variable($sid, 'browser', $_SERVER["HTTP_USER_AGENT"]);
$variables['browser'] = $_SERVER["HTTP_USER_AGENT"];

echo json_encode($variables);
?>