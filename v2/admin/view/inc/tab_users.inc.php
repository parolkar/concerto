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

<table class="fullWidth noMarginPadding">
    <tr>
        <?php if ($user->is_superadmin()) { ?>
            <td class="noMarginPadding" valign="top" style="width:50%;">
                <div class="fullWidth ui-widget-content ui-corner-all" align="center"><?= Language::string(78) ?></div>
                <div class="fullWidth" align="center" id="divUserForm"><?php include Ini::$internal_path . 'admin/view/User_form.php'; ?></div><br />
                <div class="fullWidth" align="center" id="divUserList"><?php include Ini::$internal_path . 'admin/view/User_list.php'; ?></div>
            </td>
            <td class="noMarginPadding" valign="top" style="width:50%; padding-left: 10px;">
                <div class="fullWidth ui-widget-content ui-corner-all" align="center"><?= Language::string(79) ?></div>
                <div class="fullWidth" align="center" id="divGroupForm"><?php include Ini::$internal_path . 'admin/view/Group_form.php'; ?></div><br />
                <div class="fullWidth" align="center" id="divGroupList"><?php include Ini::$internal_path . 'admin/view/Group_list.php'; ?></div>
            </td>
        <?php } ?>
    </tr>
</table>