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

$vals = array();
if (isset($_POST['value'])) {
    $vals = $_POST['value'];
}
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0) {
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}

$table = Table::from_mysql_id($vals[5]);
$description = Language::string(372);
if ($table != null) {
    $description.=" " . Language::string(373) . ":<hr/>" . $table->get_description();
}
?>

<div class="divSectionSummary sortableHandle">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(5) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span id="spanExpandDetail_<?= $_POST['counter'] ?>" class="spanExpandDetail spanIcon ui-icon ui-icon-folder-<?= $_POST['detail'] == 1 ? "open" : "collapsed" ?> tooltip" title="<?= Language::string(390) ?>" onclick="Test.uiToggleDetails(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(5) ?></td>
            <td class="tdSectionColumnAction"><input onchange="Test.uiSetVarNameChanged($(this))" type="text" class="ui-state-focus comboboxSetVars comboboxVars controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all fullWidth" value="<?= htmlspecialchars(isset($vals[4]) ? $vals[4] : "", ENT_QUOTES) ?>" /></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-newwin tooltip" title="<?= Language::string(511) ?>" onclick="Test.duplicateSection(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnIcon <?= User::view_class() ?>"><span class="spanIcon ui-icon ui-icon-script tooltip" title="<?= Language::string(447) ?>" onclick="Test.convertToLowerLevel(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnEnd <?= User::view_class() ?>"><input type="checkbox" id="chkEndSection_<?= $_POST['counter'] ?>" class="chkEndSection tooltip" <?= $_POST['end'] == 1 ? "checked" : "" ?> title="<?= Language::string(369) ?>" /></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Test.uiRemoveSection(<?= $_POST['counter'] ?>)" title="<?= Language::string(59) ?>"></span></td>
            <td class="tdSectionColumnButton"><button class="btnAddSection noWrap" onclick="Test.uiAddLogicSection(0,<?= $_POST['counter'] ?>)"><?= Language::string(619) ?></button></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?= $_POST['detail'] == 1 || $_POST['oid'] == 0 ? "" : "notVisible" ?>">
    <div align="center">
        <b><?= Language::string(235) ?></b> <input type="radio" name="radioSetVarType_<?= $_POST['counter'] ?>" class="radioSetVarType_<?= $_POST['counter'] ?> radioSetVarType" <?= !isset($vals[2]) || $vals[2] == 0 ? "checked" : "" ?> value="0" onchange="Test.changeSetVarType(<?= $_POST['counter'] ?>)" />, 
        <b><?= Language::string(236) ?></b> <input type="radio" name="radioSetVarType_<?= $_POST['counter'] ?>" class="radioSetVarType_<?= $_POST['counter'] ?> radioSetVarType" <?= $vals[2] == 1 ? "checked" : "" ?> value="1" onchange="Test.changeSetVarType(<?= $_POST['counter'] ?>)" />
    </div>

    <div class="divSetVarType_0_<?= $_POST['counter'] ?> <?= isset($vals[2]) && $vals[2] != 0 ? "notVisible" : "" ?>">
        <table class="fullWidth">
            <tr>
                <td class="noWrap"><b><?= Language::string(435) ?>:</b></td>
                <td>
                    <span class="spanIcon ui-icon ui-icon-plus tooltip" title="<?= Language::string(205) ?>" onclick="Test.uiAddNewRelatedObject(5)"></span>
                </td>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars(Template::strip_html($description), ENT_QUOTES) ?>"></span></td>
                <?php if (isset($vals[5]) && $vals[5] != 0) { ?>
                    <td>
                        <span class="spanIcon ui-icon ui-icon-extlink tooltip" title="<?= Language::string(522) ?>" onclick="Test.uiGoToRelatedObject(5,<?= $vals[5] ?>)"></span>
                    </td>
                <?php } ?>
                <td class="fullWidth">
                    <select class="fullWidth controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all" onchange="Test.uiRefreshSectionContent(<?= $_POST['type'] ?>, <?= $_POST['counter'] ?>, Test.getSectionValues(Test.sectionDivToObject($('#divSection_<?= $_POST['counter'] ?>'))))">
                        <option value="0">&lt;<?= Language::string(239) ?>&gt;</option>
                        <?php
                        $sql = $logged_user->mysql_list_rights_filter("Table", "`name` ASC");
                        $z = mysql_query($sql);
                        while ($r = mysql_fetch_array($z)) {
                            $table = Table::from_mysql_id($r[0]);
                            ?>
                            <option value="<?= $table->id ?>" <?= isset($vals[5]) && $vals[5] == $table->id ? "selected" : "" ?> ><?= $table->name ?> ( <?= $table->get_system_data() ?> )</option>
                        <?php } ?>
                    </select>
                </td>
            </tr>    
        </table>
        <table class="fullWidth">
            <tr>
                <td style="width:50%;" valign="top" align="center">
                    <fieldset class="ui-widget-content">
                        <legend class="" align="center"><b><?= Language::string(436) ?>:</b></legend>
                        <table class="fullWidth">
                            <tr>
                                <td class="fullWidth">
                                    <select class="fullWidth controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_column ui-widget-content ui-corner-all fullWidth">
                                        <option value="0">&lt;<?= Language::string(241) ?>&gt;</option>
                                        <?php
                                        if (isset($vals[5])) {
                                            $table = Table::from_mysql_id($vals[5]);
                                            if ($table != null) {
                                                $cols = $table->get_TableColumns();
                                                foreach ($cols as $col) {
                                                    ?>
                                                    <option value="<?= $col->index ?>" <?= isset($vals[6]) && $vals[6] == $col->index ? "selected" : "" ?> ><?= $col->name ?></option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php
                            if (isset($vals[0])) {
                                for ($i = 1; $i <= $vals[0]; $i++) {
                                    ?>
                                    <tr>
                                        <td>
                                            <select class="fullWidth controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_column ui-widget-content ui-corner-all fullWidth">
                                                <option value="0">&lt;<?= Language::string(241) ?>&gt;</option>
                                                <?php
                                                if (isset($vals[5])) {
                                                    $table = Table::from_mysql_id($vals[5]);
                                                    if ($table != null) {
                                                        $cols = $table->get_TableColumns();
                                                        foreach ($cols as $col) {
                                                            ?>
                                                            <option value="<?= $col->index ?>" <?= isset($vals[6 + $i]) && $vals[6 + $i] == $col->index ? "selected" : "" ?> ><?= $col->name ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveSetVarColumn(<?= $_POST['counter'] ?>,<?= ($i - 1) ?>)" title="<?= Language::string(20) ?>"></span></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </table>

                        <table class="tableSetVarColumnControl_<?= $_POST['counter'] ?>">
                            <tr>
                                <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddSetVarColumn(<?= $_POST['counter'] ?>)" title="<?= Language::string(129) ?>"></span></td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
                <td style="width:50%;" valign="top" align="center">
                    <fieldset class="ui-widget-content">
                        <legend class="" align="center"><b><?= Language::string(437) ?>:</b></legend>
                        <?php
                        if (isset($vals[1])) {
                            $i = 7 + $vals[0];
                            for ($j = 1; $j <= $vals[1]; $j++) {
                                if ($j == 1) {
                                    ?>
                                    <table class="fullWidth">
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <select class="controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_link ui-widget-content ui-corner-all <?= ($j != 1 ? "" : "notVisible") ?>">
                                                <option value="AND" <?= isset($vals[$i]) && $vals[$i] == "AND" ? "selected" : "" ?>><?= Language::string(227) ?></option>
                                                <option value="OR" <?= isset($vals[$i]) && $vals[$i] == "OR" ? "selected" : "" ?>><?= Language::string(228) ?></option>
                                            </select>
                                        </td>
                                        <?php $i++; ?>
                                        <td>
                                            <select class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all">
                                                <option value="0">&lt;<?= Language::string(241) ?>&gt;</option>
                                                <?php
                                                if (isset($vals[5])) {
                                                    $table = Table::from_mysql_id($vals[5]);
                                                    if ($table != null) {
                                                        $cols = $table->get_TableColumns();
                                                        foreach ($cols as $col) {
                                                            ?>
                                                            <option value="<?= $col->index ?>" <?= isset($vals[$i]) && $vals[$i] == $col->index ? "selected" : "" ?> ><?= $col->name ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select> 
                                        </td>
                                        <td>
                                            <?php $i++; ?>
                                            <select class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all">
                                                <option value="!=" <?= isset($vals[$i]) && $vals[$i] == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
                                                <option value="=" <?= isset($vals[$i]) && $vals[$i] == "=" ? "selected" : "" ?>><?= Language::string(222) ?></option>
                                                <option value=">" <?= isset($vals[$i]) && $vals[$i] == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
                                                <option value=">=" <?= isset($vals[$i]) && $vals[$i] == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
                                                <option value="<" <?= isset($vals[$i]) && $vals[$i] == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
                                                <option value="<=" <?= isset($vals[$i]) && $vals[$i] == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
                                                <option value="LIKE" <?= isset($vals[$i]) && $vals[$i] == "LIKE" ? "selected" : "" ?>><?= Language::string(243) ?></option>
                                                <option value="NOT LIKE" <?= isset($vals[$i]) && $vals[$i] == "NOT LIKE" ? "selected" : "" ?>><?= Language::string(244) ?></option>
                                            </select> 
                                        </td>
                                        <?php $i++; ?>
                                        <td class="fullWidth">
                                            <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars(isset($vals[$i]) ? $vals[$i] : "", ENT_QUOTES) ?>" /> 
                                        </td>
                                        <td><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveSetVarCondition(<?= $_POST['counter'] ?>,<?= ($j - 1) ?>)" title="<?= Language::string(230) ?>"></span></td>
                                    </tr>
                                    <?php
                                    $i++;
                                    if ($j > $vals[1]) {
                                        ?>
                                    </table>
                                    <?php
                                }
                            }
                        }
                        ?>

                        <table class="tableSetVarConditionControl_<?= $_POST['counter'] ?>">
                            <tr>
                                <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddSetVarCondition(<?= $_POST['counter'] ?>)"  title="<?= Language::string(229) ?>"></span></td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
    </div>

    <div class="divSetVarType_1_<?= $_POST['counter'] ?> <?= !isset($vals[2]) || $vals[2] != 1 ? "notVisible" : "" ?>">
        <textarea id="textareaCodeMirror_<?= $_POST['counter'] ?>"><?= (isset($vals[3]) ? $vals[3] : "") ?></textarea>
    </div>
</div>