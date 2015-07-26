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

if (isset($oid)) {
    if (!$logged_user->is_module_writeable($class_name))
        die(Language::string(81));

    $parameters = $obj->get_parameter_CustomSectionVariables();
    $returns = $obj->get_return_CustomSectionVariables();
    $code = $obj->code;
}
else {
    if (!$logged_user->is_module_writeable($_POST['class_name']))
        die(Language::string(81));

    $oid = $_POST['oid'];
    $obj = CustomSection::from_mysql_id($oid);

    if (!$logged_user->is_object_editable($obj))
        die(Language::string(81));

    $parameters = array();
    if (array_key_exists("parameters", $_POST)) {
        foreach ($_POST['parameters'] as $par) {
            array_push($parameters, json_decode($par));
        }
    }
    $returns = array();
    if (array_key_exists("returns", $_POST)) {
        foreach ($_POST['returns'] as $ret) {
            array_push($returns, json_decode($ret));
        }
    }
    $code = $_POST['code'];
    $class_name = $_POST['class_name'];
}
?>

<script>
    $(function(){
        CustomSection.uiRefreshComboboxes();
    
        Methods.iniTooltips();
        $(".tooltipCustomSectionLogic").tooltip({
            content:function(){
                return "<?= Language::string(104) ?><hr/>"+$(this).next().val();
            },
            position:{ my: "left top", at: "left bottom", offset: "15 0" }
        });
    });
</script>

<table class="fullWidth">
    <tr>
        <td style="width:50%;" valign="top" align="center">
            <fieldset class="ui-widget-content">
                <legend class="" align="center">
                    <table>
                        <tr>
                            <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(105) ?>"></span></td>
                            <td><b><?= Language::string(106) ?>:</b></td>
                        </tr>
                    </table>
                </legend>
                <div class="div<?= $class_name ?>Parameters">
                    <?php
                    if (count($parameters) > 0) {
                        ?>
                        <table class="fullWidth table<?= $class_name ?>Parameters">
                            <?php
                        }
                        $i = 0;
                        foreach ($parameters as $param) {
                            ?>
                            <tr>
                                <td>
                                    <span class="spanIcon tooltipCustomSectionLogic ui-icon ui-icon-document-b" onclick="CustomSection.uiEditDescription($(this).next())" title="<?= Language::string(107) ?>"></span>
                                    <textarea class="notVisible"><?= $param->description ?></textarea>
                                </td>
                                <td class="fullWidth">
                                    <input onchange="CustomSection.uiVarNameChanged($(this))" type="text" class="comboboxCustomSectionVars comboboxCustomSectionVarsParameter ui-widget-content ui-corner-all fullWidth" value="<?= htmlspecialchars($param->name, ENT_QUOTES) ?>" />
                                </td>
                                <td><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="CustomSection.uiRemoveParameter(<?= $i ?>)" title="<?= Language::string(110) ?>"></span></td>
                            </tr>
                            <?php
                            $i++;
                        }
                        if (count($parameters) > 0) {
                            ?>
                        </table>
                        <?php
                    } else {
                        ?>
                        <div class="ui-state-error padding margin" align="center"><?= Language::string(108) ?></div>
                        <?php
                    }
                    ?>
                    <div class="notVisible">
                        <?php
                        foreach ($parameters as $param) {
                            ?>
                            <input class="inputCustomSectionParameterVar" type="hidden" value="<?= $param->name ?>" />
                            <?php
                        }
                        ?>
                    </div>
                    <table>
                        <tr>
                            <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="CustomSection.uiAddParameter()" title="<?= Language::string(109) ?>"></span></td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </td>


        <td style="width:50%;" valign="top" align="center">
            <fieldset class="ui-widget-content">
                <legend class="" align="center">
                    <table>
                        <tr>
                            <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(111) ?>"></span></td>
                            <td><b><?= Language::string(113) ?>:</b></td>
                        </tr>
                    </table></legend>
                <div class="div<?= $class_name ?>Returns">
                    <?php
                    if (count($returns) > 0) {
                        ?>
                        <table class="fullWidth table<?= $class_name ?>Returns">
                            <?php
                        }
                        $i = 0;
                        foreach ($returns as $ret) {
                            ?>
                            <tr>
                                <td>
                                    <span class="spanIcon tooltipCustomSectionLogic ui-icon ui-icon-document-b" onclick="CustomSection.uiEditDescription($(this).next())" title="<?= Language::string(107) ?>"></span>
                                    <textarea class="notVisible"><?= $ret->description ?></textarea>
                                </td>
                                <td class="fullWidth">
                                    <input onchange="CustomSection.uiVarNameChanged($(this))" type="text" class="comboboxCustomSectionVars comboboxCustomSectionVarsReturn ui-widget-content ui-corner-all fullWidth" value="<?= htmlspecialchars($ret->name, ENT_QUOTES) ?>" />
                                </td>
                                <td><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="CustomSection.uiRemoveReturn(<?= $i ?>)" title="<?= Language::string(116) ?>"></span></td>
                            </tr>
                            <?php
                            $i++;
                        }
                        if (count($returns) > 0) {
                            ?>
                        </table>
                        <?php
                    } else {
                        ?>
                        <div class="ui-state-error padding margin" align="center"><?= Language::string(114) ?></div>
                        <?php
                    }
                    ?>
                    <div class="notVisible">
                        <?php
                        foreach ($returns as $ret) {
                            ?>
                            <input class="inputCustomSectionReturnVar" type="hidden" value="<?= $ret->name ?>" />
                            <?php
                        }
                        ?>
                    </div>
                    <table>
                        <tr>
                            <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="CustomSection.uiAddReturn()" title="<?= Language::string(115) ?>"></span></td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </td>
    </tr>
</table>

