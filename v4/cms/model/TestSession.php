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

class TestSession extends OTable {

    public static $mysql_table_name = "TestSession";
    public $Test_id = 0;
    public $status = 0;
    public $time_limit = 0;
    public $HTML = "";
    public $head = "";
    public $Template_id = 0;
    public $Template_UserWorkspace_id = 0;
    public $time_tamper_prevention = 0;
    public $hash = "";
    public $debug = 0;
    public $release = 0;
    public $output = "";
    public $error_output = "";
    public $state = "";
    public $UserWorkspace_id = 0;
    public $loader_Template_id = 0;
    public $effect_show = "none";
    public $effect_show_options = "";
    public $effect_hide = "none";
    public $effect_hide_options = "";
    public $loader_HTML = "";
    public $loader_head = "";
    public $loader_effect_show = "none";
    public $loader_effect_show_options = "";
    public $loader_effect_hide = "none";
    public $loader_effect_hide_options = "";
    public $loader_UserWorkspace_id = 0;
    public $resume_declined = 0;

    const TEST_SESSION_STATUS_NEW = 0;
    const TEST_SESSION_STATUS_WORKING = 1;
    const TEST_SESSION_STATUS_TEMPLATE = 2;
    const TEST_SESSION_STATUS_COMPLETED = 3;
    const TEST_SESSION_STATUS_ERROR = 4;
    const TEST_SESSION_STATUS_TAMPERED = 5;
    const TEST_SESSION_STATUS_WAITING = 6;
    const TEST_SESSION_STATUS_SERIALIZED = 7;
    const TEST_SESSION_STATUS_QTI_INIT = 8;
    const TEST_SESSION_STATUS_QTI_RP = 9;
    const TEST_SESSION_STATUS_WAITING_CODE = 10;

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public function get_Template() {
        $ws = UserWorkspace::from_mysql_id($this->Template_UserWorkspace_id);
        if ($ws == null)
            return null;
        $sql = sprintf("SELECT * FROM `%s`.`%s` WHERE `id`=%d", $ws->db_name, Template::get_mysql_table(), $this->Template_id);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return Template::from_mysql_result($r);
        }
        return null;
    }

    public function get_UserWorkspace() {
        return UserWorkspace::from_mysql_id($this->UserWorkspace_id);
    }

    public function register() {
        if (array_key_exists("sids", $_SESSION)) {
            if (array_key_exists(session_id(), $_SESSION['sids'])) {
                TestSession::unregister($_SESSION['sids'][session_id()], $this->UserWorkspace_id);
                $_SESSION['sids'][session_id()] = $this->UserWorkspace_id . "-" . $this->id;
            }
            else
                $_SESSION['sids'][session_id()] = $this->UserWorkspace_id . "-" . $this->id;
        }
        else {
            $_SESSION['sids'] = array();
            $_SESSION['sids'][session_id()] = $this->UserWorkspace_id . "-" . $this->id;
        }
    }

    public static function unregister($id, $returnDB) {
        $ids = explode("-", $id);
        TestSession::change_db($ids[0]);
        $obj = TestSession::from_mysql_id($ids[1]);
        if ($obj != null)
            $obj->remove();
        unset($_SESSION['sids'][session_id()]);
        TestSession::change_db($returnDB);
    }

    public static function start_new($wid, $test_id, $debug = false) {

        $session = new TestSession();
        $session->Test_id = $test_id;
        $session->debug = ($debug ? 1 : 0);
        $session->UserWorkspace_id = $wid;

        $lid = $session->mysql_save();

        $session = TestSession::from_mysql_id($lid);

        if (!$debug) {
            $sql = sprintf("UPDATE `%s` SET `session_count`=`session_count`+1 WHERE `%s`.`id`=%d", Test::get_mysql_table(), Test::get_mysql_table(), $test_id);
            mysql_query($sql);
        }

        $test = $session->get_Test();
        if ($test != null) {
            posix_mkfifo($session->get_RSession_fifo_path(), 0774);
        }

        if ($debug)
            $session->register();

        return $session;
    }

    public function remove($close = true) {
        if ($close)
            $this->close();
        $this->mysql_delete();
    }

    public function mysql_delete() {
        parent::mysql_delete();
        $this->remove_returns();
    }

    public function remove_returns() {
        $sql = sprintf("DELETE FROM `%s` WHERE `TestSession_id`=%d", TestSessionReturn::get_mysql_table(), $this->id);
        mysql_query($sql);
    }

    public function close() {
        if (TestServer::get_server_status() == TestServer::SERVER_STATUS_RUNNING)
            TestServer::send(json_encode(array("type" => 1, "code" => "close", "workspace_id" => $this->UserWorkspace_id, "session_id" => $this->id, "hash" => $this->hash)));
        $this->remove_files();
    }

    public function serialize() {
        if (TestServer::get_server_status() == TestServer::SERVER_STATUS_RUNNING)
            TestServer::send(json_encode(array("type" => 1, "code" => "serialize", "workspace_id" => $this->UserWorkspace_id, "session_id" => $this->id, "hash" => $this->hash)));
    }

    public function remove_files() {
        if (file_exists($this->get_RSession_file_path()))
            unlink($this->get_RSession_file_path());
        if (file_exists($this->get_RSession_fifo_path()))
            unlink($this->get_RSession_fifo_path());
    }

    public function does_RSession_file_exists() {
        if (file_exists($this->get_RSession_file_path()))
            return true;
        else
            return false;
    }

    public function RCall($values = null, $code = null, $resume_from_last_template = false) {
        if (Ini::$log_server_events)
            TestServer::log_debug("TestSession->RCall --- R call initiated #" . session_id());

        //resume from last template
        if ($resume_from_last_template) {

            $response = array(
                "data" => array(
                    "HEAD" => $this->head,
                    "HASH" => $this->hash,
                    "TIME_LIMIT" => $this->time_limit,
                    "HTML" => $this->HTML,
                    "TEST_ID" => $this->Test_id,
                    "TEST_SESSION_ID" => $this->id,
                    "STATUS" => TestSession::TEST_SESSION_STATUS_TEMPLATE,
                    "TEMPLATE_ID" => $this->Template_id,
                    "LOADER_TEMPLATE_ID" => $this->loader_Template_id,
                    "FINISHED" => 0,
                    "EFFECT_SHOW" => $this->effect_show,
                    "EFFECT_HIDE" => $this->effect_hide,
                    "EFFECT_SHOW_OPTIONS" => $this->effect_show_options,
                    "EFFECT_HIDE_OPTIONS" => $this->effect_hide_options,
                    "LOADER_HTML" => $this->loader_HTML,
                    "LOADER_HEAD" => $this->loader_head,
                    "LOADER_EFFECT_SHOW" => $this->loader_effect_show,
                    "LOADER_EFFECT_SHOW_OPTIONS" => $this->loader_effect_show_options,
                    "LOADER_EFFECT_HIDE" => $this->loader_effect_hide,
                    "LOADER_EFFECT_HIDE_OPTIONS" => $this->loader_effect_hide_options,
                    "LOADER_HTML" => $this->loader_HTML,
                    "LOADER_HEAD" => $this->loader_head,
                    "LOADER_EFFECT_SHOW" => $this->loader_effect_show,
                    "LOADER_EFFECT_SHOW_OPTIONS" => $this->loader_effect_show_options,
                    "LOADER_EFFECT_HIDE" => $this->loader_effect_hide,
                    "LOADER_EFFECT_HIDE_OPTIONS" => $this->loader_effect_hide_options
                )
            );
            return $response;
        }

        //R server connection
        $command_obj = json_encode(array(
            "workspace_id" => $this->UserWorkspace_id,
            "session_id" => $this->id,
            "hash" => $this->hash,
            "values" => $values,
            "code" => $code,
            "type" => 0,
            "IP" => $_SERVER["REMOTE_ADDR"]
        ));

        if (Ini::$log_server_events)
            TestServer::log_debug("TestSession->RCall --- checking for server");
        if (TestServer::get_server_status() == TestServer::SERVER_STATUS_STOPPED) {
            TestServer::start_process();
            TestServer::wait_until_started();
        } else if (TestServer::get_server_status() == TestServer::SERVER_STATUS_STARTING) {
            TestServer::wait_until_started();
        }
        if (Ini::$log_server_events)
            TestServer::log_debug("TestSession->RCall --- server found, trying to send");

        $response = TestServer::send($command_obj);
        $result = json_decode(trim($response));
        if (Ini::$log_server_events)
            TestServer::log_debug("TestSession->RCall --- sent and recieved response");

        $status = TestSession::TEST_SESSION_STATUS_ERROR;
        $removed = false;
        $release = 0;
        $html = "";
        $head = "";
        $Template_id = 0;
        $debug = 0;
        $hash = "";
        $time_limit = 0;
        $Test_id = 0;
        $finished = 0;

        $loader_Template_id = 0;
        $loader_HTML = "";
        $loader_head = "";
        $loader_effect_show = "none";
        $loader_effect_hide = "none";
        $loader_effect_show_options = "";
        $loader_effect_hide_options = "";

        $effect_show = "none";
        $effect_hide = "none";
        $effect_show_options = "";
        $effect_hide_options = "";
        $state = "[]";

        $output = "";
        $thisSession = TestSession::from_mysql_id($this->id);

        $return = $result->return;

        if ($thisSession != null) {

            $output = $thisSession->output;
            $error_output = $thisSession->error_output;
            $state = $thisSession->state;

            $status = $thisSession->status;
            $release = $thisSession->release;
            $html = $thisSession->HTML;
            $head = $thisSession->head;
            $Template_id = $thisSession->Template_id;
            $debug = $thisSession->debug;
            $hash = $thisSession->hash;
            $time_limit = $thisSession->time_limit;
            $Test_id = $thisSession->Test_id;

            $loader_Template_id = $thisSession->loader_Template_id;
            $loader_HTML = $thisSession->loader_HTML;
            $loader_head = $thisSession->loader_head;
            $loader_effect_hide = $thisSession->loader_effect_hide;
            $loader_effect_hide_options = $thisSession->loader_effect_hide_options;
            $loader_effect_show = $thisSession->loader_effect_show;
            $loader_effect_show_options = $thisSession->loader_effect_show_options;

            $effect_hide = $thisSession->effect_hide;
            $effect_hide_options = $thisSession->effect_hide_options;
            $effect_show = $thisSession->effect_show;
            $effect_show_options = $thisSession->effect_show_options;

            if ($return != 0) {
                $status = TestSession::TEST_SESSION_STATUS_ERROR;
            }

            if ($status == TestSession::TEST_SESSION_STATUS_WORKING && $release == 1)
                $status = TestSession::TEST_SESSION_STATUS_COMPLETED;

            $thisSession->status = $status;
            $thisSession->mysql_save();

            switch ($status) {
                case TestSession::TEST_SESSION_STATUS_COMPLETED: {
                        if ($debug) {
                            TestSession::unregister($thisSession->UserWorkspace_id . "-" . $thisSession->id, $thisSession->UserWorkspace_id);
                            $removed = true;
                        }
                        else
                            $thisSession->close();
                        break;
                    }
                case TestSession::TEST_SESSION_STATUS_ERROR: {
                        if ($debug) {
                            TestSession::unregister($thisSession->UserWorkspace_id . "-" . $thisSession->id, $thisSession->UserWorkspace_id);
                            $removed = true;
                        } else {
                            $thisSession->close();
                            if (Ini::$log_r_errors)
                                $thisSession->log_error(nl2br(htmlspecialchars($error_output, ENT_QUOTES)));
                        }
                        break;
                    }
                case TestSession::TEST_SESSION_STATUS_TAMPERED: {
                        if ($debug) {
                            TestSession::unregister($thisSession->UserWorkspace_id . "-" . $thisSession->id, $thisSession->UserWorkspace_id);
                            $removed = true;
                        }
                        else
                            $thisSession->close();
                        break;
                    }
                case TestSession::TEST_SESSION_STATUS_TEMPLATE: {
                        if ($release) {
                            if ($debug) {
                                TestSession::unregister($thisSession->UserWorkspace_id . "-" . $thisSession->id, $thisSession->UserWorkspace_id);
                                $removed = true;
                            }
                            else
                                $thisSession->close();
                        }
                        break;
                    }
            }
        }
        else
            $removed = true;

        $debug_data = false;
        $logged_user = User::get_logged_user();
        if ($logged_user != null)
            $debug_data = true;

        if ($release == 1 || $status == TestSession::TEST_SESSION_STATUS_COMPLETED || $status == TestSession::TEST_SESSION_STATUS_ERROR || $status == TestSession::TEST_SESSION_STATUS_TAMPERED) {
            $finished = 1;
        }

        $response = array(
            "data" => array(
                "HEAD" => $head,
                "HASH" => $hash,
                "TIME_LIMIT" => $time_limit,
                "HTML" => $html,
                "TEST_ID" => $Test_id,
                "TEST_SESSION_ID" => $this->id,
                "STATUS" => $status,
                "TEMPLATE_ID" => $Template_id,
                "FINISHED" => $finished,
                "LOADER_TEMPLATE_ID" => $loader_Template_id,
                "LOADER_HTML" => $loader_HTML,
                "LOADER_HEAD" => $loader_head,
                "LOADER_EFFECT_SHOW" => $loader_effect_show,
                "LOADER_EFFECT_SHOW_OPTIONS" => $loader_effect_show_options,
                "LOADER_EFFECT_HIDE" => $loader_effect_hide,
                "LOADER_EFFECT_HIDE_OPTIONS" => $loader_effect_hide_options,
                "EFFECT_SHOW" => $effect_show,
                "EFFECT_HIDE" => $effect_hide,
                "EFFECT_SHOW_OPTIONS" => $effect_show_options,
                "EFFECT_HIDE_OPTIONS" => $effect_hide_options
            )
        );

        if ($debug_data) {

            if (!is_array($response))
                $response = array();
            if ($output != null) {
                if (strpos(trim($output), ">") !== 0 && strpos(trim($output), "[") !== 0) {
                    $output = "> " . trim($output);
                }
            }

            if ($debug) {
                $state = json_decode($state, true);
                if ($state != null) {
                    foreach ($state as $k => $v) {
                        $state[$k] = htmlspecialchars($v, ENT_QUOTES);
                    }
                    $state = json_encode($state);
                } else {
                    $state = "[]";
                }

                $response["debug"] = array(
                    "return" => $return,
                    "output" => nl2br(htmlspecialchars($output, ENT_QUOTES)),
                    "error_output" => nl2br(htmlspecialchars($error_output, ENT_QUOTES)),
                    "state" => nl2br($state)
                );
            }
        }

        if (Ini::$timer_tamper_prevention && !$removed) {
            $sql = sprintf("UPDATE `%s` SET `time_tamper_prevention`=%d WHERE `id`=%d", TestSession::get_mysql_table(), time(), $this->id);
            mysql_query($sql);
        }

        return $response;
    }

    public static function change_db($UserWorkspace_id) {
        $wokspace = UserWorkspace::from_mysql_id($UserWorkspace_id);
        if ($wokspace != null) {
            mysql_select_db($wokspace->db_name);
        }
    }

    public function get_RSession_file_path() {
        return Ini::$path_data . $this->UserWorkspace_id . "/session_" . $this->id . ".Rs";
    }

    public function get_RSession_fifo_path() {
        return Ini::$path_data . $this->UserWorkspace_id . "/fifo_" . $this->id;
    }

    public function mysql_save() {
        $new = false;
        if ($this->id == 0)
            $new = true;
        $lid = parent::mysql_save();
        if ($new) {
            $ts = TestSession::from_mysql_id($lid);
            $ts->hash = TestSession::generate_hash($lid);
            $ts->mysql_save();
        }
        return $lid;
    }

    public static function generate_hash($id) {
        return md5("cts" . $id . "." . rand(0, 100) . "." . time());
    }

    public static function authorized_session($wid, $id, $hash) {
        $session = TestSession::from_property(array("id" => $id, "UserWorkspace_id" => $wid, "hash" => $hash), false);
        if ($session == null)
            return null;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_ERROR: return null;
            case TestSession::TEST_SESSION_STATUS_TAMPERED: return null;
            case TestSession::TEST_SESSION_STATUS_COMPLETED: return null;
        }
        return $session;
    }

    public static function forward($tid, $sid, $hash, $values, $btn_name, $debug, $time, $wid = null, $resume_from_last_template = false, $code = null) {
        $workspace = UserWorkspace::from_mysql_id($wid);
        if ($workspace != null) {
            mysql_select_db($workspace->db_name);
        } else {
            return false;
        }

        if (is_string($values))
            $values = json_decode($values, true);

        $session = null;
        $result = array();
        if ($wid != null && $sid != null && $hash != null) {
            $session = TestSession::authorized_session($wid, $sid, $hash);

            if ($session != null) {

                if ($btn_name != null) {
                    if ($values != null) {
                        $values["LAST_PRESSED_BUTTON_NAME"] = $btn_name;
                    }
                }

                if (Ini::$timer_tamper_prevention && $session->time_limit > 0 && $time - $session->time_tamper_prevention - Ini::$timer_tamper_prevention_tolerance > $session->time_limit) {
                    if ($session->debug == 1)
                        TestSession::unregister($session->UserWorkspace_id . "-" . $session->id, $session->UserWorkspace_id);
                    else
                        $session->close();

                    $result = array(
                        "data" => array(
                            "HASH" => $hash,
                            "TIME_LIMIT" => 0,
                            "HTML" => "",
                            "TEST_ID" => 0,
                            "TEST_SESSION_ID" => $sid,
                            "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                            "TEMPLATE_ID" => 0,
                            "HEAD" => "",
                            "FINISHED" => 1
                        )
                    );
                    if ($session->debug == 1) {
                        $result["debug"] = array(
                            "return" => 0,
                            "output" => "",
                            "state" => "[]"
                        );
                    }
                } else {
                    $result = $session->RCall($values, $code, $resume_from_last_template);
                }
            } else {
                $result = array(
                    "data" => array(
                        "HASH" => $hash,
                        "TIME_LIMIT" => 0,
                        "HTML" => "",
                        "TEST_ID" => 0,
                        "TEST_SESSION_ID" => $sid,
                        "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                        "TEMPLATE_ID" => 0,
                        "HEAD" => "",
                        "FINISHED" => 1
                    ),
                    "debug" => array(
                        "return" => 0,
                        "output" => "",
                        "state" => "[]"
                    )
                );
            }
        } else {
            if ($wid != null && $tid != null) {
                if ($debug == 1)
                    $debug = true;
                else
                    $debug = false;

                $test = Test::from_mysql_id($tid);

                if ($test->type != 2)
                    $session = TestSession::start_new($wid, $tid, $debug);

                if ($values == null)
                    $values = array();

                if ($test != null && $test->type != 2) {
                    $values = $test->verified_input_values($values);
                } else {
                    $result = array(
                        "data" => array(
                            "HASH" => $hash,
                            "TIME_LIMIT" => 0,
                            "HTML" => "",
                            "TEST_ID" => $tid,
                            "TEST_SESSION_ID" => $sid,
                            "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                            "TEMPLATE_ID" => 0,
                            "HEAD" => "",
                            "FINISHED" => 1
                        ),
                        "debug" => array(
                            "return" => 0,
                            "output" => "",
                            "state" => "[]"
                        )
                    );
                    return $result;
                }

                $result = $result = $session->RCall($values, $code, $resume_from_last_template);
            }
        }
        return $result;
    }

    public function log_error($message = null, $type = TestSessionLog::TEST_SESSION_LOG_TYPE_R) {
        if ($message == null)
            $message = $this->error_output;
        TestSession::create_session_log($type, $this->UserWorkspace_id, $this->id, $this->Test_id, $this->hash, $this->Template_id, $this->Template_UserWorkspace_id, $this->status, $message, $_SERVER['REMOTE_ADDR'], $_SERVER["HTTP_USER_AGENT"]);
    }

    public static function create_session_log($type, $wid, $sid, $tid, $hash, $template, $template_workspace, $status, $message, $ip, $browser) {
        $log = new TestSessionLog();
        $log->UserWorkspace_id = $wid;
        $log->IP = $ip;
        $log->Template_id = $template;
        $log->Template_UserWorkspace_id = $template_workspace;
        $log->TestSession_id = $sid;
        $log->Test_id = $tid;
        $log->browser = $browser;
        $log->hash = $hash;
        $log->message = $message;
        $log->status = $status;
        $log->type = $type;
        $log->mysql_save();
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`TestSession` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `status` tinyint(4) NOT NULL,
            `time_limit` int(11) NOT NULL,
            `HTML` text NOT NULL,
            `head` text NOT NULL,
            `effect_show` text NOT NULL,
            `effect_hide` text NOT NULL,
            `effect_show_options` text NOT NULL,
            `effect_hide_options` text NOT NULL,
            `loader_Template_id` bigint(20) NOT NULL,
            `loader_HTML` text NOT NULL,
            `loader_head` text NOT NULL,
            `loader_effect_show` text NOT NULL,
            `loader_effect_hide` text NOT NULL,
            `loader_effect_show_options` text NOT NULL,
            `loader_effect_hide_options` text NOT NULL,
            `loader_UserWorkspace_id` bigint(20) NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `Template_UserWorkspace_id` bigint(20) NOT NULL,
            `time_tamper_prevention` INT NOT NULL,
            `hash` text NOT NULL,
            `debug` tinyint(1) NOT NULL,
            `release` tinyint(1) NOT NULL,
            `output` longtext NOT NULL,
            `error_output` longtext NOT NULL,
            `resume_declined` tinyint(1) NOT NULL,
            `state` longtext NOT NULL,
            `UserWorkspace_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        return mysql_query($sql);
    }

}

?>