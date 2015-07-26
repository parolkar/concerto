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
    $logged_user = User::get_logged_user();
}

if ($logged_user == null) {
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}
$writeable = $logged_user->is_module_writeable($class_name);

$cols = $class_name::get_list_columns();
$fields_schema = "";
foreach ($cols as $col) {
    if (!$col["show"])
        continue;
    if ($fields_schema != "")
        $fields_schema.=",";
    else
        $fields_schema.="{";
    $fields_schema.=sprintf("'%s': { type: '%s'}", $col["property"], $col["type"]);
}
$fields_schema.="}";

$columns_def = "[";

$check_template = "<input type='checkbox' class='chk" . $class_name . "List' value='" . '${ id }' . "' onchange='" . $class_name . ".uiListCheckToggle(this," . '${ id }' . ")' />";

$columns_def.=sprintf("{ title:'', width:30, filterable: false, sortable: false, groupable: false, template: \"%s\"},", $check_template);
foreach ($cols as $col) {
    if (!$col["show"])
        continue;
    $columns_def.="{";
    if (array_key_exists("width", $col))
        $columns_def.=sprintf("width: %s,", $col["width"]);
    if (array_key_exists("format", $col))
        $columns_def.=sprintf("format: \"%s\",", $col["format"]);
    if (array_key_exists("template", $col))
        $columns_def.=sprintf("'template': \"%s\",", $col["template"]);
    $columns_def.=sprintf("title: \"%s\",", $col["name"]);
    $columns_def.=sprintf("field: \"%s\",", $col["property"]);
    $columns_def.=sprintf("filterable: %s,", $col["searchable"] ? "true" : "false");
    $columns_def.=sprintf("sortable: %s,", $col["sortable"] ? "true" : "false");
    $columns_def.=sprintf("groupable: %s", $col["groupable"] ? "true" : "false");
    $columns_def.="}";
    $columns_def.=",";
}

$action_template = "#if(editable) {#<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-pencil' onclick='$class_name.uiEdit(" . '${ id }' . ")' title='" . Language::string(203) . "'></span>#}#";
$action_template.="#if(editable) {#<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-trash' onclick='$class_name.uiDelete(" . '${ id }' . ")' title='" . Language::string(204) . "'></span>#}#";
if ($class_name::$exportable) {
    $action_template.="<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-arrowthickstop-1-n' onclick='$class_name.uiExport(" . '${ id }' . ")' title='" . Language::string(265) . "'></span>";
    $action_template.="<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-gear' onclick='$class_name.uiUpload(" . '${ id }' . ")' title='" . Language::string(375) . "'></span>";
}
$columns_def.=sprintf("{ title:'', width:85, filterable: false, sortable: false, groupable: false, resizable: false, template: \"%s\"}", $action_template);
$columns_def.="]";
?>

<script>
    $(function(){
        $("#div<?= $class_name ?>Grid").kendoGrid({
            dataBound:function(e){
                Methods.iniTooltips();
                if(this.dataSource.group().length == 0) {
                    setTimeout( function() {
                        $(".k-grouping-header").html(dictionary["s339"]);
                    });
                }
<?= $class_name ?>.uiRefreshCheckedList();
            },
            //toolbar: kendo.template($("#script<?= $class_name ?>ToolbarTemplate").html()),
            toolbar: [
                { name: "add", template: '<button class="btnAdd" onclick="<?= $class_name ?>.uiAdd()"><?= Language::string(205) ?></button>'}
<?php
if ($class_name::$exportable) {
    echo ",";
    ?>
                        { name: "import", template: '<button class="btnImport" onclick="<?= $class_name ?>.uiImport()"><?= Language::string(266) ?></button>' },
                        { name: "download", template: '<button class="btnDownload" onclick="<?= $class_name ?>.uiDownload()"><?= Language::string(374) ?></button>' }
<?php } ?>
            ],
            dataSource: {
                transport:{
                    read: { url:"query/get_object_list.php?class_name=<?= $class_name ?>", dataType:"json" } }, schema:{ model:{
                        fields:<?= $fields_schema ?>
                    }
                },
                pageSize:<?= $class_name ?>.listLength
            },
            //scrollable: false,
            filterable:{
                messages: {
                    info: dictionary["s340"],
                    filter: dictionary["s341"],
                    clear: dictionary["s342"],
                    and: dictionary["s227"],
                    or: dictionary["s228"]
                },
                operators: {
                    string: {
                        contains: dictionary["s344"],
                        eq: dictionary["s222"],
                        neq: dictionary["s221"],
                        startswith: dictionary["s343"],
                        endswith: dictionary["s345"]
                    },
                    number: {
                        eq: dictionary["s222"],
                        neq: dictionary["s221"],
                        gte: dictionary["s224"],
                        gt: dictionary["s223"],
                        lte: dictionary["s226"],
                        lt: dictionary["s225"]
                    }
                }
            },
            sortable:true,
            columnMenu:{
              messages: {
                  filter: dictionary["s341"],
                  columns: dictionary["s533"],
                  sortAscending: dictionary["s534"],
                  sortDescending: dictionary["s535"]
              }  
            },
            pageable: {
                refresh:true,
                pageSizes:true,
                messages: {
                    display: dictionary["s527"],
                    empty: dictionary["s528"],
                    page: dictionary["s529"],
                    of: dictionary["s530"],
                    itemsPerPage: dictionary["s531"],
                    first: dictionary["s523"],
                    previous: dictionary["s524"],
                    next: dictionary["s525"],
                    last: dictionary["s526"],
                    refresh: dictionary["s532"]
                }
            },
            groupable:true,
            resizable: true,
            scrollable:true,
            columns:<?= $columns_def ?>
        });
            
        Methods.iniIconButton(".btnAdd", "plus");
        Methods.iniIconButton(".btnImport","arrowthickstop-1-s");
        Methods.iniIconButton(".btnDownload","gear");
        
        Methods.iniIconButton(".btnCheckAll","check");
        Methods.iniIconButton(".btnUncheckAll","close");
        Methods.iniIconButton(".btnRemoveChecked","trash");
        Methods.iniIconButton(".btnExportChecked","arrowthickstop-1-n");
    });
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend class=""><b><?= Language::string(199) ?></b></legend>
    <table class="fullWidth">
        <tr>
            <td align="center">
                <button class="btnCheckAll" onclick="<?= $class_name ?>.uiListCheckAll()"><?= Language::string(512) ?></button>
                <button class="btnUncheckAll" onclick="<?= $class_name ?>.uiListUncheckAll()"><?= Language::string(513) ?></button>
            </td>
            <td align="center">
                <?= Language::string(514) ?><font id="font<?= $class_name ?>CheckedCount">0</font>
            </td>
            <td align="center">
                <button class="btnRemoveChecked" onclick="<?= $class_name ?>.uiDelete(<?= $class_name ?>.checkedList)"><?= Language::string(515) ?></button>
                <?php if ($class_name::$exportable) { ?><button class="btnExportChecked" onclick="<?= $class_name ?>.uiExport(<?= $class_name ?>.checkedList)"><?= Language::string(516) ?></button><?php } ?>
            </td>
        </tr>
    </table>
    <div id="div<?= $class_name ?>Grid" class="margin grid">
    </div>
</fieldset>