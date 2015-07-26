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

function Table() {
}
;
OModule.inheritance(Table);

Table.className = "Table";

Table.onAfterEdit = function()
{
    Table.isIndexGridInitialized = false;
    Table.crudIndexesDeleted = [];
    Table.crudColumnsDeleted = [];
    Table.crudDataDeleted = [];

    Table.crudColumnsUpdated = [];
    Table.crudIndexesUpdated = [];
    Table.crudDataUpdated = [];
};

Table.onAfterSave = function() {
    Test.uiTablesChanged();
    Table.uiEdit(Table.currentID);
};

Table.onAfterDelete = function() {
    Test.uiTablesChanged();
}

Table.onAfterImport = function() {
    Test.uiTablesChanged();
}

Table.onAfterAdd = function() {
}

Table.getAddSaveObject = function()
{
    return {
        oid: this.currentID,
        class_name: this.className,
        name: $("#form" + this.className + "InputName").val()
    };
};

Table.onViewSwitch = function(view) {
    var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');
    if (grid != null) {
        if (view == 0) {
            grid.hideColumn(2);
            grid.hideColumn(3);
            grid.hideColumn(4);
            grid.hideColumn(5);
        } else {
            grid.showColumn(2);
            grid.showColumn(3);
            grid.showColumn(4);
            grid.showColumn(5);
        }
    }
}

Table.getFullSaveObject = function(isNew) {
    if (isNew == null) {
        isNew = false;
    }

    var obj = this.getAddSaveObject();
    obj["deleteIndexes"] = Table.getSerializedCrudDeleted("indexes");
    obj["deleteData"] = Table.getSerializedCrudDeleted("data");
    obj["deleteColumns"] = Table.getSerializedCrudDeleted("columns");
    obj["updateIndexes"] = Table.getSerializedCrudUpdated("indexes", isNew);
    obj["updateData"] = Table.getSerializedCrudUpdated("data", isNew);
    obj["updateColumns"] = Table.getSerializedCrudUpdated("columns", isNew);
    obj["description"] = $("#form" + this.className + "TextareaDescription").val();

    return obj;
}

Table.crudIndexesDeleted = [];
Table.crudColumnsDeleted = [];
Table.crudDataDeleted = [];

Table.crudColumnsUpdated = [];
Table.crudIndexesUpdated = [];
Table.crudDataUpdated = [];

Table.getSerializedCrudDeleted = function(collection) {
    switch (collection) {
        case "columns":
            {
                return $.toJSON(Table.crudColumnsDeleted);
                break;
            }
        case "indexes":
            {
                return $.toJSON(Table.crudIndexesDeleted);
                break;
            }
        case "data":
            {
                var grid = $("#div" + this.className + "GridData").data('kendoGrid');
                if (grid != null) {
                    var data = grid.dataSource.data();
                    if (data.length == 0)
                        return "*";
                }
                return $.toJSON(Table.crudDataDeleted);
                break;
            }
    }
}

Table.getSerializedCrudUpdated = function(collection, isNew) {
    switch (collection) {
        case "columns":
            {
                var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');
                var cols = new Array();
                if (grid != null) {
                    var data = grid.dataSource.data();

                    for (var i = 0; i < data.length; i++) {
                        if (Table.crudColumnsUpdated.indexOf(data[i].id) != -1 || data[i].id == "" || isNew) {
                            if (isNew && data[i].name == "id")
                                continue;
                            cols.push({
                                id: isNew ? "" : data[i].id,
                                name: data[i].name,
                                type: data[i].type,
                                lengthValues: data[i].lengthValues,
                                defaultValue: data[i].defaultValue,
                                attributes: data[i].attributes,
                                nullable: data[i].nullable
                            });
                        }
                    }
                }
                return $.toJSON(cols);
                break;
            }
        case "indexes":
            {
                var grid = $("#div" + this.className + "GridIndex").data('kendoGrid');
                var indexes = new Array();
                if (grid != null) {
                    var data = grid.dataSource.data();

                    for (var i = 0; i < data.length; i++) {
                        if (Table.crudIndexesUpdated.indexOf(data[i].id) != -1 || data[i].id == "" || isNew) {
                            indexes.push({
                                id: isNew ? "" : data[i].id,
                                type: data[i].type,
                                columns: data[i].columns
                            });
                        }
                    }
                }
                return $.toJSON(indexes);
                break;
            }
        case "data":
            {
                var grid = $("#div" + this.className + "GridData").data('kendoGrid');
                var row = new Array();
                if (grid != null) {
                    var data = grid.dataSource.data();

                    for (var i = 0; i < data.length; i++) {
                        if (Table.crudDataUpdated.indexOf(data[i].id) != -1 || data[i].id == null || isNew) {
                            row.push(data[i]);
                            if (isNew) {
                                row[row.length - 1].id = null;
                            }
                        }
                    }
                }
                return $.toJSON(row);
                break;
            }
    }
}

Table.uiSaveValidate = function(ignoreOnBefore, isNew) {
    var thisClass = this;
    if (!this.checkRequiredFields([
        $("#form" + this.className + "InputName").val()
    ])) {
        Methods.alert(dictionary["s415"], "alert");
        return false;
    }

    //check if the table name is free
    $.post("query/Table_name_check.php", {
        oid: this.currentID,
        name: $("#form" + this.className + "InputName").val()
    }, function(data) {
        switch (data.result) {
            case OModule.queryResults.OK:
                {
                    Table.uiSaveValidated(ignoreOnBefore, isNew);
                    break;
                }
            case 1:
                {
                    Methods.alert(dictionary["s632"], "info", dictionary["s274"])
                    break;
                }
            case OModule.queryResults.notLoggedIn:
                {
                    thisClass.onNotLoggedIn(dictionary["s274"]);
                    break;
                }
        }
    }, "json");
}

Table.uiRemoveColumn = function(obj) {
    var thisClass = this;
    Methods.confirm(dictionary["s34"], dictionary["s35"], function() {
        var grid = $("#div" + thisClass.className + "GridStructure").data('kendoGrid');
        var index = obj.closest('tr')[0].sectionRowIndex;
        var item = grid.dataItem(grid.tbody.find("tr:eq(" + index + ")"));

        if (item.id != "") {
            Table.crudUpdate(Table.crudColumnsDeleted, item.id);
        }

        grid.removeRow(grid.tbody.find("tr:eq(" + index + ")"));

        var dataGrid = $("#div" + thisClass.className + "GridData").data('kendoGrid');

        dataGrid.columns.splice(index, 1);
        for (var i = 0; i < dataGrid.dataSource.data().length; i++) {
            delete dataGrid.dataSource.data()[i][item.name];
            delete dataGrid.dataSource.data()[i].fields[item.name]
            delete dataGrid.dataSource.data()[i].defaults[item.name]
        }
        delete Table.dataGridSchemaFields[item.name];

        Table.onColumnChange(item.name);

        Table.structureEmptyCheck();
        Table.uiRefreshDataGrid();
    });
}

Table.structureEmptyCheck = function() {
    var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');
    if (grid.dataSource.data().length > 0) {
        $("#div" + this.className + "GridDataContainer").show();
        $("#btn" + this.className + "DataGridCaption").show();
        $("#div" + this.className + "DataStructureEmptyCaption").hide();
    }
    else {
        $("#div" + this.className + "GridDataContainer").hide();
        $("#btn" + this.className + "DataGridCaption").hide();
        $("#div" + this.className + "DataStructureEmptyCaption").show();
    }
}

Table.uiRefreshDataGrid = function() {
    var grid = $("#div" + this.className + "GridData").data('kendoGrid');

    var columns = grid.columns;
    var items = grid.dataSource.data();

    Table.uiReloadDataGrid(items, columns);
}
Table.uiRefreshStructureGrid = function() {
    var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');

    var columns = grid.columns;
    var items = grid.dataSource.data();

    Table.uiReloadStructureGrid(items, columns);
}

Table.structureGridSchemaFields = null;
Table.dataGridSchemaFields = null;


Table.uiReloadDataGrid = function(data, columns) {
    var thisClass = this;
    var grid = $("#div" + this.className + "GridData").data('kendoGrid');
    grid.destroy();
    $("#div" + this.className + "GridDataContainer").html("<div id='div" + this.className + "GridData' class='grid'></div>");

    var dataSource = new kendo.data.DataSource({
        data: data,
        schema: {
            model: {
                id: "id",
                fields: Table.dataGridSchemaFields
            }
        },
        pageSize: 20
    });

    $("#div" + thisClass.className + "GridData").kendoGrid({
        save: function(e) {
            if (e.model.id != null) {
                Table.crudUpdate(Table.crudDataUpdated, e.model.id);
            }
        },
        dataBound: function(e) {
            Methods.iniTooltips();
            Table.uiIniHTMLTooltips();
        },
        dataSource: dataSource,
        scrollable: true,
        resizable: true,
        sortable: true,
        columnMenu: {
            sortable: false,
            columns: true,
            messages: {
                filter: dictionary["s341"],
                columns: dictionary["s533"]
            }
        },
        pageable: {
            refresh: true,
            pageSizes: true,
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
        filterable: {
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
        columns: columns,
        toolbar: [
            {
                name: "create",
                template: '<button class="btnAdd" onclick="Table.uiAddRow()">' + dictionary["s37"] + '</button>'
            },
            {
                name: "clear",
                template: '<button class="btnRemove" onclick="Table.uiClearRows()">' + dictionary["s366"] + '</button>'
            }
        ],
        editable: {
            mode: "incell",
            confirmation: false
        }
    });
    Methods.iniIconButton(".btnAdd", "plus");
    Methods.iniIconButton(".btnRemove", "trash");
}

Table.uiIniDataGrid = function() {
    var thisClass = this;

    $("#div" + this.className + "GridDataContainer").html("<div id='div" + this.className + "GridData' class='grid'></div>");

    $.post("query/Table_column_list.php?oid=" + this.currentID, {}, function(data) {
        var fields = {};
        fields["id"] = {
            editable: false,
            nullable: true
        };
        var columns = [];
        for (var i = 0; i < data.length; i++)
        {
            var templateSet = false;
            var title = data[i].name;
            fields[data[i].name] = {}

            var col = {
                title: title,
                field: data[i].name
            };

            if (i >= 20)
                col["hidden"] = true;

            col["editor"] = Table.stringEditor;
            fields[data[i].name]["type"] = "string";
            fields[data[i].name]["editable"] = true;
            if (data[i].name == "id")
                fields[data[i].name]["editable"] = false;
            fields[data[i].name]["defaultValue"] = data[i].defaultValue;
            if (fields[data[i].name]["defaultValue"] != null && fields[data[i].name]["defaultValue"].toLowerCase().trim() == "null")
                fields[data[i].name]["defaultValue"] = null;
            if (fields[data[i].name]["defaultValue"] != null && fields[data[i].name]["defaultValue"].toLowerCase().trim() == "current_timestamp")
                fields[data[i].name]["defaultValue"] = kendo.toString(new Date(), "yyyy-MM-dd HH:mm:ss");
            fields[data[i].name]["nullable"] = data[i].nullable == 1;

            switch (data[i].type) {
                case "tinyint":
                case "smallint":
                case "mediumint":
                case "int":
                case "bigint":
                case "decimal":
                case "float":
                case "double":
                case "real":
                case "bit":
                case "serial":
                    {
                        col["editor"] = Table.numberEditor;
                        fields[data[i].name]["type"] = "number";
                        break;
                    }
                case "set":
                case "enum":
                    {
                        col["editor"] = Table.setEditor;
                        break;
                    }
                case "date":
                    {
                        fields[data[i].name]["type"] = "date";
                        col["editor"] = Table.dateEditor;
                        col["format"] = "{0:yyyy-MM-dd}";
                        fields[data[i].name]["parse"] = function(val) {
                            if (val == null)
                                return null;
                            return kendo.format("{0:yyyy-MM-dd}", val);
                        }
                        col["template"] = "#= " + data[i].name + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + data[i].name + ",'yyyy-MM-dd') #";
                        templateSet = true;
                        break;
                    }
                case "timestamp":
                case "datetime":
                    {
                        fields[data[i].name]["type"] = "date";
                        col["editor"] = Table.dateTimeEditor;
                        col["format"] = "{0:yyyy-MM-dd HH:mm:ss}";
                        fields[data[i].name]["parse"] = function(val) {
                            if (val == null)
                                return null;
                            return kendo.format("{0:yyyy-MM-dd HH:mm:ss}", val);
                        }
                        col["template"] = "#= " + data[i].name + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + data[i].name + ",'yyyy-MM-dd HH:mm:ss') #";
                        templateSet = true;
                        break;
                    }
                case "year":
                    {
                        fields[data[i].name]["type"] = "date";
                        col["editor"] = Table.yearEditor;
                        col["format"] = "{0:yyyy}";
                        fields[data[i].name]["parse"] = function(val) {
                            if (val == null)
                                return null;
                            return kendo.format("{0:yyyy}", val);
                        }
                        col["template"] = "#= " + data[i].name + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + data[i].name + ",'yyyy') #";
                        templateSet = true;
                        break;
                    }
                case "time":
                    {
                        fields[data[i].name]["type"] = "date";
                        col["editor"] = Table.timeEditor;
                        col["format"] = "{0:HH:mm:ss}";
                        fields[data[i].name]["parse"] = function(val) {
                            if (val == null)
                                return null;
                            return kendo.format("{0:HH:mm:ss}", val);
                        }
                        col["template"] = "#= " + data[i].name + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + data[i].name + ",'HH:mm:ss') #";
                        templateSet = true;
                        break;
                    }
                case "boolean":
                    {
                        fields[data[i].name]["type"] = "numeric";
                        col["editor"] = Table.boolEditor;
                        col["template"] = "<div align='center'><input type='checkbox' #= " + data[i].name + "==1 ? checked='checked' : '' # disabled readonly /></div>";
                        templateSet = true;
                        break;
                    }
                case "tinytext":
                case "mediumtext":
                case "longtext":
                case "text":
                    {
                        col["editor"] = Table.htmlEditor;
                        col["template"] = '<div class="horizontalMargin" align="center">' +
                                '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\'' + data[i].name + '\')" title="' + dictionary["s130"] + '"></span>' +
                                '<textarea class="notVisible">#=' + data[i].name + '#</textarea>' +
                                '</div>';
                        fields[data[i].name]["type"] = "string";
                        fields[data[i].name]["editable"] = false;
                        templateSet = true;
                        break;
                    }
            }

            if (fields[data[i].name]["nullable"] && !templateSet)
                col["template"] = "#= " + data[i].name + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':" + data[i].name + " #";

            columns.push(col);
        }
        columns.push({
            title: ' ',
            width: 30,
            sortable: false,
            filterable: false,
            resizable: false,
            template: '<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="' + thisClass.className + '.uiRemoveRow($(this))" title="' + dictionary["s11"] + '"></span>'
        });

        var dataSource = new kendo.data.DataSource({
            transport: {
                read: {
                    url: "query/Table_data_list.php?oid=" + thisClass.currentID,
                    dataType: "json"
                }
            },
            schema: {
                model: {
                    id: "id",
                    fields: fields
                }
            },
            pageSize: 20
        });

        Table.dataGridSchemaFields = fields;

        $("#div" + thisClass.className + "GridData").kendoGrid({
            save: function(e) {
                if (e.model.id != null) {
                    Table.crudUpdate(Table.crudDataUpdated, e.model.id);
                }
            },
            dataBound: function(e) {
                Methods.iniTooltips();
                Table.uiIniHTMLTooltips();
                if (this.dataSource.group().length == 0) {
                    setTimeout(function() {
                        $(".k-grouping-header").html(dictionary["s339"]);
                    });
                }
            },
            dataSource: dataSource,
            scrollable: true,
            resizable: true,
            sortable: true,
            columnMenu: {
                sortable: false,
                columns: true,
                messages: {
                    filter: dictionary["s341"],
                    columns: dictionary["s533"]
                }
            },
            pageable: {
                refresh: true,
                pageSizes: true,
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
            filterable: {
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
                    },
                    date: {
                        eq: dictionary["s222"],
                        neq: dictionary["s221"],
                        gte: dictionary["s596"],
                        gt: dictionary["s597"],
                        lte: dictionary["s598"],
                        lt: dictionary["s599"]
                    }
                }
            },
            columns: columns,
            toolbar: [
                {
                    name: "create",
                    template: '<button class="btnAdd" onclick="Table.uiAddRow()">' + dictionary["s37"] + '</button>'
                },
                {
                    name: "clear",
                    template: '<button class="btnRemove" onclick="Table.uiClearRows()">' + dictionary["s366"] + '</button>'
                }
            ],
            editable: {
                mode: "incell",
                confirmation: false
            }
        });

        Methods.iniIconButton(".btnAdd", "plus");
        Methods.iniIconButton(".btnRemove", "trash");

    }, "json");
}

Table.uiReloadStructureGrid = function(data, columns) {
    var thisClass = this;
    var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');
    grid.destroy();
    $("#div" + this.className + "GridStructureContainer").html("<div id='div" + this.className + "GridStructure' class='grid'></div>");

    var dataSource = new kendo.data.DataSource({
        data: data,
        schema: {
            model: {
                fields: Table.structureGridSchemaFields
            }
        }//,
        //pageSize: 20
    });

    $("#div" + thisClass.className + "GridStructure").kendoGrid({
        dataBound: function(e) {
            Table.structureEmptyCheck();
            Methods.iniTooltips();
            Table.uiRefreshIndexableColumns();
        },
        dataSource: dataSource,
        columns: columns,
        toolbar: [
            {
                name: "create",
                template: '<button class="btnAdd" onclick="Table.uiAddColumn()">' + dictionary["s37"] + '</button>'
            }
        ],
        editable: false,
        scrollable: true,
        resizable: true//,
                /*
                 pageable: {
                 pageSizes: true,
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
                 }
                 */
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.isIndexGridInitialized = false;
Table.uiIniStructureGrid = function() {
    Table.isIndexGridInitialized = false;
    var thisClass = this;

    $("#div" + this.className + "GridStructureContainer").html("<div id='div" + this.className + "GridStructure' class='grid'></div>");

    var fields = {
        id: {
            type: "string"
        },
        name: {
            type: "string"
        },
        type: {
            type: "string"
        },
        lengthValues: {
            type: "string"
        },
        defaultValue: {
            type: "string"
        },
        attributes: {
            type: "string"
        },
        nullable: {
            type: "number"
        }
    };

    var dataSource = new kendo.data.DataSource({
        transport: {
            read: {
                url: "query/Table_column_list.php?oid=" + thisClass.currentID,
                dataType: "json"
            }
        },
        schema: {
            model: {
                id: "id",
                fields: fields
            }
        }//,
        //pageSize: 20
    });

    Table.structureGridSchemaFields = fields;

    $("#div" + this.className + "GridStructure").kendoGrid({
        dataBound: function(e) {
            Table.structureEmptyCheck();
            Methods.iniTooltips();
            if (!Table.isIndexGridInitialized) {
                Table.uiIniIndexGrid();
                Table.isIndexGridInitialized = true;
            }
            Table.uiRefreshIndexableColumns();
        },
        dataSource: dataSource,
        columns: [{
                title: dictionary["s70"],
                field: "name"
            }, {
                title: dictionary["s122"],
                field: "type"
            }, {
                title: dictionary["s585"],
                field: "lengthValues",
                hidden: Methods.currentView == 0
            }, {
                title: dictionary["s538"],
                field: "defaultValue",
                hidden: Methods.currentView == 0
            }, {
                title: dictionary["s588"],
                field: "attributes",
                hidden: Methods.currentView == 0
            }, {
                title: dictionary["s590"],
                field: "nullable",
                template: '<input type="checkbox" #= nullable==1?"checked":"" # disabled />',
                hidden: Methods.currentView == 0
            }, {
                title: ' ',
                width: 50,
                template: '#if(name!="id"){#<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="' + thisClass.className + '.uiEditColumn($(this))" title="' + dictionary["s19"] + '"></span>' +
                        '<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="' + thisClass.className + '.uiRemoveColumn($(this))" title="' + dictionary["s20"] + '"></span>#}#'
            }],
        toolbar: [
            {
                name: "create",
                template: '<button class="btnAdd" onclick="Table.uiAddColumn()">' + dictionary["s37"] + '</button>'
            }
        ],
        editable: false,
        scrollable: true,
        resizable: true//,
                /*
                 pageable: {
                 pageSizes: true,
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
                 }
                 */
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.indexGridSchemaFields = null;
Table.uiIniIndexGrid = function() {
    var thisClass = this;

    $("#div" + this.className + "GridIndexContainer").html("<div id='div" + this.className + "GridIndex' class='grid'></div>");

    var fields = {
        id: {
            type: "string"
        },
        name: {
            type: "string"
        },
        type: {
            type: "string"
        },
        columns: {
            type: "string"
        }
    };

    var dataSource = new kendo.data.DataSource({
        transport: {
            read: {
                url: "query/Table_index_list.php?oid=" + thisClass.currentID,
                dataType: "json"
            }
        },
        schema: {
            model: {
                id: "id",
                fields: fields
            }
        }//,
        //pageSize: 20
    });

    Table.indexGridSchemaFields = fields;

    $("#div" + this.className + "GridIndex").kendoGrid({
        dataBound: function(e) {
            Methods.iniTooltips();
        },
        dataSource: dataSource,
        columns: [{
                title: dictionary["s70"],
                field: "name"
            }, {
                title: dictionary["s122"],
                field: "type"
            }, {
                title: dictionary["s602"],
                field: "columns"
            }, {
                title: ' ',
                width: 50,
                template: '#if(type!="primary"){#<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="' + thisClass.className + '.uiEditIndex($(this))" title="' + dictionary["s603"] + '"></span>' +
                        '<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="' + thisClass.className + '.uiRemoveIndex($(this))" title="' + dictionary["s604"] + '"></span>#}#'
            }],
        toolbar: [
            {
                name: "create",
                template: '<button class="btnAdd" onclick="Table.uiAddIndex()">' + dictionary["s605"] + '</button>'
            }
        ],
        editable: false,
        scrollable: true//,
                /*
                 pageable: {
                 pageSizes: true,
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
                 }
                 */
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.uiRefreshIndexableColumns = function() {
    var grid = $("#div" + Table.className + "GridStructure").data('kendoGrid');
    var cols = grid.dataSource.data();

    var container = $("#div" + this.className + "IndexableColumns");
    container.html("");
    for (var i = 0; i < cols.length; i++) {
        container.append("<input type='checkbox' class='checbkoxIndexableColumn' value='" + cols[i].name + "' />" + cols[i].name + "<br/>");
    }
}

Table.getIndexColumns = function() {
    var result = [];
    $(".checbkoxIndexableColumn:checked").each(function() {
        result.push($(this).val());
    });
    return result.join(",");
}

Table.decodeIndexColumns = function(columns) {
    return columns.split(",");
}

Table.uiAddIndex = function() {
    var thisClass = this;

    var type = $("#form" + Table.className + "SelectIndexType");

    $("#div" + this.className + "IndexDialog").dialog({
        title: dictionary["s610"],
        resizable: false,
        modal: true,
        width: 400,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            type.val("index");
            $(".checbkoxIndexableColumn").attr("checked", false);
            //$('.ui-widget-overlay').css('position', 'absolute');
            $(this).dialog("destroy");
        },
        buttons: [
            {
                text: dictionary["s37"],
                click: function() {

                    if ($(".checbkoxIndexableColumn:checked").length == 0)
                    {
                        Methods.alert(dictionary["s612"], "alert", dictionary["s611"]);
                        return;
                    }

                    var indexGrid = $("#div" + thisClass.className + "GridIndex").data('kendoGrid');
                    indexGrid.dataSource.add({
                        type: type.val(),
                        columns: Table.getIndexColumns()
                    })

                    $(this).dialog("close");

                    Methods.iniTooltips();
                }
            },
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.uiRemoveIndex = function(obj) {
    var thisClass = this;
    Methods.confirm(dictionary["s613"], dictionary["s614"], function() {
        var grid = $("#div" + thisClass.className + "GridIndex").data('kendoGrid');
        var index = obj.closest('tr')[0].sectionRowIndex;
        var item = grid.dataItem(grid.tbody.find("tr:eq(" + index + ")"));

        if (item.id != "") {
            Table.crudUpdate(Table.crudIndexesDeleted, item.id);
        }

        grid.removeRow(grid.tbody.find("tr:eq(" + index + ")"));
    });
}

Table.uiEditIndex = function(obj) {
    var thisClass = this;

    var indexGrid = $("#div" + thisClass.className + "GridIndex").data('kendoGrid');
    var index = obj.closest('tr')[0].sectionRowIndex;
    var item = indexGrid.dataItem(indexGrid.tbody.find("tr:eq(" + index + ")"));

    var oldType = item.type;
    var oldColumns = Table.decodeIndexColumns(item.columns);

    var type = $("#form" + Table.className + "SelectIndexType");
    type.val(oldType);

    Table.uiRefreshIndexableColumns();
    for (var i = 0; i < oldColumns.length; i++) {
        var col = oldColumns[i];
        $(".checbkoxIndexableColumn[value='" + col + "']").attr("checked", true);
    }

    $("#div" + this.className + "IndexDialog").dialog({
        title: dictionary["s615"],
        modal: true,
        resizable: false,
        width: 400,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            type.val("index");
            Table.uiRefreshIndexableColumns();
            //$('.ui-widget-overlay').css('position', 'absolute');
            $(this).dialog("destroy");
        },
        buttons: [
            {
                text: dictionary["s95"],
                click: function() {
                    if ($(".checbkoxIndexableColumn:checked").length == 0)
                    {
                        Methods.alert(dictionary["s612"], "alert", dictionary["s611"]);
                        return;
                    }

                    if (item.id != "") {
                        Table.crudUpdate(Table.crudIndexesUpdated, item.id);
                    }

                    //structGrid mod start
                    var rowIndex = indexGrid.dataSource.data()[index];
                    rowIndex["type"] = type.val();
                    rowIndex["columns"] = Table.getIndexColumns();
                    Table.uiRefreshIndexGrid();

                    $(this).dialog("close");

                    Methods.iniTooltips();
                }
            },
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.onColumnChange = function(oldName, newName) {
    var grid = $("#div" + this.className + "GridIndex").data('kendoGrid');
    var items = grid.dataSource.data();
    var columns = grid.columns;

    var refreshRequired = false;
    for (var i = 0; i < items.length; i++) {
        var cols = items[i].columns;
        var colsArray = Table.decodeIndexColumns(cols);
        if (colsArray.indexOf(oldName) != -1) {
            refreshRequired = true;
            if (newName != null) {
                colsArray[colsArray.indexOf(oldName)] = newName;
                items[i].columns = colsArray.join(",");
            } else {
                colsArray.splice(colsArray.indexOf(oldName), 1);
                items[i].columns = colsArray.join(",");
            }
            if (colsArray.length == 0) {
                items.splice(i, 1);
                i--;
            }
        }
    }

    if (refreshRequired) {
        Table.uiReloadIndexGrid(items, columns);
    }
}

Table.uiRefreshIndexGrid = function() {
    var grid = $("#div" + this.className + "GridIndex").data('kendoGrid');

    var columns = grid.columns;
    var items = grid.dataSource.data();

    Table.uiReloadIndexGrid(items, columns);
}

Table.uiReloadIndexGrid = function(data, columns) {
    var thisClass = this;
    var grid = $("#div" + this.className + "GridIndex").data('kendoGrid');
    grid.destroy();
    $("#div" + this.className + "GridIndexContainer").html("<div id='div" + this.className + "GridIndex' class='grid'></div>");

    var dataSource = new kendo.data.DataSource({
        data: data,
        schema: {
            model: {
                fields: Table.indexGridSchemaFields
            }
        }//,
        //pageSize: 20
    });

    $("#div" + thisClass.className + "GridIndex").kendoGrid({
        dataBound: function(e) {
            Methods.iniTooltips();
        },
        dataSource: dataSource,
        columns: columns,
        toolbar: [
            {
                name: "create",
                template: '<button class="btnAdd" onclick="Table.uiAddIndex()">' + dictionary["s37"] + '</button>'
            }
        ],
        editable: false,
        scrollable: false//,
                /*
                 pageable: {
                 pageSizes: true,
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
                 }
                 */
    });
    Methods.iniIconButton(".btnAdd", "plus");
}

Table.uiAddRow = function() {
    var grid = $("#div" + this.className + "GridData").data('kendoGrid');
    grid.addRow();

    Methods.iniTooltips();
    Table.uiIniHTMLTooltips();
}

Table.getColumns = function() {
    var grid = $("#div" + this.className + "GridStructure").data('kendoGrid');
    var cols = new Array();
    if (grid == null)
        return cols;
    var data = grid.dataSource.data();

    for (var i = 0; i < data.length; i++) {
        cols.push({
            name: data[i].name,
            type: data[i].type,
            lengthValues: data[i].lengthValues,
            defaultValue: data[i].defaultValue,
            attributes: data[i].attributes,
            nullable: data[i].nullable
        })
    }
    return cols;
}

Table.getIndexes = function() {
    var grid = $("#div" + this.className + "GridIndex").data('kendoGrid');
    var cols = new Array();
    if (grid == null)
        return cols;
    var data = grid.dataSource.data();

    for (var i = 0; i < data.length; i++) {
        cols.push({
            type: data[i].type,
            columns: data[i].columns
        })
    }
    return cols;
}

Table.getSerializedIndexes = function() {
    var indexes = Table.getIndexes();
    var result = new Array();
    for (var i = 0; i < indexes.length; i++) {
        result.push($.toJSON(indexes[i]));
    }
    return result;
}

Table.getSerializedColumns = function() {
    var cols = Table.getColumns();
    var result = new Array();
    for (var i = 0; i < cols.length; i++) {
        result.push($.toJSON(cols[i]));
    }
    return result;
}

Table.getRows = function() {
    var result = new Array();

    var struct = $("#div" + this.className + "GridStructure").data('kendoGrid');
    var data = $("#div" + this.className + "GridData").data('kendoGrid');

    var cols = struct.dataSource.data();
    var rows = data.dataSource.data();

    for (var i = 0; i < rows.length; i++) {
        var row = {};
        for (var j = 0; j < cols.length; j++) {
            row[cols[j].name] = rows[i][cols[j].name];
        }
        result.push($.toJSON(row));
    }
    return result;
}

Table.doesColumnExists = function(name) {
    var grid = $("#div" + this.className + "GridData").data('kendoGrid');

    var columns = grid.columns;
    for (var i = 0; i < columns.length; i++) {
        if (columns[i].field == name)
            return true;
    }
    return false;
}

Table.restrictedColumnNames = [
    "parent",
    "this",
    "data",
    "function",
    "var",
    "if",
    "true",
    "false",
    "for",
    "while",
    "continue",
    "return",
    "break",
    "case",
    "switch",
    "null"
];
Table.uiEditColumn = function(obj) {
    var thisClass = this;
    var structGrid = $("#div" + thisClass.className + "GridStructure").data('kendoGrid');
    var index = obj.closest('tr')[0].sectionRowIndex;
    var item = structGrid.dataItem(structGrid.tbody.find("tr:eq(" + index + ")"));
    var itemID = item.id;
    var oldName = item.name;
    var oldType = item.type;
    var oldLengthValues = item.lengthValues;
    var oldDefaultValue = item.defaultValue;
    var oldAttributes = item.attributes;
    var oldNullable = item.nullable;

    var name = $("#form" + Table.className + "InputColumnName");
    name.val(oldName);

    var typeClass = "sctSimple";
    if (Methods.currentView === 1)
        typeClass = "sctAdvanced";
    var type = $(".form" + Table.className + "SelectColumnType." + typeClass);
    type.val(oldType);

    var lengthValues = $("#form" + Table.className + "InputColumnLength");
    lengthValues.val(oldLengthValues);
    var defaultValue = $("#form" + Table.className + "InputColumnDefault");
    defaultValue.val(oldDefaultValue);
    var attributes = $("#form" + Table.className + "SelectColumnAttributes");
    attributes.val(oldAttributes);
    var nullable = $("#form" + Table.className + "CheckboxColumnNull");
    nullable.attr("checked", oldNullable == 1);

    $("#div" + this.className + "Dialog").dialog({
        title: dictionary["s12"],
        modal: true,
        resizable: false,
        width: 400,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            name.val("");
            type.val("text");
            lengthValues.val("");
            defaultValue.val("");
            attributes.val("");
            nullable.attr("checked", false);
            //$('.ui-widget-overlay').css('position', 'absolute');
            $(this).dialog("destroy");
        },
        buttons: [
            {
                text: dictionary["s95"],
                click: function() {
                    name.val($.trim(name.val()));

                    if (name.val() == "")
                    {
                        Methods.alert(dictionary["s13"], dictionary["s14"]);
                        return;
                    }

                    if (Table.doesColumnExists(name.val()) && oldName != name.val())
                    {
                        Methods.alert(dictionary["s15"], "alert", dictionary["s14"]);
                        return;
                    }

                    if (!Test.variableValidation(name.val(), false))
                    {
                        var oldValue = name.val();
                        var newValue = Test.convertVariable(name.val(), false);
                        name.val(newValue);
                        Methods.alert(dictionary["s1"].format(oldValue, newValue), "info", dictionary["s2"]);
                        return;
                    }

                    if (Table.restrictedColumnNames.indexOf(name.val()) != -1) {
                        Methods.alert(dictionary["s693"], "alert", dictionary["s14"]);
                        return;
                    }

                    Table.onColumnChange(oldName, name.val());
                    if (itemID != "") {
                        Table.crudUpdate(Table.crudColumnsUpdated, itemID);
                    }

                    //structGrid mod start
                    var rowStruct = structGrid.dataSource.data()[index];
                    rowStruct["name"] = name.val();
                    rowStruct["type"] = type.val();
                    rowStruct["lengthValues"] = lengthValues.val();
                    rowStruct["defaultValue"] = defaultValue.val();
                    rowStruct["attributes"] = attributes.val();
                    rowStruct["nullable"] = nullable.is(":checked") ? 1 : 0;
                    Table.uiRefreshStructureGrid();

                    //dataGrid mod start
                    var dataGrid = $("#div" + thisClass.className + "GridData").data('kendoGrid');

                    var templateSet = false;

                    dataGrid.columns[index]["title"] = name.val();
                    dataGrid.columns[index]["field"] = name.val();

                    if (oldName != name.val()) {
                        delete Table.dataGridSchemaFields[oldName];
                    }
                    Table.dataGridSchemaFields[name.val()] = {};

                    dataGrid.columns[index]["editor"] = Table.stringEditor;
                    Table.dataGridSchemaFields[name.val()]["type"] = "string";
                    Table.dataGridSchemaFields[name.val()]["editable"] = true;
                    Table.dataGridSchemaFields[name.val()]["defaultValue"] = defaultValue.val();
                    if (Table.dataGridSchemaFields[name.val()]["defaultValue"] != null && Table.dataGridSchemaFields[name.val()]["defaultValue"].toLowerCase().trim() == "null")
                        Table.dataGridSchemaFields[name.val()]["defaultValue"] = null;
                    if (Table.dataGridSchemaFields[name.val()]["defaultValue"] != null && Table.dataGridSchemaFields[name.val()]["defaultValue"].toLowerCase().trim() == "current_timestamp")
                        Table.dataGridSchemaFields[name.val()]["defaultValue"] = kendo.toString(new Date(), "yyyy-MM-dd HH:mm:ss");
                    Table.dataGridSchemaFields[name.val()]["nullable"] = nullable.is(":checked");

                    switch (type.val()) {
                        case "tinyint":
                        case "smallint":
                        case "mediumint":
                        case "int":
                        case "bigint":
                        case "decimal":
                        case "float":
                        case "double":
                        case "real":
                        case "bit":
                        case "serial":
                            {
                                dataGrid.columns[index]["editor"] = Table.numberEditor;
                                Table.dataGridSchemaFields[name.val()]["type"] = "number";
                                break;
                            }
                        case "set":
                        case "enum":
                            {
                                dataGrid.columns[index]["editor"] = Table.setEditor;
                                break;
                            }
                        case "date":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                dataGrid.columns[index]["editor"] = Table.dateEditor;
                                dataGrid.columns[index]["format"] = "{0:yyyy-MM-dd}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy-MM-dd}", val);
                                }
                                dataGrid.columns[index]["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy-MM-dd') #";
                                templateSet = true;
                                break;
                            }
                        case "timestamp":
                        case "datetime":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                dataGrid.columns[index]["editor"] = Table.dateTimeEditor;
                                dataGrid.columns[index]["format"] = "{0:yyyy-MM-dd HH:mm:ss}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy-MM-dd HH:mm:ss}", val);
                                }
                                dataGrid.columns[index]["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy-MM-dd HH:mm:ss') #";
                                templateSet = true;
                                break;
                            }
                        case "year":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                dataGrid.columns[index]["editor"] = Table.yearEditor;
                                dataGrid.columns[index]["format"] = "{0:yyyy}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy}", val);
                                }
                                dataGrid.columns[index]["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy') #";
                                templateSet = true;
                                break;
                            }
                        case "time":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                dataGrid.columns[index]["editor"] = Table.timeEditor;
                                dataGrid.columns[index]["format"] = "{0:HH:mm:ss}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:HH:mm:ss}", val);
                                }
                                dataGrid.columns[index]["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'HH:mm:ss') #";
                                templateSet = true;
                                break;
                            }
                        case "boolean":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "numeric";
                                dataGrid.columns[index]["editor"] = Table.boolEditor;
                                dataGrid.columns[index]["template"] = "<div align='center'><input type='checkbox' #= " + name.val() + "==1 ? checked='checked' : '' # disabled readonly /></div>";
                                templateSet = true;
                                break;
                            }
                        case "tinytext":
                        case "mediumtext":
                        case "longtext":
                        case "text":
                            {
                                dataGrid.columns[index]["editor"] = Table.htmlEditor;
                                dataGrid.columns[index]["template"] = '<div class="horizontalMargin" align="center">' +
                                        '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\'' + name.val() + '\')" title="' + dictionary["s130"] + '"></span>' +
                                        '<textarea class="notVisible">#=' + name.val() + '#</textarea>' +
                                        '</div>';
                                Table.dataGridSchemaFields[name.val()]["type"] = "string";
                                Table.dataGridSchemaFields[name.val()]["editable"] = false;
                                templateSet = true;
                                break;
                            }
                    }

                    if (Table.dataGridSchemaFields[name.val()]["nullable"] && !templateSet)
                        dataGrid.columns[index]["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':" + name.val() + " #";


                    for (var i = 0; i < dataGrid.dataSource.data().length; i++) {
                        var item = dataGrid.dataSource.data()[i];
                        item[name.val()] = item[oldName];

                        item.fields[name.val()] = item.fields[oldName];
                        if (item.fields[name.val()] == undefined)
                            item.fields[name.val()] = {};

                        item.fields[name.val()]['type'] = Table.dataGridSchemaFields[name.val()]["type"];
                        item.fields[name.val()]['defaultValue'] = Table.dataGridSchemaFields[name.val()]["defaultValue"];
                        item.fields[name.val()]['editable'] = Table.dataGridSchemaFields[name.val()]["editable"];
                        item.fields[name.val()]['nullable'] = Table.dataGridSchemaFields[name.val()]["nullable"];
                        item.fields[name.val()]['parse'] = Table.dataGridSchemaFields[name.val()]["parse"];

                        if (oldName != name.val()) {
                            delete item[oldName];
                            delete item.fields[oldName];
                            delete item.defaults[oldName];
                        }
                        item.defaults[name.val()] = Table.dataGridSchemaFields[name.val()]["defaultValue"];
                    }

                    Table.uiRefreshDataGrid();

                    $(this).dialog("close");

                    Methods.iniTooltips();
                    Table.uiIniHTMLTooltips();
                }
            },
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.uiExportCSV = function() {
    var thisClass = this;
    $("#div" + Table.className + "DialogExportCSV").dialog({
        title: dictionary["s329"],
        modal: true,
        resizable: false,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            //$('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons: [{
                text: dictionary["s265"],
                click: function() {
                    var delimeter = $("#inputTableCSVExportDelimeter").val();
                    var enclosure = $("#inputTableCSVExportEnclosure").val();

                    if ($.trim(delimeter) == "" || $.trim(enclosure) == "") {
                        Methods.alert(dictionary["s334"], "alert", dictionary["s25"]);
                        return;
                    }

                    var header = $("#inputTableCSVExportHeader").is(":checked") ? 1 : 0;
                    location.href = 'query/Table_csv_export.php?oid=' + thisClass.currentID + "&delimeter=" + delimeter + "&enclosure=" + enclosure + "&header=" + header;
                    $(this).dialog("close");
                }
            }, {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.uiImportTable = function() {
    var thisClass = this;
    Methods.confirm(dictionary["s639"], dictionary["s25"], function() {
        $.post("view/Table_import_mysql.php", {
            oid: thisClass.currentID
        }, function(data) {
            $("#div" + Table.className + "DialogImportMySQL").html(data);
            var selectTable = $("#form" + thisClass.className + "SelectMySQLTable");

            $("#div" + Table.className + "DialogImportMySQL").dialog({
                title: dictionary["s21"],
                modal: true,
                resizable: false,
                open: function() {
                    $('.ui-widget-overlay').css('position', 'fixed');
                },
                close: function() {
                    //$('.ui-widget-overlay').css('position', 'absolute');  
                },
                buttons: [{
                        text: dictionary["s22"],
                        click: function() {
                            if (selectTable.val() == 0) {
                                Methods.alert(dictionary["s24"], "alert", dictionary["s25"]);
                                return;
                            }

                            Methods.uiBlock($("#div" + Table.className + "DialogImportMySQL").parent());
                            $.post("query/Table_mysql_import.php", {
                                oid: thisClass.currentID,
                                table: selectTable.val()
                            }, function(data) {
                                Methods.uiUnblock($("#div" + Table.className + "DialogImportMySQL").parent());
                                $("#div" + Table.className + "DialogImportMySQL").dialog("close");
                                switch (parseInt(data.result)) {
                                    case OModule.queryResults.OK:
                                        {
                                            thisClass.uiEdit(thisClass.currentID);
                                            Methods.alert(dictionary["s26"], "info", dictionary["s25"]);
                                            break;
                                        }
                                    case OModule.queryResults.notLoggedIn:
                                        {
                                            thisClass.onNotLoggedIn(dictionary["s25"]);
                                            break;
                                        }
                                    case OModule.queryResults.transactionError:
                                        {
                                            Methods.alert(dictionary["s616"] + data.message, "alert", dictionary["s25"]);
                                            break;
                                        }
                                    case -7:
                                        {
                                            Methods.alert(dictionary["s638"], "alert", dictionary["s25"]);
                                            break;
                                        }
                                    default:
                                        {
                                            Methods.alert(dictionary["s30"], "alert", dictionary["s25"]);
                                            break;
                                        }
                                }
                            }, "json");
                        }
                    }, {
                        text: dictionary["s23"],
                        click: function() {
                            $(this).dialog("close");
                        }
                    }]
            });
        });
    });
}

Table.isFileUploaded = false;
Table.uiImportCSV = function() {
    var thisClass = this;
    Methods.confirm(dictionary["s639"], dictionary["s25"], function() {
        $("#div" + Table.className + "DialogImportCSV").dialog({
            title: dictionary["s27"],
            resizable: false,
            modal: true,
            width: 400,
            close: function() {
                //$('.ui-widget-overlay').css('position', 'absolute');
            },
            beforeClose: function() {

            },
            open: function() {
                $('.ui-widget-overlay').css('position', 'fixed');
                $('#file' + Table.className + 'CSVImport').fileupload({
                    dataType: 'json',
                    url: 'js/lib/fileupload/php/index.php',
                    formData: function(form) {
                        return [{
                                name: "oid",
                                value: Table.currentID
                            }]
                    },
                    send: function(e, data) {
                        Methods.modalProgress();
                        $("#div" + Table.className + "DialogImportCSV").dialog("close");
                    },
                    progress: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        Methods.changeProgress(progress);
                    },
                    done: function(e, data) {
                        $.each(data.result, function(index, file) {
                            Table.isFileUploaded = true;
                            var delimeter = $("#inputTableCSVImportDelimeter").val();
                            var enclosure = $("#inputTableCSVImportEnclosure").val();

                            if ($.trim(delimeter) == "" || $.trim(enclosure) == "") {
                                Methods.alert(dictionary["s334"], "alert", dictionary["s25"]);
                                return;
                            }

                            Methods.confirm(dictionary["s28"], dictionary["s29"], function() {
                                Methods.uiBlock($("#div" + Table.className + "DialogImportCSV").parent());
                                $.post("query/Table_csv_import.php", {
                                    oid: Table.currentID,
                                    file: file.name,
                                    delimeter: delimeter,
                                    enclosure: enclosure,
                                    header: $("#inputTableCSVImportHeader").is(":checked") ? 1 : 0,
                                    id: $("#inputTableCSVImportID").is(":checked") ? 1 : 0
                                }, function(data) {
                                    Methods.uiUnblock($("#div" + Table.className + "DialogImportCSV").parent());
                                    $("#div" + Table.className + "DialogImportCSV").dialog("close");
                                    switch (parseInt(data.result)) {
                                        case OModule.queryResults.OK:
                                            {
                                                Methods.alert(dictionary["s26"], "info", dictionary["s25"]);
                                                Table.uiEdit(Table.currentID);
                                                break;
                                            }
                                        case OModule.queryResults.notLoggedIn:
                                            {
                                                thisClass.onNotLoggedIn(dictionary["s25"]);
                                                break;
                                            }
                                            //file doesn't exist
                                        case -3:
                                            {
                                                Methods.alert(dictionary["s272"], "alert", dictionary["s25"]);
                                                break;
                                            }
                                        case OModule.queryResults.transactionError:
                                            {
                                                Methods.alert(dictionary["s616"] + data.message, "alert", dictionary["s25"]);
                                                break;
                                            }
                                        case -7:
                                            {
                                                Methods.alert(dictionary["s638"], "alert", dictionary["s25"]);
                                                break;
                                            }
                                        default:
                                            {
                                                Methods.alert(dictionary["s30"], "alert", dictionary["s25"]);
                                                Table.uiEdit(Table.currentID);
                                                break;
                                            }
                                    }
                                }, "json");
                            });
                        });
                    }
                });
            },
            buttons: [{
                    text: dictionary["s23"],
                    click: function() {
                        $(this).dialog("close");
                    }
                }]
        });
    });
}

Table.stringEditor = function(container, options) {
    $("<textarea style='resize:none; margin:auto; width:100%; height:100px;' data-bind='value:" + options.field + "' />").appendTo(container);
}

Table.dateEditor = function(container, options) {
    $("<input id='cellDatepicker' type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
    $("#cellDatepicker").kendoDatePicker({
        format: "yyyy-MM-dd"
    });
}

Table.dateTimeEditor = function(container, options) {
    $("<input id='cellDateTimepicker' type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
    $("#cellDateTimepicker").kendoDateTimePicker({
        format: "yyyy-MM-dd HH:mm:ss"
    });
}

Table.yearEditor = function(container, options) {
    $("<input id='cellYearpicker' type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
    $("#cellYearpicker").kendoDatePicker({
        format: "yyyy",
        start: "century",
        depth: "decade"
    });
}

Table.timeEditor = function(container, options) {
    $("<input id='cellTimepicker' type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
    $("#cellTimepicker").kendoTimePicker({
        interval: 1,
        format: "HH:mm:ss"
    });
}

Table.setEditor = function(container, options) {
    var grid = $("#div" + Table.className + "GridStructure").data('kendoGrid');
    var items = grid.dataSource.data();

    var editor = $("<select style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />");

    var col = null;
    for (var i = 0; i < items.length; i++) {
        if (items[i].name == options.field) {
            col = items[i];
            break;
        }
    }

    if (col.nullable == 1) {
        //editor.html("<option value=''>&lt;"+dictionary["s73"]+"&gt;</option>");
    }

    if (col.lengthValues.trim() != "") {
        var data = col.lengthValues.split("','");

        if (data.length > 0) {
            if (data[0].charAt(0) == "'")
                data[0] = data[0].substr(1);

            var last = data[data.length - 1];
            if (last.charAt(last.length - 1) == "'") {
                last = last.substr(0, last.length - 1);
                data[data.length - 1] = last;
            }
        }

        for (var i = 0; i < data.length; i++) {
            editor.html(editor.html() + "<option value='" + data[i] + "'>" + data[i] + "</option>");
        }
    }

    editor.appendTo(container);
}

Table.boolEditor = function(container, options) {
    var editor = $("<select style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "'><option value='0'>" + dictionary["s595"] + "</option><option value='1'>" + dictionary["s594"] + "</option></select>");

    editor.appendTo(container);
}

Table.numberEditor = function(container, options) {
    $("<input type='text' style='resize:none; margin:auto; width:100%;' data-bind='value:" + options.field + "' />").appendTo(container);
}
Table.htmlEditor = function(container, options) {
    $("<textarea style='resize:none; margin:auto; width:100%; height:100px;' data-bind='value:" + options.field + "' />").appendTo(container);
}

Table.uiAddColumn = function() {
    var thisClass = this;

    var name = $("#form" + Table.className + "InputColumnName");
    var typeClass = "sctSimple";
    if (Methods.currentView === 1)
        typeClass = "sctAdvanced";
    var type = $(".form" + Table.className + "SelectColumnType." + typeClass);
    var lengthValues = $("#form" + Table.className + "InputColumnLength");
    var defaultValue = $("#form" + Table.className + "InputColumnDefault");
    var attributes = $("#form" + Table.className + "SelectColumnAttributes");
    var nullable = $("#form" + Table.className + "CheckboxColumnNull");

    $("#div" + this.className + "Dialog").dialog({
        title: dictionary["s31"],
        resizable: false,
        modal: true,
        width: 400,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            name.val("");
            type.val("text");
            //$('.ui-widget-overlay').css('position', 'absolute');
            $(this).dialog("destroy");
        },
        buttons: [
            {
                text: dictionary["s37"],
                click: function() {
                    name.val($.trim(name.val()));

                    if (name.val() == "")
                    {
                        Methods.alert(dictionary["s13"], dictionary["s14"]);
                        return;
                    }

                    if (!Test.variableValidation(name.val(), false))
                    {
                        var oldValue = name.val();
                        var newValue = Test.convertVariable(name.val(), false);
                        name.val(newValue);
                        Methods.alert(dictionary["s1"].format(oldValue, newValue), "info", dictionary["s2"]);
                        return;
                    }

                    if (Table.doesColumnExists(name.val()))
                    {
                        Methods.alert(dictionary["s15"], "alert", dictionary["s14"]);
                        return;
                    }

                    if (Table.restrictedColumnNames.indexOf(name.val()) != -1) {
                        Methods.alert(dictionary["s693"], "alert", dictionary["s14"]);
                        return;
                    }

                    var structGrid = $("#div" + thisClass.className + "GridStructure").data('kendoGrid');
                    structGrid.dataSource.add({
                        name: name.val(),
                        type: type.val(),
                        lengthValues: lengthValues.val(),
                        defaultValue: defaultValue.val(),
                        attributes: attributes.val(),
                        nullable: nullable.is(":checked") ? 1 : 0
                    })

                    //dataGrid mod start
                    var dataGrid = $("#div" + thisClass.className + "GridData").data('kendoGrid');

                    var templateSet = false;

                    var col = {
                        title: name.val(),
                        field: name.val()
                    };

                    Table.dataGridSchemaFields[name.val()] = {};

                    col["editor"] = Table.stringEditor;
                    Table.dataGridSchemaFields[name.val()]["type"] = "string";
                    Table.dataGridSchemaFields[name.val()]["editable"] = true;
                    Table.dataGridSchemaFields[name.val()]["defaultValue"] = defaultValue.val();
                    if (Table.dataGridSchemaFields[name.val()]["defaultValue"] != null && Table.dataGridSchemaFields[name.val()]["defaultValue"].toLowerCase().trim() == "null")
                        Table.dataGridSchemaFields[name.val()]["defaultValue"] = null;
                    if (Table.dataGridSchemaFields[name.val()]["defaultValue"] != null && Table.dataGridSchemaFields[name.val()]["defaultValue"].toLowerCase().trim() == "current_timestamp")
                        Table.dataGridSchemaFields[name.val()]["defaultValue"] = kendo.toString(new Date(), "yyyy-MM-dd HH:mm:ss");
                    Table.dataGridSchemaFields[name.val()]["nullable"] = nullable.is(":checked");

                    switch (type.val()) {
                        case "tinyint":
                        case "smallint":
                        case "mediumint":
                        case "int":
                        case "bigint":
                        case "decimal":
                        case "float":
                        case "double":
                        case "real":
                        case "bit":
                        case "serial":
                            {
                                col["editor"] = Table.numberEditor;
                                Table.dataGridSchemaFields[name.val()]["type"] = "number";
                                break;
                            }
                        case "set":
                        case "enum":
                            {
                                col["editor"] = Table.setEditor;
                                break;
                            }
                        case "date":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                col["editor"] = Table.dateEditor;
                                col["format"] = "{0:yyyy-MM-dd}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy-MM-dd}", val);
                                }
                                col["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy-MM-dd') #";
                                templateSet = true;
                                break;
                            }
                        case "timestamp":
                        case "datetime":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                col["editor"] = Table.dateTimeEditor;
                                col["format"] = "{0:yyyy-MM-dd HH:mm:ss}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy-MM-dd HH:mm:ss}", val);
                                }
                                col["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy-MM-dd HH:mm:ss') #";
                                templateSet = true;
                                break;
                            }
                        case "year":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                col["editor"] = Table.yearEditor;
                                col["format"] = "{0:yyyy}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:yyyy}", val);
                                }
                                col["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'yyyy') #";
                                templateSet = true;
                                break;
                            }
                        case "time":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "date";
                                col["editor"] = Table.timeEditor;
                                col["format"] = "{0:HH:mm:ss}";
                                Table.dataGridSchemaFields[name.val()]["parse"] = function(val) {
                                    if (val == null)
                                        return null;
                                    return kendo.format("{0:HH:mm:ss}", val);
                                }
                                col["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':kendo.toString(" + name.val() + ",'HH:mm:ss') #";
                                templateSet = true;
                                break;
                            }
                        case "boolean":
                            {
                                Table.dataGridSchemaFields[name.val()]["type"] = "numeric";
                                col["editor"] = Table.boolEditor;
                                col["template"] = "<div align='center'><input type='checkbox' #= " + name.val() + "==1 ? checked='checked' : '' # disabled readonly /></div>";
                                templateSet = true;
                                break;
                            }
                        case "tinytext":
                        case "mediumtext":
                        case "longtext":
                        case "text":
                            {
                                col["editor"] = Table.htmlEditor;
                                col["template"] = '<div class="horizontalMargin" align="center">' +
                                        '<span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next(),\'' + name.val() + '\')" title="' + dictionary["s130"] + '"></span>' +
                                        '<textarea class="notVisible">#=' + name.val() + '#</textarea>' +
                                        '</div>';
                                Table.dataGridSchemaFields[name.val()]["type"] = "string";
                                Table.dataGridSchemaFields[name.val()]["editable"] = false;
                                templateSet = true;
                                break;
                            }
                    }

                    if (Table.dataGridSchemaFields[name.val()]["nullable"] && !templateSet)
                        col["template"] = "#= " + name.val() + "==null?'<span style=\"font-style:italic;\"><b>null</b></span>':" + name.val() + " #";

                    dataGrid.columns.splice(dataGrid.columns.length - 1, 0, col);

                    for (var i = 0; i < dataGrid.dataSource.data().length; i++) {
                        var row = dataGrid.dataSource.data()[i];
                        row[name.val()] = Table.dataGridSchemaFields[name.val()]["defaultValue"];
                        row.fields[name.val()] = {
                            type: Table.dataGridSchemaFields[name.val()]["type"],
                            defaultValue: Table.dataGridSchemaFields[name.val()]["defaultValue"],
                            editable: type.val() != "tinytext" && type.val() != "mediumtext" && type.val() != "longtext" && type.val() != "text",
                            nullable: Table.dataGridSchemaFields[name.val()]["nullable"],
                            parse: Table.dataGridSchemaFields[name.val()]["parse"]
                        }
                        row.defaults[name.val()] = Table.dataGridSchemaFields[name.val()]["defaultValue"];
                    }

                    Table.uiRefreshDataGrid();

                    $(this).dialog("close");

                    Methods.iniTooltips();
                    Table.structureEmptyCheck();
                    Table.uiIniHTMLTooltips();
                    //dataGrid mod end
                }
            },
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.uiRemoveRow = function(obj) {
    var thisClass = this;
    var index = obj.closest('tr')[0].sectionRowIndex;
    Methods.confirm(dictionary["s32"], dictionary["s33"], function() {
        var grid = $("#div" + thisClass.className + "GridData").data('kendoGrid');
        var item = grid.dataItem(grid.tbody.find("tr:eq(" + index + ")"));

        if (item.id != 0 && item.id != null) {
            Table.crudUpdate(Table.crudDataDeleted, item.id);
        }

        grid.removeRow(grid.tbody.find("tr:eq(" + index + ")"));
    });
}

Table.uiClearRows = function() {
    var thisClass = this;
    Methods.confirm(dictionary["s368"], dictionary["s367"], function() {
        var grid = $("#div" + thisClass.className + "GridData").data('kendoGrid');

        var columns = grid.columns;
        var items = [];

        Table.uiReloadDataGrid(items, columns);
    });
}

Table.uiChangeHTML = function(obj, field) {
    var grid = $("#div" + this.className + "GridData").data('kendoGrid');
    var index = obj.closest("tr")[0].sectionRowIndex;
    var item = grid.dataItem(grid.tbody.find("tr:eq(" + index + ")"));

    if (item.id != null) {
        Table.crudUpdate(Table.crudDataUpdated, item.id);
    }

    $("#form" + Table.className + "TextareaHTML").val(obj.val());
    $("#div" + Table.className + "DialogHTML").dialog({
        title: dictionary["s36"],
        resizable: false,
        modal: true,
        width: 840,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            var name = $("#form" + Table.className + "TextareaHTML").attr("name");
            var instance = CKEDITOR.instances[name];
            if (instance)
            {
                instance.setMode("wysiwyg");
            }
            //$('.ui-widget-overlay').css('position', 'absolute');  
        },
        create: function() {
            var thisDialog = $("#div" + Table.className + "DialogHTML");
            Methods.iniCKEditor($("#form" + Table.className + "TextareaHTML"), function() {
                thisDialog.dialog("option", "position", "center");
            }, 800);
        },
        buttons: [
            {
                text: dictionary["s38"],
                click: function() {
                    obj.val(Methods.getCKEditorData($(this).find('textarea:eq(0)')));
                    item[field] = Methods.getCKEditorData($(this).find('textarea:eq(0)'));
                    $(this).dialog("close");
                }
            },
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
}

Table.uiIniHTMLTooltips = function() {
    $(".tooltipTableStructure").tooltip({
        content: function() {
            return dictionary["s39"] + "<hr/>" + $(this).next().val();
        },
        position: {
            my: "left top",
            at: "left bottom",
            offset: "15 0"
        },
        show: false,
        hide: false
    });
}