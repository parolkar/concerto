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
date_default_timezone_set("Europe/Dublin");
require_once('../lib/nusoap/nusoap.php');

$query = "";
if (isset($_POST['query']))
{
    $query = $_POST['query'];
    unset($_POST['query']);
}
$server_path = "";
if (isset($_POST['server_path']))
{
    $server_path = $_POST['server_path'];
    unset($_POST['server_path']);
}
$client = new nusoap_client($server_path."?wsdl",true);
$result = $client->call("query", array("query" => $query, "post" => json_encode($_POST)));
echo $result;
?>