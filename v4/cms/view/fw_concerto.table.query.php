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

//vars

$name = "concerto.table.query";

$db = User::get_current_db();
if (array_key_exists("db", $_POST))
    $workspace_id = $_POST['db'];

$table_name = "";
if (array_key_exists("table_name", $_POST))
    $table_name = $_POST['table_name'];

$type = "SELECT";
if (array_key_exists("type", $_POST))
    $type = $_POST['type'];

$ws = UserWorkspace::from_property(array("db_name" => $db), false);
TestSession::change_db($ws->id);

$table = Table::from_property(array("name" => $table_name), false);
$table_columns = array();
if ($table != null)
    $table_columns = $table->get_columns();

$select_section = array(array("v" => 0, "w0" => "*", "c" => "*"));
$select_section = json_encode($select_section);
if (array_key_exists('select_section', $_POST)) {
    $select_section = $_POST['select_section'];
}
$select_section = json_decode($select_section);
if (array_key_exists("select_section_add", $_POST) && $_POST['select_section_add'] == 1)
    array_push($select_section, json_decode(json_encode(array("v" => 0, "w0" => "*", "c" => "*"))));

$where_section = array();
$where_section = json_encode($where_section);
if (array_key_exists('where_section', $_POST)) {
    $where_section = $_POST['where_section'];
}
$where_section = json_decode($where_section);
$where_count = count($where_section);
if (array_key_exists("where_section_add", $_POST) && $_POST['where_section_add'] == 1)
    array_push($where_section, json_decode(json_encode(array("v" => 0, "w0" => "AND", "w1" => "id", "w2" => "=", "w3" => "", "c" => ($where_count > 0 ? "AND" : "") . " `id` ="))));
$where_count = count($where_section);

$order_section = array();
$order_section = json_encode($order_section);
if (array_key_exists('order_section', $_POST)) {
    $order_section = $_POST['order_section'];
}
$order_section = json_decode($order_section);
$order_count = count($order_section);
if (array_key_exists("order_section_add", $_POST) && $_POST['order_section_add'] == 1)
    array_push($order_section, json_decode(json_encode(array("v" => 0, "w0" => "id", "w1" => "ASC", "c" => "`id` ASC"))));
$order_count = count($order_section);

$group_section = array();
$group_section = json_encode($group_section);
if (array_key_exists('group_section', $_POST)) {
    $group_section = $_POST['group_section'];
}
$group_section = json_decode($group_section);
$group_count = count($group_section);
if (array_key_exists("group_section_add", $_POST) && $_POST['group_section_add'] == 1)
    array_push($group_section, json_decode(json_encode(array("v" => 0, "w0" => "id", "w1" => "ASC", "c" => "`id` ASC"))));
$group_count = count($group_section);

$having_section = array();
$having_section = json_encode($having_section);
if (array_key_exists('having_section', $_POST)) {
    $having_section = $_POST['having_section'];
}
$having_section = json_decode($having_section);
$having_count = count($having_section);
if (array_key_exists("having_section_add", $_POST) && $_POST['having_section_add'] == 1)
    array_push($having_section, json_decode(json_encode(array("v" => 0, "w0" => "AND", "w1" => "id", "w2" => "=", "w3" => "", "c" => ($having_count > 0 ? "AND" : "") . " `id` ="))));
$having_count = count($having_section);

$limit_section = array("w0" => 0, "w1" => 0, "w2" => 30);
if (array_key_exists('limit_section', $_POST)) {
    $limit_section = $_POST['limit_section'];
    $limit_section = json_decode($limit_section, true);
}

if (!array_key_exists("w0", $limit_section) || !$limit_section["w0"])
    $limit_section["w0"] = 0;
if (!array_key_exists("w1", $limit_section) || !$limit_section["w1"])
    $limit_section["w1"] = 0;
if (!array_key_exists("w2", $limit_section) || !$limit_section["w2"])
    $limit_section["w2"] = 30;

$set_section = array();
$set_section = json_encode($set_section);
if (array_key_exists('set_section', $_POST)) {
    $set_section = $_POST['set_section'];
}
$set_section = json_decode($set_section);
$set_count = count($set_section);
if (array_key_exists("set_section_add", $_POST) && $_POST['set_section_add'] == 1)
    array_push($set_section, json_decode(json_encode(array("v" => 0, "w0" => "id", "w1" => "", "c" => " `id` ="))));
$set_count = count($set_section);

$params = "list()";
if (array_key_exists("params", $_POST))
    $params = $_POST['params'];
?>

<script>
    $(function() {
        Methods.iniTooltips();
        $(".divFWradioMenu").buttonset();
        Methods.iniCodeMirror("taFWarg-params", "r", false, true, false, false);
    });
</script>

<!-- params -->
<table class='fullWidth <?= User::view_class() ?>'>
    <tr argName="params">
        <td class='divFunctionWidgetArgTableDescColumn divFunctionWidgetArgTable'>
            <span class="spanIcon ui-icon ui-icon-help functionArgTooltip" title="<?= Language::string(753) ?>"></span>
        </td>
        <td class='divFunctionWidgetArgTableNameColumn divFunctionWidgetArgTable noWrap'><?= Language::string(106) ?></td>
        <td class='divFunctionWidgetArgTableValueColumn divFunctionWidgetArgTable'>
            <textarea id='taFWarg-params' class='notVisible'><?= $params ?></textarea>
        </td>
    </tr>
</table>

<!-- workspace and table -->
<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(711) ?>"></span></td>
                <td class=""><b><?= Language::string(710) ?></b></td>
            </tr>
        </table>
    </legend>
    <div class="divFormElement" style="width:50%;">
        <table class="fullWidth">
            <tr>
                <td class="noWrap tdFormLabel"><?= Language::string(679) ?>:</td>
                <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(712) ?>"></span></td>
                <td>
                    <div class="divFormControl">
                        <select id="selectFWdb" class="fullWidth ui-widget-content ui-corner-all" onchange="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')">
                            <?php
                            $current_db = $db;

                            $label_added = false;
                            foreach ($logged_user->get_workspaces() as $workspace) {
                                if (!$label_added) {
                                    $label_added = true;
                                    ?>
                                    <optgroup label="<?= $logged_user->get_full_name() ?>">
                                        <?php
                                    }
                                    ?>
                                    <option value="<?= $workspace->db_name ?>" <?= $current_db == $workspace->db_name ? "selected" : "" ?> ><?= $workspace->get_formatted_name() ?></option>
                                    <?php
                                }
                                ?>
                            </optgroup>
                            <?php
                            $sql = sprintf("SELECT `id` FROM `%s`.`%s` WHERE `id`!=%s ORDER BY `lastname` ASC, `firstname` ASC", Ini::$db_master_name, User::get_mysql_table(), $logged_user->id);
                            $z = mysql_query($sql);
                            while ($r = mysql_fetch_array($z)) {
                                $label_added = false;
                                $user = User::from_mysql_id($r[0]);
                                foreach ($user->get_workspaces() as $workspace) {
                                    if ($logged_user->is_workspace_accessible($workspace->db_name)) {
                                        if (!$label_added) {
                                            $label_added = true;
                                            ?>
                                            <optgroup label="<?= $user->get_full_name() ?>">
                                                <?php
                                            }
                                            ?>
                                            <option value="<?= $workspace->db_name ?>" <?= $current_db == $workspace->db_name ? "selected" : "" ?> ><?= $workspace->get_formatted_name() ?></option>
                                            <?php
                                        }
                                    }
                                }
                                if ($label_added) {
                                    ?>
                                </optgroup>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divFormElement" style="width:50%;">
        <table class="fullWidth">
            <tr>
                <td class="noWrap tdFormLabel"><?= Language::string(713) ?>:</td>
                <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(710) ?>"></span></td>
                <td>
                    <div class="divFormControl">
                        <select id="selectFWtable" class="fullWidth ui-widget-content ui-corner-all" onchange="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')">
                            <option value="0">&lt;<?= Language::string(73) ?>&gt;</option>
                            <?php
                            $sql = sprintf("SELECT * FROM `%s`.`%s` ORDER BY `name` ASC ", $db, Table::get_mysql_table());
                            $z = mysql_query($sql);
                            while ($r = mysql_fetch_array($z)) {
                                $t = Table::from_mysql_result($r);
                                ?>
                                <option value="<?= $t->name ?>" <?= $t->name == $table_name ? "selected" : "" ?>><?= $t->id . ". " . $t->name ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</fieldset>

<div align="center" class="ui-state-error <?= $table != null ? "notVisible" : "" ?>">
    <?= Language::string(24) ?>
</div>
<?php
if ($table == null) {
    exit();
}
?>

<!-- query type -->
<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(715) ?>"></span></td>
                <td class=""><b><?= Language::string(714) ?></b></td>
            </tr>
        </table>
    </legend>
    <div class="divFormElement" style="width:100%;" align="center">
        <label for="radioFWtype0"><input type="radio" id="radioFWtype0" name="radioFWtype" onclick="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')" value="SELECT" <?= $type == "SELECT" ? "checked" : "" ?> /><?= Language::string(716) ?></label>
        <label for="radioFWtype1"><input type="radio" id="radioFWtype1" name="radioFWtype" onclick="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')" value="INSERT" <?= $type == "INSERT" ? "checked" : "" ?> /><?= Language::string(717) ?></label>
        <label for="radioFWtype2"><input type="radio" id="radioFWtype2" name="radioFWtype" onclick="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')" value="UPDATE" <?= $type == "UPDATE" ? "checked" : "" ?> /><?= Language::string(718) ?></label>
        <label for="radioFWtype3"><input type="radio" id="radioFWtype3" name="radioFWtype" onclick="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')" value="REPLACE" <?= $type == "REPLACE" ? "checked" : "" ?> /><?= Language::string(720) ?></label>
        <label for="radioFWtype4"><input type="radio" id="radioFWtype4" name="radioFWtype" onclick="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')" value="DELETE" <?= $type == "DELETE" ? "checked" : "" ?> /><?= Language::string(719) ?></label>
    </div>
</fieldset>

<!-- select columns -->
<?php if ($type == "SELECT") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(721) ?>"></span></td>
                    <td class=""><b><?= Language::string(602) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWselectSection">
                <?php
                for ($i = 0; $i < count($select_section); $i++) {
                    ?>
                    <script>
        Test.functionWizardCM["tdFWselect<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWselectSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWselectRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWselectRadioMenuWizard<?= $i ?>" name="radioFWselectRadioMenu<?= $i ?>" <?= $select_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWselect<?= $i ?>', 0)" />
                                <label for="radioFWselectRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWselectRadioMenuCode<?= $i ?>" name="radioFWselectRadioMenu<?= $i ?>" <?= $select_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWselect<?= $i ?>', 1)" />
                                <label for="radioFWselectRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWselect<?= $i ?> fullWidth  <?= $select_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWselect<?= $i ?>-0">
                                <select id="selectFWselectColumn<?= $i ?>" class="fullWidth ui-widget-content ui-corner-all" onchange="Test.functionWizardCM['tdFWselect<?= $i ?>'].setValue($(this).val())">
                                    <option value="*">&lt;<?= Language::string(724) ?>&gt;</option>
                                    <?php
                                    foreach ($table_columns as $col) {
                                        ?>
                                        <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $select_section[$i]->w0 ? "selected" : "" ?>><?= $col->name ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="tdFWselect<?= $i ?>-1  <?= $select_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
        Test.functionWizardCM["tdFWselect<?= $i ?>"] = Methods.iniCodeMirror("taFWselectCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWselectCode<?= $i ?>" class="notVisible"><?= $select_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td class="<?= $i == 0 ? "notVisible" : "" ?>">
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWselectSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'select_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- set -->
<?php if ($type == "INSERT" || $type == "REPLACE" || $type == "UPDATE") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(751) ?>"></span></td>
                    <td class=""><b><?= Language::string(750) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWsetSection">
                <?php
                for ($i = 0; $i < count($set_section); $i++) {
                    ?>
                    <script>
        Test.functionWizardCM["tdFWset<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWsetSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWsetRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWsetRadioMenuWizard<?= $i ?>" name="radioFWsetRadioMenu<?= $i ?>" <?= $set_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWset<?= $i ?>', 0)" />
                                <label for="radioFWsetRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWsetRadioMenuCode<?= $i ?>" name="radioFWsetRadioMenu<?= $i ?>" <?= $set_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWset<?= $i ?>', 1)" />
                                <label for="radioFWsetRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWset<?= $i ?> fullWidth  <?= $set_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWset<?= $i ?>-0">
                                <table class="fullWidth">
                                    <tr>
                                        <td>
                                            <select id="selectFWsetColumn<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                Test.functionWizardCM['tdFWset<?= $i ?>'].setValue($('#selectFWsetColumn<?= $i ?>').val() + ' = ');">
                                                        <?php
                                                        foreach ($table_columns as $col) {
                                                            ?>
                                                    <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $set_section[$i]->w0 ? "selected" : "" ?>><?= $col->name ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td class="fullWidth">
                                            <script>
        Test.functionWizardCM["tdFWset<?= $i ?>-w1"] = Methods.iniCodeMirror("taFWsetCode<?= $i ?>-w1", "r", false, true, false, false);
                                            </script>
                                            <textarea id="taFWsetCode<?= $i ?>-w1" class="notVisible"><?= $set_section[$i]->w1 ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tdFWset<?= $i ?>-1  <?= $set_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
                                    Test.functionWizardCM["tdFWset<?= $i ?>"] = Methods.iniCodeMirror("taFWsetCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWsetCode<?= $i ?>" class="notVisible"><?= $set_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td>
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWsetSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center" class="ui-state-highlight <?= $set_count > 0 ? "notVisible" : "" ?>">
                <?= Language::string(752) ?>
            </div>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'set_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- where -->
<?php if ($type == "SELECT" || $type == "DELETE" || $type == "UPDATE") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(727) ?>"></span></td>
                    <td class=""><b><?= Language::string(726) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWwhereSection">
                <?php
                for ($i = 0; $i < count($where_section); $i++) {
                    ?>
                    <script>
                                    Test.functionWizardCM["tdFWwhere<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWwhereSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWwhereRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWwhereRadioMenuWizard<?= $i ?>" name="radioFWwhereRadioMenu<?= $i ?>" <?= $where_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWwhere<?= $i ?>', 0)" />
                                <label for="radioFWwhereRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWwhereRadioMenuCode<?= $i ?>" name="radioFWwhereRadioMenu<?= $i ?>" <?= $where_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWwhere<?= $i ?>', 1)" />
                                <label for="radioFWwhereRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWwhere<?= $i ?> fullWidth  <?= $where_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWwhere<?= $i ?>-0">
                                <table class="fullWidth">
                                    <tr>
                                        <td style="<?= $i == 0 ? "visibility:hidden;" : "" ?>">
                                            <select id="selectFWwhereCondLink<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWwhere<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWwhereCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWwhereColumn<?= $i ?>').val() + ' ' + $('#selectFWwhereOperator<?= $i ?>').val());
                                                    ">
                                                <option value="AND" <?= $where_section[$i]->w0 == "AND" ? "selected" : "" ?>><?= Language::string(227) ?></option>
                                                <option value="OR" <?= $where_section[$i]->w0 == "OR" ? "selected" : "" ?>><?= Language::string(228) ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWwhereColumn<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWwhere<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWwhereCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWwhereColumn<?= $i ?>').val() + ' ' + $('#selectFWwhereOperator<?= $i ?>').val());
                                                    ">
                                                        <?php
                                                        foreach ($table_columns as $col) {
                                                            ?>
                                                    <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $where_section[$i]->w1 ? "selected" : "" ?>><?= $col->name ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWwhereOperator<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="                                                 Test.functionWizardCM['tdFWwhere<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWwhereCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWwhereColumn<?= $i ?>').val() + ' ' + $('#selectFWwhereOperator<?= $i ?>').val());
                                                    ">
                                                <option value="=" <?= $where_section[$i]->w2 == "=" ? "selected" : "" ?>><?= Language::string(222) ?></option>
                                                <option value="!=" <?= $where_section[$i]->w2 == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
                                                <option value=">" <?= $where_section[$i]->w2 == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
                                                <option value=">=" <?= $where_section[$i]->w2 == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
                                                <option value="<" <?= $where_section[$i]->w2 == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
                                                <option value="<=" <?= $where_section[$i]->w2 == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
                                                <option value="LIKE" <?= $where_section[$i]->w2 == "LIKE" ? "selected" : "" ?>><?= Language::string(728) ?></option>
                                                <option value="NOT LIKE" <?= $where_section[$i]->w2 == "NOT LIKE" ? "selected" : "" ?>><?= Language::string(729) ?></option>
                                            </select>
                                        </td>
                                        <td class="fullWidth">
                                            <script>
                                    Test.functionWizardCM["tdFWwhere<?= $i ?>-w3"] = Methods.iniCodeMirror("taFWwhereCode<?= $i ?>-w3", "r", false, true, false, false);
                                            </script>
                                            <textarea id="taFWwhereCode<?= $i ?>-w3" class="notVisible"><?= $where_section[$i]->w3 ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tdFWwhere<?= $i ?>-1  <?= $where_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
                                    Test.functionWizardCM["tdFWwhere<?= $i ?>"] = Methods.iniCodeMirror("taFWwhereCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWwhereCode<?= $i ?>" class="notVisible"><?= $where_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td>
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWwhereSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center" class="ui-state-highlight <?= $where_count > 0 ? "notVisible" : "" ?>">
                <?= Language::string(730) ?>
            </div>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'where_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- group by -->
<?php if ($type == "SELECT") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin <?= User::view_class() ?>">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(737) ?>"></span></td>
                    <td class=""><b><?= Language::string(736) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWgroupSection">
                <?php
                for ($i = 0; $i < count($group_section); $i++) {
                    ?>
                    <script>
                                    Test.functionWizardCM["tdFWorder<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWgroupSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWorderRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWgroupRadioMenuWizard<?= $i ?>" name="radioFWgroupRadioMenu<?= $i ?>" <?= $group_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWgroup<?= $i ?>', 0)" />
                                <label for="radioFWgroupRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWgroupRadioMenuCode<?= $i ?>" name="radioFWgroupRadioMenu<?= $i ?>" <?= $group_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWgroup<?= $i ?>', 1)" />
                                <label for="radioFWgroupRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWgroup<?= $i ?> fullWidth  <?= $group_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWgroup<?= $i ?>-0">
                                <table class="fullWidth">
                                    <tr>
                                        <td class="fullWidth">
                                            <select id="selectFWgroupColumn<?= $i ?>" class="fullWidth ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWorder<?= $i ?>'].setValue($('#selectFWgroupColumn<?= $i ?>').val() + ' ' + $('#selectFWgroupDir<?= $i ?>').val());
                                                    ">
                                                        <?php
                                                        foreach ($table_columns as $col) {
                                                            ?>
                                                    <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $group_section[$i]->w0 ? "selected" : "" ?>><?= $col->name ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWgroupDir<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWgroup<?= $i ?>'].setValue($('#selectFWgroupColumn<?= $i ?>').val() + ' ' + $('#selectFWgroupDir<?= $i ?>').val());
                                                    ">
                                                <option value="ASC" <?= "ASC" == $group_section[$i]->w1 ? "selected" : "" ?>><?= Language::string(733) ?></option>
                                                <option value="DESC" <?= "DESC" == $group_section[$i]->w1 ? "selected" : "" ?>><?= Language::string(734) ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tdFWgroup<?= $i ?>-1  <?= $group_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
                                    Test.functionWizardCM["tdFWgroup<?= $i ?>"] = Methods.iniCodeMirror("taFWgroupCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWgroupCode<?= $i ?>" class="notVisible"><?= $group_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td>
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWgroupSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center" class="ui-state-highlight <?= $group_count > 0 ? "notVisible" : "" ?>">
                <?= Language::string(738) ?>
            </div>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'group_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- having -->
<?php if ($type == "SELECT") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin <?= User::view_class() ?>">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(740) ?>"></span></td>
                    <td class=""><b><?= Language::string(739) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWhavingSection">
                <?php
                for ($i = 0; $i < count($having_section); $i++) {
                    ?>
                    <script>
                                    Test.functionWizardCM["tdFWhaving<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWhavingSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWhavingRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWhavingRadioMenuWizard<?= $i ?>" name="radioFWhavingRadioMenu<?= $i ?>" <?= $having_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWhaving<?= $i ?>', 0)" />
                                <label for="radioFWhavingRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWhavingRadioMenuCode<?= $i ?>" name="radioFWhavingRadioMenu<?= $i ?>" <?= $having_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWhaving<?= $i ?>', 1)" />
                                <label for="radioFWhavingRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWhaving<?= $i ?> fullWidth  <?= $having_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWhaving<?= $i ?>-0">
                                <table class="fullWidth">
                                    <tr>
                                        <td style="<?= $i == 0 ? "visibility:hidden;" : "" ?>">
                                            <select id="selectFWhavingCondLink<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWhaving<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWhavingCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWhavingColumn<?= $i ?>').val() + ' ' + $('#selectFWhavingOperator<?= $i ?>').val());
                                                    ">
                                                <option value="AND" <?= $having_section[$i]->w0 == "AND" ? "selected" : "" ?>><?= Language::string(227) ?></option>
                                                <option value="OR" <?= $having_section[$i]->w0 == "OR" ? "selected" : "" ?>><?= Language::string(228) ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWhavingColumn<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWhaving<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWhavingCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWhavingColumn<?= $i ?>').val() + ' ' + $('#selectFWhavingOperator<?= $i ?>').val());
                                                    ">
                                                        <?php
                                                        foreach ($table_columns as $col) {
                                                            ?>
                                                    <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $having_section[$i]->w1 ? "selected" : "" ?>><?= $col->name ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWhavingOperator<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWhaving<?= $i ?>'].setValue((<?= $i ?> != 0 ? $('#selectFWhavingCondLink<?= $i ?>').val() : '') + ' ' + $('#selectFWhavingColumn<?= $i ?>').val() + ' ' + $('#selectFWhavingOperator<?= $i ?>').val());
                                                    ">
                                                <option value="=" <?= $having_section[$i]->w2 == "=" ? "selected" : "" ?>><?= Language::string(222) ?></option>
                                                <option value="!=" <?= $having_section[$i]->w2 == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
                                                <option value=">" <?= $having_section[$i]->w2 == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
                                                <option value=">=" <?= $having_section[$i]->w2 == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
                                                <option value="<" <?= $having_section[$i]->w2 == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
                                                <option value="<=" <?= $having_section[$i]->w2 == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
                                                <option value="LIKE" <?= $having_section[$i]->w2 == "LIKE" ? "selected" : "" ?>><?= Language::string(728) ?></option>
                                                <option value="NOT LIKE" <?= $having_section[$i]->w2 == "NOT LIKE" ? "selected" : "" ?>><?= Language::string(729) ?></option>
                                            </select>
                                        </td>
                                        <td class="fullWidth">
                                            <script>
                                    Test.functionWizardCM["tdFWhaving<?= $i ?>-w3"] = Methods.iniCodeMirror("taFWhavingCode<?= $i ?>-w3", "r", false, true, false, false);
                                            </script>
                                            <textarea id="taFWhavingCode<?= $i ?>-w3" class="notVisible"><?= $having_section[$i]->w3 ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tdFWhaving<?= $i ?>-1  <?= $having_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
                                    Test.functionWizardCM["tdFWhaving<?= $i ?>"] = Methods.iniCodeMirror("taFWhavingCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWhavingCode<?= $i ?>" class="notVisible"><?= $having_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td>
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWhavingSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center" class="ui-state-highlight <?= $having_count > 0 ? "notVisible" : "" ?>">
                <?= Language::string(741) ?>
            </div>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'having_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- order by -->
<?php if ($type == "SELECT" || $type == "DELETE" || $type == "UPDATE") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin <?= $type != "SELECT" && $type != "DELETE" && $type != "UPDATE" ? "notVisible" : "" ?>">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(732) ?>"></span></td>
                    <td class=""><b><?= Language::string(731) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:100%; height: auto;" align="left">
            <table class="fullWidth tableFWorderSection">
                <?php
                for ($i = 0; $i < count($order_section); $i++) {
                    ?>
                    <script>
                                    Test.functionWizardCM["tdFWorder<?= $i ?>"] = null;
                    </script>
                    <tr class="trFWorderSectionElem<?= $i ?>" index="<?= $i ?>">
                        <td class="noWrap">
                            <div align="center" class="divFWradioMenu <?= User::view_class() ?>" id="divFWorderRadioMenu<?= $i ?>">
                                <input type="radio" id="radioFWorderRadioMenuWizard<?= $i ?>" name="radioFWorderRadioMenu<?= $i ?>" <?= $order_section[$i]->v == 0 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWorder<?= $i ?>', 0)" />
                                <label for="radioFWorderRadioMenuWizard<?= $i ?>"><?= Language::string(722) ?></label>
                                <input type="radio" id="radioFWorderRadioMenuCode<?= $i ?>" name="radioFWorderRadioMenu<?= $i ?>" <?= $order_section[$i]->v == 1 ? "checked" : "" ?> onclick="Test.uiFunctionWizardToggleView('.tdFWorder<?= $i ?>', 1)" />
                                <label for="radioFWorderRadioMenuCode<?= $i ?>"><?= Language::string(723) ?></label>
                            </div>
                        </td>
                        <td class="tdFWorder<?= $i ?> fullWidth  <?= $order_section[$i]->v == 0 ? "" : "notVisible" ?>">
                            <div class="tdFWorder<?= $i ?>-0">
                                <table class="fullWidth">
                                    <tr>
                                        <td class="fullWidth">
                                            <select id="selectFWorderColumn<?= $i ?>" class="fullWidth ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWorder<?= $i ?>'].setValue($('#selectFWorderColumn<?= $i ?>').val() + ' ' + $('#selectFWorderDir<?= $i ?>').val());
                                                    ">
                                                        <?php
                                                        foreach ($table_columns as $col) {
                                                            ?>
                                                    <option value="`<?= $col->name ?>`" <?= "`" . $col->name . "`" == $order_section[$i]->w0 ? "selected" : "" ?>><?= $col->name ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="selectFWorderDir<?= $i ?>" class="ui-widget-content ui-corner-all" onchange="
                                                Test.functionWizardCM['tdFWorder<?= $i ?>'].setValue($('#selectFWorderColumn<?= $i ?>').val() + ' ' + $('#selectFWorderDir<?= $i ?>').val());
                                                    ">
                                                <option value="ASC" <?= "ASC" == $order_section[$i]->w1 ? "selected" : "" ?>><?= Language::string(733) ?></option>
                                                <option value="DESC" <?= "DESC" == $order_section[$i]->w1 ? "selected" : "" ?>><?= Language::string(734) ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tdFWorder<?= $i ?>-1  <?= $order_section[$i]->v == 0 ? "notVisible" : "" ?>">
                                <script>
                                    Test.functionWizardCM["tdFWorder<?= $i ?>"] = Methods.iniCodeMirror("taFWorderCode<?= $i ?>", "mysql", false, false, false, false);
                                </script>
                                <textarea id="taFWorderCode<?= $i ?>" class="notVisible"><?= $order_section[$i]->c ?></textarea>
                            </div>
                        </td>
                        <td>
                            <span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveFWsectionElem('<?= $name ?>', '.trFWorderSectionElem<?= $i ?>')" title="<?= Language::string(20) ?>"></span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div align="center" class="ui-state-highlight <?= $order_count > 0 ? "notVisible" : "" ?>">
                <?= Language::string(735) ?>
            </div>
            <div align="center">
                <span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddFWsectionElem('<?= $name ?>', 'order_section')" title="<?= Language::string(725) ?>"></span>
            </div>
        </div>
    </fieldset>
<?php } ?>

<!-- limit -->
<?php if ($type == "SELECT" || $type == "DELETE" || $type == "UPDATE") { ?>
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(743) ?>"></span></td>
                    <td class=""><b><?= Language::string(742) ?></b></td>
                </tr>
            </table>
        </legend>
        <div class="divFormElement" style="width:30%;" align="left">
            <select class="fullWidth ui-widget-content ui-corner-all" id="radioFWlimit" name="radioFWlimit" onchange="Test.uiRefreshExtendedFunctionWizard('<?= $name ?>')">
                <option value="0" <?= $limit_section["w0"] == "0" ? "selected" : "" ?> ><?= Language::string(744) ?></option>
                <option value="1" <?= $limit_section["w0"] == "1" ? "selected" : "" ?> ><?= Language::string(745) ?></option>
            </select>
        </div>
        <div class="divFormElement <?= $limit_section["w0"] == 0 ? "notVisible" : "" ?>" style="width:35%;" align="left">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel"><?= Language::string(746) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(747) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input id="selectFWlimitOffset" class="fullWidth ui-widget-content ui-corner-all" value="<?= $limit_section["w1"] ?>" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="divFormElement <?= $limit_section["w0"] == 0 ? "notVisible" : "" ?>" style="width:35%;" align="left">
            <table class="fullWidth">
                <tr>
                    <td class="noWrap tdFormLabel"><?= Language::string(748) ?>:</td>
                    <td class="tdFormIcon"><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(749) ?>"></span></td>
                    <td>
                        <div class="divFormControl">
                            <input id="selectFWlimitNumber" class="fullWidth ui-widget-content ui-corner-all" value="<?= $limit_section["w2"] ?>" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>
<?php } ?>