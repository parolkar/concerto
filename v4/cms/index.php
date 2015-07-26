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
    require_once'../Ini.php';
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
        <title>Concerto Platform</title>
        <link rel="stylesheet" href="css/styles.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="../css/QTI.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="css/jQueryUI/cupertino/jquery-ui-1.10.3.custom.min.css" />

        <link rel="stylesheet" href="js/lib/selectmenu/jquery.ui.selectmenu.css" />
        <link rel="stylesheet" href="js/lib/kendo-ui/styles/kendo.common.min.css" />
        <link rel="stylesheet" href="js/lib/kendo-ui/styles/kendo.default.min.css" />
        <link rel="stylesheet" href="lib/CodeMirror/lib/codemirror.css" />
        <link rel="stylesheet" href="lib/CodeMirror/theme/neat.css" />
        <link rel="stylesheet" href="lib/CodeMirror/addon/hint/simple-hint.css" />

        <script type="text/javascript" src="js/lib/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="js/lib/jquery-migrate-1.1.1.min.js"></script>
        <script type="text/javascript" src="js/lib/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="js/lib/selectmenu/jquery.ui.selectmenu.js"></script>
        <script type="text/javascript" src="js/lib/jquery.json-2.3.min.js"></script>
        <script type="text/javascript" src="js/lib/jsSHA/src/sha512.js"></script>
        <script type="text/javascript" src="js/lib/jquery.blockUI.js"></script>

        <script src="../js/lib/jquery.cookie.js"></script>
        <script src="../js/Compatibility.js?timestamp=<?= time() ?>"></script>
        <script src="../js/Concerto.js?timestamp=<?= time() ?>"></script>
        <script src="../js/QTI.js?timestamp=<?= time() ?>"></script>

        <script src="js/OModule.js?timestamp=<?= time() ?>"></script>
        <script src="js/Methods.js?timestamp=<?= time() ?>"></script>
        <script src="js/User.js?timestamp=<?= time() ?>"></script>
        <script src="js/Template.js?timestamp=<?= time() ?>"></script>
        <script src="js/Table.js?timestamp=<?= time() ?>"></script>
        <script src="js/Test.js?timestamp=<?= time() ?>"></script>
        <script src="js/QTIAssessmentItem.js?timestamp=<?= time() ?>"></script>
        <script src="lib/ckeditor/ckeditor.js"></script>
        <script src="lib/ckeditor/adapters/jquery.js"></script>
        
        <script src="lib/CodeMirror/lib/codemirror.js"></script>
        <script src="lib/CodeMirror/addon/format/formatting.js"></script>
        <script src="lib/CodeMirror/addon/edit/matchbrackets.js"></script>
        <script src="lib/CodeMirror/addon/hint/simple-hint.js"></script>
        <script src="lib/CodeMirror/mode/htmlmixed/htmlmixed.js"></script>
        <script src="lib/CodeMirror/mode/r/r.js"></script>
        <script src="lib/CodeMirror/mode/xml/xml.js"></script>
        <script src="lib/CodeMirror/mode/javascript/javascript.js"></script>
        <script src="lib/CodeMirror/mode/css/css.js"></script>
        <script src="lib/CodeMirror/mode/mysql/mysql.js"></script>
        
        <script src="js/lib/jquery-ui-dialog-ckeditor-patch.js"></script>
        <script src="js/lib/fileupload/jquery.iframe-transport.js"></script>
        <script src="js/lib/fileupload/jquery.fileupload.js"></script>
        <script src="lib/jfeed/build/dist/jquery.jfeed.js"></script>

        <script>User.sessionID='<?= session_id(); ?>';</script>
        <?= Language::load_js_dictionary() ?>
        <script type="text/javascript" src="js/lib/kendo-ui/js/kendo.web.min.js"></script>
        <script type="text/javascript" src="js/lib/kendo-ui/js/cultures/kendo.culture.<?= Language::get_kendo_culture() ?>.min.js"></script>
        <script type="text/javascript">
            kendo.culture("<?= Language::get_kendo_culture() ?>");
        </script>
    </head>
    <body>
        <div id="content" align="center">
            <?php
            if (User::get_logged_user() == null) {
                include 'view/log_in.php';
            } else {
                include 'view/layout.php';
            }
            ?>
        </div>

        <div id="divProgressDialog" class="notVisible">
            <div id="divProgressBar" class="fullWidth"></div>
        </div>
        <div id="divGeneralDialog" class="notVisible"></div>
        <div id="divAddFormDialog" class="notVisible"></div>

        <script>
            //password recovery
<?php
if (array_key_exists("pruid", $_GET) && array_key_exists("pruh", $_GET)) {
    if (User::recover_password($_GET['pruid'], $_GET['pruh'])) {
        ?>
                    $(function(){
                        Methods.alert(dictionary["s431"], "info", dictionary["s427"]);
                    });
        <?php
    } else {
        
    }
}
?>
        </script>
    </body>
</html>