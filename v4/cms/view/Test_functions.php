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

$expanded = false;
if (isset($_POST) && array_key_exists("isFunctionToolbarExpanded", $_POST) && $_POST['isFunctionToolbarExpanded'] == 1) {
    $expanded = true;
}
?>

<script>
    $(function() {
        Methods.iniTooltips();
        Methods.iniIconButton(".btnFunctionToolbarControl", "<?= $expanded ? "minus" : "plus" ?>");

        $(".divFunctionToolbarContent").accordion({
            heightStyle: "auto"
        });
<?php if (!$expanded) { ?>
            $(".divFunctionToolbarContent").hide(0);
<?php } ?>

        $(".tooltipDocDescription").tooltip({
            tooltipClass: "tooltipWindow",
            position: {
                my: "left top",
                at: "left bottom",
                offset: "15 0"
            },
            content: function() {
                var html = Test.getDocContent($(this).next().val());
                var obj = $(html);
                var desc = "";
                obj.find("h3").each(function() {
                    if ($(this).html() == "Description")
                        desc = $(this).next().html();
                });
                return desc + "<br/><br/>" + dictionary["s709"];
            },
            show: false,
            hide: false
        });
    });
</script>

<div class="divFunctionToolbarContent">
    <h3><?= Language::string(695) ?></h3>
    <div>
        <table class='fullWidth'>
            <?php
            $count = 0;
            $concerto_package = RDocLibrary::from_property(array("name" => "concerto"), false);
            if ($concerto_package == null) {
                ?>
                <tr>
                    <td colspan='4' class='ui-state-error'><?= Language::string(697) ?></td>
                </tr>
                <?php
            } else {
                $sql = sprintf("SELECT * FROM `%s`.`%s` WHERE `RDocLibrary_id` = %s ORDER BY `name` ASC", Ini::$db_master_name, RDocFunction::get_mysql_table(), $concerto_package->id);
                $z = mysql_query($sql);
                while ($r = mysql_fetch_array($z)) {
                    $func = RDocFunction::from_mysql_result($r);
                    $count++;
                    $doc = RDoc::from_mysql_id($func->RDoc_id);
                    $doc_html = "";
                    if ($doc != null) {
                        $doc_html = $doc->HTML;
                    }
                    $adv = false;
                    $adv_functions = array(
                        "concerto.qti.get",
                        "concerto.qti.initialize",
                        "concerto.qti.responseProcessing",
                        "concerto.table.get",
                        "concerto.template.fillHTML",
                        "concerto.table.fillSQL",
                        "concerto.template.get",
                        "concerto.test.get",
                        "concerto.workspace.get"
                    );
                    ?>
                    <tr onmouseover='Test.uiMouseOverFunctionToolbarTr(<?= $count ?>)' class="<?= in_array($func->name, $adv_functions) ? User::view_class() : "" ?>">
                        <td class='tdFunctionToolbarIcon'>
                            <span class="spanIcon ui-icon ui-icon-help tooltipDocDescription" title="<?= Language::string(708) ?>" onclick='Test.uiDocDialog(Test.getDocContent($(this).next().val()))'></span>
                            <textarea class='notVisible'><?= $doc_html ?></textarea>
                        </td>
                        <td class='tdFunctionToolbarName tdFunctionToolbarIndexable tdFunctionToolbarIndex<?= $count ?>'><?= $func->name ?></td>
                        <td class='tdFunctionToolbarPackage tdFunctionToolbarIndexable tdFunctionToolbarIndex<?= $count ?>'><?= $concerto_package->name ?></td>
                        <td class='tdFunctionToolbarIcon'>
                            <span onclick='Test.uiAddFunctionWidgetFromToolbar($(this).parent().prev().prev().html(), Test.getDocContent($(this).parent().prev().prev().prev().find("textarea").val()))' class="spanIcon ui-icon ui-icon-arrow-1-e tooltip" title="<?= Language::string(699) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                if ($count == 0) {
                    ?>
                    <tr>
                        <td colspan='4' class='ui-state-error'><?= Language::string(697) ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
    </div>
    <h3><?= Language::string(696) ?></h3>
    <div>
        <table class='fullWidth'>
            <?php
            $count = 0;
            $sql = "SELECT `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`id`,
                            `" . Ini::$db_master_name . "`.`" . UserFunction::get_mysql_table() . "`.`id`
                            FROM `" . Ini::$db_master_name . "`.`" . UserFunction::get_mysql_table() . "` 
                            LEFT JOIN `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "` 
                                ON `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`id`=`" . Ini::$db_master_name . "`.`" . UserFunction::get_mysql_table() . "`.`RDocFunction_id`
                            WHERE `" . Ini::$db_master_name . "`.`" . UserFunction::get_mysql_table() . "`.`User_id`=" . $logged_user->id . "
                            ORDER BY `" . Ini::$db_master_name . "`.`" . RDocFunction::get_mysql_table() . "`.`name` ASC";
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z)) {
                $func = RDocFunction::from_mysql_id($r[0]);
                $count++;
                $doc = RDoc::from_mysql_id($func->RDoc_id);
                $lib = RDocLibrary::from_mysql_id($func->RDocLibrary_id);
                $doc_html = "";
                if ($doc != null) {
                    $doc_html = $doc->HTML;
                }
                ?>
                <tr onmouseover='Test.uiMouseOverFunctionToolbarTr(<?= $count ?>)'>
                    <td class='tdFunctionToolbarIcon'>
                        <span class="spanIcon ui-icon ui-icon-help tooltipDocDescription" title="<?= Language::string(708) ?>" onclick='Test.uiDocDialog(Test.getDocContent($(this).next().val()))'></span>
                        <textarea class='notVisible'><?= $doc_html ?></textarea>
                    </td>
                    <td class='tdFunctionToolbarName tdFunctionToolbarIndexable tdFunctionToolbarIndex<?= $count ?>'><?= $func->name ?></td>
                    <td class='tdFunctionToolbarPackage tdFunctionToolbarIndexable tdFunctionToolbarIndex<?= $count ?>'><?= $lib->name ?></td>
                    <td class='tdFunctionToolbarIcon'>
                        <span onclick='User.removeFavouriteFunction(<?= $r[1] ?>)' class="spanIcon ui-icon ui-icon-minus tooltip" title="<?= Language::string(700) ?>"></span>
                    </td>
                    <td class='tdFunctionToolbarIcon'>
                        <span onclick='Test.uiAddFunctionWidgetFromToolbar($(this).parent().prev().prev().prev().html(), Test.getDocContent($(this).parent().prev().prev().prev().prev().find("textarea").val()))' class="spanIcon ui-icon ui-icon-arrow-1-e tooltip" title="<?= Language::string(699) ?>"></span>
                    </td>
                </tr>
                <?php
            }
            if ($count == 0) {
                ?>
                <tr>
                    <td colspan='4' class='ui-state-error'><?= Language::string(698) ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
</div>
<div align='right'>
    <button class='btnFunctionToolbarControl ui-state-highlight' onclick='Test.uiToggleFunctionToolbar()'><?= Language::string(694) ?></button>
</div>