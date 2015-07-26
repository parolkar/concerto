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
    require_once'Ini.php';
    $ini = new Ini();
}

$server = new nusoap_server();
$server->configureWSDL('ConcertoClientWSDL', 'urn:ConcertoClientWSDL');

$server->register('run_test', // method name
        array(
    'tid' => 'xsd:string',
    'sid' => 'xsd:string',
    'hash' => 'xsd:string',
    'btn_name' => 'xsd:string',
    'values' => 'xsd:string',
    'debug' => 'xsd:string',
    'pass' => 'xsd:string',
    'time' => 'xsd:string',
    'resume_from_last_template' => 'xsd:string'
        ), array(
    'result' => 'xsd:string'
        ), 'urn:ConcertoClientWSDL', // namespace
        'urn:ConcertoClientWSDL#run_test', // soapaction
        'rpc', // style
        'encoded', // use
        'start/resume Concerto test'            // documentation
);

$server->register('get_last_html', // method name
        array(
    'sid' => 'xsd:string',
    'hash' => 'xsd:string',
    'pass' => 'xsd:string'
        ), array(
    'result' => 'xsd:string'
        ), 'urn:ConcertoClientWSDL', // namespace
        'urn:ConcertoClientWSDL#get_last_html', // soapaction
        'rpc', // style
        'encoded', // use
        'get Concerto test session last HTML'            // documentation
);

$server->register('get_returns', // method name
        array(
    'sid' => 'xsd:string',
    'hash' => 'xsd:string',
    'pass' => 'xsd:string'
        ), array(
    'result' => 'xsd:string'
        ), 'urn:ConcertoClientWSDL', // namespace
        'urn:ConcertoClientWSDL#get_last_html', // soapaction
        'rpc', // style
        'encoded', // use
        'get Concerto test session return variables'            // documentation
);

function authorize_WS($pass) {
    if (md5(Ini::$remote_client_password) == $pass)
        return true;
    else
        return false;
}

function run_test($tid, $sid, $hash, $btn_name, $values, $debug, $pass, $time, $resume_from_last_template) {
    if (!authorize_WS($pass))
        return false;

    $result = TestSession::forward($tid, $sid, $hash, json_decode($values), $btn_name, $debug, $time, $resume_from_last_template == "1");
    return json_encode($result);
}

function get_last_html($sid, $hash, $pass) {
    if (!authorize_WS($pass))
        return false;

    $session = TestSession::from_property(array("id" => $sid, "hash" => $hash), false);
    if ($session == null)
        return false;
    return json_encode(array("HTML" => $session->HTML));
}

function get_returns($sid, $hash, $pass) {
    if (!authorize_WS($pass))
        return false;

    $session = TestSession::from_property(array("id" => $sid, "hash" => $hash), false);
    if ($session == null)
        return false;

    $return = TestSessionReturn::from_property(array("TestSession_id" => $sid));
    $result = array();
    foreach ($return as $ret) {
        $result[$ret->name] = $ret->value;
    }
    return json_encode($result);
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>