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

    public $Test_id = 0;
    public static $mysql_table_name = "TestSession";
    public $counter = 1;
    public $status = 0;
    public $time_limit = 0;
    public $HTML = "";
    public $Template_id = 0;
    public $time_tamper_prevention = 0;
    public $hash = "";
    public $Template_TestSection_id = 0;
    public $debug = 0;
    public $release = 0;
    public $serialized = 0;
    public $effect_show = "none";
    public $effect_hide = "none";
    public $effect_show_options = "";
    public $effect_hide_options = "";
    public $loader_Template_id = 0;
    public $loader_HTML = "";
    public $loader_head = "";
    public $loader_effect_show = "none";
    public $loader_effect_hide = "none";
    public $loader_effect_show_options = "";
    public $loader_effect_hide_options = "";

    const TEST_SESSION_STATUS_CREATED = 0;
    const TEST_SESSION_STATUS_WORKING = 1;
    const TEST_SESSION_STATUS_TEMPLATE = 2;
    const TEST_SESSION_STATUS_COMPLETED = 3;
    const TEST_SESSION_STATUS_ERROR = 4;
    const TEST_SESSION_STATUS_TAMPERED = 5;

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public function register() {
        if (array_key_exists("sids", $_SESSION)) {
            if (array_key_exists(session_id(), $_SESSION['sids'])) {
                TestSession::unregister($_SESSION['sids'][session_id()]);
                $_SESSION['sids'][session_id()] = $this->id;
            }
            else
                $_SESSION['sids'][session_id()] = $this->id;
        }
        else {
            $_SESSION['sids'] = array();
            $_SESSION['sids'][session_id()] = $this->id;
        }
    }

    public static function unregister($id) {
        $obj = TestSession::from_mysql_id($id);
        if ($obj != null)
            $obj->remove();
        unset($_SESSION['sids'][session_id()]);
    }

    public static function start_new($test_id, $debug = false) {
        $session = new TestSession();
        $session->Test_id = $test_id;
        $session->debug = ($debug ? 1 : 0);

        $test = Test::from_mysql_id($test_id);
        if ($test != null) {
            $loader = $test->get_loader_Template();
            if ($loader != null) {
                $session->loader_Template_id = $loader->id;
                $session->loader_HTML = $loader->HTML;
                $session->loader_head = $loader->head;
                $session->loader_effect_hide = $loader->effect_hide;
                $session->loader_effect_hide_options = $loader->effect_hide_options;
                $session->loader_effect_show = $loader->effect_show;
                $session->loader_effect_show_options = $loader->effect_show_options;
            }
        }

        $lid = $session->mysql_save();

        $sql = sprintf("UPDATE `%s` SET `session_count`=`session_count`+1 WHERE `%s`.`id`=%d", Test::get_mysql_table(), Test::get_mysql_table(), $test_id);
        mysql_query($sql);

        $session = TestSession::from_mysql_id($lid);
        if ($debug)
            $session->register();
        return $session;
    }

    public function remove($sockets = true) {
        $this->close($sockets);
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

    public function close($sockets = true) {
        if ($sockets && TestServer::is_running())
            TestServer::send("close:" . $this->id);
        $this->remove_files();
    }

    public function serialize() {
        if (TestServer::is_running())
            TestServer::send("serialize:" . $this->id);
    }

    public function remove_files() {
        if (file_exists($this->get_RSource_file_path()))
            unlink($this->get_RSource_file_path());
        if (file_exists($this->get_RSession_file_path()))
            unlink($this->get_RSession_file_path());
    }

    public function resume($values = array()) {
        return $this->run_Test($this->counter, $values);
    }

    public function run_Test($counter = null, $values = array()) {
        $ini_code_required = false;
        if ($counter == null)
            $ini_code_required = true;
        $test = $this->get_Test();
        if ($counter == null) {
            $counter = $test->get_starting_counter();
        }
        $this->counter = $counter;
        $this->status = TestSession::TEST_SESSION_STATUS_WORKING;
        $this->mysql_save();

        $code = "
            USER_IP <<- '".$_SERVER["REMOTE_ADDR"]."'
            ";
        $protected_vars = $test->get_TestProtectedVariables_name();
        foreach ($values as $v) {
            $val = json_decode($v);
            if (!property_exists($val, "name") || trim($val->name) == "" || strpos(trim($val->name), "CONCERTO_") === 0 || in_array(trim($val->name), $protected_vars))
                continue;

            if ($val->value === "NA") {
                $code.=sprintf("
                        %s <- NA
                        ", $val->name);
            } else {
                if (!is_array($val->value)) {
                    $code.=sprintf("
                    %s <- '%s'
                    %s <<- convertVariable(%s)
                    ", $val->name, addcslashes($val->value,"'"), $val->name, $val->name);
                } else {
                    $code.=sprintf("
                    %s <- c()
                    ", $val->name);
                    foreach ($val->value as $v) {
                        $code.=sprintf("
                            %s <- c(%s,'%s')
                            ", $val->name, $val->name, addcslashes($v,"'"));
                    }
                    $code.=sprintf("
                        %s <<- convertVariable(%s)
                        ", $val->name, $val->name);
                }
            }
        }
        $code.=sprintf("
            CONCERTO_TEST_FLOW<-%d
            
            while(CONCERTO_TEST_FLOW > 0){
                CONCERTO_TEST_FLOW <- do.call(paste('CONCERTO_Test',CONCERTO_TEST_ID,'Section',CONCERTO_TEST_FLOW,sep=''),list())
            }
            CONCERTO_FLOW_LOOP_FINISHED <- TRUE
            
            if(CONCERTO_TEST_FLOW==-2) update.session.release(1)
            ", $counter);

        return $this->RCall($code, $ini_code_required);
    }

    public function debug_syntax($ts_id, $close = false) {
        $ts = TestSection::from_mysql_id($ts_id);
        $result = $this->RCall($ts->get_RFunction(), false, $close, true);
        return $result;
    }

    public function does_RSession_file_exists() {
        if (file_exists($this->get_RSession_file_path()))
            return true;
        else
            return false;
    }

    public function RCall($code, $include_ini_code = false, $close = false, $debug_syntax = false) {
        $command = "";
        if (!$debug_syntax) {
            if ($include_ini_code)
                $command = $this->get_ini_RCode();
            else
                $command.=$this->get_next_ini_RCode();
        }

        $command.=$code;
        if (!$debug_syntax)
            $command.=$this->get_post_RCode();

        $output = array();
        $return = -999;

        $command_obj = json_encode(array(
            "session_id" => $this->id,
            "code" => $command,
            "close" => 0
                ));

        if (TestServer::$debug)
            TestServer::log_debug("TestSession->RCall --- checking for server");
        if (!TestServer::is_running())
            TestServer::start_process();
        if (TestServer::$debug)
            TestServer::log_debug("TestSession->RCall --- server found, trying to send");
        $response = TestServer::send($command_obj);
        $result = json_decode(trim($response));
        if (TestServer::$debug)
            TestServer::log_debug("TestSession->RCall --- sent and recieved response");

        $output = explode("\n", $result->output);
        $return = $result->return;

        $thisSession = null;
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

        if (!$debug_syntax) {
            $thisSession = TestSession::from_mysql_id($this->id);
            if ($thisSession != null) {
                $status = $thisSession->status;
                $release = $thisSession->release;
                $html = $thisSession->HTML;
                $Template_id = $thisSession->Template_id;
                $debug = $thisSession->debug;
                $hash = $thisSession->hash;
                $time_limit = $thisSession->time_limit;
                $Test_id = $thisSession->Test_id;

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

                if ($status == TestSession::TEST_SESSION_STATUS_WORKING && $release == 1 || $close)
                    $status = TestSession::TEST_SESSION_STATUS_COMPLETED;

                $thisSession->status = $status;
                $thisSession->mysql_save();

                switch ($status) {
                    case TestSession::TEST_SESSION_STATUS_COMPLETED: {
                            if ($debug) {
                                TestSession::unregister($thisSession->id);
                                $removed = true;
                            }
                            else
                                $thisSession->serialize();
                            break;
                        }
                    case TestSession::TEST_SESSION_STATUS_ERROR:
                    case TestSession::TEST_SESSION_STATUS_TAMPERED: {
                            if ($debug) {
                                TestSession::unregister($thisSession->id);
                                $removed = true;
                            }
                            else
                                $thisSession->close();
                            break;
                        }
                    case TestSession::TEST_SESSION_STATUS_TEMPLATE: {
                            if ($debug) {
                                $html = Template::strip_html($html);
                                if ($release)
                                    TestSession::unregister($thisSession->id);
                            }
                            else {
                                $head = Template::from_mysql_id($Template_id)->head;
                                if ($release)
                                    $thisSession->serialize();
                            }
                            break;
                        }
                }
            }
            else
                $removed = true;
        }

        $test = Test::from_mysql_id($this->Test_id);
        $debug_data = false;
        $logged_user = User::get_logged_user();
        if ($logged_user != null)
            $debug_data = $logged_user->is_object_readable($test);

        if ($release == 1 || $status == TestSession::TEST_SESSION_STATUS_COMPLETED || $status == TestSession::TEST_SESSION_STATUS_ERROR || $status == TestSession::TEST_SESSION_STATUS_TAMPERED) {
            $finished = 1;
        }

        if (!$debug_syntax) {
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
        }

        if ($debug_data) {
            for ($i = 0; $i < count($output); $i++) {
                if (strpos($output[$i], "CONCERTO_DB_PASSWORD <-") !== false)
                    $output[$i] = "[hidden]";
                $output[$i] = htmlspecialchars($output[$i], ENT_QUOTES);
            }

            $command_lines = explode("\n", $command);
            for ($i = 0; $i < count($command_lines); $i++) {
                if (strpos($command_lines[$i], "CONCERTO_DB_PASSWORD <-") !== false)
                    $command_lines[$i] = "[hidden]";
            }
            $command = implode("\n", $command_lines);
            $command = htmlspecialchars($command, ENT_QUOTES);

            if (!is_array($response))
                $response = array();
            $response["debug"] = array(
                "code" => $command,
                "return" => $return,
                "output" => $output
            );
        }

        if (Ini::$timer_tamper_prevention && !$debug_syntax && !$removed) {
            $sql = sprintf("UPDATE `%s` SET `time_tamper_prevention`=%d WHERE `id`=%d", TestSession::get_mysql_table(), time(), $this->id);
            mysql_query($sql);
        }

        return $response;
    }

    public function get_next_ini_RCode() {
        $code = "";
        return $code;
    }

    public function get_post_RCode() {
        $code = "";

        $test = $this->get_Test();
        $returns = $test->get_return_TestVariables();

        foreach ($returns as $ret) {
            $code.=sprintf("update.session.return('%s')
                ", $ret->name);
        }
        return $code;
    }

    public function write_RSource_file($code) {
        $file = fopen($this->get_RSource_file_path(), 'w');
        fwrite($file, $code);
        fclose($file);
    }

    public function get_RSource_file_path() {
        return Ini::$path_temp . $this->get_Test()->Owner_id . "/session_" . $this->id . ".R";
    }

    public function get_RSession_file_path() {
        return Ini::$path_temp . $this->get_Test()->Owner_id . "/session_" . $this->id . ".Rs";
    }

    public function get_ini_RCode() {
        include Ini::$path_internal . 'SETTINGS.php';
        $path = Ini::$path_temp . $this->get_Test()->Owner_id;
        if (!is_dir($path))
            mkdir($path, 0777);
        $code = sprintf("
            options(encoding='UTF-8')
            options(error=quote(stop(geterrmessage())))
            CONCERTO_TEST_ID <- %d
            CONCERTO_TEST_SESSION_ID <- %d
            
            CONCERTO_DB_HOST <- '%s'
            CONCERTO_DB_PORT <- as.numeric(%d)
            CONCERTO_DB_LOGIN <- '%s'
            CONCERTO_DB_PASSWORD <- '%s'
            CONCERTO_DB_NAME <- '%s'
            %s
            CONCERTO_DB_TIMEZONE <- '%s'
            ", $this->Test_id, $this->id, $db_host, ($db_port != "" ? $db_port : "3306"), $db_user, $db_password, $db_name, ($path_mysql_home != "" ? "Sys.setenv('MYSQL_HOME'='" . $path_mysql_home . "')" : ""), Ini::$mysql_timezone);
        $code .= "CONCERTO_TEMP_PATH <- '" . $path . "'
            source('" . Ini::$path_internal . "lib/R/mainmethods.R" . "')
            source('" . Ini::$path_internal . "lib/R/QTI.R" . "')
            ";
        $code .=$this->get_Test()->get_TestSections_RFunction_declaration();
        return $code;
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

    public static function authorized_session($id, $hash) {
        $session = TestSession::from_property(array("id" => $id, "hash" => $hash), false);
        if ($session == null)
            return null;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_ERROR: return null;
            case TestSession::TEST_SESSION_STATUS_TAMPERED: return null;
            case TestSession::TEST_SESSION_STATUS_COMPLETED: return null;
        }
        return $session;
    }

    public static function forward($tid, $sid, $hash, $values, $btn_name, $debug, $time, $resume_from_last_template = false) {
        $session = null;
        $result = array();
        if ($sid != null && $hash != null) {
            $session = TestSession::authorized_session($sid, $hash);

            if ($session != null) {
                if ($values == null)
                    $values = array();

                if ($btn_name != null) {
                    array_push($values, json_encode(array(
                                "name" => "LAST_PRESSED_BUTTON_NAME",
                                "value" => $btn_name
                            )));
                }

                if (Ini::$timer_tamper_prevention && $session->time_limit > 0 && $time - $session->time_tamper_prevention - Ini::$timer_tamper_prevention_tolerance > $session->time_limit) {
                    if ($session->debug == 1)
                        TestSession::unregister($session->id);
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
                            "code" => 0,
                            "return" => "",
                            "output" => ""
                        );
                    }
                } else {
                    if (!$resume_from_last_template)
                        $result = $session->resume($values);
                    else {
                        $ts = TestSection::from_mysql_id($session->Template_TestSection_id);
                        if ($ts == null)
                            $result = $session->resume($values);
                        else
                            $result = $session->run_Test($ts->counter, $values);
                    }
                }
            }
            else {
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
                        "code" => 0,
                        "return" => "",
                        "output" => ""
                    )
                );
            }
        } else {
            if ($tid != null) {
                if ($debug == 1)
                    $debug = true;
                else
                    $debug = false;
                $session = TestSession::start_new($tid, $debug);

                if ($values == null)
                    $values = array();

                $test = $session->get_Test();
                if ($test != null) {
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
                            "code" => 0,
                            "return" => "",
                            "output" => ""
                        )
                    );
                    return $result;
                }

                $result = $session->run_test(null, $values);
            }
        }
        return $result;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSession`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSession` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `counter` int(11) NOT NULL,
            `status` tinyint(4) NOT NULL,
            `time_limit` int(11) NOT NULL,
            `HTML` text NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `time_tamper_prevention` INT NOT NULL,
            `hash` text NOT NULL,
            `Template_TestSection_id` bigint(20) NOT NULL,
            `debug` tinyint(1) NOT NULL,
            `release` tinyint(1) NOT NULL,
            `serialized` tinyint(1) NOT NULL,
            `loader_Template_id` bigint(20) NOT NULL,
            `loader_HTML` text NOT NULL,
            `loader_head` text NOT NULL,
            `loader_effect_show` text NOT NULL,
            `loader_effect_hide` text NOT NULL,
            `loader_effect_show_options` text NOT NULL,
            `loader_effect_hide_options` text NOT NULL,
            `effect_show` text NOT NULL,
            `effect_hide` text NOT NULL,
            `effect_show_options` text NOT NULL,
            `effect_hide_options` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>