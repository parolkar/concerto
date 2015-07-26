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
    $(function(){
        Methods.currentView = <?= User::is_simple_view() ? 0 : 1 ?>;
        $("#tnd_mainMenu").tabs({
            show:function(event,ui){
                if(ui.index==0){
                    Test.uiRefreshCodeMirrors();
                }
                if(ui.index==3){
                    Template.uiRefreshCodeMirrors();
                }
            }
        });
        $(".tooltipTabs").tooltip({
            position:{ my: "left top", at: "left bottom", offset: "15 0" },
            tooltipClass:"tooltipWindow"
        });
        
        Methods.currentVersion = "<?= Ini::$version ?>";
        Methods.checkLatestVersion(function(isNewerVersion,version){
            var div = $("#divVersionCheck");
            var newer = isNewerVersion==1;
            if(newer)
            {
                div.css("color","red");
                div.html("<?= Language::string(262) ?> <a href='http://code.google.com/p/concerto-platform'><?= Language::string(263) ?> v"+version+"</a>");
            }
            else
            {
                div.css("color","green");
                div.html("<?= Language::string(264) ?>");
            }
        });
        $("#divUsersAccordion").accordion({
            collapsible:true,
            active:false,
            animated:false,
            change:function(){
                $(this).accordion("resize");
            }
        });
        
<?php
if (Ini::$cms_session_keep_alive) {
    ?>
                User.sessionKeepAlive(<?= Ini::$cms_session_keep_alive_interval ?>);
    <?php
}
?>
    });
</script>
<div class="table" align="center" style="width: 970px;"><?php include Ini::$path_internal . 'cms/view/includes/header.inc.php'; ?></div>

<div align="center" class="" style="width: 970px;" >
    <div id="tnd_mainMenu">
        <ul>
            <?php
            if ($logged_user->is_module_accesible("Test")) {
                ?>
                <li><a href="#tnd_mainMenu-tests" class="tooltipTabs" title="<?= Language::string(193) ?>"><?= Language::string(88) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("QTIAssessmentItem")) {
                ?>
                <li class="<?= User::view_class() ?>"><a href="#tnd_mainMenu-QTI" class="tooltipTabs" title="<?= Language::string(460) ?>"><?= Language::string(459) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("CustomSection")) {
                ?>
                <li class="<?= User::view_class() ?>"><a href="#tnd_mainMenu-customSections" class="tooltipTabs" title="<?= Language::string(194) ?>"><?= Language::string(84) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("Template")) {
                ?>
                <li><a href="#tnd_mainMenu-templates" class="tooltipTabs" title="<?= Language::string(195) ?>"><?= Language::string(167) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("Table")) {
                ?>
                <li><a href="#tnd_mainMenu-tables" class="tooltipTabs" title="<?= Language::string(196) ?>"><?= Language::string(85) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("User") || $logged_user->is_module_accesible("UserType") || $logged_user->is_module_accesible("UserGroup")) {
                ?>
                <li class="<?= User::view_class() ?>"><a href="#tnd_mainMenu-users" class="tooltipTabs" title="<?= Language::string(197) ?>"><?= Language::string(198) ?></a></li>
            <?php } ?>
        </ul>

        <?php
        if ($logged_user->is_module_accesible("Test")) {
            ?>
            <div id="tnd_mainMenu-tests">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_tests.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("QTIAssessmentItem")) {
            ?>
            <div id="tnd_mainMenu-QTI">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_QTI.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("CustomSection")) {
            ?>
            <div id="tnd_mainMenu-customSections">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_custom_sections.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("Template")) {
            ?>
            <div id="tnd_mainMenu-templates">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_templates.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("Table")) {
            ?>
            <div id="tnd_mainMenu-tables">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_tables.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("User") || $logged_user->is_module_accesible("UserType") || $logged_user->is_module_accesible("UserGroup")) {
            ?>
            <div id="tnd_mainMenu-users">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_users.inc.php'; ?>
            </div>
        <?php } ?>

    </div>
</div>

<div id="divDialogUpload" class="notVisible">
</div>

<div id="divDialogDownload" class="notVisible">
</div>

<div class="margin padding table" style="margin-bottom:50px;" align="center"><?php include Ini::$path_internal . 'cms/view/includes/footer.inc.php'; ?></div>