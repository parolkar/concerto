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
if ($logged_user == null)
    header("Location: " . Ini::$path_external . "cms/index.php");

if (!is_array($_GET['oid']))
    $oid = array($_GET['oid']);
else
    $oid = $_GET['oid'];

$xml = new DOMDocument('1.0', 'UTF-8');
$export = $xml->createElement("export");
$export->setAttribute("version", Ini::$version);
$xml->appendChild($export);
foreach ($oid as $id) {
    $obj = $_GET['class_name']::from_mysql_id($id);
    if (!$logged_user->is_object_readable($obj))
        die(Language::string(81));

    $xml_elem = new DOMDocument('1.0', 'UTF-8');
    $xml_elem->loadXML($obj->export());
    $xpath = new DOMXPath($xml_elem);
    $search = $xpath->query("/export/*");
    foreach ($search as $elem) {
        $newNode = $xml->importNode($elem, true);
        $export->appendChild($newNode);
    }
}

header('Content-Disposition: attachment; filename="export_' . $_GET['class_name'] . '.concerto"');
header('Content-Type: application/x-download');

echo gzcompress($xml->saveXML(), 1);
?>