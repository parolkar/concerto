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

class TestInstance {

    private $r = null;
    private $pipes;
    public $code_execution_halted = false;
    private $last_action_time;
    private $last_execution_time;
    public $TestSession_id = 0;
    public $UserWorkspace_id = 0;
    public $is_working = false;
    public $is_data_ready = false;
    public $response = "";
    public $error_response = "";
    public $code = "";
    public $is_serializing = false;
    public $is_serialized = false;
    public $is_finished = false;
    public $pending_variables = null;
    public $debug_code_appended = false;
    public $IP = "";

    public function __construct($session_id = 0, $workspace_id = 0, $ip = "") {
        $this->TestSession_id = $session_id;
        $this->UserWorkspace_id = $workspace_id;
        $this->IP = $ip;
    }

    public function is_timedout() {
        if (time() - $this->last_action_time > Ini::$r_instances_timeout) {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->is_timedout() --- Test instance #" . $this->UserWorkspace_id . ":" . $this->TestSession_id . " timedout");
            return true;
        }
        else
            return false;
    }

    public function is_execution_timedout() {
        TestSession::change_db($this->UserWorkspace_id);
        $session = $this->get_TestSession();

        if (time() - $this->last_execution_time > Ini::$r_max_execution_time && $session->status == TestSession::TEST_SESSION_STATUS_WORKING) {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->is_execution_timedout() --- Test instance #" . $this->UserWorkspace_id . ":" . $this->TestSession_id . " execution timedout");
            return true;
        }
        else
            return false;
    }

    public function is_started() {
        if ($this->r == null)
            return false;
        if (is_resource($this->r)) {
            $status = proc_get_status($this->r);
            return $status["running"];
        }
        else
            return false;
    }

    public function start() {
        $env = array();
        if (Ini::$unix_locale != "") {
            $encoding = Ini::$unix_locale;
            $env = array(
                'LANG' => $encoding
            );
        }

        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->start() --- Test instance starting");
        $this->last_action_time = time();
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $workspace = $this->get_UserWorkspace();

        $owner = $workspace->get_owner();
        $userR = $owner->get_UserR();

        if ($userR == null) {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->start() --- Test instance NOT started. R user doesn't exist.");
            return false;
        }

        $this->r = proc_open("sudo -u " . $userR->login . " " . Ini::$path_r_exe . " --no-save --no-restore --quiet", $descriptorspec, $this->pipes, Ini::$path_data, $env);
        if (is_resource($this->r)) {
            if (Ini::$log_server_events) {
                $status = proc_get_status($this->r);
                TestServer::log_debug("TestInstance->start() --- Test instance started, pid: " . $status['pid']);
            }

            if (!stream_set_blocking($this->pipes[0], 0)) {
                if (Ini::$log_server_events) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #0");
                    return false;
                }
            }
            if (!stream_set_blocking($this->pipes[1], 0)) {
                if (Ini::$log_server_events) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #1");
                    return false;
                }
            }
            if (!stream_set_blocking($this->pipes[2], 0)) {
                if (Ini::$log_server_events) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #2");
                    return false;
                }
            }

            return true;
        } else {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->start() --- Error: Test instance NOT started");
            return false;
        }
    }

    public function stop($terminate = false) {
        TestSession::change_db($this->UserWorkspace_id);
        $session = TestSession::from_mysql_id($this->TestSession_id);

        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->stop() --- stopping instance #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);

        if ($this->is_started()) {
            //if ($session->status == TestSession::TEST_SESSION_STATUS_TEMPLATE)
            //    $this->send_close_signal();

            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);

            if ($this->is_execution_timedout() || $terminate) {
                $this->terminate_processess();
            }
            $ter = proc_terminate($this->r, 9);
            $ret = proc_close($this->r);
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->stop() --- Test instance closed with: " . $ter . ":" . $ret);
        } else {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->stop() --- not started");
        }
        return null;
    }

    public function terminate_processess() {
        $status = proc_get_status($this->r);
        if ($status !== false) {
            $ppid = $status['pid'];

            $workspace = $this->get_UserWorkspace();

            $owner = $workspace->get_owner();
            $userR = $owner->get_UserR();

            TestInstance::kill_children($ppid, $userR->login, true);
        }
    }

    public static function kill_children($ppid, $user, $kill_self = true) {
        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->terminate_processess() --- killing children of pid:" . $ppid);

        $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
        foreach ($pids as $pid) {
            if (is_numeric($pid))
                TestInstance::kill_children($pid, $user);
        }
        if (!$kill_self)
            return;
        if (is_numeric($ppid)) {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->terminate_processess() --- killing " . $ppid);

            `sudo -u $user /bin/kill $ppid 2>&1`;
        }
    }

    public function serialize() {
        TestSession::change_db($this->UserWorkspace_id);
        $session = $this->get_TestSession();

        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->serialize() --- Serializing #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);

        $this->response = "";
        $this->error_response = "";
        $this->is_serializing = true;
        $this->is_serialized = false;
        $this->is_working = true;
        $fp = fopen($session->get_RSession_fifo_path(), "r+");
        stream_set_blocking($fp, 0);
        fwrite($fp, "serialize");
        fclose($fp);

        if ($session->debug == 1) {
            $session->status = TestSession::TEST_SESSION_STATUS_SERIALIZED;
            $session->mysql_save();
        }
    }

    public function send_variables($variables = null) {
        TestSession::change_db($this->UserWorkspace_id);
        $session = $this->get_TestSession();

        $variables = json_encode($variables);

        if (Ini::$log_server_events) {
            TestServer::log_debug("TestInstance->send_variables() --- sending variables to session #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
            if (Ini::$log_server_streams)
                TestServer::log_debug($variables, true);
        }


        $this->response = "";
        $this->error_response = "";
        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "r+");
        stream_set_blocking($fp, 0);
        fwrite($fp, $variables);
        fclose($fp);

        if (Ini::$log_server_events) {
            TestServer::log_debug("TestInstance->send_variables() --- finished sending variables to session #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
        }
    }

    public function send_close_signal() {
        TestSession::change_db($this->UserWorkspace_id);
        $session = $this->get_TestSession();

        if ($this->is_serialized)
            return;

        if (Ini::$log_server_events) {
            TestServer::log_debug("TestInstance->send_close_signal() --- sending close signal to session #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
        }


        $this->response = "";
        $this->error_response = "";
        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "r+");
        stream_set_blocking($fp, 0);
        fwrite($fp, "close");
        fclose($fp);

        if (Ini::$log_server_events) {
            TestServer::log_debug("TestInstance->send_close_signal() --- finished sending close signal to session #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
        }
    }

    public function read() {
        TestSession::change_db($this->UserWorkspace_id);

        $this->code_execution_halted = false;
        $this->last_action_time = time();

        $result = "";
        $error = "";
        while ($append = fread($this->pipes[1], 4096)) {
            $result.=$append;
        }

        $session = TestSession::from_mysql_id($this->TestSession_id);
        $change_status = false;

//serialized
        if ($session->status == TestSession::TEST_SESSION_STATUS_SERIALIZED) {
            $this->is_serialized = true;
            $this->is_data_ready = true;

            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->read() --- Serialized instance recognized on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
        } else {

            //template
            if ($session->status == TestSession::TEST_SESSION_STATUS_TEMPLATE && !$this->is_serializing) {
                $this->is_data_ready = true;
                if (Ini::$log_server_events)
                    TestServer::log_debug("TestInstance->read()--- Template instance recognized on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);

                if ($session->release == 1) {
                    $this->is_finished = true;
                    if (Ini::$log_server_events)
                        TestServer::log_debug("TestInstance->read()--- Final template instance recognized on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
                }
            }
        }

        $lines = explode("\n", $result);
        if (count($lines) > 0 && !$this->is_data_ready) {
            $last_line = $lines[count($lines) - 1];

            if ($last_line == "> ") {
                $this->is_data_ready = true;

                if ($session->status != TestSession::TEST_SESSION_STATUS_COMPLETED) {
                    $change_status = true;
                    $session->status = TestSession::TEST_SESSION_STATUS_WAITING;
                } else {
                    $this->is_finished = true;
                    if (Ini::$log_server_events)
                        TestServer::log_debug("TestInstance->read()--- Completed instance recognised on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
                }
            }
            if (strpos($last_line, "+ ") === 0 && $session->debug == 1) {
                $this->is_data_ready = true;

                $change_status = true;
                $session->status = TestSession::TEST_SESSION_STATUS_WAITING_CODE;
                if (Ini::$log_server_events)
                    TestServer::log_debug("TestInstance->read()--- Waiting for code instance recognised on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
            }
        }

        while ($append = fread($this->pipes[2], 4096)) {
            $error.=$append;
        }
        if (strpos($error, 'Execution halted') !== false || $this->is_execution_timedout()) {
            if (Ini::$log_server_events)
                TestServer::log_debug("TestInstance->read()--- Error instance recognised on #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
            $this->code_execution_halted = true;
            $this->is_data_ready = true;

            $change_status = true;
            $session->status = TestSession::TEST_SESSION_STATUS_ERROR;

            if ($this->is_execution_timedout())
                $error.="
TIMEOUT
";
        }

        $this->response.=$result;
        $this->error_response .= $error;

        if (strlen($this->response) > TestServer::$response_limit)
            $this->response = "( ... )
" . substr($this->response, strlen($this->response) - TestServer::$response_limit);
        if (strlen($this->error_response) > TestServer::$response_limit)
            $this->error_response = "( ... )
" . substr($this->error_response, strlen($this->error_response) - TestServer::$response_limit);

        if ($this->is_data_ready) {
            $session->output = $this->response;
            $session->error_output = $this->error_response;
            $change_status = true;
        }

        if ($change_status) {
            $session->mysql_save();
        }

        if ($session->status == TestSession::TEST_SESSION_STATUS_WORKING && $this->pending_variables != null) {
            $this->send_variables($this->pending_variables);
            $this->pending_variables = null;
        }

        if ($this->is_data_ready) {
            $this->last_action_time = time();
            return $this->response;
        }

        return null;
    }

    public function run($code, $variables = null, $reset_responses = true) {
        TestSession::change_db($this->UserWorkspace_id);

        $session = TestSession::from_mysql_id($this->TestSession_id);

        $this->pending_variables = $variables;
        $is_new = false;
        $send_code = "";

        $change_status = false;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_NEW: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code();
                    break;
                }
            case TestSession::TEST_SESSION_STATUS_SERIALIZED: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code(true);
                    break;
                }
            case TestSession::TEST_SESSION_STATUS_TEMPLATE: {
                    $change_status = true;
                    break;
                }
        }

        if ($change_status) {
            $session->status = TestSession::TEST_SESSION_STATUS_WORKING;
            $session->mysql_save();
        }

        if ($code != null)
            $send_code .= $code;
        else {
            if ($is_new) {
                if ($session->debug == 0) {
                    $test = Test::from_mysql_id($session->Test_id);
                    if ($test != null) {

                        //URL params
                        if ($variables != null) {
                            $params = $test->get_parameter_TestVariables();
                            $params_declaration = "";
                            $variables = json_encode($variables);
                            $variables = json_decode($variables, true);
                            foreach ($params as $param) {
                                foreach ($variables as $var_k => $var_v) {
                                    if ($var_k == $param->name) {
                                        $params_declaration .=$var_k . " <- '" . addcslashes($var_v, "'") . "'\n";
                                    }
                                }
                            }
                            $send_code .= $params_declaration;
                        }
                        $send_code.= $test->code . $this->get_final_code();
                    }
                }
            }
        }

        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->run()--- Sending " . strlen($send_code) . " data to test instance #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);
        $this->last_action_time = time();
        $this->last_execution_time = time();

        $lines = explode("\n", $send_code);
        $code = "";
        $i = -1;
        foreach ($lines as $line) {
            $i++;
            $line = trim($line);
            if ($line == "") {
                continue;
            }
            $code .= $line . "\n";
        }
        $this->code = $code;
        if ($reset_responses) {
            $this->response = "";
            $this->error_response = "";
        }

        $bytes = fwrite($this->pipes[0], $code);

        if (Ini::$log_server_events)
            TestServer::log_debug("TestInstance->run()--- " . $bytes . " written to test instance #" . $this->UserWorkspace_id . ":" . $this->TestSession_id);

        $this->is_working = true;
        $this->is_data_ready = false;
    }

    public function get_ini_code($unserialize = false) {
        TestSession::change_db($this->UserWorkspace_id);

        $code = "";
        $session = $this->get_TestSession();
        if ($session == null)
            return($code . "stop('session #" . $this->UserWorkspace_id . ":" . $this->TestSession_id . " does not exist!')
");
        $test = $this->get_Test();
        if ($test == null)
            return($code . "stop('test #" . $this->UserWorkspace_id . ":" . $session->Test_id . " does not exist!')
");

        include Ini::$path_internal . 'SETTINGS.php';
        $path = Ini::$path_data . $session->UserWorkspace_id;

        $workspace = $this->get_UserWorkspace();
        $owner = $workspace->get_owner();
        $main_workspace = $owner->get_main_UserWorkspace();

        $code .= sprintf('
            library(concerto)
            
            Sys.setlocale("LC_ALL","C") 

            CONCERTO_TEST_ID <- %d
            CONCERTO_TEST_SESSION_ID <- %d
            
            CONCERTO_DB_HOST <- "%s"
            CONCERTO_DB_PORT <- as.numeric(%d)
            CONCERTO_DB_LOGIN <- "%s"
            CONCERTO_DB_PASSWORD <- "%s"
            CONCERTO_DB_NAME <- "%s"
            CONCERTO_TEMP_PATH <- "%s"
            CONCERTO_DB_TIMEZONE <- "%s"
            CONCERTO_MEDIA_PATH <- "%s"
            CONCERTO_WORKSPACE_ID <- %s
            CONCERTO_WORKSPACE_PREFIX <- "%s"
            CONCERTO_USER_IP <- "%s"
            CONCERTO_MEDIA_URL <- "%s"
            library(concerto)
                
            concerto:::concerto.initialize(CONCERTO_TEST_ID,CONCERTO_TEST_SESSION_ID,CONCERTO_WORKSPACE_ID,CONCERTO_WORKSPACE_PREFIX,CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_TEMP_PATH,CONCERTO_MEDIA_PATH,CONCERTO_DB_TIMEZONE,%s,CONCERTO_USER_IP,CONCERTO_MEDIA_URL)
            %s
            
            rm(CONCERTO_TEST_ID)
            rm(CONCERTO_TEST_SESSION_ID)
            rm(CONCERTO_DB_HOST)
            rm(CONCERTO_DB_PORT)
            rm(CONCERTO_DB_LOGIN)
            rm(CONCERTO_DB_PASSWORD)
            rm(CONCERTO_DB_NAME)
            rm(CONCERTO_TEMP_PATH)
            rm(CONCERTO_DB_TIMEZONE)
            rm(CONCERTO_MEDIA_PATH)
            rm(CONCERTO_WORKSPACE_ID)
            rm(CONCERTO_WORKSPACE_PREFIX)
            rm(CONCERTO_USER_IP)
            rm(CONCERTO_MEDIA_URL)
            
            if(!exists("onUnserialize")) onUnserialize <- function(lastReturn){ concerto.template.show(HTML="<h3>Session timed out.</h3>",finalize=T) }
            
            %s
            ', $test->id, $this->TestSession_id, $db_host, ($db_port != "" ? $db_port : "3306"), $main_workspace->db_login, $main_workspace->db_password, $workspace->db_name, $path, $mysql_timezone, Ini::$path_internal_media . $owner->id . "/", $workspace->id, Ini::$db_users_db_name_prefix, $this->IP, Ini::$path_external_media . $owner->id . "/", $unserialize ? "FALSE" : "TRUE", $unserialize ? '
                concerto:::concerto.unserialize()
                concerto:::concerto.db.connect(CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_DB_TIMEZONE)' : "", $unserialize ? 'if(exists("onUnserialize")) do.call("onUnserialize",list(lastReturn=rjson::fromJSON("' . addcslashes(json_encode($this->pending_variables), '"') . '")), envir = .GlobalEnv);
' : "");

        if ($unserialize)
            $this->pending_variables = null;
        return $code;
    }

    public function get_final_code() {
        $code = '
concerto:::concerto.finalize()
';
        return $code;
    }

    public function get_TestSession() {
        TestSession::change_db($this->UserWorkspace_id);

        return TestSession::from_mysql_id($this->TestSession_id);
    }

    public function get_Test() {
        TestSession::change_db($this->UserWorkspace_id);
        $session = $this->get_TestSession();
        if ($session == null)
            return null;
        return Test::from_mysql_id($session->Test_id);
    }

    public function get_UserWorkspace() {
        return UserWorkspace::from_mysql_id($this->UserWorkspace_id);
    }

}

?>
