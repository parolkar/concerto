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

$client = new nusoap_client(Ini::$path_online_library_ws . "?wsdl", true);
$result = $client->call("download", array(
    "id" => $_POST['oid']
        ));

if ($result != false) {
    $obj = new $_POST['class_name']();

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->loadXML(gzuncompress(base64_decode($result)));

    $response = $obj->import_XML($xml);
    if (is_numeric($response)) {
        $oid = $response;
        $result = 0;
        echo json_encode(array("result" => $result, "oid" => $oid));
        exit();
    } else {
        echo $response;
    }
} else
    echo json_encode(array("result" => -3));