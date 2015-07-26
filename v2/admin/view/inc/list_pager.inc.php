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

if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
}
$user = User::get_logged_user();
if ($user == null)
    die(Language::string(85));
?>

<script>
    $(function(){
        $("#listLength<?= $class_name ?>").val(<?= $class_name ?>.listLength);
    });
</script>

<div class="pager fullWidth ui-widget-header ui-corner-all" align="center" id="table<?= $class_name ?>ListPager">
    <form>
        <button class="first btnFirstOnly ui-icon-seek-first"></button>
        <button class="prev btnPrevOnly ui-icon-seek-prev"></button>
        <input type="text" class="pagedisplay" readonly style="width: 50px;" />
        <select class="pagesize" id="listLength<?= $class_name ?>" onchange="<?=$class_name?>.uiChangeListLength(this.value)">
            <option value="10">10 <?= Language::string(102) ?></option>
            <option value="25">25 <?= Language::string(102) ?></option>
            <option value="50">50 <?= Language::string(102) ?></option>
            <option value="100">100 <?= Language::string(102) ?></option>
        </select>
        <button class="next btnNextOnly ui-icon-seek-next"></button>
        <button class="last btnEndOnly ui-icon-seek-end"></button>
    </form>
</div>