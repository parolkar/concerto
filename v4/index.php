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

if (!isset($ini)) {
    require_once'Ini.php';
    $ini = new Ini();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Przemyslaw Lis" />
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="Expires" content="-1"/>
        <title>Concerto</title>

        <link rel="stylesheet" href="css/styles.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="css/QTI.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="cms/css/jQueryUI/cupertino/jquery-ui-1.10.3.custom.min.css" />

        <script type="text/javascript" src="cms/js/lib/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery-migrate-1.1.1.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery.json-2.3.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery-ui-1.10.3.custom.min.js"></script>

        <script type="text/javascript" src="js/lib/jquery.cookie.js"></script>
        <script type="text/javascript" src="js/lib/moment.js"></script>
        <script type="text/javascript" src="js/Compatibility.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="js/Concerto.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="js/QTI.js?timestamp=<?= time() ?>"></script>
        <script>
            $(function() {
<?php
if (Ini::$log_js_errors) {
    ?>
                    window.onerror = function(msg, url, linenumber) {
                        if (test != null && !test.isDebug) {
                            if (test.data != null) {
                                var workspaceID = test.workspaceID;
                                var sessionID = test.data.TEST_SESSION_ID;
                                var hash = test.data.HASH;

                                var message = msg + " (" + url + ":" + linenumber + ")";
                                $.ajax({
                                    async: true,
                                    url: "query/log.php",
                                    type: "POST",
                                    data: {
                                        wid: workspaceID,
                                        sid: sessionID,
                                        hash: hash,
                                        message: message
                                    }
                                });
                            }
                        }
                    };
    <?php
}
?>

                var values = {};
<?php
foreach ($_GET as $key => $value) {
    if ($key == "sid" || $key == "tid" || $key == "wid" || $key == "hash")
        continue;
    ?>
                    values["<?= $key ?>"] = "<?= addcslashes($value, '"') ?>";
    <?php
}

if ((array_key_exists("sid", $_GET) || array_key_exists("tid", $_GET)) && array_key_exists("wid", $_GET)) {
    ?>
                    test = new Concerto($("#divTestContainer"),<?= array_key_exists("wid", $_GET) ? "'" . $_GET['wid'] . "'" : "null" ?>,<?= array_key_exists("hash", $_GET) ? "'" . $_GET['hash'] . "'" : "null" ?>,<?= array_key_exists("sid", $_GET) ? $_GET['sid'] : "null" ?>,<?= array_key_exists("tid", $_GET) ? $_GET['tid'] : "null" ?>);
                    test.run(null, values);
<?php } ?>
            });
        </script>
    </head>

    <body>
        <div id="divTestContainer">
            <div align="center" style="color: red; font-weight: bold;"><noscript>Your browser does not support JavaScript!</noscript></div>
            <br/>
            <div align="center"><img src="cms/css/img/logo.png" /> v<?= Ini::$version ?></div>
            <div align="center">
                <div style="display: table;">
                    <fieldset class="ui-widget-content">
                        <legend>available tests</legend>
                        <select id="selectTest" class="ui-widget-content" onchange="Concerto.selectTest()">
                            <option value="0">&lt;none selected&gt;</option>
                            <?php
                            $query = array();
                            $sql = sprintf("SELECT `id`,`db_name` FROM `%s`.`%s`", Ini::$db_master_name, UserWorkspace::get_mysql_table());
                            $z = mysql_query($sql);
                            while ($r = mysql_fetch_array($z)) {
                                $sql = sprintf("(SELECT `id`,%s as `wid`,`name` FROM `%s`.`%s` WHERE `type`=1)", $r['id'], $r['db_name'], Test::get_mysql_table());
                                array_push($query, $sql);
                            }

                            $query = implode(" UNION ", $query) . " ORDER BY `name` ASC";

                            $z = mysql_query($query);
                            while ($r = mysql_fetch_array($z)) {
                                ?>
                                <option value="<?= $r['id'] ?>" workspace="<?= $r["wid"] ?>"><?= $r['name'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </fieldset>
                </div>
            </div>
            <div id="divSessionResumeDialog" title="session resuming" style="display:none;">
                <p>This test has an ongoing session. To resume the test, press the OK button below</p>
                <p><label for="chkStartNew"><input type="checkbox" id="chkStartNew" /> No, please start a new test</label></p>
            </div>
        </div>
    </body>
</html>