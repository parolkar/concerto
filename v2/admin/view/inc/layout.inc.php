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

$user = User::get_logged_user();
if ($user == null) exit();
?>
<script type="text/javascript">
    $(function(){
        $("#layout_tabs").tabs({
        });

<?= (Ini::$is_rstudio_on ? '$("#rStudioFrame").css("min-height",$(window).height()-150);' : "") ?>
        
        Methods.checkLatestVersion('<?= Ini::$version ?>', function(isNewerVersion,version,link){
            var div = $("#divVersionCheck");
            var newer = isNewerVersion==1;
            if(newer)
            {
                div.addClass("ui-state-error");
                div.html("<?=Language::string(139)?> <a href='"+link+"'><?=Language::string(140)?> v"+version+"</a>");
            }
            else
            {
                div.html("<?=Language::string(141)?>");
            }
        });
    });
</script>

<div class="fullWidth ui-widget-content ui-corner-all noWrap" align="center"><?php include Ini::$internal_path . 'admin/view/inc/header.inc.php'; ?></div>
<div id="layout_tabs">
    <ul>
        <li><a href="#layout_tab1"><?= Language::string(71) ?></a></li>
        <?= (Ini::$is_rstudio_on ? '<li><a href="#layout_tab2">RStudio</a></li>' : "") ?>
        <?= ($user->is_superadmin() ? '<li><a href="#layout_tab4">' . Language::string(78) . '</a></li>' : "") ?>
        <!--<li><a href="#layout_tab5">help</a></li>-->
    </ul>
    <div id="layout_tab1" class="layout_tab"><?php include 'view/inc/tab_item.inc.php' ?></div>
    <?php if (Ini::$is_rstudio_on)
    { ?>
        <div id="layout_tab2" class="layout_tab">
            <iframe id="rStudioFrame" src="<?= Ini::$rstudio_url ?>" width="100%" frameborder="0"></iframe>
        </div>
    <?php } ?>
    <?php if ($user->is_superadmin())
    { ?><div id="layout_tab4" class="layout_tab"><?php include'view/inc/tab_users.inc.php'; ?></div><?php } ?>
<!--<div id="layout_tab5" class="layout_tab"><?php include 'view/inc/tab_help.inc.php' ?></div>-->
</div>

<div style="display:none;" id="divBuiltInRFunctionsDocDialog" title="<?= Language::string(124) ?>">
    <table class="fullWidth">
        <tr>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(125) ?></th>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(126) ?></th>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(127) ?></th>
        </tr>
        <tr>
            <td class="ui-widget-content ui-corner-all ui-state-highlight"><b>set.var(var_name,var_value)</b></td>
            <td class="ui-widget-content ui-corner-all"><?= Language::string(128) ?></td>
            <td class="ui-widget-content ui-corner-all"><b>set.var("greeting","hello!")</b></td>
        </tr>
        <tr>
            <td class="ui-widget-content ui-corner-all ui-state-highlight"><b>set.next.template(next_item_template_id)</b></td>
            <td class="ui-widget-content ui-corner-all"><?= Language::string(129) ?></td>
            <td class="ui-widget-content ui-corner-all"><b>set.next.template(2)</b></td>
        </tr>
    </table>
</div>