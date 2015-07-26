<?php
//MySQL
$db_host = "localhost"; //MySQL server host
$db_port = "3306"; // MySQL server port
$db_master_user = "db_master_user"; // MySQL user name with ALL privileges
$db_master_password = "db_password"; // MySQL password for user with ALL privileges
$db_master_name = "db_master_name"; // Concerto master MySQL database name ( user need to create it manually prior to running /setup )
$db_users_name_prefix = "concerto_"; // prefix for MySQL user names created by Concerto ( workspace id will be appended to it ) - SET IT ONLY ONCE PRIOR TO RUNNING /setup
$db_users_db_name_prefix = "concerto_"; // prefix for MySQL database names created by Concerto ( workspace id will be appended to it ) - SET IT ONLY ONCE PRIOR TO RUNNING /setup

//paths
$path_external = "http://domain.com/"; // Concerto full URL ending with slash character ('/'), e.g. http://domain.com/concerto/
$path_r_script = "/usr/bin/Rscript"; // Rscript executable path, e.g. /usr/bin/Rscript
$path_r_exe = "/usr/bin/R"; // R executable path, e.g. /usr/bin/R
$path_php_exe = "/usr/bin/php"; // php executable path, e.g. /usr/bin/php
$path_sock = ""; // socks directory path ending with slash character ('/'), leave blank for default - /[concerto_installation_path]/socks/
$path_data = ""; // data directory path ending with slash character ('/'), leave blank for default - /[concerto_installation_path]/data/

//R connection
$server_socks_type = "UNIX"; // socket server type ( UNIX or TCP, UNIX highly reccomended )
$server_host = "127.0.0.1"; // choose host to connect to ( only when TCP )
$server_port = "8888"; // choose port used for connection ( only when TCP )
$r_instances_persistant_instance_timeout = 60 * 15; // after set period of instance inactivity in seconds the instance will be serialized and closed
$r_instances_persistant_server_timeout = 60 * 20; // after set period of server inactivity in seconds the server will be closed ( new instances can restart it anytime )
$r_max_execution_time = 30; // maximum R execution time after which instance will be terminated ( prevents infinite loops in R on server )
$unix_locale = ""; // Unix locale LANG variable. Must be installed on the system. Leave blank for none/default, e.g. en_GB.UTF8
$timer_tamper_prevention = false; // DEPRECATED
$timer_tamper_prevention_tolerance = 30; // DEPRECATED

//general
$timezone = 'Europe/London'; // PHP timezone settings
$mysql_timezone = '+0:00'; // MySQL timezone settings, leave blank to make it the same as $timezone
$public_registration = false; // is open registration from login form allowed
$cms_session_keep_alive = true; // prevents session expiry when in panel
$cms_session_keep_alive_interval = 300000; // time interval between session keep alive requests in miliseconds

//remote client
$remote_client_password = "pass"; // password required by remote clients to use this Concerto server

//cron
$r_users_name_prefix = "concerto_"; // prefix for Linux users created by Concerto ( user id will be appended to it ) - SET IT ONLY ONCE PRIOR TO RUNNING /setup
$r_users_group = "concerto"; // Linux group name for users above
$php_user = "www-data"; // php user name
$php_user_group = "www-data"; // group of php user above

//logs
$log_server_events = false; // socket communication info and test server php errors will be printed to a text file in data directory
$log_server_streams = false; // socket streams will be logged too
$log_js_errors = true; // logs all test specific js errors from client side
$log_r_errors = true; // logs all test specific R errors

//ALWAYS RUN /setup AFTER CHANGING SETTINGS IN THIS FILE!
?>