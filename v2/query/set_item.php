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

$item = Item::from_mysql_id($_POST['template_id']);
$item->HTML = stripslashes($item->HTML);
while (strpos($item->HTML, "{{template:") !== false) {
    $html = $item->HTML;
    $index = strpos($item->HTML, "{{template:");
    $html = substr($html, $index + 11);
    $id = substr($html, 0, strpos($html, "}}"));
    $subitem = Item::from_mysql_id($id);
    $item->HTML = str_replace("{{template:" . $id . "}}", $subitem->HTML, $item->HTML);
}

$vars_array = array(
    "HTML" => $item->HTML,
    "timer" => $item->timer,
    "default_button" => ($item->get_default_Button() != null ? $item->get_default_Button()->name : "<none>")
);

echo json_encode($vars_array);
?>