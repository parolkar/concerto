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

if (!isset($obj)) {
    $obj = Test::from_mysql_id($_POST['oid']);
}
?>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(253) ?>"></span></td>
                <td class=""><b><?= Language::string(122) ?>:</b></td>
            </tr>
        </table>
    </legend>
    <select id="formTestSelectSectionType" class="fullWidth ui-widget-content ui-corner-all">
        <optgroup label="<?= Language::string(147) ?>">
            <?php
            foreach (DS_TestSectionType::get_all_selectable() as $section) {
                ?>
                <option id="optionSectionType<?= $section->id ?>" value="<?= $section->id ?>" class="<?= $section->id == DS_TestSectionType::LOWER_LEVEL_R_CODE || $section->id == DS_TestSectionType::LOOP || $section->id == DS_TestSectionType::QTI_INITIALIZATION || $section->id == DS_TestSectionType::QTI_RESPONSE_PROCESSING ? User::view_class() : "" ?>" ><?= $section->get_name() ?> <?= $section->id == DS_TestSectionType::LOOP ? Language::string(621) : "" ?></option>
            <?php } ?>
        </optgroup>
        <?php
        $sql = $logged_user->mysql_list_rights_filter("CustomSection", "`name` ASC");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) > 0) {
            ?>
            <optgroup label="<?= Language::string(148) ?>" class="<?= User::view_class() ?>">
                <?php
                while ($r = mysql_fetch_array($z)) {
                    $cs = CustomSection::from_mysql_id($r[0]);
                    ?>
                    <option id="optionSectionType<?= DS_TestSectionType::CUSTOM ?>" value="<?= DS_TestSectionType::CUSTOM ?>:<?= $cs->id ?>" class="<?= User::view_class() ?>" ><?= $cs->name ?> ( <?= $cs->get_system_data() ?> )</option>
                    <?php
                }
                ?>
            </optgroup>
            <?php
        }
        ?>
        <?php
        $sql = $logged_user->mysql_list_rights_filter("Test", "`name` ASC");
        $z = mysql_query($sql);
        if (mysql_num_rows($z) > 0) {
            ?>
            <optgroup label="<?= Language::string(404) ?>">
                <?php
                while ($r = mysql_fetch_array($z)) {
                    if ($r[0] == $obj->id)
                        continue;
                    $cs = Test::from_mysql_id($r[0]);
                    ?>
                    <option id="optionSectionType<?= DS_TestSectionType::TEST ?>" value="<?= DS_TestSectionType::TEST ?>:<?= $cs->id ?>" ><?= $cs->name ?> ( <?= $cs->get_system_data() ?> )</option>
                    <?php
                }
                ?>
            </optgroup>
            <?php
        }
        ?>
    </select>
</fieldset>