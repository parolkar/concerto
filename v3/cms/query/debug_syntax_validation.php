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

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null)
{
    echo json_encode(array("result" => -1));
    exit();
}

$session = TestSession::start_new($_POST['Test_id']);
$test = $session->get_Test();

if ($test == null)
{
    echo json_encode(array("result" => -2));
    exit();
}

$section = TestSection::from_property(array("Test_id" => $test->id,"counter"=>$_POST['counter']),false);

$close = true;
$next_section = $section->get_next_TestSection();
$next_counter = 0;
if($next_section!=null) $next_counter = $next_section->counter;


$result = $session->debug_syntax($section->id, $close);

$session->remove();

echo json_encode(array("result" => 0, "response" => $result, "next_counter"=>$next_counter));
?>