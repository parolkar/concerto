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

if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null)
{
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}
?>

<div class="divSectionSummary">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(7) ?>"></span></td>
            <td class="tdSectionColumnIcon"></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(7) ?></td>
            <td class="tdSectionColumnAction"></td>
            <td class="tdSectionColumnIcon <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnEnd <?= User::view_class() ?>"></td>
            <td class="tdSectionColumnIcon"></td>
            <td class="tdSectionColumnButton"></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?=$_POST['detail']==1 || $_POST['oid'] == 0 ?"":"notVisible"?>">

</div>