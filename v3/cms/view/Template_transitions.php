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
    if (!$logged_user->is_object_editable($obj))
        die(Language::string(81));
}
else {
    $oid = $_POST['oid'];
    $obj = Template::from_mysql_id($oid);

    $class_name = $_POST['class_name'];

    if (!$logged_user->is_module_writeable($class_name))
        die(Language::string(81));
    if (!$logged_user->is_object_editable($obj))
        die(Language::string(81));
}

$effect_show = $obj->effect_show;
if (array_key_exists("effect_show", $_POST)) {
    $effect_show = $_POST['effect_show'];
}
if ($effect_show == "")
    $effect_show = "none";

$effect_show_options = $obj->effect_show_options;
if (array_key_exists("effect_show_options", $_POST)) {
    $effect_show_options = $_POST['effect_show_options'];
}

$effect_hide = $obj->effect_hide;
if (array_key_exists("effect_hide", $_POST)) {
    $effect_hide = $_POST['effect_hide'];
}
if ($effect_hide == "")
    $effect_hide = "none";

$effect_hide_options = $obj->effect_hide_options;
if (array_key_exists("effect_hide_options", $_POST)) {
    $effect_hide_options = $_POST['effect_hide_options'];
}
?>

<script>
    $(function(){
        Methods.iniTooltips();
        Template.setEffectOptions(true, "<?= addcslashes($effect_show_options, '"') ?>");
        Template.setEffectOptions(false, "<?= addcslashes($effect_hide_options, '"') ?>");
    });
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(540) ?>"></span></td>
                <td class=""><b><?= Language::string(539) ?></b></td>
            </tr>
        </table>
    </legend>

    <table class="fullWidth">
        <tr>
            <td style="width:50%;" valign="top" align="center">
                <fieldset class="ui-widget-content">
                    <legend class="" align="center">
                        <table>
                            <tr>
                                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(543) ?>"></span></td>
                                <td><b><?= Language::string(541) ?>:</b></td>
                            </tr>
                        </table>
                    </legend>

                    <table class="fullWidth">
                        <tr>
                            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(545) ?>:</td>
                            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(546) ?>"></span></td>
                            <td class="fullWidth">
                                <div class="horizontalMargin">
                                    <select id="form<?= $class_name ?>SelectEffectShow" class="fullWidth ui-widget-content ui-corner-all" onchange="Template.uiChangeEffect(true)">
                                        <option value="none">&lt;<?= Language::string(73) ?>&gt;</option>
                                        <option value="blind" <?= ($effect_show == "blind" ? "selected" : "") ?>>blind</option>
                                        <option value="clip" <?= ($effect_show == "clip" ? "selected" : "") ?>>clip</option>
                                        <option value="drop" <?= ($effect_show == "drop" ? "selected" : "") ?>>drop</option>
                                        <option value="explode" <?= ($effect_show == "explode" ? "selected" : "") ?>>explode</option>
                                        <option value="fade" <?= ($effect_show == "fade" ? "selected" : "") ?>>fade</option>
                                        <option value="fold" <?= ($effect_show == "fold" ? "selected" : "") ?>>fold</option>
                                        <option value="puff" <?= ($effect_show == "puff" ? "selected" : "") ?>>puff</option>
                                        <option value="slide" <?= ($effect_show == "slide" ? "selected" : "") ?>>slide</option>
                                        <option value="scale" <?= ($effect_show == "scale" ? "selected" : "") ?>>scale</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>

                            <?php if ($effect_show != "none") { ?>
                        <div id="div<?= $class_name ?>EffectShow" class="fullWidth" style="margin-top:20px;">
                            <table class="fullWidth">
                                <?php
                                switch ($effect_show) {
                                    case "blind": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(548) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowBlindDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "clip": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(548) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowClipDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "drop": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(553) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowDropDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="left"><?= Language::string(554) ?></option>
                                                            <option value="right"><?= Language::string(555) ?></option>
                                                            <option value="up"><?= Language::string(556) ?></option>
                                                            <option value="down"><?= Language::string(557) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "explode": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(559) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(558) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>ShowExplodePieces" class="fullWidth ui-widget-content ui-corner-all" value="9" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "fade": {
                                            break;
                                        }
                                    case "fold": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(560) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(561) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="checkbox" id="input<?= $class_name ?>ShowFoldHorizFirst" class="ui-widget-content ui-corner-all" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(562) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(563) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>ShowFoldSize" class="fullWidth ui-widget-content ui-corner-all" value="15" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "puff": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(564) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(565) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>ShowPuffPercent" class="fullWidth ui-widget-content ui-corner-all" value="150" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "slide": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(553) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowSlideDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="left"><?= Language::string(554) ?></option>
                                                            <option value="right"><?= Language::string(555) ?></option>
                                                            <option value="up"><?= Language::string(556) ?></option>
                                                            <option value="down"><?= Language::string(557) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "scale": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(568) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowScaleDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="both"><?= Language::string(569) ?></option>
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(570) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(571) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>ShowScaleOrigin" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="['top','left']"><?= Language::string(572) ?></option>
                                                            <option value="['top','center']"><?= Language::string(573) ?></option>
                                                            <option value="['top','right']"><?= Language::string(574) ?></option>
                                                            <option value="['middle','left']"><?= Language::string(575) ?></option>
                                                            <option value="['middle','center']"><?= Language::string(576) ?></option>
                                                            <option value="['middle','right']"><?= Language::string(577) ?></option>
                                                            <option value="['bottom','left']"><?= Language::string(578) ?></option>
                                                            <option value="['bottom','center']"><?= Language::string(579) ?></option>
                                                            <option value="['bottom','right']"><?= Language::string(580) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(564) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(565) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>ShowScalePercent" class="fullWidth ui-widget-content ui-corner-all" value="0" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                }
                                ?>
                                <tr>
                                    <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(551) ?>:</td>
                                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(552) ?>"></span></td>
                                    <td class="fullWidth">
                                        <div class="horizontalMargin">
                                            <input type="text" id="input<?= $class_name ?>ShowDuration" value="1000" class="fullWidth ui-widget-content ui-corner-all" />
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
<?php } ?>

                </fieldset>
            </td>
            <td style="width:50%;" valign="top" align="center">
                <fieldset class="ui-widget-content">
                    <legend class="" align="center">
                        <table>
                            <tr>
                                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(544) ?>"></span></td>
                                <td><b><?= Language::string(542) ?>:</b></td>
                            </tr>
                        </table>
                    </legend>

                    <table class="fullWidth">
                        <tr>
                            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(545) ?>:</td>
                            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(546) ?>"></span></td>
                            <td class="fullWidth">
                                <div class="horizontalMargin">
                                    <select id="form<?= $class_name ?>SelectEffectHide" class="fullWidth ui-widget-content ui-corner-all" onchange="Template.uiChangeEffect(false)">
                                        <option value="none">&lt;<?= Language::string(73) ?>&gt;</option>
                                        <option value="blind" <?= ($effect_hide == "blind" ? "selected" : "") ?>>blind</option>
                                        <option value="clip" <?= ($effect_hide == "clip" ? "selected" : "") ?>>clip</option>
                                        <option value="drop" <?= ($effect_hide == "drop" ? "selected" : "") ?>>drop</option>
                                        <option value="explode" <?= ($effect_hide == "explode" ? "selected" : "") ?>>explode</option>
                                        <option value="fade" <?= ($effect_hide == "fade" ? "selected" : "") ?>>fade</option>
                                        <option value="fold" <?= ($effect_hide == "fold" ? "selected" : "") ?>>fold</option>
                                        <option value="puff" <?= ($effect_hide == "puff" ? "selected" : "") ?>>puff</option>
                                        <option value="slide" <?= ($effect_hide == "slide" ? "selected" : "") ?>>slide</option>
                                        <option value="scale" <?= ($effect_hide == "scale" ? "selected" : "") ?>>scale</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>

                            <?php if ($effect_hide != "none") { ?>
                        <div id="div<?= $class_name ?>EffectHide" class="fullWidth" style="margin-top:20px;">
                            <table class="fullWidth">
                                <?php
                                switch ($effect_hide) {
                                    case "blind": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(548) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideBlindDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "clip": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(548) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideClipDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "drop": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(553) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideDropDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="left"><?= Language::string(554) ?></option>
                                                            <option value="right"><?= Language::string(555) ?></option>
                                                            <option value="up"><?= Language::string(556) ?></option>
                                                            <option value="down"><?= Language::string(557) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "explode": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(559) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(558) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>HideExplodePieces" class="fullWidth ui-widget-content ui-corner-all" value="9" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "fade": {
                                            break;
                                        }
                                    case "fold": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(560) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(561) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="checkbox" id="input<?= $class_name ?>HideFoldHorizFirst" class="ui-widget-content ui-corner-all" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(562) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(563) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>HideFoldSize" class="fullWidth ui-widget-content ui-corner-all" value="15" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "puff": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(564) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(565) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>HidePuffPercent" class="fullWidth ui-widget-content ui-corner-all" value="150" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "slide": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(553) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideSlideDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="left"><?= Language::string(554) ?></option>
                                                            <option value="right"><?= Language::string(555) ?></option>
                                                            <option value="up"><?= Language::string(556) ?></option>
                                                            <option value="down"><?= Language::string(557) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                    case "scale": {
                                            ?>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(547) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(568) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideScaleDirection" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="both"><?= Language::string(569) ?></option>
                                                            <option value="horizontal"><?= Language::string(549) ?></option>
                                                            <option value="vertical"><?= Language::string(550) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(570) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(571) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <select id="select<?= $class_name ?>HideScaleOrigin" class="fullWidth ui-widget-content ui-corner-all">
                                                            <option value="['top','left']"><?= Language::string(572) ?></option>
                                                            <option value="['top','center']"><?= Language::string(573) ?></option>
                                                            <option value="['top','right']"><?= Language::string(574) ?></option>
                                                            <option value="['middle','left']"><?= Language::string(575) ?></option>
                                                            <option value="['middle','center']"><?= Language::string(576) ?></option>
                                                            <option value="['middle','right']"><?= Language::string(577) ?></option>
                                                            <option value="['bottom','left']"><?= Language::string(578) ?></option>
                                                            <option value="['bottom','center']"><?= Language::string(579) ?></option>
                                                            <option value="['bottom','right']"><?= Language::string(580) ?></option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(564) ?>:</td>
                                                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(565) ?>"></span></td>
                                                <td class="fullWidth">
                                                    <div class="horizontalMargin">
                                                        <input type="text" id="input<?= $class_name ?>HideScalePercent" class="fullWidth ui-widget-content ui-corner-all" value="0" />
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        }
                                }
                                ?>
                                <tr>
                                    <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(551) ?>:</td>
                                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(552) ?>"></span></td>
                                    <td class="fullWidth">
                                        <div class="horizontalMargin">
                                            <input type="text" id="input<?= $class_name ?>HideDuration" value="1000" class="fullWidth ui-widget-content ui-corner-all" />
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
<?php } ?>

                </fieldset>
            </td>
        </tr>
    </table>

</fieldset>