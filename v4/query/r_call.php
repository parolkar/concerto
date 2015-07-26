<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

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

header('Content-type: application/json; charset=utf-8');

$time = time();
if (!isset($ini)) {
    require_once'../Ini.php';
    $ini = new Ini();
}

$wid = null;
if (array_key_exists("wid", $_POST))
    $wid = $_POST['wid'];
$tid = null;
if (array_key_exists("tid", $_POST))
    $tid = $_POST['tid'];
$sid = null;
if (array_key_exists("sid", $_POST))
    $sid = $_POST['sid'];
$hash = null;
if (array_key_exists("hash", $_POST))
    $hash = $_POST['hash'];
$values = null;
if (array_key_exists("values", $_POST))
    $values = $_POST['values'];
$btn_name = null;
if (array_key_exists("btn_name", $_POST))
    $btn_name = $_POST['btn_name'];
$debug = null;
if (array_key_exists("debug", $_POST))
    $debug = $_POST['debug'];
$code = null;
if (array_key_exists("code", $_POST))
    $code = $_POST['code'];

$resume_from_last_template = 0;
if (array_key_exists("resume_from_last_template", $_POST))
    $resume_from_last_template = $_POST['resume_from_last_template'];

$result = TestSession::forward($tid, $sid, $hash, $values, $btn_name, $debug, $time, $wid, $resume_from_last_template == 1, $code);

echo json_encode($result);
?>