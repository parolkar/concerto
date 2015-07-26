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

function Test() {
}
;
OModule.inheritance(Test);

Test.className = "Test";

Test.widgetTypes = {
    table: 1,
    template: 2,
    test: 4,
    QTI: 5
}

Test.onAfterEdit = function()
{
    Test.currentFromLine = -1;
    Test.currentToLine = -1;
    Test.debugStopped = true;
    Test.functionWidgets = [];
    Test.crudLogsDeleted = [];
};

Test.onAfterImport = function() {
    Template.uiList();
    Table.uiList();
    QTIAssessmentItem.uiList();

    Test.uiTestsChanged();
    Test.uiTablesChanged();
    Test.uiTemplatesChanged();
    Test.uiQTIAssessmentItemsChanged();
}

Test.onAfterAdd = function() {
}

Test.onAfterSave = function()
{
};

Test.onAfterDelete = function() {
    Test.uiTestsChanged();
}

Test.getAddSaveObject = function()
{
    return {
        oid: this.currentID,
        class_name: this.className,
        name: $("#form" + this.className + "InputName").val(),
        type: $("#form" + this.className + "SelectType").val()
    };
};

Test.getFullSaveObject = function(isNew) {
    if (isNew == null) {
        isNew = false;
    }

    var obj = this.getAddSaveObject();
    obj["parameters"] = Test.getSerializedParameterVariables();
    obj["returns"] = Test.getSerializedReturnVariables();
    obj["description"] = $("#form" + this.className + "TextareaDescription").val();
    obj["code"] = $("#textareaTestLogic").val();
    obj["deleteLogs"] = Test.getSerializedCrudDeleted("logs");
    return obj;
}

Test.uiSaveValidate = function(ignoreOnBefore, isNew) {
    var thisClass = this;

    if (!this.checkRequiredFields([
        $("#form" + this.className + "InputName").val()
    ])) {
        Methods.alert(dictionary["s415"], "alert");
        return false;
    }

    $.post("query/check_module_unique_fields.php", {
        "class_name": this.className,
        "oid": (isNew ? 0 : this.currentID),
        "fields[]": [$.toJSON({
                name: "name",
                value: $("#form" + this.className + "InputName").val()
            })]
    }, function(data) {
        switch (data.result) {
            case OModule.queryResults.OK:
                {
                    thisClass.uiSaveValidated(ignoreOnBefore, isNew);
                    break;
                }
            case 1:
                {
                    Methods.alert(dictionary["s336"], "alert", dictionary["s274"]);
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

Test.logicCodeMirror = null;
Test.codeMirrors = new Array();
Test.uiRefreshCodeMirrors = function() {
    for (var i = 0; i < Test.codeMirrors.length; i++) {
        try {
            Test.codeMirrors[i].refresh();
        }
        catch (err) {

        }
    }
}

Test.uiGoToRelatedObject = function(type, oid) {
    if (oid == 0)
        return;
    switch (type) {
        //templates
        case Test.widgetTypes.template:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-Template");
                Template.uiEdit(oid);
                break;
            }
            //tables
        case Test.widgetTypes.table:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-Table");
                Table.uiEdit(oid);
                break;
            }
            //tests
        case Test.widgetTypes.test:
            {
                Test.uiEdit(oid);
                break;
            }
            //QTI
        case Test.widgetTypes.QTIInitialization:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-QTIAssessmentItem");
                QTIAssessmentItem.uiEdit(oid);
                break;
            }
    }
}

Test.uiTemplatesChanged = function() {
}

Test.uiTestsChanged = function() {
}

Test.uiQTIAssessmentItemsChanged = function() {
}

Test.uiTablesChanged = function() {
    $(".divFunctionWidget").each(function() {
        if ($(this).attr("funcName") == "concerto.table.query") {
            Test.uiRefreshExtendedFunctionWizard("concerto.table.query");
        }
    });
}

Test.variableValidation = function(value, special) {
    if (special == null)
        special = true;
    var oldValue = value;
    var newValue = Test.convertVariable(oldValue, special);
    if (oldValue != newValue)
        return false;
    else
        return true;
}

Test.convertVariable = function(value, special) {
    if (special == null)
        special = true;
    if (special) {
        value = value.replace(/[^A-Z^a-z^0-9^\.^_]/gi, "");
        value = value.replace(/\.{2,}/gi, ".");
    }
    else
        value = value.replace(/[^A-Z^a-z^0-9^_]/gi, "");
    value = value.replace(/^([^A-Z^a-z]{1,})*/gi, "");
    value = value.replace(/([^A-Z^a-z^0-9]{1,})$/gi, "");
    return value;
}

Test.getReturnVars = function() {
    var vars = new Array();
    $(".inputReturnVar").each(function() {
        var v = {
            name: $(this).val(),
            section: [Test.sectionDivToObject($(this).parents(".divSection"))],
            type: ["return"]
        };
        var exists = false;
        for (var i = 0; i < vars.length; i++) {
            if (v.name == vars[i].name) {
                vars[i].section = vars[i].section.concat(v.section);
                vars[i].type = vars[i].type.concat(v.type);
                exists = true;
                break;
            }
        }
        if (!exists) {
            vars.push(v);
        }
    });
    return vars;
};

Test.getParameterVars = function() {
    var vars = new Array();
    $(".inputParameterVar").each(function() {
        var v = {
            name: $(this).val(),
            section: [Test.sectionDivToObject($(this).parents(".divSection"))],
            type: ["parameter"]
        };
        var exists = false;
        for (var i = 0; i < vars.length; i++) {
            if (v.name == vars[i].name) {
                vars[i].section = vars[i].section.concat(v.section);
                vars[i].type = vars[i].type.concat(v.type);
                exists = true;
                break;
            }
        }
        if (!exists) {
            vars.push(v);
        }
    });
    return vars;
};

Test.uiVarNameChanged = function(obj) {
    if (obj != null) {
        var oldValue = obj.val();
        if (!Test.variableValidation(oldValue)) {
            var newValue = Test.convertVariable(oldValue);
            obj.val(newValue);
            Methods.alert(dictionary["s1"].format(oldValue, newValue), "info", dictionary["s2"]);
        }
    }
};

Test.uiAddParameter = function() {
    var vars = this.getSerializedParameterVariables();
    var v = {
        name: "",
        description: ""
    };
    vars.push($.toJSON(v));
    this.uiRefreshVariables(vars, null);
};

Test.uiRemoveParameter = function(index) {
    var vars = this.getSerializedParameterVariables();
    vars.splice(index, 1);
    this.uiRefreshVariables(vars, null);
};

Test.uiAddReturn = function() {
    var vars = this.getSerializedReturnVariables();
    var v = {
        name: "",
        description: ""
    };
    vars.push($.toJSON(v));
    this.uiRefreshVariables(null, vars);
};

Test.uiRemoveReturn = function(index) {
    var vars = this.getSerializedReturnVariables();
    vars.splice(index, 1);
    this.uiRefreshVariables(null, vars);
};

Test.getSerializedParameterVariables = function() {
    var vars = new Array();
    $(".table" + this.className + "Parameters tr").each(function() {
        var v = {};
        v["name"] = $(this).find("input").val();
        v["description"] = $(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

Test.getSerializedReturnVariables = function() {
    var vars = new Array();
    $(".table" + this.className + "Returns tr").each(function() {
        var v = {};
        v["name"] = $(this).find("input").val();
        v["description"] = $(this).find("textarea").val();
        vars.push($.toJSON(v));
    });
    return vars;
}

Test.uiRefreshVariables = function(parameters, returns) {
    if (parameters == null)
        parameters = this.getSerializedParameterVariables();
    if (returns == null)
        returns = this.getSerializedReturnVariables();

    Methods.uiBlock("#div" + Test.className + "Variables");
    $.post("view/Test_variables.php", {
        oid: this.currentID,
        class_name: this.className,
        parameters: parameters,
        returns: returns
    }, function(data) {
        Methods.uiUnblock("#div" + Test.className + "Variables");
        $("#div" + Test.className + "Variables").html(data);
    })
}

Test.onScroll = function() {
    if ($("#divTestResponse").length > 0) {
        if ($(window).scrollTop() > $("#divTestResponse").offset().top) {
            $(".divTestVerticalElement").css("position", "fixed");

            $(".divTestVerticalElement:eq(0)").css("top", "0px");
            $(".divTestVerticalElement:eq(1)").css("top", $(".divTestVerticalElement:eq(1)").css("height"));

            $(".divTestVerticalElement:eq(0)").css("width", "49%");
            $(".divTestVerticalElement:eq(1)").css("width", "49%");

        } else {
            $(".divTestVerticalElement").css("position", "relative");
            $(".divTestVerticalElement").css("top", "auto");

            $(".divTestVerticalElement:eq(0)").css("width", "100%");
            $(".divTestVerticalElement:eq(1)").css("width", "100%");
        }
    }
}

Test.debugWindow = null;

Test.uiStartDebug = function(url, uid) {
    Test.debugStopped = false;
    Test.debugClearOutput();
    Test.logicCodeMirror.toTextArea();
    Test.logicCodeMirror = Methods.iniCodeMirror("textareaTestLogic", "r", true);

    $("#btnStartDebug").button("disable");
    $("#btnStartDebug").button("option", "label", dictionary["s324"]);
    $("#btnStopDebug").button("enable");

    Test.debugWindow = window.open(url);
    Test.debugWindow.onload = function() {
        Test.debugInitializeTest(uid);
    }
}

Test.currentFromLine = -1;
Test.currentToLine = -1;
Test.debugInitializeTest = function(uid) {
    //initialzing
    if (Test.debugStopped)
        return;
    Test.uiChangeDebugStatus(dictionary["s655"]);
    Test.debugWindow.test = new Test.debugWindow.Concerto($(Test.debugWindow.document).find("#divTestContainer"), uid, null, null, Test.currentID, "query/",
            function(data) {
                if (Test.debugStopped)
                    return;
                switch (parseInt(data.data.STATUS)) {
                    case Concerto.statusTypes.waiting:
                        {
                            Test.debugAppendOutput(data.debug.output);
                            Test.debugAppendOutput("<br />");
                            Test.debugAppendOutput(data.debug.error_output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                            Test.debugSetState(data.debug.state);
                            if (Test.debugIsCurrentLineLast()) {
                                //test finished
                                Test.uiChangeDebugStatus(dictionary["s656"]);
                                Test.debugCloseTestWindow();
                                break;
                            }
                            Test.debugRunNextLine();
                            Test.uiChangeDebugStatus(dictionary["s657"].format(Test.currentFromLine + 1));
                            break;
                        }
                    case Concerto.statusTypes.waitingCode:
                        {
                            Test.debugAppendOutput(data.debug.output);
                            Test.debugAppendOutput("<br />");
                            Test.debugAppendOutput(data.debug.error_output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                            Test.debugSetState(data.debug.state);
                            Test.debugRunNextLine();
                            Test.uiChangeDebugStatus(dictionary["s657"].format(Test.currentFromLine + 1));
                            break;
                        }
                    case Concerto.statusTypes.template:
                        {
                            if (parseInt(data.data.FINISHED) == 1) {
                                Test.debugAppendOutput(data.debug.output);
                                Test.debugAppendOutput("<br />");
                                Test.debugAppendOutput(data.debug.error_output);
                                Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                                Test.debugSetState(data.debug.state);

                                Test.uiChangeDebugStatus(dictionary["s656"]);
                                Test.debugCloseTestWindow();
                                break;
                            }
                            Test.debugAppendOutput(data.debug.output);
                            Test.debugAppendOutput("<br />");
                            Test.debugAppendOutput(data.debug.error_output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                            //Test.debugSetState(data.debug.state);
                            Test.uiChangeDebugStatus(dictionary["s658"], "ui-state-error");
                            break;
                        }
                    case Concerto.statusTypes.completed:
                        {
                            Test.debugAppendOutput(data.debug.output);
                            Test.debugAppendOutput("<br />");
                            Test.debugAppendOutput(data.debug.error_output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                            Test.debugSetState(data.debug.state);

                            Test.uiChangeDebugStatus(dictionary["s656"]);
                            Test.debugCloseTestWindow();
                            break;
                        }
                    case Concerto.statusTypes.error:
                        {
                            Test.uiChangeDebugStatus(dictionary["s659"].format(Test.currentFromLine + 1), "ui-state-error");
                            Test.debugAppendOutput(data.debug.output);
                            Test.debugAppendOutput("<br />");
                            Test.debugAppendOutput(data.debug.error_output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.output);
                            Test.uiAddOutputLineWidget(Test.currentToLine, data.debug.error_output, "ui-state-error");
                            Test.debugSetState(data.debug.state);
                            Test.debugCloseTestWindow();
                            break;
                        }
                    case Concerto.statusTypes.tampered:
                        {
                            Test.uiChangeDebugStatus(dictionary["s660"], "ui-state-error");
                            Test.debugCloseTestWindow();
                            break;
                        }
                }
            },
            function(data) {
            },
            true, false, null, false);
    Test.debugWindow.test.run(null, null);
}
Test.debugCloseTestWindow = function() {
    Test.debugWindow.close();
}
Test.debugClearOutput = function() {
    $("#divTestOutputContent").html("");
}

Test.debugAppendOutput = function(output) {
    $("#divTestOutputContent").append(output);
}

Test.debugSetState = function(state) {
    var obj = $.parseJSON(state);
    var html = "<table style='margin-top:10px;'>";
    for (var k in obj) {
        if (k == "concerto")
            continue;
        html += "<tr><td class='tdStateLabel' valign='top'>" + k + ": </td><td class='tdStateValue' valign='top' style='word-break: break-all;'>" + obj[k] + "</td></tr>";
    }
    html += "</table>";
    $("#divTestSessionStateContent").html(html);
}

Test.debugGetCurrentCode = function() {
    return Test.logicCodeMirror.getRange({
        line: Test.currentFromLine,
        ch: 0
    }, {
        line: Test.currentToLine,
        ch: Test.logicCodeMirror.getLine(Test.currentToLine).length
    });
}

Test.debugIsCurrentLineLast = function() {
    if (Test.logicCodeMirror.lineCount() - 1 == Test.currentToLine)
        return true;
    else
        return false;
}

Test.debugRunNextLine = function() {
    Test.currentFromLine = Test.currentToLine + 1;
    Test.currentToLine = Test.currentFromLine;
    Test.logicCodeMirror.setSelection({
        line: Test.currentFromLine,
        ch: 0
    }, {
        line: Test.currentToLine,
        ch: Test.logicCodeMirror.getLine(Test.currentToLine).length
    });

    var code = Test.debugGetCurrentCode();
    if (code.trim() == "" || (code.length > 0 && code.indexOf("#") == 0)) {
        if (Test.debugIsCurrentLineLast()) {
            Test.uiChangeDebugStatus(dictionary["s656"]);
            Test.debugCloseTestWindow();
        } else {
            Test.debugRunNextLine();
        }
        return;
    }
    Test.debugWindow.test.run(null, null, Test.debugGetCurrentCode());
}

Test.uiChangeDebugStatus = function(label, style) {
    $("#tdTestDebugStatus").html(label);
    $("#tdTestDebugStatus").removeClass("ui-state-highlight");
    $("#tdTestDebugStatus").removeClass("ui-state-error");
    if (style != null) {
        $("#tdTestDebugStatus").addClass(style);
    } else {
        $("#tdTestDebugStatus").addClass("ui-state-highlight");
    }
}

Test.debugStopped = true;
Test.uiStopDebug = function() {
    Test.currentFromLine = -1;
    Test.currentToLine = -1;
    Test.debugStopped = true;

    Test.logicCodeMirror.toTextArea();
    Test.logicCodeMirror = Methods.iniCodeMirror("textareaTestLogic", "r", false, true, true);
    Test.logicCodeMirror.on("focus", function(instance) {
        if (Test.isFunctionToolbarExpanded()) {
            Test.uiToggleFunctionToolbar();
        }
    });

    $("#btnStartDebug").button("enable");
    $("#btnStopDebug").button("disable");
    Test.uiChangeDebugStatus(dictionary["s691"]);
}

Test.uiAddOutputLineWidget = function(lineNo, output, style) {

    if (style == null)
        style = "ui-state-highlight";
    var outputLines = output.split("<br />");
    var output = [];
    for (var i = 0; i < outputLines.length; i++) {
        var line = $.trim(outputLines[i]);
        if (line.indexOf("&gt;") == 0 || line.indexOf("+") == 0)
            continue;
        output.push(line);
    }
    if (output.length == 0)
        return;
    var obj = $("<div class='divInlineWidget " + style + "'>" + output.join("<br />") + "</div>")[0];
    if (lineNo != -1)
        Test.logicCodeMirror.addLineWidget(lineNo, obj);
    else
        Test.logicCodeMirror.addLineWidget(lineNo, obj, {
            above: true
        });
}

Test.codeMirrorAutocompleteWidget = null;
Test.autoCompleteDocs = [];
Test.documentationLoaderIsWorking = false;
Test.getFuncDoc = function(func, pack) {
    for (var i = 0; i < Test.autoCompleteDocs.length; i++) {
        var doc = Test.autoCompleteDocs[i];
        if (doc.func == func && doc.pack == pack)
            return doc;
    }
    return null;
}
Test.getDocContent = function(html) {
    html = html.substr(html.indexOf("<body>") + 6);
    html = html.replace("</body></html>", "");
    html = "<div id='divFunctionDoc'>" + html + "</div>";
    return html;
}

Test.functionWidgets = [];
Test.functionWidgetOptionComments = true;
Test.functionWidgetOptionFormat = true;
Test.functionWidgetExtended = [
    "concerto.table.query"
];
Test.uiAddFunctionWidget = function(instance, func, html) {
    var date = new Date();
    var id = "func-" + func + "-" + date.getTime() + "-" + Math.floor((Math.random() * 1000));
    id = id.replace(/\./g, "___");

    if (html == null)
        html = $("#divFunctionDoc").html();

    Test.uiRemoveAllFunctionWidgets();
    var parsedDoc = Test.getParsedDoc(func, html);

    var widget = $("<div class='divFunctionWidget ui-widget-content' funcName='" + func + "' id='" + id + "'></div>");
    widget.append("<div class='ui-widget-header divFunctionWidgetTitle divFunctionWidgetElement'>" + parsedDoc.title + "</div>");
    widget.append("<div class='divFunctionWidgetDescription divFunctionWidgetElement'>" + parsedDoc.description + "</div>");

    var cmArgValues = [];

    var argTable = $("<table class='fullWidth'></table>");
    for (var i = 0; i < parsedDoc.arguments.length; i++) {
        var formattedName = parsedDoc.arguments[i].name.replace(/\./g, "___");
        var descSpan = $('<span class="spanIcon ui-icon ui-icon-help functionArgTooltip" title="asd"></span>');
        var descText = $('<div class="notVisible divFunctionWidgetArgDescHelper">' + parsedDoc.arguments[i].description + '</div>')

        argTable.append("<tr argName='" + parsedDoc.arguments[i].name + "'>" +
                "<td class='divFunctionWidgetArgTableDescColumn divFunctionWidgetArgTable'>" + (parsedDoc.arguments[i].description != "" ? (descSpan[0].outerHTML + descText[0].outerHTML) : "") + "</td>" +
                "<td class='divFunctionWidgetArgTableNameColumn divFunctionWidgetArgTable noWrap'>" + parsedDoc.arguments[i].name + "</td>" +
                "<td class='divFunctionWidgetArgTableValueColumn divFunctionWidgetArgTable'><textarea id='" + id + "-" + formattedName + "' class='notVisible'>" + parsedDoc.arguments[i].value + "</textarea></td>" +
                "</tr>");
    }

    if (Test.functionWidgetExtended.indexOf(func) == -1)
        widget.append("<div class='divFunctionWidgetElement divFunctionWidgetArgTable'>" + argTable[0].outerHTML + "</div>");
    else {
        widget.append("<div class='divFunctionWidgetElement divFunctionWidgetArgTable' style='display:none;'>" + argTable[0].outerHTML + "</div>");
        widget.append("<div class='divFunctionWidgetElement divFunctionWidgetArgTableExt'></div>");
    }

    var optionsCollection = [
        $("<div class='divFunctionWidgetOption'><label for='" + id + "-option-comments'><input type='checkbox' id='" + id + "-option-comments' " + (Test.functionWidgetOptionComments ? "checked" : "") + " />" + dictionary["s684"] + "</label></options>"),
        $("<div class='divFunctionWidgetOption'><label for='" + id + "-option-autoformat'><input type='checkbox' id='" + id + "-option-autoformat' " + (Test.functionWidgetOptionFormat ? "checked" : "") + " />" + dictionary["s685"] + "</label></options>")
    ]
    var options = $("<div class='divFunctionWidgetElement divFunctionWidgetOptions'></div>");
    for (var i = 0; i < optionsCollection.length; i++) {
        options.append(optionsCollection[i]);
    }
    options.append($("<div style='clear:both;' />"));
    widget.append(options);

    widget.append("<div class='divFunctionWidgetElement divFunctionWidgetButtons' align='center'>" +
            "<button class='btnApply' onclick=''>" + dictionary["s683"] + "</button>" +
            "<button class='btnCancel' onclick=''>" + dictionary["s23"] + "</button>" +
            "</div>");

    var fw = instance.addLineWidget(instance.getCursor(true).line, widget[0]);
    fw["widgetID"] = id;

    if (Test.functionWidgetExtended.indexOf(func) == -1) {
        var chosen = false;
        var chosenCM = null;
        for (var i = 0; i < parsedDoc.arguments.length; i++) {
            var formattedName = parsedDoc.arguments[i].name.replace(/\./g, "___");
            var cm = Methods.iniCodeMirror(id + "-" + formattedName, "r", false, true, false, false);
            if (!chosen) {
                chosenCM = cm;
                chosen = true;
            }
            cmArgValues.push(cm);
        }
        if (chosenCM != null)
            chosenCM.focus();
    }

    Methods.iniIconButton(".btnApply", "disk");
    Methods.iniIconButton(".btnCancel", "cancel");

    $(".functionArgTooltip").tooltip({
        tooltipClass: "tooltipWindow",
        position: {
            my: "left top",
            at: "left bottom",
            offset: "15 0"
        },
        content: function() {
            return $(this).next().html();
        },
        show: false,
        hide: false
    });

    Test.functionWidgets.push(fw);

    widget.find("button.btnCancel").click(function() {
        fw.clear();
    });

    widget.find("button.btnApply").click(function() {
        Test.uiApplyFunctionWidget(func, fw);
    });

    if (Test.functionWidgetExtended.indexOf(func) != -1) {
        Test.uiRefreshExtendedFunctionWizard(func);
    }
}

Test.uiApplyFunctionWidget = function(func, fw) {
    var id = fw["widgetID"];
    var widget = $("#" + id);
    var title = widget.find(".divFunctionWidgetTitle").text().replace(/\n/g, "");
    var funcName = widget.attr("funcName");

    Test.functionWidgetOptionComments = $("#" + id + "-option-comments").is(":checked");
    Test.functionWidgetOptionFormat = $("#" + id + "-option-autoformat").is(":checked");

    var result = "";
    if (Test.functionWidgetOptionComments)
        result += "\n# " + title;

    result += "\n" + funcName + "(";

    var table = widget.find(".divFunctionWidgetArgTable table");
    var isFirst = true;

    var efwValues = null;
    if (Test.functionWidgetExtended.indexOf(func) != -1)
        efwValues = Test.getExtendedFunctionWizardValues(func);

    table.find("tr").each(function() {
        var name = $(this).attr("argName");
        var formattedName = name.replace(/\./g, "___");
        var value = $("#" + id + "-" + formattedName).val();

        if (Test.functionWidgetExtended.indexOf(func) != -1) {
            value = Test.getExtendedFunctionWidgetValue(func, name, efwValues);
        }

        if (jQuery.trim(value) == "")
            return;

        if (!isFirst)
            result += ",";
        isFirst = false;
        result += "\n";
        if (Test.functionWidgetOptionComments)
            result += "\n";

        if (Test.functionWidgetOptionComments)
            result += " # " + $(this).find("div.divFunctionWidgetArgDescHelper").text().replace(/\n/g, "") + "\n";

        if (name == "...")
            result += value;
        else {
            result += name + "=" + value;
        }
    });

    result += "\n)";
    Test.logicCodeMirror.replaceRange(result, Test.logicCodeMirror.getCursor());
    fw.clear();

    if (Test.functionWidgetOptionFormat) {
        var range = {
            from: {
                line: 0,
                ch: 0
            },
            to: {
                line: Test.logicCodeMirror.lineCount() - 1,
                ch: Test.logicCodeMirror.getLine(Test.logicCodeMirror.lineCount() - 1).length
            }
        }
        Test.logicCodeMirror.autoFormatRange(range.from, range.to);
        Test.logicCodeMirror.autoIndentRange(range.from, range.to);
        Test.logicCodeMirror.setSelection(range.to);
    }
    Test.logicCodeMirror.focus();
}

Test.uiRemoveAllFunctionWidgets = function() {
    for (var i = 0; i < Test.functionWidgets.length; i++) {
        if (Test.functionWidgets[i] != null)
            Test.functionWidgets[i].clear();
    }
    Test.functionWidgets = [];
}

Test.getParsedDoc = function(func, html) {
    var parsedDoc = {};
    var obj = $(html);

    parsedDoc["title"] = obj.find("h2").html();
    parsedDoc["description"] = "";
    parsedDoc["usage"] = "";
    obj.find("h3").each(function() {
        if ($(this).html() == "Description")
            parsedDoc["description"] = $(this).next().html();
        if ($(this).html() == "Usage")
            parsedDoc["usage"] = jQuery.trim($(this).next().html()) + "\n";
    });

    parsedDoc["arguments"] = [];
    var usage = parsedDoc["usage"];
    var arguments = usage.substr(usage.indexOf(func + "(") + (func + "(").length);
    arguments = arguments.substr(0, arguments.indexOf(")\n"));
    var split = arguments.split(",");

    var argName = "";
    var argValue = "";
    var specials = [
        {char: "'", close: "'", count: 0},
        {char: '"', close: '"', count: 0},
        {char: "{", close: "}", count: 0},
        {char: "(", close: ")", count: 0},
        {char: "[", close: "]", count: 0}
    ];
    var nullified = false;
    var nullifyingChar = "";
    var continued = false;

    for (var i = 0; i < split.length; i++) {
        if (split[i].indexOf("=") == -1 && !continued) {
            var name = jQuery.trim(split[i]);
            var desc = "";

            obj.find("table[summary='R argblock'] tr").each(function() {
                if ($(this).children("td:eq(0)").children("code").html() == name) {
                    desc = $(this).children("td:eq(1)").children("p").html();
                }
            })

            parsedDoc["arguments"].push({
                name: name,
                value: "",
                description: desc
            });
        }
        else {
            if (!continued) {
                argName = jQuery.trim(split[i].substr(0, split[i].indexOf("=")));
                argValue = jQuery.trim(split[i].substr(split[i].indexOf("=") + 1));

                specials = [
                    {char: "'", close: "'", count: 0},
                    {char: '"', close: '"', count: 0},
                    {char: "{", close: "}", count: 0},
                    {char: "(", close: ")", count: 0},
                    {char: "[", close: "]", count: 0}
                ]

                nullified = false;
                nullifyingChar = "";
            } else {
                argValue += ", " + jQuery.trim(split[i]);
            }
            continued = false;

            var partArgValue = jQuery.trim(split[i]);

            for (var k = 0; k < partArgValue.length; k++) {
                var char = partArgValue[k];

                for (var j = 0; j < specials.length; j++) {
                    if (specials[j].close == char && specials[j].count % 2 == 1) {
                        if (nullified) {
                            if (nullifyingChar == specials[j].char) {
                                nullified = false;
                                nullifyingChar = "";
                                specials[j].count--;
                            }
                        } else {
                            specials[j].count--;
                        }
                        continue;
                    }

                    if (specials[j].char == char) {
                        if (!nullified)
                            specials[j].count++;
                        if (j == 0 || j == 1) {
                            nullified = true;
                            nullifyingChar = specials[j].char;
                        }
                        continue;
                    }

                }
            }

            for (var k = 0; k < specials.length; k++) {
                if (specials[k].count > 0) {
                    continued = true;
                    break;
                }
            }

            if (!continued) {
                var desc = "";
                var name = argName;

                obj.find("table[summary='R argblock'] tr").each(function() {
                    if ($(this).children("td:eq(0)").children("code").html() == name) {
                        desc = $(this).children("td:eq(1)").children("p").html();
                    }
                })

                parsedDoc["arguments"].push({
                    name: argName,
                    value: argValue,
                    description: desc
                });
            }
        }
    }

    parsedDoc["value"] = "";
    $(obj).find("h3").each(function() {
        if ($(this).html() == "Value") {
            var curr = $(this).next();
            while (!curr.is("h3")) {
                parsedDoc["value"] += curr[0].outerHTML;
                curr = curr.next();
            }
        }
    });

    return parsedDoc;
}

Test.uiWriteAutocompleteDoc = function(html) {
    var infoBar = '<div>' +
            '<table>' +
            '<tr><td style="width:30px;"><span class="spanIcon ui-icon ui-icon-info"></span></td>' +
            '<td><b>Ctrl+Enter:</b> ' + dictionary["s689"] + ', <b>Enter:</b> ' + dictionary["s690"] + ', <b>Esc:</b> ' + dictionary["s23"] + ', <b>F7:</b> ' + dictionary["s705"] + '</td>' +
            '</tr>' +
            '</table>' +
            '</div>';
    var html = Test.getDocContent(html);
    $("#divCodeAutocompleteDoc").html(infoBar + html);
}

Test.iniAutoCompleteCodeMirror = function(mode, instance, widgetsPossible) {
    switch (mode) {
        case "r":
            {
                var breakChar = [
                    '"',
                    "'",
                    "(",
                    ")",
                    "[",
                    "]",
                    "{",
                    "}",
                    " ",
                    "-",
                    "+",
                    "*",
                    "/",
                    "!",
                    "%",
                    "|",
                    "^",
                    "&",
                    "=",
                    ","
                ];

                var cursor = instance.getCursor();
                var funcName = "";
                var ch = cursor.ch - 1;
                while (ch >= 0) {
                    var firstChar = instance.getRange({
                        line: cursor.line,
                        ch: ch
                    }, {
                        line: cursor.line,
                        ch: ch + 1
                    });
                    if (breakChar.indexOf(firstChar) != -1) {
                        break;
                    }
                    funcName = instance.getRange({
                        line: cursor.line,
                        ch: ch
                    }, cursor);
                    ch--;
                }
                if (funcName.length > 0) {
                    $("#divCodeAutocomplete").remove();
                    var obj = $("<div id='divCodeAutocomplete' style='position:absolute; z-index:9999;'><table><tr><td valign='top'><select size='5' id='selectCodeAutocomplete' style='min-width:100px;' class='ui-widget-content ui-corner-all'></select></td><td><div id='divCodeAutocompleteDoc' style='min-width:300px; padding:10px;' class='ui-widget-content'>" + dictionary["s664"] + "</td></tr></table></div>");
                    var pos = instance.cursorCoords(false, "page");
                    $("body").append(obj);
                    obj.css("top", pos.top);
                    obj.css("left", pos.left);
                    Methods.uiBlock("#divCodeAutocomplete");
                    $.post("query/r_autocomplete.php", {
                        string: funcName
                    }, function(data) {
                        if (data.functions != null) {
                            for (var i = 0; i < data.functions.length; i++) {
                                var name = data.functions[i].name;
                                var pack = data.functions[i].pack;
                                var id = data.functions[i].id;

                                $("#selectCodeAutocomplete").append("<option value='" + name + "' pack='" + pack + "' id='" + id + "'>" + name + "</option>");
                            }

                            var code = null;
                            $("#selectCodeAutocomplete").change(function() {
                                var option = $(this).find("option[value='" + $(this).val() + "']");

                                var doc = Test.getFuncDoc(option.attr("value"), option.attr("pack"));
                                if (doc == null) {
                                    $("#divCodeAutocompleteDoc").html(dictionary["s319"]);

                                    if (!Test.documentationLoaderIsWorking) {
                                        Test.documentationLoaderIsWorking = true;
                                        $.post("query/r_documentation.php", {
                                            func: option.attr("value"),
                                            pack: option.attr("pack")
                                        }, function(data) {

                                            Test.autoCompleteDocs.push({
                                                func: option.attr("value"),
                                                pack: option.attr("pack"),
                                                html: data.html
                                            });

                                            Test.uiWriteAutocompleteDoc(data.html);
                                            Test.documentationLoaderIsWorking = false;
                                            $("#selectCodeAutocomplete").change();
                                        }, "json");
                                    }
                                } else {
                                    Test.uiWriteAutocompleteDoc(doc.html);
                                }
                            });
                            $("#selectCodeAutocomplete").blur(function() {
                                $("#divCodeAutocomplete").remove();
                            });
                            $("#selectCodeAutocomplete").keydown(function(e) {
                                code = (e.keyCode ? e.keyCode : e.which);
                                //ctrl + enter
                                if (e.ctrlKey && code == 13) {
                                    if (!widgetsPossible)
                                        return;
                                    var selectedOption = $("#selectCodeAutocomplete").children("option:selected");
                                    if (selectedOption.length == 0)
                                        return;
                                    var doc = Test.getFuncDoc(selectedOption.attr("value"), selectedOption.attr("pack"));
                                    if (doc == null)
                                        return;
                                    Test.uiAddFunctionWidget(instance, selectedOption.attr("value"), Test.getDocContent(doc.html));
                                    $("#selectCodeAutocomplete").blur();
                                    instance.focus();
                                    e.preventDefault();

                                    instance.setSelection({
                                        line: cursor.line,
                                        ch: ch + 1
                                    }, cursor);
                                    instance.replaceSelection("\n");
                                    instance.setSelection(cursor);
                                    return;
                                }
                                //F7
                                if (code == 118) {
                                    var selectedOption = $("#selectCodeAutocomplete").children("option:selected");
                                    $("#selectCodeAutocomplete").blur();
                                    instance.focus();
                                    if (selectedOption.length == 0)
                                        return;
                                    User.addFavouriteFunction(selectedOption.attr("id"));
                                    e.preventDefault();
                                }
                                //enter
                                if (code == 13) {
                                    var selectedOption = $("#selectCodeAutocomplete").children("option:selected");
                                    if (selectedOption.length == 0)
                                        return;
                                    instance.replaceRange($("#selectCodeAutocomplete").val() + "()", {
                                        line: cursor.line,
                                        ch: ch + 1
                                    }, instance.getCursor());
                                    $("#selectCodeAutocomplete").blur();
                                    instance.focus();
                                    instance.setCursor({line: instance.getCursor().line, ch: instance.getCursor().ch - 1})
                                    e.preventDefault();
                                }
                                //backspace
                                if (code == 8) {
                                    e.preventDefault();
                                }
                                //escape
                                if (code == 27) {
                                    $("#selectCodeAutocomplete").blur();
                                    instance.focus();
                                }
                            });
                            $("#selectCodeAutocomplete").focus();
                        }
                        else {
                            $("#divCodeAutocomplete").remove();
                        }

                        Methods.uiUnblock("#divCodeAutocomplete");
                    }, "json");
                }
                break;
            }
    }
}

Test.uiAddFunctionWidgetFromToolbar = function(func, html) {

    if (Test.logicCodeMirror.options.readonly)
        return;

    var instance = Test.logicCodeMirror;
    Test.uiAddFunctionWidget(instance, func, html);
    instance.focus();

    var cursor = instance.getCursor();
    instance.setSelection({
        line: cursor.line,
        ch: cursor.ch + 1
    }, cursor);
    instance.replaceSelection("\n");
    instance.setSelection(cursor);

    if (Test.isFunctionToolbarExpanded()) {
        Test.uiToggleFunctionToolbar();
    }
}

Test.uiMouseOverFunctionToolbarTr = function(index) {
    $(".tdFunctionToolbarIndexable").removeClass("ui-state-highlight");
    $(".tdFunctionToolbarIndex" + index).addClass("ui-state-highlight");
}

Test.uiToggleFunctionToolbar = function() {
    var button = $(".btnFunctionToolbarControl");
    var toolbar = $(".divFunctionToolbar");
    var accordion = $(".divFunctionToolbarContent");
    if (!Test.isFunctionToolbarExpanded()) {
        //was collapsed
        button.button("option", "icons", {
            primary: "ui-icon-minus",
            secondary: null
        });
        toolbar.removeClass("divFunctionToolbar-collapsed");
        toolbar.addClass("divFunctionToolbar-expanded");

        accordion.show(1000, function() {
            $(".divFunctionToolbarContent").accordion("refresh");
        });
    } else {
        //was expanded
        button.button("option", "icons", {
            primary: "ui-icon-plus",
            secondary: null
        });
        toolbar.removeClass("divFunctionToolbar-expanded");
        toolbar.addClass("divFunctionToolbar-collapsed");

        accordion.hide(1000);
    }
}

Test.isFunctionToolbarExpanded = function() {
    var button = $(".btnFunctionToolbarControl");
    if (button.button("option", "icons").primary == "ui-icon-plus")
        return false;
    else
        return true;
}

Test.uiRefreshFunctionToolbar = function() {
    var toolbar = $(".divFunctionToolbar");
    $.post("view/Test_functions.php", {
        isFunctionToolbarExpanded: Test.isFunctionToolbarExpanded() ? 1 : 0
    }, function(data) {
        toolbar.html(data);
    });
}

Test.uiDocDialog = function(html) {
    $("#divTestDialogDoc").html(html);
    $("#divTestDialogDoc").dialog({
        modal: true,
        resizable: true,
        title: dictionary["s707"],
        width: 925,
        height: 600,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            $('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons: [
            {
                text: dictionary["s629"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    })
}

Test.functionWizardCM = {};
Test.uiFunctionWizardToggleView = function(container, view) {
    var wizard = $(container + "-0");
    var code = $(container + "-1");
    if (view == 0) {
        wizard.removeClass("notVisible");
        code.addClass("notVisible");
    } else {
        wizard.addClass("notVisible");
        code.removeClass("notVisible");
        Test.functionWizardCM[container.substr(1)].refresh();
    }
}

Test.uiRemoveFWsectionElem = function(func, elem) {
    $(elem).remove();
    Test.uiRefreshExtendedFunctionWizard(func);
}

Test.uiAddFWsectionElem = function(func, section) {
    var values = Test.getExtendedFunctionWizardValues(func);
    values[section + "_add"] = 1;
    Test.uiRefreshExtendedFunctionWizard(func, values);

}

Test.uiRefreshExtendedFunctionWizard = function(func, values) {
    if (values == null)
        values = Test.getExtendedFunctionWizardValues(func);
    Methods.uiBlock(".divFunctionWidgetArgTableExt");
    $.post("view/fw_" + func + ".php",
            values,
            function(data) {
                Methods.uiUnblock(".divFunctionWidgetArgTableExt");
                $(".divFunctionWidgetArgTableExt").html(data);
            });
}

Test.getExtendedFunctionWidgetValue = function(func, argName, values) {
    var result = "";
    switch (func) {
        case "concerto.table.query":
            {
                if (argName == "params") {
                    return values["params"];
                }
                if (argName == "sql") {
                    result += 'paste("';
                    result += "\n" + values.type + "\n";
                    switch (values.type) {
                        case "SELECT":
                            {
                                var select_section = $.parseJSON(values.select_section);
                                var select_result = "";
                                for (var i = 0; i < select_section.length; i++) {
                                    var ss = select_section[i];
                                    if (select_result != "")
                                        select_result += ",\n";
                                    if (parseInt(ss.v) == 0)
                                        select_result += ss.w0;
                                    else
                                        select_result += ss.c;
                                }
                                result += select_result + "\n";

                                result += " FROM `" + values.db + "`.`" + values.table_name + "`\n";

                                var where_section = $.parseJSON(values.where_section);
                                var where_result = "";
                                if (where_section != null) {
                                    result += "WHERE\n";
                                    for (var i = 0; i < where_section.length; i++) {
                                        var ws = where_section[i];

                                        if (ws.v == 0) {
                                            if (i != 0)
                                                where_result += ws.w0 + " ";
                                            where_result += ws.w1 + " ";
                                            where_result += ws.w2 + " ";
                                            where_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ws.w3 + ')),"\'' + "\n";
                                        } else {
                                            where_result += ws.c + "\n";
                                        }
                                    }
                                    result += where_result;
                                }

                                var group_section = $.parseJSON(values.group_section);
                                var group_result = "";
                                if (group_section != null) {
                                    result += "GROUP BY\n";
                                    for (var i = 0; i < group_section.length; i++) {
                                        var gs = group_section[i];
                                        if (group_result != "")
                                            group_result += ", ";
                                        if (parsInt(gs.v) == 0) {
                                            group_result += gs.w0 + " ";
                                            group_result += gs.w1 + "\n";
                                        }
                                        else
                                            group_result += gs.c + "\n";

                                    }
                                    result += group_result;
                                }

                                var having_section = $.parseJSON(values.having_section);
                                var having_result = "";
                                if (having_section != null) {
                                    result += "HAVING\n";
                                    for (var i = 0; i < having_section.length; i++) {
                                        var hs = having_section[i];

                                        if (hs.v == 0) {
                                            if (i != 0)
                                                having_result += hs.w0 + " ";
                                            having_result += hs.w1 + " ";
                                            having_result += hs.w2 + " ";
                                            having_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + hs.w3 + ')),"\'' + "\n";
                                        } else {
                                            having_result += hs.c + "\n";
                                        }
                                    }
                                    result += having_result;
                                }

                                var order_section = $.parseJSON(values.order_section);
                                var order_result = "";
                                if (order_section != null) {
                                    result += "ORDER BY\n";
                                    for (var i = 0; i < order_section.length; i++) {
                                        var os = order_section[i];
                                        if (order_result != "")
                                            order_result += ", ";
                                        if (parseInt(os.v) == 0) {
                                            order_result += os.w0 + " ";
                                            order_result += os.w1 + "\n";
                                        }
                                        else
                                            order_result += os.c + "\n";
                                    }
                                    result += order_result;
                                }

                                var limit_section = $.parseJSON(values.limit_section);
                                if (parseInt(limit_section.w0) == 1) {
                                    result += "LIMIT " + limit_section.w1 + "," + limit_section.w2 + "\n";
                                }
                                break;
                            }
                        case "INSERT":
                            {
                                result += " INTO `" + values.db + "`.`" + values.table_name + "`\n";

                                var set_section = $.parseJSON(values.set_section);
                                var set_result = "";
                                if (set_section != null) {
                                    result += "SET \n";
                                    for (var i = 0; i < set_section.length; i++) {
                                        var ss = set_section[i];
                                        if (set_result != "")
                                            set_result += ",\n";
                                        if (parseInt(ss.v) == 0) {
                                            set_result += ss.w0 + "=";
                                            set_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ss.w1 + ')),"\'' + "\n";
                                        }
                                        else
                                            set_result += ss.c + "\n";
                                    }
                                    result += set_result + "\n";
                                }
                                break;
                            }
                        case "DELETE":
                            {
                                result += " FROM `" + values.db + "`.`" + values.table_name + "`\n";

                                var where_section = $.parseJSON(values.where_section);
                                var where_result = "";
                                if (where_section != null) {
                                    result += "WHERE\n";
                                    for (var i = 0; i < where_section.length; i++) {
                                        var ws = where_section[i];

                                        if (ws.v == 0) {
                                            if (i != 0)
                                                where_result += ws.w0 + " ";
                                            where_result += ws.w1 + " ";
                                            where_result += ws.w2 + " ";
                                            where_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ws.w3 + ')),"\'' + "\n";
                                        } else {
                                            where_result += ws.c + "\n";
                                        }
                                    }
                                    result += where_result;
                                }

                                var order_section = $.parseJSON(values.order_section);
                                var order_result = "";
                                if (order_section != null) {
                                    result += "ORDER BY\n";
                                    for (var i = 0; i < order_section.length; i++) {
                                        var os = order_section[i];
                                        if (order_result != "")
                                            order_result += ", ";
                                        if (parseInt(os.v) == 0) {
                                            order_result += os.w0 + " ";
                                            order_result += os.w1 + "\n";
                                        }
                                        else
                                            order_result += os.c + "\n";
                                    }
                                    result += order_result;
                                }

                                var limit_section = $.parseJSON(values.limit_section);
                                if (parseInt(limit_section.w0) == 1) {
                                    result += "LIMIT " + limit_section.w1 + "," + limit_section.w2 + "\n";
                                }
                                break;
                            }
                        case "REPLACE":
                            {
                                result += " INTO `" + values.db + "`.`" + values.table_name + "`\n";

                                var set_section = $.parseJSON(values.set_section);
                                var set_result = "";
                                if (set_section != null) {
                                    result += "SET \n";
                                    for (var i = 0; i < set_section.length; i++) {
                                        var ss = set_section[i];
                                        if (set_result != "")
                                            set_result += ",\n";
                                        if (parseInt(ss.v) == 0) {
                                            set_result += ss.w0 + "=";
                                            set_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ss.w1 + ')),"\'' + "\n";
                                        }
                                        else
                                            set_result += ss.c + "\n";
                                    }
                                    result += set_result + "\n";
                                }
                                break;
                            }
                        case "UPDATE":
                            {
                                result += "`" + values.db + "`.`" + values.table_name + "`\n";

                                var set_section = $.parseJSON(values.set_section);
                                var set_result = "";
                                if (set_section != null) {
                                    result += "SET \n";
                                    for (var i = 0; i < set_section.length; i++) {
                                        var ss = set_section[i];
                                        if (set_result != "")
                                            set_result += ",\n";
                                        if (parseInt(ss.v) == 0) {
                                            set_result += ss.w0 + "=";
                                            set_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ss.w1 + ')),"\'' + "\n";
                                        }
                                        else
                                            set_result += ss.c + "\n";
                                    }
                                    result += set_result + "\n";
                                }

                                var where_section = $.parseJSON(values.where_section);
                                var where_result = "";
                                if (where_section != null) {
                                    result += "WHERE\n";
                                    for (var i = 0; i < where_section.length; i++) {
                                        var ws = where_section[i];

                                        if (ws.v == 0) {
                                            if (i != 0)
                                                where_result += ws.w0 + " ";
                                            where_result += ws.w1 + " ";
                                            where_result += ws.w2 + " ";
                                            where_result += '\'",dbEscapeStrings(concerto$db$connection,toString(' + ws.w3 + ')),"\'' + "\n";
                                        } else {
                                            where_result += ws.c + "\n";
                                        }
                                    }
                                    result += where_result;
                                }

                                var order_section = $.parseJSON(values.order_section);
                                var order_result = "";
                                if (order_section != null) {
                                    result += "ORDER BY\n";
                                    for (var i = 0; i < order_section.length; i++) {
                                        var os = order_section[i];
                                        if (order_result != "")
                                            order_result += ", ";
                                        if (parseInt(os.v) == 0) {
                                            order_result += os.w0 + " ";
                                            order_result += os.w1 + "\n";
                                        }
                                        else
                                            order_result += os.c + "\n";
                                    }
                                    result += order_result;
                                }

                                var limit_section = $.parseJSON(values.limit_section);
                                if (parseInt(limit_section.w0) == 1) {
                                    result += "LIMIT " + limit_section.w1 + "," + limit_section.w2 + "\n";
                                }
                                break;
                            }
                    }
                    result += '",sep="")';
                }
                break;
            }
    }
    return result;
}

Test.getExtendedFunctionWizardValues = function(func) {
    var values = {};
    switch (func) {
        case "concerto.table.query":
            {
                values["db"] = $("#selectFWdb").val();
                values["table_name"] = $("#selectFWtable").val();
                values["type"] = $("input[name='radioFWtype']:checked").val();

                values["params"] = $("#taFWarg-params").val();

                values["select_section"] = [];
                $(".tableFWselectSection tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWselectRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWselectColumn" + index).val();
                    elem["c"] = $("#taFWselectCode" + index).val();
                    values["select_section"].push(elem);
                });
                if (values["select_section"].length == 0)
                    delete values["select_section"];
                else
                    values["select_section"] = $.toJSON(values["select_section"]);

                values["where_section"] = [];
                $(".tableFWwhereSection > tbody > tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWwhereRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWwhereCondLink" + index).val();
                    elem["w1"] = $("#selectFWwhereColumn" + index).val();
                    elem["w2"] = $("#selectFWwhereOperator" + index).val();
                    elem["w3"] = $("#taFWwhereCode" + index + "-w3").val();
                    elem["c"] = $("#taFWwhereCode" + index).val();
                    values["where_section"].push(elem);
                });
                if (values["where_section"].length == 0)
                    delete values["where_section"];
                else
                    values["where_section"] = $.toJSON(values["where_section"]);

                values["order_section"] = [];
                $(".tableFWorderSection > tbody > tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWorderRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWorderColumn" + index).val();
                    elem["w1"] = $("#selectFWorderDir" + index).val();
                    elem["c"] = $("#taFWorderCode" + index).val();
                    values["order_section"].push(elem);
                });
                if (values["order_section"].length == 0)
                    delete values["order_section"];
                else
                    values["order_section"] = $.toJSON(values["order_section"]);

                values["group_section"] = [];
                $(".tableFWgroupSection > tbody > tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWgroupRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWgroupColumn" + index).val();
                    elem["w1"] = $("#selectFWgroupDir" + index).val();
                    elem["c"] = $("#taFWgroupCode" + index).val();
                    values["group_section"].push(elem);
                });
                if (values["group_section"].length == 0)
                    delete values["group_section"];
                else
                    values["group_section"] = $.toJSON(values["group_section"]);

                values["having_section"] = [];
                $(".tableFWhavingSection > tbody > tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWhavingRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWhavingCondLink" + index).val();
                    elem["w1"] = $("#selectFWhavingColumn" + index).val();
                    elem["w2"] = $("#selectFWhavingOperator" + index).val();
                    elem["w3"] = $("#taFWhavingCode" + index + "-w3").val();
                    elem["c"] = $("#taFWhavingCode" + index).val();
                    values["having_section"].push(elem);
                });
                if (values["having_section"].length == 0)
                    delete values["having_section"];
                else
                    values["having_section"] = $.toJSON(values["having_section"]);

                values["limit_section"] = {};
                values["limit_section"]["w0"] = $("#radioFWlimit").val();
                if (parseInt($("#radioFWlimit").val()) == 1) {
                    values["limit_section"]["w1"] = $("#selectFWlimitOffset").val();
                    values["limit_section"]["w2"] = $("#selectFWlimitNumber").val();
                }
                values["limit_section"] = $.toJSON(values["limit_section"]);

                values["set_section"] = [];
                $(".tableFWsetSection > tbody > tr").each(function() {
                    var index = $(this).attr("index");
                    var elem = {};
                    elem["v"] = $("#radioFWsetRadioMenuWizard" + index).is(":checked") ? 0 : 1;
                    elem["w0"] = $("#selectFWsetColumn" + index).val();
                    elem["w1"] = $("#taFWsetCode" + index + "-w1").val();
                    elem["c"] = $("#taFWsetCode" + index).val();
                    values["set_section"].push(elem);
                });
                if (values["set_section"].length == 0)
                    delete values["set_section"];
                else
                    values["set_section"] = $.toJSON(values["set_section"]);
            }
    }
    return values;
}

Test.logsGridSchemaFields = null;
Test.uiIniLogsGrid = function() {
    var thisClass = this;

    $("#div" + this.className + "GridLogsContainer").html("<div id='div" + this.className + "GridLogs' class='grid'></div>");

    var fields = {
        id: {
            type: "string"
        },
        type: {
            type: "number"
        },
        IP: {
            type: "string"
        },
        browser: {
            type: "string"
        },
        created: {
            type: "string"
        },
        message: {
            type: "string"
        }
    };

    var dataSource = new kendo.data.DataSource({
        transport: {
            read: {
                url: "query/Test_logs_list.php?oid=" + thisClass.currentID,
                dataType: "json"
            }
        },
        schema: {
            model: {
                id: "id",
                fields: fields
            }
        },
        pageSize: 5
    });

    Test.logsGridSchemaFields = fields;

    $("#div" + this.className + "GridLogs").kendoGrid({
        dataBound: function(e) {
            Methods.iniTooltips();
        },
        dataSource: dataSource,
        columns: [{
                title: dictionary["s756"],
                field: "created",
                width: 150
            }, {
                title: dictionary["s122"],
                field: "type",
                template: '#if(type==0) {#' + dictionary["s763"] + "#} else {#" + dictionary["s764"] + "#}#",
                width: 100
            }, {
                title: dictionary["s757"],
                field: "message",
                encoded: false
            }, {
                title: dictionary["s758"],
                field: "browser"
            }, {
                title: dictionary["s759"],
                field: "IP",
                width: 120
            }, {
                title: ' ',
                width: 50,
                template: '<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="' + thisClass.className + '.uiRemoveLog($(this))" title="' + dictionary["s760"] + '"></span>'
            }],
        toolbar: [
            {
                name: "clear",
                template: '<button class="btnRemove" onclick="Test.uiClearLogs()">' + dictionary["s765"] + '</button>'
            }
        ],
        editable: false,
        scrollable: true,
        resizable: true,
        sortable: true,
        columnMenu: {
            sortable: false,
            columns: true,
            messages: {
                filter: dictionary["s341"],
                columns: dictionary["s533"],
                sortAscending: dictionary["s534"],
                sortDescending: dictionary["s535"]
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
        }
    });
    Methods.iniIconButton(".btnRemove", "trash");
}

Test.uiRefreshLogsGrid = function() {
    var grid = $("#div" + this.className + "GridLogs").data('kendoGrid');

    var columns = grid.columns;
    var items = grid.dataSource.data();

    Test.uiReloadLogsGrid(items, columns);
}

Test.uiReloadLogsGrid = function(data, columns) {
    var thisClass = this;
    var grid = $("#div" + this.className + "GridLogs").data('kendoGrid');
    grid.destroy();
    $("#div" + this.className + "GridLogsContainer").html("<div id='div" + this.className + "GridLogs' class='grid'></div>");

    var dataSource = new kendo.data.DataSource({
        data: data,
        schema: {
            model: {
                fields: Test.logsGridSchemaFields
            }
        }
    });

    $("#div" + thisClass.className + "GridLogs").kendoGrid({
        dataBound: function(e) {
            Methods.iniTooltips();
        },
        dataSource: dataSource,
        columns: columns,
        toolbar: [
            {
                name: "clear",
                template: '<button class="btnRemove" onclick="Test.uiClearLogs()">' + dictionary["s765"] + '</button>'
            }
        ],
        editable: false,
        scrollable: true,
        resizable: true,
        sortable: true,
        columnMenu: {
            sortable: false,
            columns: true,
            messages: {
                filter: dictionary["s341"],
                columns: dictionary["s533"],
                sortAscending: dictionary["s534"],
                sortDescending: dictionary["s535"]
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
        }
    });
    Methods.iniIconButton(".btnRemove", "trash");
}

Test.uiClearLogs = function() {
    var thisClass = this;
    Methods.confirm(dictionary["s368"], dictionary["s367"], function() {
        var grid = $("#div" + thisClass.className + "GridLogs").data('kendoGrid');

        var columns = grid.columns;
        var items = [];

        Test.uiReloadLogsGrid(items, columns);
    });
}

Test.crudLogsDeleted = [];
Test.uiRemoveLog = function(obj) {
    var thisClass = this;
    Methods.confirm(dictionary["s761"], dictionary["s762"], function() {
        var grid = $("#div" + thisClass.className + "GridLogs").data('kendoGrid');
        var index = obj.closest('tr')[0].sectionRowIndex;
        var item = grid.dataItem(grid.tbody.find("tr:eq(" + index + ")"));

        if (item.id != "") {
            Test.crudUpdate(Test.crudLogsDeleted, item.id);
        }

        grid.removeRow(grid.tbody.find("tr:eq(" + index + ")"));
    });
}

Test.getSerializedCrudDeleted = function(collection) {
    switch (collection) {
        case "logs":
            {
                var grid = $("#div" + this.className + "GridLogs").data('kendoGrid');
                if (grid != null) {
                    var data = grid.dataSource.data();
                    if (data.length == 0)
                        return "*";
                }
                return $.toJSON(Table.crudLogsDeleted);
                break;
            }
    }
}