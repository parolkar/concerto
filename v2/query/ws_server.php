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
    require_once '../model/Ini.php';
    $ini = new Ini();
}
require_once(Ini::$internal_path . "lib/nusoap/nusoap.php");

$server = new nusoap_server();
$server->configureWSDL('ConcertoClientWSDL', 'urn:ConcertoClientWSDL');
$server->register('query', // method name
        array('query' => 'xsd:string', 'post' => 'xsd:string'), // input parameters
        array('result' => 'xsd:string'), // output parameters
        'urn:ConcertoClientWSDL', // namespace
        'urn:ConcertoClientWSDL#query', // soapaction
        'rpc', // style
        'encoded', // use
        'Executes query.'            // documentation
);

function curl_post($query, $post)
{
    $post_fields = "";
    foreach ($post as $k => $v)
    {
        if ($post_fields != "") $post_fields.="&";
        $post_fields.=$k . "=" . urlencode($v);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, Ini::$external_path . $query);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function query($query, $post)
{
    $args = array();
    foreach (json_decode($post) as $k => $v)
    {
        $args[$k]=$v;
    }
    $data = curl_post($query, $args);
    return $data;
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
