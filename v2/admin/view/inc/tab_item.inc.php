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

if (User::get_logged_user() == null)
    exit();

?>

<script type="text/javascript">
    $(function(){       
        $("#divItemTabs").tabs({
            show:function(ui,event)
            {
                if($("#divItemTabs").tabs("option","selected")==1)
                {
                    Item.RCodeMirrors = new Array();
                    for(var i=0;i<Item.RCodeMirrorsIds.length;i++) 
                    {
                        var cm = Methods.iniCodeMirror(Item.RCodeMirrorsIds[i],"r");
                        Item.RCodeMirrors.push(cm);
                    }
                }
                else
                {
                    for(var i=0;i<Item.RCodeMirrors.length;i++) 
                    {
                        Item.RCodeMirrors[i].toTextArea();
                    }           
                }
            }
        }); 
    });
</script>

<table class="fullWidth noMarginPadding" >
    <tr>
        <td class="noMarginPadding" style="padding-right:10px; vertical-align: top;">
            <div id="divItemList" class="fullWidth"><?php include Ini::$internal_path . "admin/view/Item_list.php"; ?></div>
        </td>
        <td style="vertical-align: top;" class="fullWidth noMarginPadding">
            <div id="divItemForm" class="fullWidth"><?php include Ini::$internal_path . "admin/view/Item_form.php"; ?></div>
            <div id="divItemTabs">
                <ul>
                    <li><a href="#tabItemPresentation"><?= Language::string(117) ?></a></li>
                    <li><a href="#tabItemInteraction"><?= Language::string(118) ?></a></li>
                </ul>
                <div id="tabItemPresentation">
                    <?php include Ini::$internal_path . "admin/view/tab_item_presentation.php"; ?>
                </div>
                <div id="tabItemInteraction">
                    <?php include Ini::$internal_path . "admin/view/tab_item_interaction.php"; ?>
                </div>
            </div>
        </td>
    </tr>
</table>

<!--dialogs-->
<div style="display:none;" id="divItemsSessionVariablesDialog" title="<?= Language::string(130) ?>">
    <table class="fullWidth">
        <tr>
            <td valign="top" align="center">
                <table class="ui-widget-content ui-corner-all fullWidth">
                    <caption class="ui-widget-header ui-corner-all noWrap">
                        <?= Language::string(67) ?>
                        <button class="btnInfoSentVariables"></button>
                    </caption>
                    <thead>
                        <tr>
                            <th class="ui-widget-header ui-corner-all"><?= Language::string(50) ?></th>
                            <th class="ui-widget-header ui-corner-all"><?= Language::string(68) ?></th>
                        </tr>
                    </thead>
                    <tbody id="itemSentVariables">
                    </tbody>
                </table>
            </td>
            <td valign="top" align="center">
                <table class="ui-widget-content ui-corner-all fullWidth">
                    <caption class="ui-widget-header ui-corner-all noWrap">
                        <?= Language::string(69) ?>
                        <button class="btnInfoAcceptedVariables"></button>
                    </caption>
                    <thead>
                        <tr>
                            <th class="ui-widget-header ui-corner-all"><?= Language::string(50) ?></th>
                        </tr>
                    </thead>
                    <tbody id="itemAcceptedVariables">
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>