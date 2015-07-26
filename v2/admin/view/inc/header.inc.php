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
    require_once '../../../model/Ini.php';
    $ini = new Ini();
}
$user = User::get_logged_user();
if ($user == null) die(Language::string(85));
?>

<style type="text/css">
<?php
$icons = "";
foreach (Language::languages() as $lng_node)
{
    $attr = $lng_node->getAttributeNode("id")->value;
    if ($icons != "") $icons.=",";
    $icons.="{find:'.flagIcon_" . $attr . "'} ";
    ?>
        .flagIcon_<?= $attr ?> .ui-selectmenu-item-icon { background: url(css/img/<?= $attr ?>.png) center no-repeat; }
<?php } ?>
</style>
<script>
    $(function(){
        Methods.iniIconButtons();
        $("#listHeaderLanguageSelect").selectmenu({
            icons:[<?= $icons ?>],
            style:"dropdown",
            width:100,
            change:function(){
                location.href='index.php?lng='+$(this).val();
            }
        });
    })
</script>

<table class="fullWidth">
    <tr>
        <td class="noWrap" align="center" style="width:33%;" valign="middle">
            <button class="btnHomepage" onclick="location.href='http://code.google.com/p/concerto-platform'" />
            <button class="btnGoogleGroup" onclick="location.href='http://groups.google.com/group/concerto-platform'" style="margin-left:5px;"/>
        </td>
        <td class="noWrap" align="center" style="width:33%;" valign="middle">
            <div style="font-size:1.2em; font-weight: bold;">
                Concerto Platform, v<?= Ini::$version ?> 
            </div>
            <div id="divVersionCheck"></div>
        </td>
        <td class="noWrap" align="center" style="width:33%;" valign="middle">
            <select id="listHeaderLanguageSelect">
                <?php
                foreach (Language::languages() as $lng_node)
                {
                    $attr = $lng_node->getAttributeNode("id")
                    ?>
                    <option class="flagIcon_<?= $attr->value ?>" value="<?= $attr->value ?>" <?= $_SESSION['lng'] == $attr->value ? "selected" : "" ?>><?= $lng_node->nodeValue ?></option>
                <?php } ?>
            </select>&nbsp;
            <label for="btnLogout"><?= Language::string(84) ?>: <b><?= $user->login . ", " . $user->get_full_name() ?></label>
            <button name="btnLogout" id="btnLogout" class="btnLogout" onclick="User.uiLogOut()"/>
        </td>
    </tr>
</table>