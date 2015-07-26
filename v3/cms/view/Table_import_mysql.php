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
        Methods.iniTooltips();
    })
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(255) ?>"></span></td>
                <td class=""><b><?= Language::string(124) ?></b></td>
            </tr>
        </table>
    </legend>
    <select id="formTableSelectMySQLTable" class="fullWidth ui-widget-content ui-corner-all">
        <option value="0">&lt;<?= Language::string(73) ?>&gt;</option>
        <?php
        $sql = "SHOW TABLES";
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            if (in_array($r[0], Ini::get_system_tables()) || (strpos($r[0], Table::get_table_prefix()) !== false && strpos($r[0], Table::get_table_prefix()) == 0))
                continue;
            ?>
            <option value="<?= $r[0] ?>"><?= $r[0] ?></option>
        <?php } ?>
    </select>
</fieldset>