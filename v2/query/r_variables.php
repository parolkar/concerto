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

$user = User::get_logged_user();
if ($user == null || !isset($_POST['oid']))
    die(Language::string(85));

$file = Ini::$temp_path.$_POST['oid'].".rs";
if(!file_exists($file)) 
{
    echo json_encode(array("exists"=>0));
    exit();
}

$code = "library(session); ";
$code.= "restore.session(\"".$file."\"); ";
$code.= "print(ls()); ";

$return=0;
$output=array();
exec(Ini::$rscript_path . " -e '".$code."'", $output, $return);

$result = "";
for($i=4;$i<count($output);$i++) $result .= $output[$i]."<br/>";

echo json_encode(array("exists"=>1,"result"=>  $result));
?>