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

$included = false;
if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
    $included = true;
}

$user = User::get_logged_user();
if ($user == null)
    die(Language::string(85));

$oid = isset($_POST['oid']) ? $_POST['oid'] : 0;

$default_btn = null;
$item = Item::from_mysql_id($oid);
if ($item == null)
    $item = new Item();
else {
    if ($included)
        $default_btn = $item->get_default_Button();
}

$buttons = (isset($_POST['buttons']) ? $_POST['buttons'] : array());
$selection = $default_btn != null ? $default_btn->name : (isset($_POST['selection']) ? $_POST['selection'] : "");
?>

<select id="formItemSelectDefault" class="fullWidth">
    <option value="0">&lt;<?= Language::string(95) ?>&gt;</option>
    <?php
    foreach ($buttons as $button) {
        if ($button == "")
            continue;
        ?>
        <option value="<?= $button ?>" <?= ($selection == $button ? "selected" : "") ?> ><?= $button ?></option>    
    <?php } ?>
    }
    ?>
</select>