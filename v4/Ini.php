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

class Ini {

    private static $error_reporting = true;
    public static $path_internal = "";
    public static $path_external = "";
    public static $path_internal_media = "";
    public static $path_external_media = "";
    public static $path_php_exe = "";
    public static $path_r_exe = "";
    public static $path_r_script = "";
    public static $path_data = "";
    public static $version = "4.0.0.beta9";
    public static $server_host = "127.0.0.1";
    public static $server_port = "8888";
    public static $path_unix_sock = "";
    public static $path_unix_sock_dir = "";
    public static $server_socks_type = 0;
    public static $r_instances_timeout = 900;
    public static $r_max_execution_time = 180;
    public static $r_server_timeout = 1080;
    public static $timer_tamper_prevention = true;
    public static $timer_tamper_prevention_tolerance = 30;
    public static $path_online_library_ws = "";
    public static $remote_client_password = "";
    public static $public_registration = false;
    public static $cms_session_keep_alive = true;
    public static $cms_session_keep_alive_interval = 600;
    public static $unix_locale = "";
    public static $contact_emails = "pl362@cam.ac.uk,mk583@cam.ac.uk,vm298@cam.ac.uk";
    public static $forum_url = "http://concerto.e-psychometrics.com/forum/";
    public static $project_homepage_url = "http://code.google.com/p/concerto-platform/";
    public static $project_changelog_url = "https://code.google.com/p/concerto-platform/wiki/changelog";
    public static $timezone = "Europe/London";
    public static $mysql_timezone = "+0:00";
    public static $r_users_name_prefix = "concerto_";
    public static $r_users_group = "concerto";
    public static $php_user = "www-data";
    public static $php_user_group = "www-data";
    public static $db_users_name_prefix = "concerto_";
    public static $db_users_db_name_prefix = "concerto_";
    public static $db_master_name = "";
    public static $db_host = "";
    public static $db_port = "";
    public static $log_server_events = false;
    public static $log_server_streams = false;
    public static $log_js_errors = true;
    public static $log_r_errors = true;

    function __construct($connect = true, $session = true, $headers = true) {
        //if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); 
        //else ob_start();
        if ($headers) {
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        include __DIR__ . "/SETTINGS.php";
        if ($session)
            @session_start();
        date_default_timezone_set($timezone);
        if (self::$error_reporting)
            error_reporting(E_ALL);
        else
            error_reporting(0);

        $this->load_settings();

        if (Ini::$unix_locale != "")
            setlocale(LC_ALL, Ini::$unix_locale);

        $this->load_classes();
        if ($connect) {
            if (!$this->initialize_db_connection())
                die("Error initializing DB connection!");
        }

        if ($session) {
            if (!isset($_GET['lng'])) {
                if (!isset($_SESSION['lng']))
                    $_SESSION['lng'] = "en";
            }
            else
                $_SESSION['lng'] = $_GET['lng'];

            Language::load_dictionary();
        }
    }

    private function load_settings() {
        include __DIR__ . "/SETTINGS.php";
        self::$path_external = $path_external;
        self::$path_external_media = self::$path_external . "media/";
        self::$path_internal = str_replace("\\", "/", __DIR__) . "/";
        self::$path_internal_media = self::$path_internal . "media/";
        self::$path_r_script = $path_r_script;
        self::$path_r_exe = $path_r_exe;
        self::$path_php_exe = $path_php_exe;
        if ($path_data != "")
            self::$path_data = $path_data;
        else
            self::$path_data = self::$path_internal . "data/";
        if ($path_sock != "")
            self::$path_unix_sock_dir = $path_sock;
        else
            self::$path_unix_sock_dir = self::$path_internal . "socks/";
        self::$path_unix_sock = self::$path_unix_sock_dir . "RConcerto.sock";
        self::$server_socks_type = $server_socks_type == "UNIX" ? 0 : 1;
        self::$server_host = $server_host;
        self::$server_port = $server_port;
        self::$r_instances_timeout = $r_instances_persistant_instance_timeout;
        self::$r_server_timeout = $r_instances_persistant_server_timeout;
        self::$r_max_execution_time = $r_max_execution_time;
        self::$path_online_library_ws = "http://concerto.e-psychometrics.com/demo/online_library/ws.php";
        self::$remote_client_password = $remote_client_password;
        self::$public_registration = $public_registration;
        self::$cms_session_keep_alive = $cms_session_keep_alive;
        self::$cms_session_keep_alive_interval = $cms_session_keep_alive_interval;
        self::$unix_locale = $unix_locale;
        self::$timer_tamper_prevention = $timer_tamper_prevention;
        self::$timer_tamper_prevention_tolerance = $timer_tamper_prevention_tolerance;
        self::$timezone = $timezone;
        if ($mysql_timezone == "")
            self::$mysql_timezone = $timezone;
        else
            self::$mysql_timezone = $mysql_timezone;
        self::$r_users_name_prefix = $r_users_name_prefix;
        self::$r_users_group = $r_users_group;
        self::$php_user = $php_user;
        self::$php_user_group = $php_user_group;
        self::$db_users_name_prefix = $db_users_name_prefix;
        self::$db_users_db_name_prefix = $db_users_db_name_prefix;
        self::$db_master_name = $db_master_name;
        self::$db_host = $db_host;
        self::$db_port = $db_port;
        self::$log_js_errors = $log_js_errors;
        self::$log_r_errors = $log_r_errors;
        self::$log_server_events = $log_server_events;
        self::$log_server_streams = $log_server_streams;
    }

    public static function get_user_system_tables() {
        return array(
            "QTIAssessmentItem",
            "Table",
            "Template",
            "Test",
            "TestSession",
            "TestSessionLog",
            "TestSessionReturn",
            "TestVariable"
        );
    }

    public static function get_master_system_tables() {
        return array(
            "DS_Module",
            "DS_UserInstitutionType",
            "RDoc",
            "RDocLibrary",
            "RDocFunction",
            "Setting",
            "UserR",
            "UserWorkspace",
            "User",
            "UserFunction",
            "UserShare"
        );
    }

    public static function create_db_structure($simulate = false) {
        foreach (Ini::get_master_system_tables() as $table) {
            $sql = sprintf("SHOW TABLES IN `%s` LIKE '%s'", Ini::$db_master_name, $table);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) == 0) {
                if ($simulate) {
                    return true;
                } else {
                    if (!$table::create_db())
                        return json_encode(array("result" => 1, "param" => $table));
                }
            }
        }

        foreach (User::get_all_db() as $db_name) {
            foreach (Ini::get_user_system_tables() as $table) {
                $sql = sprintf("SHOW TABLES IN `%s` LIKE '%s'", $db_name, $table);
                $z2 = mysql_query($sql);
                if (mysql_num_rows($z2) == 0) {
                    if ($simulate) {
                        return true;
                    } else {
                        if (!$table::create_db($db_name))
                            return json_encode(array("result" => 1, "param" => $table));
                    }
                }
            }
        }

        if ($simulate) {
            return false;
        }
        return json_encode(array("result" => 0));
    }

    private function initialize_db_connection() {
        include __DIR__ . "/SETTINGS.php";
        $h = mysql_connect($db_host . ($db_port != "" ? ":" . $db_port : ""), $db_master_user, $db_master_password);
        if (!$h)
            return false;
        mysql_set_charset('utf8', $h);
        mysql_query(sprintf("SET time_zone = '%s';", Ini::$mysql_timezone));

        $db = User::get_current_db();
        if ($db == null)
            $db = Ini::$db_master_name;
        if (mysql_select_db($db, $h))
            return true;
        else
            return false;
    }

    private function load_classes() {
        require_once self::$path_internal . "cms/lib/simplehtmldom/simple_html_dom.php";
        require_once self::$path_internal . "cms/lib/nusoap/nusoap.php";
        require_once self::$path_internal . "cms/model/Language.php";
        require_once self::$path_internal . "cms/model/OTable.php";
        require_once self::$path_internal . "cms/model/RDoc.php";
        require_once self::$path_internal . "cms/model/RDocLibrary.php";
        require_once self::$path_internal . "cms/model/RDocFunction.php";
        require_once self::$path_internal . "cms/model/Setting.php";
        require_once self::$path_internal . "cms/model/OModule.php";
        require_once self::$path_internal . "cms/model/User.php";
        require_once self::$path_internal . "cms/model/UserFunction.php";
        require_once self::$path_internal . "cms/model/UserR.php";
        require_once self::$path_internal . "cms/model/UserShare.php";
        require_once self::$path_internal . "cms/model/UserWorkspace.php";
        require_once self::$path_internal . "cms/model/Template.php";
        require_once self::$path_internal . "cms/model/Table.php";
        require_once self::$path_internal . "cms/model/TableColumn.php";
        require_once self::$path_internal . "cms/model/TableIndex.php";
        require_once self::$path_internal . "cms/model/Test.php";
        require_once self::$path_internal . "cms/model/TestServer.php";
        require_once self::$path_internal . "cms/model/TestInstance.php";
        require_once self::$path_internal . "cms/model/TestSession.php";
        require_once self::$path_internal . "cms/model/TestSessionLog.php";
        require_once self::$path_internal . "cms/model/TestSessionReturn.php";
        require_once self::$path_internal . "cms/model/TestVariable.php";
        require_once self::$path_internal . "cms/model/QTI/OQTIElement.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/AExpression.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/AndExp.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/AnyN.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/BaseValue.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Contains.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Correct.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/CustomOperator.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/DefaultExp.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Delete.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Divide.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/DurationGTE.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/DurationLT.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Equal.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/EqualRounded.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/FieldValue.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Gt.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Gte.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Index.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Inside.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/IntegerDivide.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/IntegerModulus.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/IntegerToFloat.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/IsNull.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Lt.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Lte.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/MapResponse.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/MapResponsePoint.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Match.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Member.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Multiple.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Not.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/NullExp.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Ordered.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/OrExp.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/PatternMatch.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Power.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Product.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Random.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/RandomInteger.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/RandomFloat.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Round.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/StringMatch.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Substring.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Subtract.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Sum.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Truncate.php";
        require_once self::$path_internal . "cms/model/QTI/expressions/Variable.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ABodyElement.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AAssociableChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AFeedbackElement.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AGapChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AHotspot.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AHotspotAssociableChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AHotspotChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ABlockInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AGraphicInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ABlockStringInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AInlineInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AInlineStringInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AStringInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AssociableHotspot.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/AssociateInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ChoiceInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/CustomInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/DrawingInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/EndAttemptInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ExtendedTextInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/FeedbackBlock.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/FeedbackInline.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/Gap.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GapImg.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GapMatchInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GapText.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GraphicAssociateInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GraphicGapMatchInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/GraphicOrderInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/HotspotChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/HotspotInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/Hottext.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/HottextInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/ItemBody.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/InlineChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/InlineChoiceInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/MatchInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/Object.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/OrderInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/PositionObjectInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/PositionObjectStage.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/PrintedVariable.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/Prompt.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/RubricBlock.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/SelectPointInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/SimpleAssociableChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/SimpleChoice.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/SimpleMatchSet.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/SliderInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/TextEntryInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/presentation/UploadInteraction.php";
        require_once self::$path_internal . "cms/model/QTI/AItemVariable.php";
        require_once self::$path_internal . "cms/model/QTI/AOutcomeVariable.php";
        require_once self::$path_internal . "cms/model/QTI/AResponseRule.php";
        require_once self::$path_internal . "cms/model/QTI/AResponseVariable.php";
        require_once self::$path_internal . "cms/model/QTI/ATemplateElement.php";
        require_once self::$path_internal . "cms/model/QTI/ATemplateRule.php";
        require_once self::$path_internal . "cms/model/QTI/ATemplateVariable.php";
        require_once self::$path_internal . "cms/model/QTI/AVariableDeclaration.php";
        require_once self::$path_internal . "cms/model/QTI/AreaMapEntry.php";
        require_once self::$path_internal . "cms/model/QTI/AreaMapping.php";
        require_once self::$path_internal . "cms/model/QTI/AssessmentItem.php";
        require_once self::$path_internal . "cms/model/QTI/CorrectResponse.php";
        require_once self::$path_internal . "cms/model/QTI/DefaultValue.php";
        require_once self::$path_internal . "cms/model/QTI/ExitResponse.php";
        require_once self::$path_internal . "cms/model/QTI/ExitTemplate.php";
        require_once self::$path_internal . "cms/model/QTI/MapEntry.php";
        require_once self::$path_internal . "cms/model/QTI/Mapping.php";
        require_once self::$path_internal . "cms/model/QTI/ModalFeedback.php";
        require_once self::$path_internal . "cms/model/QTI/OutcomeDeclaration.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseCondition.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseDeclaration.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseElse.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseElseIf.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseIf.php";
        require_once self::$path_internal . "cms/model/QTI/ResponseProcessing.php";
        require_once self::$path_internal . "cms/model/QTI/SetCorrectResponse.php";
        require_once self::$path_internal . "cms/model/QTI/SetDefaultValue.php";
        require_once self::$path_internal . "cms/model/QTI/SetOutcomeValue.php";
        require_once self::$path_internal . "cms/model/QTI/SetTemplateValue.php";
        require_once self::$path_internal . "cms/model/QTI/Stylesheet.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateBlock.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateCondition.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateElse.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateElseIf.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateIf.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateInline.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateDeclaration.php";
        require_once self::$path_internal . "cms/model/QTI/TemplateProcessing.php";
        require_once self::$path_internal . "cms/model/QTI/Value.php";
        require_once self::$path_internal . "cms/model/QTI/QTIAssessmentItem.php";
        require_once self::$path_internal . "cms/model/ODataSet.php";
        require_once self::$path_internal . "cms/model/DS_UserInstitutionType.php";
        require_once self::$path_internal . "cms/model/DS_Module.php";
    }

    public static function check_sent_data() {
        foreach ($_POST as $k => $v) {
            if (is_array($_POST[$k])) {
                for ($i = 0; $i < count($_POST[$k]); $i++) {
                    $_POST[$k][$i] = mysql_real_escape_string($_POST[$k][$i]);
                }
            }
            else
                $_POST[$k] = mysql_real_escape_string($_POST[$k]);
        }
        foreach ($_GET as $k => $v) {
            if (is_array($_GET[$k])) {
                for ($i = 0; $i < count($_GET[$k]); $i++) {
                    $_GET[$k][$i] = mysql_real_escape_string($_GET[$k][$i]);
                }
            }
            else
                $_GET[$k] = mysql_real_escape_string($_GET[$k]);
        }

        foreach ($_SESSION as $k => $v) {
            if (is_array($_SESSION[$k])) {
                for ($i = 0; $i < count($_SESSION[$k]); $i++) {
                    $_SESSION[$k][$i] = mysql_real_escape_string($_SESSION[$k][$i]);
                }
            }
            else
                $_SESSION[$k] = mysql_real_escape_string($_SESSION[$k]);
        }
    }

}

?>