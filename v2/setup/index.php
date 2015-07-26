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
}
include'../SETTINGS.php';

class Setup
{

    public static function php_version_check()
    {
        $v = phpversion();
        $nums = explode(".", $v);
        if ($nums[0] < 5) return false;
        if ($nums[0] == 5 && $nums[1] < 3) return false;
        if ($nums[0] == 5 && $nums[1] >= 3) return true;
        if ($nums[0] > 5) return true;
    }

    public static function php_safe_mode_check()
    {
        return!ini_get("safe_mode");
    }

    public static function php_short_open_tag_check()
    {
        return ini_get("short_open_tag");
    }

    public static function file_paths_check($path)
    {
        if (file_exists($path) && is_file($path)) return true;
        else return false;
    }

    public static function directory_paths_check($path)
    {
        if (file_exists($path) && is_dir($path)) return true;
        else return false;
    }

    public static function directory_writable_check($path)
    {
        if (is_writable($path)) return true;
        else return false;
    }

    public static function rscript_check($path)
    {
        if ($path == "") return false;
        $array = array();
        $return = 0;
        exec($path . " -e 1+1", $array, $return);
        return ($return == 0);
    }

    public static function url_exists_check($url, $slash_check=false)
    {
// Version 4.x supported
        if ($url == "") return false;
        if ($slash_check && substr($url, strlen($url) - 1, 1) != '/')
                return false;
        $handle = curl_init($url);
        if (false === $handle)
        {
            return false;
        }
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
        curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")); // request as if Firefox   
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
        $connectable = curl_exec($handle);
        curl_close($handle);
        return $connectable;
    }

    public static function mysql_connection_check($host, $port, $login, $password)
    {
        if (@mysql_connect($host . $port, $login, $password)) return true;
        else return false;
    }

    public static function mysql_select_db_check($db_name)
    {
        if (@mysql_select_db($db_name)) return true;
        else return false;
    }

    public static function r_package_check($path, $package)
    {
        $array = array();
        $return = 0;
        exec($path . " -e 'library(" . $package . ")'", $array, $return);
        return ($return == 0);
    }

    public static function superadmin_check()
    {
        $sql = sprintf("SELECT * FROM `%s` WHERE `superadmin`=1", User::get_mysql_table());
        $z = mysql_query($sql);
        if (mysql_num_rows($z) == 0) return false;
        else return true;
    }

}
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Concerto Platform - test page</title>
        <link rel="stylesheet" type="text/css" href="../css/redmond/jquery-ui-1.8.16.custom.css" />
        <link rel="stylesheet" type="text/css" href="../css/styles.css" />

        <script type="text/javascript" src="../js/lib/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="../js/lib/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="../admin/js/Methods.js"></script>
        <script type="text/javascript" src="../lib/jfeed/build/dist/jquery.jfeed.js"></script>
    </head>

    <body>
        <div align="center" class="ui-widget-header ui-corner-all"><h1>Concerto platform - <?= Ini::$version != "" ? "v" . Ini::$version . " - " : "" ?>test page</h1></div>
        <br/>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th class="ui-widget-header"><h3>test description</h3></th>
        <th class="ui-widget-header"><h3>test result</h3></th>
    <th class="ui-widget-header"><h3>recommendation</h3></th>
</tr>
</thead>
<tbody>
    <tr>
        <?php
        $ok = true;

        $test = Setup::php_version_check();
        ?>
        <td class="ui-widget-content">PHP version at least <b>v5.3</b></td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP version: <b><?= phpversion() ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Update your PHP to v5.3 or higher") ?></td>
    </tr>
    <?php $ok = $ok && $test; ?>

    <?php if ($ok)
    { ?>
        <tr>
            <?php
            $test = Setup::php_safe_mode_check();
            ?>
            <td class="ui-widget-content">PHP <b>'safe mode'</b> must be turned <b>OFF</b></td>
            <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP <b>'safe mode'</b> is turned <b><?= ($test ? "OFF" : "ON") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
            <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Ask your server administrator to turn PHP 'safe mode' OFF") ?></td>
            <?php $ok = $ok && $test; ?>
        </tr>
    <?php } ?>

    <?php if ($ok)
    { ?>
        <tr>
            <?php
            $test = Setup::php_short_open_tag_check();
            ?>
            <td class="ui-widget-content">PHP <b>'short open tag'</b> must be turned <b>ON</b></td>
            <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP <b>'short open tag'</b> is turned <b><?= ($test ? "ON" : "OFF") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
            <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Ask your server administrator to turn PHP 'short open tag' ON") ?></td>
            <?php $ok = $ok && $test; ?>
        </tr>
    <?php } ?>

    <?php if ($ok)
    { ?>
        <tr>
            <?php
            $test = Setup::mysql_connection_check($db_host, $db_port, $db_user, $db_password);
            ?>
            <td class="ui-widget-content"><b>MySQL</b> connection test</td>
            <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">Host: <b><?= $db_host ?></b>, Port: <b><?= $db_port ?></b>, Login: <b><?= $db_user ?></b> <b><?= ($test ? "CONNECTED" : "CAN'T CONNECT") ?></b> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
            <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>db_host, db_port, db_user, db_password</b> in /SETTINGS.php file.") ?></td>
            <?php $ok = $ok && $test; ?>
        </tr>
    <?php } ?>

    <?php if ($ok)
    { ?>
        <tr>
            <?php
            $test = Setup::mysql_select_db_check($db_name);
            ?>
            <td class="ui-widget-content"><b>MySQL</b> database connection test</td>
            <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>MySQL</b> database <b><?= $db_name ?></b> <b><?= ($test ? "IS CONNECTABLE" : "IS NOT CONNECTABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
            <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>db_name</b> in <b>/SETTINGS.php</b> file. Check if database name is correct and if it is - check if MySQL user has required permissions to access this database.") ?></td>
            <?php $ok = $ok && $test; ?>
        </tr>
    <?php } ?>

    <?php
    if ($ok)
    {
        Ini::install_db();
        $ini = new Ini();
        Ini::install_files();
        ?>
    <script>
        $(function(){
            Methods.checkLatestVersion("<?= Ini::$version ?>", function(isNewerVersion,version,link){
                if(isNewerVersion==1) 
                {
                    $("#tdVersionCheckResult").removeClass("ui-state-highlight");
                    $("#tdVersionCheckResult").addClass("ui-state-error");
                    $("#tdVersionCheckResult").html("newer version is available: <b>v"+version+"</b>. Your current version <b>v"+Methods.currentVersion+"</b> <b style='color:red;'>IS OUTDATED</b>");
                    $("#tdVersionCheckReccomendations").html("Update to latest version by clicking on the download link below:<br/><a href='"+link+"'>DOWNLOAD LINK</a>");
                }
                else
                {
                    $("#tdVersionCheckResult").html("your current version: <b>v"+Methods.currentVersion+"</b> <b style='color:green;'>IS UP TO DATE</b>");
                }
            },"../lib/jfeed/proxy.php");
        });
    </script>
    <tr>
        <td class="ui-widget-content">Check for the latest <b>Concerto Platform</b> version</td>
        <td id="tdVersionCheckResult" class="ui-state-highlight">...checking the latest version...</td>
        <td id="tdVersionCheckReccomendation"class="ui-widget-content" align="center">-</td>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::superadmin_check();
        ?>
        <td class="ui-widget-content"><b>superadmin</b> user test</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>superadmin</b> user <b><?= ($test ? "EXISTS" : "DOESN'T EXISTS") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center">
            <?php
            if ($test) echo"-";
            else
            {
                ?>
                You must create a superadmin user to be able to log in to the Concerto Platform admin panel.<br/><br/>
                Please fill the form below:
                <table style="width:100%;">
                    <tr>
                        <td width="100">
                            login:
                        </td>
                        <td>
                            <input type="text" style="width:90%;" class="ui-state-highlight" id="sa_login" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            password:
                        </td>
                        <td>
                            <input type="password" style="width:90%;" class="ui-state-highlight" id="sa_pass" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            password confirmation:
                        </td>
                        <td>
                            <input type="password" style="width:90%;" class="ui-state-highlight" id="sa_conf" value="" />
                        </td>
                    </tr>
                </table>
                <button class="btnSave" onclick="saveSuperAdmin()" />
                <script>
                    function saveSuperAdmin()
                    {
                        var login = $("#sa_login");
                        var pass = $("#sa_pass");
                        var conf = $("#sa_conf");
                                                                                                                                    
                        if(login.val()=="" || pass.val()=="" || conf.val()=="")
                        {
                            Methods.alert("Every field must be filled.","alert");
                            return;
                        }
                        if(pass.val()!=conf.val())
                        {
                            Methods.alert("Password and password confirmation must match.","alert");
                            return;
                        }
                                                                                                                                    
                        $.post("save_superadmin.php",
                        {
                            login:login.val(),
                            pass:pass.val()
                        },
                        function(data){
                            location.reload();
                        })  
                    };
                                                                                                                                                                                                                                                                        
                    $(function(){
                        Methods.iniIconButtons();
                    })
                </script>
            <?php } ?>
        </td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::rscript_check(Ini::$rscript_path);
        ?>
        <td class="ui-widget-content"><b>Rscript</b> file path must be set.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>Rscript</b> file path: <b><?= Ini::$rscript_path ?></b> <b><?= ($test ? "EXISTS" : "DOESN'T EXISTS") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center">
            <?php
            if ($test) echo"-";
            else
            {
                ?>
                Rscript file path not set or set incorrectly. If you don't have this file on your system it could mean that your <b>R</b> installation is of version lower than <b>v2.12</b>. If that's the case you should update your R to higher version and set your Rscript path then.<br/>
                Usually the Rscript file path is <b>/usr/bin/Rscript</b>.<br/><br/>
                Please enter your Rscript file path ( e.g. <b>/usr/bin/Rscript</b> ):
                <input type="text" style="width:90%;" class="ui-state-highlight" id="rscriptInput" value="<?= Ini::$rscript_path ?>" /><br/>
                <button class="btnSave" onclick="saveRscriptPath()" />
                <script>
                    function saveRscriptPath()
                    {
                        $.post("save_setting.php",
                        {
                            name:'rscript_path',
                            value:$('#rscriptInput').val()
                        },
                        function(data){
                            location.reload();
                        })  
                    };
                                                                                                                                                                                                                                                                        
                    $(function(){
                        Methods.iniIconButtons();
                    })
                </script>
            <?php } ?>
        </td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::url_exists_check(Ini::$external_path, true);
        ?>
        <td class="ui-widget-content">You must set your application URL address.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">Your application URL: <b><?= Ini::$external_path ?></b> <b><?= ($test ? "IS CORRECT" : "IS INCORRECT") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center">
            <?php
            if ($test) echo"-";
            else
            {
                ?>
                Application URL address must be set. It isn`t right now or it is set incorrectly. It must contain protocol prefix and end with a slash character.<br/><br/>
                Please enter your application URL address ( e.g. <b>http://yourdomain.com/</b> or <b>http://yourdomain.com/concerto/</b> ):
                <input type="text" style="width:90%;" class="ui-state-highlight" id="externalPathInput" value="<?= Ini::$external_path ?>" /><br/>
                <button class="btnSave" onclick="saveExternalPath()" />
                <script>
                    function saveExternalPath()
                    {
                        $.post("save_setting.php",
                        {
                            name:'external_path',
                            value:$('#externalPathInput').val()
                        },
                        function(data){
                            location.reload();
                        })  
                    };
                                                                                                                                                                                                                                                                        
                    $(function(){
                        Methods.iniIconButtons();
                    })
                </script>
            <?php } ?>
        </td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::url_exists_check(Ini::$rstudio_url);
        ?>
        <td class="ui-widget-content">Check if <b>RStudio</b> is running on server.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>Rstudio</b> is probably: <?= ($test ? "RUNNING" : "NOT RUNNING") ?> on your server - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "CAN BE IGNORED") ?></b></td>
        <td class="ui-widget-content" align="center">
            <?php
            if ($test)
            {
                ?>
                You can turn on option of using <b>RStudio</b> in application.<br/><br/>
                To turn it on check the checkbox below:<br/>
                <input type="checkbox" class="ui-state-highlight" id="rStudioOnCheckbox" <?= Ini::get_setting(Ini::SETTING_IS_RSTUDIO_ON) == 1 ? "checked" : "" ?> /><br/>
                <button class="btnSave" onclick="saveRStudioOn()" />
                <script>
                    function saveRStudioOn()
                    {
                        $.post("save_setting.php",
                        {
                            name:'is_rstudio_on',
                            value:$('#rStudioOnCheckbox').is(":checked")?1:0
                        },
                        function(data){
                            location.reload();
                        })  
                    };
                                                                                                                                                                                                                                                                        
                    $(function(){
                        Methods.iniIconButtons();
                    })
                </script>
                <?php
            }
            else
            {
                ?>
                Either install RStudio to have a possibility of using it from within the application or ignore this and run application without RStudio.
            <?php } ?>
        </td>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::directory_writable_check(Ini::$temp_path);
        ?>
        <td class="ui-widget-content"><b>/temp</b> directory path must be writable</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/temp</b> directory: <b><?= Ini::$temp_path ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/temp</b> directory rigths to 0777.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::directory_writable_check(Ini::$internal_media_path);
        ?>
        <td class="ui-widget-content"><b>/media</b> directory path must be writable</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/media</b> directory: <b><?= Ini::$internal_media_path ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/media</b> directory rigths to 0777.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::directory_writable_check(Ini::$internal_path . "lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache");
        ?>
        <td class="ui-widget-content"><b>/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory path must be writable</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory: <b><?= Ini::$internal_path . "lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache" ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory rigths to 0777.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::r_package_check(Ini::$rscript_path, "session");
        ?>
        <td class="ui-widget-content"><b>session</b> R package must be installed.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>session</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>session</b> package as a user with root access in R console.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::r_package_check(Ini::$rscript_path, "RMySQL");
        ?>
        <td class="ui-widget-content"><b>RMySQL</b> R package must be installed.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>RMySQL</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>RMySQL</b> package as a user with root access in R console.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>

<?php if ($ok)
{ ?>
    <tr>
        <?php
        $test = Setup::r_package_check(Ini::$rscript_path, "catR");
        ?>
        <td class="ui-widget-content"><b>catR</b> R package must be installed.</td>
        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>catR</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>catR</b> package as a user with root access in R console.") ?></td>
        <?php $ok = $ok && $test; ?>
    </tr>
<?php } ?>
</tbody>
</table>
<br/>
<?php if (!$ok)
{ ?>
    <h1 class="ui-state-error" align="center">Please correct your problems using recommendations and run the test again.</h1>
    <?php
}
else
{
    ?>
    <h1 class="ui-state-highlight" align="center" style="color:green;">Test completed. Every item passed correctly.</h1>
    <h1 class="ui-state-highlight" align="center" style="color:blue;">IT IS STRONGLY RECOMMENDED TO DELETE THIS <b>/setup</b> DIRECTORY NOW ALONG WITH ALL IT'S CONTENTS FOR SECURITY REASONS!</h1>
    <h2 class="ui-state-highlight" align="center"><a href="<?= Ini::$external_path . "admin/index.php" ?>">click here to launch Concerto Platform panel</a></h2>
<?php } ?>
<div style="display:none;" id="divGeneralDialog">
</div>
</body>
</html>