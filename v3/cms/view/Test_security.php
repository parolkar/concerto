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

if (!$logged_user->is_module_writeable($class_name))
    die(Language::string(81));
if (!$logged_user->is_object_editable($obj))
    die(Language::string(81));
?>

<script>
    $(function(){
        Methods.iniIconButton("#btnExpand<?= $class_name ?>GridProtectedVariables","arrowthick-1-s");
        var fields = {
            id:{
                type:"number",
                editable: false, 
                nullable: true
            },
            name:{
                type:"string"
            }
        };

        var dataSource = new kendo.data.DataSource({
            transport:{
                read: {
                    url:"query/Test_protected_vars.php?oid="+<?= $class_name ?>.currentID,
                    dataType:"json"
                }
            },
            schema:{
                model:{
                    id:"id",
                    fields:fields
                }
            }
        });

        $("#div<?= $class_name ?>GridProtectedVars").kendoGrid({
            dataBound:function(e){
                Methods.iniTooltips();  
            },
            dataSource: dataSource,
            columns: [{
                    title:dictionary["s70"],
                    field:"name"
                },{
                    field:"id",
                    title:' ',
                    width:50,
                    template:'<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="<?= $class_name ?>.uiRemoveProtectedVar($(this))" title="'+dictionary["s204"]+'"></span>'
                }],
            toolbar:[
                {
                    name: "create", 
                    template: '<button class="btnAdd" onclick="<?= $class_name ?>.uiAddProtectedVar()">'+dictionary["s37"]+'</button>'
                }
            ],
            editable: {
                mode:"incell",
                confirmation:false
            },
            scrollable:false
        });
        Methods.iniIconButton(".btnAdd", "plus");
    })
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin <?=User::view_class()?>">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(493) ?>"></span></td>
                <td class=""><b><?= Language::string(358) ?></b></td>
            </tr>
        </table>
    </legend>
    <div id="div<?= $class_name ?>GridProtectedVars" class="grid margin" align="left" style=""></div>
</fieldset>