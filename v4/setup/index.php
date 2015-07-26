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
    require_once'../Ini.php';
    $ini = new Ini(false);
}
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Concerto Platform - setup page</title>
        <link rel="stylesheet" href="../cms/css/styles.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="../cms/css/jQueryUI/cupertino/jquery-ui-1.10.3.custom.min.css" />

        <script type="text/javascript" src="../cms/js/lib/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="../cms/js/lib/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="../js/Compatibility.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="../cms/js/Methods.js?timestamp=<?= time() ?>"></script>
        <script src="../cms/lib/jfeed/build/dist/jquery.jfeed.js"></script>

        <script src="Setup.js?timestamp=<?= time() ?>"></script>

        <script>
            
            $(function(){
                Methods.currentVersion = "<?= Ini::$version ?>";
                Setup.path_external="<?= Ini::$path_external ?>";
                
                Setup.initialize();
                Setup.run();
            })
        </script>
    </head>

    <body>
        <div align="center">
            <h2>Concerto Platform - <?= Ini::$version != "" ? "v" . Ini::$version . " - " : "" ?>setup page</h2>

            <div align="center" class="table">
                <table>
                    <tr>
                        <td id="tdLoadingStep"><img src='../css/img/ajax-loader.gif' /></td>
                        <td id="tdCurrentStep">initializing...</td>
                    </tr>
                </table>
            </div>
            <div id="divSetupProgressBar" style="width:600px;"></div>

            <div align="center" class="table">
                <table>
                    <tr>
                        <td id="tdLoadingDBStep"><img src='../css/img/ajax-loader.gif' /></td>
                        <td id="tdCurrentDBStep">please wait for database update phase...</td>
                    </tr>
                </table>
            </div>
            <div id="divSetupDBProgressBar" style="width:600px;"></div>

            <br/>
            <div align="center">
                <table class="margin">
                    <thead>
                        <tr>
                            <th class="ui-widget-header">test description</th>
                            <th class="ui-widget-header">test result</th>
                            <th class="ui-widget-header">recommendation</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySetup">



                    </tbody>
                </table>
            </div>
        </div>
        <br/>
        <div id="divGeneralDialog" class="notVisible"></div>
    </body>
</html>