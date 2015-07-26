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

$owner = User::from_mysql_id($_POST['oid']);
?>
<script>
    Methods.iniTooltips();
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(649) ?>"></span></td>
                <td class=""><b><?= Language::string(677) ?></b></td>
            </tr>
        </table>
    </legend>
    <table>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(648) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(678) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin">
                    <select id = "selectUserInviteeShareDialog" class = "fullWidth ui-widget-content ui-corner-all">
                        <option value = "0">&lt;<?= Language::string(650) ?>&gt;</option>
                        <?php
                        $sql = sprintf("SELECT * FROM `%s`.`%s` WHERE `id`!='%s' ORDER BY `lastname` ASC, `firstname` ASC", Ini::$db_master_name, User::get_mysql_table(), $owner->id);
                        $z = mysql_query($sql);
                        while ($r = mysql_fetch_array($z)) {
                            $user = User::from_mysql_result($r);
                            ?>
                            <option value="<?= $user->id ?>" name="<?= $user->get_full_name() ?>" institution="<?= $user->institution_name ?>" <?= $_POST['current_invitee_id'] == $user->id ? "selected" : "" ?>><?= $user->get_full_description() ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(679) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(680) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin">
                    <select id = "selectUserWorkspaceShareDialog" class = "fullWidth ui-widget-content ui-corner-all">
                        <option value = "0">&lt;<?= Language::string(650) ?>&gt;</option>
                        <?php
                        $sql = sprintf("SELECT * FROM `%s`.`%s` WHERE `owner_id`='%s' ORDER BY `name` ASC", Ini::$db_master_name, UserWorkspace::get_mysql_table(), $owner->id);
                        $z = mysql_query($sql);
                        while ($r = mysql_fetch_array($z)) {
                            $ws = UserWorkspace::from_mysql_result($r);
                            $ignore = false;
                            if (!$ignore) {
                                ?>
                                <option value="<?= $ws->id ?>" name="<?= $ws->name ?>" <?= array_key_exists("current_workspace_id", $_POST) && $_POST['current_workspace_id'] == $ws->id ? "selected" : "" ?>><?= $ws->get_formatted_name() ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>

</fieldset>