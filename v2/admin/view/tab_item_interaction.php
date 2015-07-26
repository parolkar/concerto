<!--
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
-->

<?php
if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
}
$user = User::get_logged_user();
if ($user == null)
    die(Language::string(85));

$oid = 0;
if (isset($_POST['oid']))
    $oid = $_POST['oid'];

$item = Item::from_mysql_id($oid);
if ($item == null)
    $item = new Item();
?>

<script>
    $(function(){
        Methods.iniIconButtons(); 
    });
</script>

<div align="center" class="fullWidth">
    <button class="btnBuiltInFunctionsDoc" onclick="Item.showBuiltInRFunctionsDocDialog()" />&nbsp;
    <button class="btnItemsSessionVariables" onclick="Item.showItemsSessionVariablesDialog()" />
</div>

<table style="width: 100%;" class="ui-widget-content ui-corner-all ui-state-focus">
    <caption class="ui-widget-header ui-corner-all noWrap">
        <?= Language::string(54) ?>
        <button class="btnInfoSubmitButtons"></button>
    </caption>
    <thead>
        <tr>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(55) ?></th>
            <th class="ui-widget-header ui-corner-all"><?= Language::string(56) ?></th>
            <th class="ui-widget-header ui-corner-all noWrap">
                <?= Language::string(58) ?>
                <button class="btnInfoRFunction"></button>
            </th>
        </tr>
    </thead>
    <tbody id="itemRInteraction">
        <?php include Ini::$internal_path . "admin/view/tab_item_interaction_buttons.php"; ?>
    </tbody>
</table>