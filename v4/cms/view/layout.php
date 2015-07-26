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
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) {
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}
?>

<script>
    $(function() {
        Methods.currentView = <?= User::is_simple_view() ? 0 : 1 ?>;
        $(window).resize(function() {
            $("#divTestResponse").css("height", Methods.winHeight() - 100);
            $(".divTestVerticalElement").css("height", ((Methods.winHeight() - 125) / 2) + "px");
            Test.onScroll();

            var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
            if (!showing)
                return;
            showing.CodeMirror.getWrapperElement().style.height = Methods.winHeight() + "px";
            showing.CodeMirror.getWrapperElement().style.width = Methods.winWidth() + "px";
        });

        $(window).scroll(function() {
            Test.onScroll();
        });

        $("#tnd_mainMenu").tabs({
            show: function(event, ui) {
                if (ui.index == 0) {
                    Test.uiRefreshCodeMirrors();
                }
                if (ui.index == 2) {
                    Template.uiRefreshCodeMirrors();
                }
            }
        });
        $(".tooltipTabs").tooltip({
            position: {my: "left top", at: "left bottom", offset: "15 0"},
            tooltipClass: "tooltipWindow",
            content: function() {
                return $(this).attr("title");
            },
            show: false,
            hide: false
        });

        Methods.currentVersion = "<?= Ini::$version ?>";
        Methods.checkLatestVersion(function(isNewerVersion, version) {
            var div = $("#divVersionCheck");
            var newer = isNewerVersion == 1;
            if (newer)
            {
                div.css("color", "red");
                div.html("<?= Language::string(262) ?> <a href='http://code.google.com/p/concerto-platform'><?= Language::string(263) ?> v" + version + "</a>");
            }
            else
            {
                div.css("color", "green");
                div.html("<?= Language::string(264) ?>");
            }
        });

        User.previousWorkspace = $("#selectWorkspace").val();

<?php
if (Ini::$cms_session_keep_alive) {
    ?>
            User.sessionKeepAlive(<?= Ini::$cms_session_keep_alive_interval ?>);
    <?php
}
?>
    });
</script>

<div align="center" style="min-width: 970px;"><?php include Ini::$path_internal . 'cms/view/includes/header.inc.php'; ?></div>

<div align="center" class="" style="min-width: 970px;" >
    <div id="tnd_mainMenu">
        <ul>
            <li><a href="#tnd_mainMenu-Test" class="tooltipTabs" title="<?= Language::string(193) ?>"><?= Language::string(88) ?></a></li>
            <li class="<?= User::view_class() ?>"><a href="#tnd_mainMenu-QTIAssessmentItem" class="tooltipTabs" title="<?= Language::string(460) ?>"><?= Language::string(459) ?></a></li>
            <li><a href="#tnd_mainMenu-Template" class="tooltipTabs" title="<?= Language::string(195) ?>"><?= Language::string(167) ?></a></li>
            <li><a href="#tnd_mainMenu-Table" class="tooltipTabs" title="<?= Language::string(196) ?>"><?= Language::string(85) ?></a></li>
            <li><a href="#tnd_mainMenu-User" class="tooltipTabs" title="<?= Language::string(197) ?>"><?= Language::string(198) ?></a></li>
            <div style="float:right;">
                <table>
                    <tr>
                        <td><?= Language::string(625) ?></td>
                        <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(626) ?>"></span></td>
                        <td>
                            <select id="selectWorkspace" class="ui-widget-content ui-corner-all" onchange="User.uiChangeWorkspace(this)">
                                <?php
                                $current_db = User::get_current_db();

                                $label_added = false;
                                foreach ($logged_user->get_workspaces() as $workspace) {
                                    if (!$label_added) {
                                        $label_added = true;
                                        ?>
                                        <optgroup label="<?= $logged_user->get_full_name() ?>">
                                            <?php
                                        }
                                        ?>
                                        <option value="<?= $workspace->db_name ?>" <?= $current_db == $workspace->db_name ? "selected" : "" ?> ><?= $workspace->get_formatted_name() ?></option>
                                        <?php
                                    }
                                    ?>
                                </optgroup>
                                <?php
                                $sql = sprintf("SELECT `id` FROM `%s`.`%s` WHERE `id`!=%s ORDER BY `lastname` ASC, `firstname` ASC", Ini::$db_master_name, User::get_mysql_table(), $logged_user->id);
                                $z = mysql_query($sql);
                                while ($r = mysql_fetch_array($z)) {
                                    $label_added = false;
                                    $user = User::from_mysql_id($r[0]);
                                    foreach ($user->get_workspaces() as $workspace) {
                                        if ($logged_user->is_workspace_accessible($workspace->db_name)) {
                                            if (!$label_added) {
                                                $label_added = true;
                                                ?>
                                                <optgroup label="<?= $user->get_full_name() ?>">
                                                    <?php
                                                }
                                                ?>
                                                <option value="<?= $workspace->db_name ?>" <?= $current_db == $workspace->db_name ? "selected" : "" ?> ><?= $workspace->get_formatted_name() ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                    if ($label_added) {
                                        ?>
                                    </optgroup>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </ul>

        <div id="tnd_mainMenu-Test">
            <?php include Ini::$path_internal . 'cms/view/includes/tab_tests.inc.php'; ?>
        </div>
        <div id="tnd_mainMenu-QTIAssessmentItem">
            <?php include Ini::$path_internal . 'cms/view/includes/tab_QTI.inc.php'; ?>
        </div>
        <div id="tnd_mainMenu-Template">
            <?php include Ini::$path_internal . 'cms/view/includes/tab_templates.inc.php'; ?>
        </div>
        <div id="tnd_mainMenu-Table">
            <?php include Ini::$path_internal . 'cms/view/includes/tab_tables.inc.php'; ?>
        </div>
        <div id="tnd_mainMenu-User">
            <?php include Ini::$path_internal . 'cms/view/includes/tab_users.inc.php'; ?>
        </div>

    </div>
</div>

<div id="divDialogUpload" class="notVisible">
</div>

<div id="divDialogDownload" class="notVisible">
</div>

<div class="margin padding table" style="margin-bottom:50px;" align="center"><?php include Ini::$path_internal . 'cms/view/includes/footer.inc.php'; ?></div>