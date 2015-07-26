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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Concerto Platform - admin v<?= Ini::$version ?></title>
        <link rel="stylesheet" type="text/css" href="../css/redmond/jquery-ui-1.8.16.custom.css" />
        <link rel="stylesheet" type="text/css" href="../css/styles.css" />

        <link rel="stylesheet" href="css/jquery.fileupload-ui.css" />
        <link rel="stylesheet" href="css/jquery.table.sorter.blue/style.css" />
        <link rel="stylesheet" href="css/jquery.tablesorter.pager.css" />
        <link rel="stylesheet" href="../lib/CodeMirror/lib/codemirror.css" />
        <link rel="stylesheet" href="../lib/CodeMirror/theme/night.css" />
        <link rel="stylesheet" href="../lib/selectmenu/jquery.ui.selectmenu.css" />

        <script type="text/javascript" src="../js/lib/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="../js/lib/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="../lib/ckeditor/ckeditor.js"></script>
        <script type="text/javascript" src="../lib/ckeditor/adapters/jquery.js"></script>
        <script type="text/javascript" src="../lib/selectmenu/jquery.ui.selectmenu.js"></script>

        <script type="text/javascript" src="js/Methods.js"></script>
        <script type="text/javascript" src="js/OModule.js"></script>
        <script type="text/javascript" src="js/Group.js"></script>
        <script type="text/javascript" src="js/User.js"></script>
        <script type="text/javascript" src="js/Item.js"></script>
        <script src="js/lib/jquery.fileupload.js"></script>
        <script src="js/lib/jquery.fileupload-ui.js"></script>
        <script src="js/lib/jquery.metadata.js"></script>
        <script src="js/lib/jquery.tablesorter.min.js"></script>
        <script src="js/lib/jquery.tablesorter.pager.js"></script>
        <script src="js/lib/jquery.tablesorter.filter.js"></script>
        <script src="../lib/CodeMirror/lib/codemirror.js"></script>
        <script src="../lib/CodeMirror/mode/htmlmixed/htmlmixed.js"></script>
        <script src="../lib/CodeMirror/mode/r/r.js"></script>
        <script src="../lib/jfeed/build/dist/jquery.jfeed.js"></script>
    </head>
    <body style="min-width:1200px;">
        <?php Language::load_js_dictionary(); ?>
        <script>
            $(function(){
                Methods.iniIconButtons(); 
            });
        </script>
        <?php
        $user = User::get_logged_user();
        if ($user == null)
        {
            include'view/inc/login.inc.php';
        }
        else
        {
            include'view/inc/layout.inc.php';
        }
        ?>
        <div style="display:none;" id="divGeneralDialog">
        </div>
    </body>
</html>