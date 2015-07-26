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
?>

<script>
    $(function(){
        Methods.iniIconButton(".btnCreateNewAccount", "document");
        $("#dd_login").dialog({
            modal:true,
            title:"<?= Language::string(211) ?>",
            resizeable:false,
            closeOnEscape:false,
            dialogClass:"no-close",
            open:function(){
                $('.ui-widget-overlay').css('position', 'fixed');
                Methods.iniTooltips();
                $(this).live("keyup", function(e) {
                    if (e.keyCode === 13) {
                        $("button").last().click();
                    }
                });
            },
            close:function(){
                //$('.ui-widget-overlay').css('position', 'absolute');
            },
            buttons:[
                {
                    text:'<?= Language::string(411) ?>',
                    click:function(){
                        User.uiLogIn();
                    }
                }
            ]
        });
    
<?php
if (Ini::$public_registration) {
    ?>
                $("#dd_login").dialog("option","buttons",[{
                        text:'<?= Language::string(424) ?>',
                        click:function(){
                            User.uiRegister();
                        }
                    },{
                        text:'<?= Language::string(411) ?>',
                        click:function(){
                            User.uiLogIn();
                        }
                    }]);
                    $("button").last().addClass("ui-state-highlight");
    <?php
}
?>
    });
</script>
<div id="dd_login">
    <span><?= Language::string(212) ?></span>
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(173) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(433) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="dd_login_inp_login" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(179) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(434) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="password" id="dd_login_inp_password" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </div>	
    <div class="padding margin ui-state-highlight" align="center"><b><?=Language::string(618)?></b></div>
    <div class="padding margin ui-state-highlight" align="center"><b><?=Language::string(456)?></b></div>
    <div class="padding fullWidth" align="right">
        <div style="cursor:pointer; text-decoration: underline;" onclick="User.uiPasswordRecovery()"><?= Language::string(425) ?></div>
    </div>
</div>

<?php
if (Ini::$public_registration) {
    ?>
    <div id="dd_register" class="notVisible">
        <span><?= Language::string(413) ?></span>
        <div class="padding ui-widget-content ui-corner-all margin">
            <table>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(173) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(260) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_login" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(179) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(261) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="password" id="dd_register_inp_password" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(182) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(183) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="password" id="dd_register_inp_password_conf" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(184) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(186) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_first_name" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(185) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(187) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_last_name" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(419) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(420) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <select id="dd_register_select_intitution_type" class="fullWidth ui-widget-content ui-corner-all">
                                <?php foreach (DS_UserInstitutionType::get_all() as $it) {
                                    ?>
                                    <option value="<?= $it->id ?>"><?= $it->get_name() ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(421) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(422) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_institution_name" value="" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel">* <?= Language::string(174) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(188) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_email" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(189) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(190) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <input type="text" id="dd_register_inp_phone" class="fullWidth ui-widget-content ui-corner-all" />
                        </div>
                    </td>
                </tr>
            </table>
        </div>	 
    </div>
    <?php
}
?>
<div id="divHiddenThemer" class="notVisible">
</div>