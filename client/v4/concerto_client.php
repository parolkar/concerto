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
$time = time();

require_once 'SETTINGS.php';
require_once 'nusoap/nusoap.php';

$method = "run_test";
if (array_key_exists("method", $_POST))
    $method = $_POST['method'];

$client = new nusoap_client($ws_url . "remote.php?wsdl", true);
switch ($method) {
    case "get_last_html": {
            $result = $client->call("get_last_html", array(
                "sid" => array_key_exists("sid", $_POST) ? $_POST['sid'] : null,
                "hash" => array_key_exists("hash", $_POST) ? $_POST['hash'] : null,
                "pass" => md5($ws_password),
                "wid" => array_key_exists("wid", $_POST) ? $_POST['wid'] : null
            ));
            break;
        }
    case "get_returns": {
            $result = $client->call("get_returns", array(
                "sid" => array_key_exists("sid", $_POST) ? $_POST['sid'] : null,
                "hash" => array_key_exists("hash", $_POST) ? $_POST['hash'] : null,
                "pass" => md5($ws_password),
                "wid" => array_key_exists("wid", $_POST) ? $_POST['wid'] : null
            ));
            break;
        }
    default: {
            $result = $client->call("run_test", array(
                "tid" => array_key_exists("tid", $_POST) ? $_POST['tid'] : null,
                "sid" => array_key_exists("sid", $_POST) ? $_POST['sid'] : null,
                "hash" => array_key_exists("hash", $_POST) ? $_POST['hash'] : null,
                "values" => array_key_exists("values", $_POST) ? json_encode($_POST['values']) : null,
                "btn_name" => array_key_exists("btn_name", $_POST) ? $_POST['btn_name'] : null,
                "debug" => array_key_exists("debug", $_POST) ? $_POST['debug'] : null,
                "pass" => md5($ws_password),
                "time" => $time,
                "wid" => array_key_exists("wid", $_POST) ? $_POST['wid'] : null,
                "resume_from_last_template" => array_key_exists("resume_from_last_template", $_POST) ? $_POST['resume_from_last_template'] : "0"
            ));
            break;
        }
}
if ($result == -1)
    die("Authentication failed!");
echo $result;
