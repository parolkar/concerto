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
?>
<script>
    $(function(){
        Methods.iniIconButtons(); 
    });
</script>
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

$buttons = isset($_POST['buttons']) ? $_POST['buttons'] : array();

$i = 0;
foreach ($buttons as $buttonName) {
    if ($buttonName == "")
        continue;
    $i++;
    $button = $item->get_Button($buttonName);
    ?>

    <tr>
        <td class="ui-corner-all">#<?= $i ?></td>
        <td class="ui-corner-all"><b><?= $buttonName ?></b></td>
        <td style="width:100%;" id="td_button_function_<?= $buttonName ?>">
            <textarea  id="button_function_<?= $buttonName ?>" name="button_function_<?= $buttonName ?>" class="codeTextarea"><?= ($button != null ? $button->function : "") ?></textarea>
        </td>
    </tr>
    <?php
}
if ($i == 0) {
    ?>
    <tr><td align="center" colspan="3"><?= Language::string(7) ?></td></tr>
<?php } ?>