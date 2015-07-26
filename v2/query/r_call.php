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

$code = "########## SESSION CODE STARTS\r\nsystem_proc_time_start<-proc.time()\r\n";

//loading session file
$session_exists = false;
$session_file = Ini::$temp_path . $_POST['SessionID'] . ".rs";
if (file_exists($session_file))
{
    $session_exists = true;
    $code .= "sink(stdout(), type='message')\r\n";
    $code .= "library(session)\r\n";
    $code .= "restore.session(\"" . $session_file . "\")\r\n";
}
else
{
    $code .= "sink(stdout(), type='message')\r\n";
    $code .= "library(session)\r\n";
    $code .= "temp_path <- '" . Ini::$temp_path . "'\r\n";
    $code .= "source('" . Ini::$main_methods_r_path . "')\r\n";

    for ($i = 0; $i < count($_POST['var_name']); $i++)
    {
        $k = $_POST['var_name'][$i];
        $v = $_POST['var_value'][$i];
        if ($k == "") continue;
        if ($k == "SessionID" || $k == "button_name" || $k == "template_id")
                continue;
        ItemButton::delete_rout_variable($_POST['SessionID'], $k);

        ItemButton::insert_rout_variable($_POST['SessionID'], $k, $v);

        $code.= $k . " <- '" . mysql_real_escape_string($v) . "'\r\n";
    }
}
unset($_POST['var_name']);
unset($_POST['var_value']);

$code .= "drv <- dbDriver('MySQL')\r\n";
$code .= "for(con in dbListConnections(drv)) { dbDisconnect(con) }\r\n";
$code .= "con <- dbConnect(drv, user = db_login, password = db_password, dbname = db_name, host = db_host, port = db_port)\r\n";

//variables
if (isset($_POST['ctr_name']) && isset($_POST['ctr_value']))
{
    for ($i = 0; $i < count($_POST['ctr_name']); $i++)
    {
        $k = $_POST['ctr_name'][$i];
        $v = $_POST['ctr_value'][$i];
        if ($k == "") continue;
        ItemButton::delete_rout_variable($_POST['SessionID'], $k);

        ItemButton::insert_rout_variable($_POST['SessionID'], $k, $v);

        $code .= $k . " <- '" . mysql_real_escape_string($v) . "'\r\n";
    }
    unset($_POST['ctr_name']);
    unset($_POST['ctr_value']);
}

foreach ($_POST as $k => $v)
{
    if ($k == "" || ($session_exists && $k == "SessionID")) continue;
    ItemButton::delete_rout_variable($_POST['SessionID'], $k);

    ItemButton::insert_rout_variable($_POST['SessionID'], $k, $v);

    $code .= $k . " <- '" . mysql_real_escape_string($v) . "'\r\n";
}

$code .= "########## SESSION CODE ENDS\r\n\r\n";

//clicked button
$current_item = Item::from_mysql_id($_POST['template_id']);
$clicked_button = $current_item->get_Button($_POST['button_name']);

$command = $clicked_button->function;

$isCommandEmpty = (trim($command) == "");
$output = array();
$return = 0;
if (!$isCommandEmpty)
{
    $command = $code . "########## BUTTON '" . $clicked_button->name . " CODE STARTS\r\n" . $command . "\r\n########## BUTTON '" . $clicked_button->name . " CODE ENDS\r\n\r\n";
    $command.="########## SESSION CODE STARTS\r\n";
    $command .= "save.session(\"" . $session_file . "\")\r\n";
    $command .= "system_proc_time <- proc.time()-system_proc_time_start\r\n";
    $command .= "print(paste('Total elapsed time: ',system_proc_time[3],' secs',sep=''))\r\n";
    $command.="########## SESSION CODE ENDS\r\n";
    $tmp_file = Ini::$temp_path . $_POST['SessionID'] . ".r";
    $file = fopen($tmp_file, 'w');
    fwrite($file, $command);
    fclose($file);

    include'../SETTINGS.php';
    exec(Ini::$rscript_path . " " . $tmp_file . " --args " . $db_host . " " . ($db_port != "" ? substr($db_port, 1) : "3306") . " " . $db_user . " " . $db_password . " " . $db_name, $output, $return);

    $file = fopen($tmp_file, 'a');
    fwrite($file, "\r\n\r\n----------OUTPUT return: " . $return . "----------\r\n\r\n");
    fwrite($file, print_r($output, true));
    fclose($file);
}

$variables = array();
$sql = sprintf("SELECT * FROM `r_out` WHERE 
	`Session_ID`='%d' 
	ORDER BY `ID` ASC", $_POST['SessionID']);
$z = mysql_query($sql);
while ($r = mysql_fetch_array($z))
{
    $variables[$r['Variable']] = $r['Value'];
}

$variables['SessionID'] = $_POST['SessionID'];

$variables["debug_rcode"] = $command;
$variables["debug_return"] = $return;
$variables["debug_output"] = "";
foreach ($output as $line) $variables["debug_output"].=$line . "<br/>";

echo json_encode($variables);
?>