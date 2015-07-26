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
        <link rel="stylesheet" href="cms/css/jQueryUI/cupertino/jquery-ui-1.9.1.custom.min.css?timestamp=<?= time() ?>" />

        <script type="text/javascript" src="cms/js/lib/jquery-1.8.2.min.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="cms/js/lib/jquery.json-2.3.min.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="cms/js/lib/jquery-ui-1.9.1.custom.min.js?timestamp=<?= time() ?>"></script>

        <script type="text/javascript" src="js/lib/jquery.cookie.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="js/Compatibility.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="js/Concerto.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="js/QTI.js?timestamp=<?= time() ?>"></script>
        <script>
            $(function() {
<?php
if (Ini::$log_document_unload) {
    ?>
                    $(window).unload(function() {
                        if (test != null) {
                            if (test.data != null) {
                                var browser = navigator.userAgent;
                                var testID = test.data.TEST_ID;
                                var sessionID = test.data.TEST_SESSION_ID;
                                var hash = test.data.HASH;
                                var template = test.data.TEMPLATE_ID;
                                var status = test.data.STATUS;
                                var finished = test.data.FINISHED;

                                var message = browser + "\ntest: " + testID + ", session: " + sessionID + ", hash: " + hash + ", status: " + status + ", finished: " + finished + ", template: " + template;
                                $.ajax({
                                    async: false,
                                    url: "query/log.php",
                                    type: "POST",
                                    data: {
                                        message: message,
                                        type: "unload"
                                    }
                                });
                            }
                        }
                    });
    <?php
}
if (Ini::$log_client_side_errors) {
    ?>
                    window.onerror = function(msg, url, linenumber) {
                        if (test != null) {
                            if (test.data != null) {
                                var browser = navigator.userAgent;
                                var testID = test.data.TEST_ID;
                                var sessionID = test.data.TEST_SESSION_ID;
                                var hash = test.data.HASH;
                                var template = test.data.TEMPLATE_ID;
                                var status = test.data.STATUS;
                                var finished = test.data.FINISHED;

                                var message = browser + "\ntest: " + testID + ", session: " + sessionID + ", hash: " + hash + ", status: " + status + ", finished: " + finished + ", template: " + template + "\n";
                                message += msg + " (" + url + ":" + linenumber + ")";
                                $.ajax({
                                    async: true,
                                    url: "query/log.php",
                                    type: "POST",
                                    data: {
                                        message: message,
                                        type: "client_side"
                                    }
                                });
                            }
                        }
                    };
    <?php
}
?>


                var values = new Array();
<?php
foreach ($_GET as $key => $value) {
    if ($key == "sid" || $key == "tid" || $key == "hash")
        continue;
    ?>
                    values.push($.toJSON({
                        name: "<?= $key ?>",
                        value: "<?= $value ?>"
                    }));
    <?php
}

if (array_key_exists("sid", $_GET) || array_key_exists("tid", $_GET)) {
    ?>
                    test = new Concerto($("#divTestContainer"),<?= array_key_exists("hash", $_GET) ? "'" . $_GET['hash'] . "'" : "null" ?>,<?= array_key_exists("sid", $_GET) ? $_GET['sid'] : "null" ?>,<?= array_key_exists("tid", $_GET) ? $_GET['tid'] : "null" ?>);
                    test.run(null, values);
<?php } ?>
            });
        </script>
    </head>

    <body>
        <div id="divTestContainer">
            <div align="center"><img src="cms/css/img/logo.png" /> v<?= Ini::$version ?></div>
            <div align="center">
                <div style="display: table;">
                    <fieldset class="ui-widget-content">
                        <legend>available tests</legend>
                        <select id="selectTest" class="ui-widget-content" onchange="Concerto.selectTest()">
                            <option value="0">&lt;none selected&gt;</option>
                            <?php
                            $z = mysql_query("SELECT * FROM `Test` WHERE `open`=1");
                            while ($r = mysql_fetch_array($z)) {
                                ?>
                                <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </fieldset>
                </div>
            </div>
            <div id="divSessionResumeDialog" title="session resuming" style="display:none;">
                <p>This test has an ongoing session. The session will be resumed. Please check the checkbox to start new session.</p>
                <p>
                <table><tr>
                        <td><input type="checkbox" id="chkResumeSession" /></td>
                        <td>start new session</td>
                    </tr>
                </table>
                </p>
            </div>
        </div>
    </body>
</html>