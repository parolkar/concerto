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

test = null;

function Test() {
}
;
OModule.inheritance(Test);

Test.sectionTypes = {
    RCode: 1,
    loadTemplate: 2,
    goTo: 3,
    ifStatement: 4,
    setVariable: 5,
    start: 6,
    end: 7,
    tableModification: 8,
    custom: 9,
    loop: 10,
    test: 11,
    lowerLevelRCode: 12,
    QTIInitialization: 13,
    QTIResponseProcessing: 14,
    forLoop: 15,
    whileLoop: 16
};

Test.className = "Test";

Test.onAfterEdit = function()
{
    if (Test.currentID == 0)
        Test.resetCounter();
    Test.allExpanded = false;
    Test.assignmentsShown = false;
};

Test.onAfterImport = function() {
    CustomSection.uiList();
    Template.uiList();
    Table.uiList();
    QTIAssessmentItem.uiList();

    Test.uiTestsChanged();
    Test.uiCustomSectionsChanged();
    Test.uiTablesChanged();
    Test.uiTemplatesChanged();
    Test.uiQTIAssessmentItemsChanged();
}

Test.onAfterAdd = function() {
}

Test.onAfterSave = function()
{
    //Test.uiTestsChanged();
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
        open: $("#form" + this.className + "CheckboxOpen").is(":checked") ? 1 : 0,
        Sharing_id: $("#form" + this.className + "SelectSharing").val()
    };
};

Test.getFullSaveObject = function() {
    var obj = this.getAddSaveObject();
    obj["protected"] = Test.getSerializedProtected();
    obj["sections"] = Test.getSerializedSections();
    obj["parameters"] = Test.getSerializedParameterVariables();
    obj["returns"] = Test.getSerializedReturnVariables();
    obj["description"] = $("#form" + this.className + "TextareaDescription").val();
    obj["loader_Template_id"] = $("#selectLoaderTemplate").val();
    if ($("#form" + this.className + "SelectOwner").length == 1)
        obj["Owner_id"] = $("#form" + this.className + "SelectOwner").val();
    return obj;
}

Test.uiSaveValidate = function(ignoreOnBefore, isNew) {
    if (!this.checkRequiredFields([
        $("#form" + this.className + "InputName").val()
    ])) {
        Methods.alert(dictionary["s415"], "alert");
        return false;
    }
    Test.uiSaveValidated(ignoreOnBefore, isNew);
}

Test.uiAddNewRelatedObject = function(type) {
    switch (type) {
        //templates
        case Test.sectionTypes.loadTemplate:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-templates");
                Template.uiAdd();
                break;
            }
            //tables
        case Test.sectionTypes.tableModification:
        case Test.sectionTypes.setVariable:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-tables");
                Table.uiAdd();
                break;
            }
            //custom sections
        case Test.sectionTypes.custom:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-customSections");
                CustomSection.uiAdd();
                break;
            }
            //tests
        case Test.sectionTypes.test:
            {
                Methods.confirmUnsavedLost(function() {
                    Test.uiAdd();
                }, [dictionary["s88"]]);
                break;
            }
            //QTI
        case Test.sectionTypes.QTIInitialization:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-QTI");
                QTIAssessmentItem.uiAdd();
                break;
            }
    }
}

Test.uiGoToRelatedObject = function(type, oid) {
    if (oid == 0)
        return;
    switch (type) {
        //templates
        case Test.sectionTypes.loadTemplate:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-templates");
                Template.uiEdit(oid);
                break;
            }
            //tables
        case Test.sectionTypes.tableModification:
        case Test.sectionTypes.setVariable:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-tables");
                Table.uiEdit(oid);
                break;
            }
            //custom sections
        case Test.sectionTypes.custom:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-customSections");
                CustomSection.uiEdit(oid);
                break;
            }
            //tests
        case Test.sectionTypes.test:
            {
                Methods.confirmUnsavedLost(function() {
                    Test.uiEdit(oid);
                }, [dictionary["s88"]]);
                break;
            }
            //QTI
        case Test.sectionTypes.QTIInitialization:
            {
                $("#tnd_mainMenu").tabs("select", "#tnd_mainMenu-QTI");
                QTIAssessmentItem.uiEdit(oid);
                break;
            }
    }
}

Test.isTestDirty = false;
Test.convertToLowerLevel = function(counter) {
    if (Test.isTestDirty) {
        Methods.alert(dictionary["s448"], "alert", dictionary["s449"]);
        return;
    }

    $.post("query/get_section_code.php", {
        oid: Test.currentID,
        counter: counter
    }, function(data) {
        switch (data.result) {
            case 0:
                {
                    Methods.confirm(dictionary["s450"], dictionary["s449"], function() {
                        $("#divSection_" + counter).attr("sectype", Test.sectionTypes.lowerLevelRCode);
                        $("#divSection_" + counter).attr("csid", 0);
                        Test.uiRefreshSectionContent(Test.sectionTypes.lowerLevelRCode, counter, [data.code]);
                    });
                    break;
                }
            case -1:
                {
                    Methods.alert(dictionary["s278"], "alert", dictionary["s449"]);
                    location.reload();
                    break;
                }
            case -2:
                {
                    Methods.alert(dictionary["s81"], "alert", dictionary["s449"]);
                    break;
                }
        }
    }, "json");
}

Test.duplicateSection = function(counter) {
    var obj = $("#divSection_" + counter);
    var end = false;
    if ($("#chkEndSection_" + counter).length > 0) {
        end = $("#chkEndSection_" + counter).is(":checked");
    }
    Test.uiWriteSection(obj.attr("sectype"), obj.attr("secparent"), Test.getCounter(), Test.getSectionValues(Test.sectionDivToObject(obj)), 0, true, obj.attr("csid"), counter, end);
}

Test.sectionCounter = 0;
Test.getCounter = function() {
    Test.sectionCounter++;
    return Test.sectionCounter;
}

Test.resetCounter = function() {
    Test.sectionCounter = 0;
}

Test.setCounter = function(value) {
    Test.sectionCounter = value;
}

Test.getSectionValues = function(section) {
    switch (section.type) {
        case Test.sectionTypes.RCode:
            {
                return [$(".divSection[seccounter=" + section.counter + "] #textareaCodeMirror_" + section.counter).val()];
            }
        case Test.sectionTypes.lowerLevelRCode:
            {
                return [$(".divSection[seccounter=" + section.counter + "] #textareaCodeMirror_" + section.counter).val()];
            }
        case Test.sectionTypes.goTo:
            {
                return [$(".divSection[seccounter=" + section.counter + "] select").val()];
            }
        case Test.sectionTypes.ifStatement:
            {
                var values = new Array();
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.test:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] #selectTest_" + section.counter).val(),
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").length,
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").length,
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.custom:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] #selectCustomSection_" + section.counter).val(),
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").length,
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").length,
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.loadTemplate:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] #selectTemplate_" + section.counter).val(),
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").length,
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").length,
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_params").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.setVariable:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_column").length - 1,
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_link").length,
                    $(".divSection[seccounter=" + section.counter + "] .radioSetVarType_" + section.counter + ":checked").val(),
                    $(".divSection[seccounter=" + section.counter + "] #textareaCodeMirror_" + section.counter).val()
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.tableModification:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] .radioTableModType_" + section.counter + ":checked").val(), //type
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_link").length, //where count
                    $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_set").length //set count
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.loop:
            {
                var values = new Array();
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.QTIInitialization:
            {
                var values = [
                    $(".divSection[seccounter=" + section.counter + "] #selectQTIAssessmentItem_" + section.counter).val(),
                ];
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter + "_rets").each(function() {
                    values.push($(this).attr("referenced"));
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.QTIResponseProcessing:
            {
                return [$(".divSection[seccounter=" + section.counter + "] select").val()];
            }
        case Test.sectionTypes.whileLoop:
            {
                var values = new Array();
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        case Test.sectionTypes.forLoop:
            {
                var values = new Array();
                $(".divSection[seccounter=" + section.counter + "] .controlValue" + section.counter).each(function() {
                    values.push($(this).val());
                });
                return values;
            }
        default:
            {
                return [];
            }
    }
}

Test.getSerializedSections = function() {
    var s = Test.getSections();
    var result = new Array();
    s.each(function() {
        var section = Test.sectionDivToObject($(this));
        var values = Test.getSectionValues(section);
        var v = {};
        for (var i = 0; i < values.length; i++) {
            v["v" + i] = values[i];
        }
        section["value"] = v;
        result.push(section);
    });
    result = $.toJSON(result);
    return result;
}

Test.getSectionTypeName = function(type) {
    switch (type) {
        case Test.sectionTypes.RCode:
            {
                return dictionary["s49"];
            }
        case Test.sectionTypes.lowerLevelRCode:
            {
                return dictionary["s445"];
            }
        case Test.sectionTypes.loadTemplate:
            {
                return dictionary["s50"];
            }
        case Test.sectionTypes.goTo:
            {
                return dictionary["s51"];
            }
        case Test.sectionTypes.ifStatement:
            {
                return dictionary["s52"];
            }
        case Test.sectionTypes.setVariable:
            {
                return dictionary["s53"];
            }
        case Test.sectionTypes.start:
            {
                return dictionary["s54"];
            }
        case Test.sectionTypes.end:
            {
                return dictionary["s55"];
            }
        case Test.sectionTypes.tableModification:
            {
                return dictionary["s56"];
            }
        case Test.sectionTypes.custom:
            {
                return dictionary["s57"];
            }
        case Test.sectionTypes.loop:
            {
                return dictionary["s391"];
            }
        case Test.sectionTypes.test:
            {
                return dictionary["s392"];
            }
        case Test.sectionTypes.QTIInitialization:
            {
                return dictionary["s481"];
            }
        case Test.sectionTypes.QTIResponseProcessing:
            {
                return dictionary["s482"];
            }
        case Test.sectionTypes.forLoop:
            {
                return dictionary["s622"];
            }
        case Test.sectionTypes.whileLoop:
            {
                return dictionary["s623"];
            }
    }
}

Test.contentsToRefresh = 0;
Test.uiRefreshSectionContent = function(type, counter, value, oid, end) {
    if (end == null) {
        if ($("#chkEndSection_" + counter).is(":checked"))
            end = 1;
        else
            end = 0;
    }
    Test.contentsToRefresh++;
    if (oid == null)
        oid = 0;
    switch (type) {
        case Test.sectionTypes.RCode:
            {
                if (value == null)
                    value = [""];
                break;
            }
        case Test.sectionTypes.lowerLevelRCode:
            {
                if (value == null)
                    value = [""];
                break;
            }
        case Test.sectionTypes.goTo:
            {
                if (value == null)
                    value = [0];
                break;
            }
        case Test.sectionTypes.loadTemplate:
            {
                if (value == null)
                    value = [0, 0, 0];
                break;
            }
        case Test.sectionTypes.ifStatement:
            {
                if (value == null)
                    value = ["", 0, ""];
                break;
            }
        case Test.sectionTypes.setVariable:
            {
                if (value == null)
                    value = [0, 0, 1, "", "", 0, 0];
                break;
            }
        case Test.sectionTypes.tableModification:
            {
                if (value == null)
                    value = [0, 0, 0];
                break;
            }
        case Test.sectionTypes.custom:
            {
                if (value == null)
                    value = [0, 0, 0];
                break;
            }
        case Test.sectionTypes.loop:
            {
                if (value == null)
                    value = [0, "", "==", "", 0, 1];
                break;
            }
        case Test.sectionTypes.test:
            {
                if (value == null)
                    value = [0, 0, 0];
                break;
            }
        case Test.sectionTypes.QTIInitialization:
            {
                if (value == null)
                    value = [0];
                break;
            }
        case Test.sectionTypes.QTIResponseProcessing:
            {
                if (value == null)
                    value = [0];
                break;
            }
        case Test.sectionTypes.whileLoop:
            {
                if (value == null)
                    value = ["", 0, ""];
            }
        case Test.sectionTypes.forLoop:
            {
                if (value == null)
                    value = ["", ""];
            }
    }

    var detail = 0;
    if ($("#divSection_" + counter).find(".divSectionDetail").is(":visible"))
        detail = 1;

    $("#divSection_" + counter).mask(dictionary["s319"]);
    $.post("view/section_content_" + type + ".php", {
        type: type,
        counter: counter,
        value: value,
        oid: oid,
        detail: detail,
        end: end ? 1 : 0
    }, function(data) {
        $("#divSection_" + counter).unmask();
        $("#divSection_" + counter).children(".divSectionContent").html(data);
        $("#divSection_" + counter).find(".divSectionSummary").attr("onmouseover", "Test.uiToggleHover(" + counter + ",true);").attr("onmouseout", "Test.uiToggleHover(" + counter + ",false);");
        switch (parseInt(type)) {
            case Test.sectionTypes.RCode:
                {
                    var cm = Methods.iniCodeMirror("textareaCodeMirror_" + counter, "r", false);
                    Test.codeMirrors.push(cm);
                    break;
                }
            case Test.sectionTypes.lowerLevelRCode:
                {
                    var cm = Methods.iniCodeMirror("textareaCodeMirror_" + counter, "r", false);
                    Test.codeMirrors.push(cm);
                    break;
                }
            case Test.sectionTypes.setVariable:
                {
                    var cm = Methods.iniCodeMirror("textareaCodeMirror_" + counter, "r", false);
                    Test.codeMirrors.push(cm);
                    break;
                }
            case Test.sectionTypes.goTo:
                {
                    var sections = Test.getSections();
                    $("#selectGoTo_" + counter).append("<option value='0'>&lt;" + dictionary["s58"] + "&gt;</option>");
                    sections.each(function() {
                        var s = Test.sectionDivToObject($(this));
                        if (s.counter == counter)
                            return;
                        $("#selectGoTo_" + counter).append("<option value='" + s.counter + "' " + (value[0] == s.counter ? "selected" : "") + ">" + s.counter + ": " + Test.getSectionTypeName(s.type) + "</option>");
                    });
                    break;
                }
            case Test.sectionTypes.QTIResponseProcessing:
                {
                    var sections = Test.getSections();
                    $("#selectQTI_" + counter).append("<option value='0'>&lt;" + dictionary["s58"] + "&gt;</option>");
                    sections.each(function() {
                        var s = Test.sectionDivToObject($(this));
                        if (s.type != Test.sectionTypes.QTIInitialization)
                            return;
                        $("#selectQTI_" + counter).append("<option value='" + s.counter + "' " + (value[0] == s.counter ? "selected" : "") + ">" + s.counter + ": " + Test.getSectionTypeName(s.type) + "</option>");
                    });
                    break;
                }
        }
        Test.contentsToRefresh--;
        if (Test.contentsToRefresh <= 0) {
            Test.uiSetVarNameChanged();
            Methods.iniIconButton(".btnAddSection", "plus");
            Methods.iniTooltips();
            Test.uiRefreshCodeMirrors();
        }
        Methods.incrementProgress();
        if (Test.assignmentsShown) {
            $("#divSection_" + counter + " .tdVarPointer").show(0);
        }
    });
};

Test.uiToggleHover = function(counter, show) {
    var obj = $("#divSection_" + counter);

    if (show) {
        $(".divSection").each(function() {
            if ($(this).attr("id") != obj.attr("id")) {
                $(this).removeClass("ui-state-highlight");
            }
        })
        obj.addClass("ui-state-highlight");

        if (obj.attr("sectype") == Test.sectionTypes.goTo) {
            $("#divSection_" + obj.find("select").val()).addClass("ui-state-highlight");
        }
    }
}

Test.uiToggleDetails = function(counter) {
    if (!$("#divSection_" + counter).children(".divSectionContent").children(".divSectionDetail").is(":visible")) {
        $("#divSection_" + counter).children(".divSectionContent").children(".divSectionDetail").show(0);
        $("#spanExpandDetail_" + counter).removeClass("ui-icon-folder-collapsed");
        $("#spanExpandDetail_" + counter).addClass("ui-icon-folder-open");
        Test.uiRefreshCodeMirrors();
    } else {
        $("#divSection_" + counter).children(".divSectionContent").children(".divSectionDetail").hide(0);
        $("#spanExpandDetail_" + counter).addClass("ui-icon-folder-collapsed");
        $("#spanExpandDetail_" + counter).removeClass("ui-icon-folder-open");
    }
}

Test.allExpanded = false;
Test.uiToggleAll = function() {
    if (Test.allExpanded) {
        $(".divSectionDetail").hide(0);
        $(".spanExpandDetail").addClass("ui-icon-folder-collapsed");
        $(".spanExpandDetail").removeClass("ui-icon-folder-open");
    } else {
        $(".divSectionDetail").show(0);
        $(".spanExpandDetail").removeClass("ui-icon-folder-collapsed");
        $(".spanExpandDetail").addClass("ui-icon-folder-open");
    }
    
    Test.allExpanded = !Test.allExpanded;
    if (Test.allExpanded) {
        Test.uiRefreshCodeMirrors();
        Methods.iniIconButton(".btnLogicToggleAll", "folder-open");
        $(".btnLogicToggleAll").button("option", "label", dictionary["s402"]);
    } else {
        Methods.iniIconButton(".btnLogicToggleAll", "folder-collapsed");
        $(".btnLogicToggleAll").button("option", "label", dictionary["s401"]);
    }
}

Test.assignmentsShown = false;
Test.uiToggleVarAssignments = function() {
    if (Test.assignmentsShown) {
        $(".tdVarPointer").hide(0);
    } else {
        $(".tdVarPointer").show(0);
    }
    Test.assignmentsShown = !Test.assignmentsShown;
    if (Test.assignmentsShown) {
        Methods.iniIconButton(".btnLogicToggleVarAssignment", "folder-open");
        $(".btnLogicToggleVarAssignment").button("option", "label", dictionary["s455"]);
    } else {
        Methods.iniIconButton(".btnLogicToggleVarAssignment", "folder-collapsed");
        $(".btnLogicToggleVarAssignment").button("option", "label", dictionary["s454"]);
    }
}

//Test.uiWriteSection=function(type,container,parent,counter,value,oid,prepend,refresh,csid,after,end){
Test.uiWriteSection = function(type, parent, counter, value, oid, refresh, csid, after, end) {
    if (end == null)
        end = false;
    if (refresh == null)
        refresh = true;
    if (counter == null)
        counter = Test.getCounter();
    if (csid == null)
        csid = 0;
    if (csid != 0 && value == null)
        value = [csid, 0, 0];
    var sortable = true;
    if (type == Test.sectionTypes.start || type == Test.sectionTypes.end)
        sortable = false;

    var container = null;
    if (parent == null || parent == 0) {
        container = $("#divTestLogic");
    } else
    {
        container = $("#divSection_" + parent).children(".divSectionContainer");
    }

    var section = $("<div />", {
        "class": "divSection smallMargin " + (!sortable ? "notSortable" : "sortable"),
        id: "divSection_" + counter,
        csid: csid,
        align: "left",
        sectype: type,
        seccounter: counter,
        secparent: parent,
        style: "z-index:20; border:1px dotted grey; border-right:1px dotted transparent; margin:5px; margin-right:0px;"
    });

    var sectionContainer = '<div class="divSectionBracket"><table class="noSpace fullWidth"><tr><td class="noSpace">{</td><td class="noSpace" align="left"><button class="btnAddSection noWrap" onclick="Test.uiAddLogicSection(' + counter + ',0)">' + dictionary["s620"] + '</button></td></tr></table></div><div class="divSectionContainer"></div><div class="divSectionBracket"><table class="noSpace"><tr><td class="noSpace">}</td></tr></table></div>';

    section.html('<div class="divSectionContent"></div>' + (type == Test.sectionTypes.ifStatement || type == Test.sectionTypes.loop || type == Test.sectionTypes.forLoop || type == Test.sectionTypes.whileLoop ? sectionContainer : ""));

    if (after != null && after != 0) {
        $(".divSection[seccounter='" + after + "']").after(section);
    }
    else {
        if (after == null) {
            container.append(section);
        }
        if (after == 0) {
            container.prepend(section);
        }
    }

    if (refresh)
        Test.uiRefreshSectionContent(type, counter, value, oid, end);

    Test.uiCheckEmptyLogic();

    Test.uiSectionChanged();
    Methods.iniTooltips();

    if (type == Test.sectionTypes.ifStatement || type == Test.sectionTypes.loop) {
        $("#divSection_" + counter + " .divSectionContainer").sortable({
            items: "> div.sortable",
            handle: ".sortableHandle",
            //tolerance:"pointer",
            opacity: 0.5,
            axis: "y",
            forcePlaceholderSize: true,
            placeholder: "ui-state-highlight"
        });
    }
};

Test.uiAddLogicSection = function(parent, after) {
    var type = $("#formTestSelectSectionType");

    if (after != null && after != 0) {
        parent = $("#divSection_" + after).attr("secparent");
    }

    $("#divTestDialog").dialog({
        modal: true,
        title: dictionary["s61"],
        resizable: false,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            //$('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons: [
            {
                text: dictionary["s23"],
                click: function() {
                    $(this).dialog("close");
                }
            },
            {
                text: dictionary["s37"],
                click: function() {
                    var vls = type.val().split(":");
                    var t = 0;
                    var csid = 0;
                    if (vls.length > 1) {
                        t = parseInt(vls[0]);
                        csid = vls[1];
                    }
                    else
                        t = parseInt(vls);

                    Test.uiWriteSection(t, parent, null, null, null, null, csid, after);
                    $(this).dialog("close");
                }
            }
        ]
    });
};

Test.uiRemoveSection = function(counter) {
    Methods.confirm(dictionary["s62"], dictionary["s63"], function() {
        $("#divSection_" + counter).remove();
        Test.uiSectionChanged();
        Test.uiCheckEmptyLogic();
        Test.uiSetVarNameChanged();
        Test.uiRefreshCodeMirrors();
    })
}

Test.getSections = function() {
    return $("#divTestLogic").find(".divSection");
}

Test.uiCheckEmptyLogic = function() {
    if (Test.getSections().length > 2)
    {
        $("#divTestEmptyLogic").hide(0);
    }
    else
        $("#divTestEmptyLogic").show(0);
};

Test.sectionDivToObject = function(div) {
    var counter = parseInt(div.attr("seccounter"));
    var type = parseInt(div.attr("sectype"));
    var parent = parseInt(div.attr("secparent"));
    var end = 0;
    if (div.find(".chkEndSection").length > 0 && div.find(".chkEndSection").is(":checked"))
        end = 1;
    return {
        counter: counter,
        type: type,
        parent: parent,
        end: end
    };
}

Test.uiTemplatesChanged = function() {
    Test.uiRefreshLoader($("#selectLoaderTemplate").val());
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.loadTemplate) {
            var vals = Test.getSectionValues(s);
            Test.uiRefreshSectionContent(s.type, s.counter, vals);
        }
    });
}

Test.uiCustomSectionsChanged = function() {
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.custom) {
            var value = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + s.counter)));
            Test.uiRefreshSectionContent(s.type, s.counter, value);
        }
    });
    Test.uiRefreshAddSectionDialog();
}

Test.uiTestsChanged = function() {
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.test) {
            var value = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + s.counter)));
            Test.uiRefreshSectionContent(s.type, s.counter, value);
        }
    });
    Test.uiRefreshAddSectionDialog();
}

Test.uiRefreshAddSectionDialog = function() {
    $.post("view/Test_section_dialog.php", {
        oid: this.currentID
    }, function(data) {
        $("#divTestDialog").html(data);
    })
}

Test.listenToSectionChanged = true;
Test.uiSectionChanged = function() {
    if (!Test.listenToSectionChanged)
        return;
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.goTo || s.type == Test.sectionTypes.QTIResponseProcessing) {
            var value = $(this).find("select").val();
            Test.uiRefreshSectionContent(s.type, s.counter, [value]);
        }
    });
}

Test.uiQTIAssessmentItemsChanged = function() {
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.QTIInitialization) {
            var value = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + s.counter)));
            Test.uiRefreshSectionContent(s.type, s.counter, value);
        }
    });
}

Test.uiAddTableModSet = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[2]++;
    vals.splice(vals[2] * 2 + 4, 0, 0);
    vals.splice(vals[2] * 2 + 4, 0, "");
    Test.uiRefreshSectionContent(Test.sectionTypes.tableModification, counter, vals);
}

Test.uiRemoveTableModSet = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[2]--;
    vals.splice(4 + index * 2, 2);
    Test.uiRefreshSectionContent(Test.sectionTypes.tableModification, counter, vals);
}

Test.uiAddTableModWhere = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[1]++;
    Test.uiRefreshSectionContent(Test.sectionTypes.tableModification, counter, vals);
}

Test.uiRemoveTableModWhere = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals.splice(4 + vals[2] * 2 + index * 4, 4);
    vals[1]--;
    Test.uiRefreshSectionContent(Test.sectionTypes.tableModification, counter, vals);
}

Test.uiAddIfCond = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals = vals.concat([0, "", "!=", ""]);
    Test.uiRefreshSectionContent(Test.sectionTypes.ifStatement, counter, vals);
}

Test.uiAddWhileCond = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals = vals.concat([0, "", "!=", ""]);
    Test.uiRefreshSectionContent(Test.sectionTypes.whileLoop, counter, vals);
}

Test.uiRemoveIfCond = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    index = 3 + 4 * index;
    vals.splice(index, 4);
    Test.uiRefreshSectionContent(Test.sectionTypes.ifStatement, counter, vals);
}

Test.uiRemoveWhileCond = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    index = 3 + 4 * index;
    vals.splice(index, 4);
    Test.uiRefreshSectionContent(Test.sectionTypes.whileLoop, counter, vals);
}

Test.uiAddSetVarColumn = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[0]++;
    vals.splice(vals[0] + 6, 0, 0);
    Test.uiRefreshSectionContent(Test.sectionTypes.setVariable, counter, vals);
}
Test.uiRemoveSetVarColumn = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals.splice(7 + index, 1);
    vals[0]--;
    Test.uiRefreshSectionContent(Test.sectionTypes.setVariable, counter, vals);
}

Test.uiAddSetVarCondition = function(counter) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[1]++;
    Test.uiRefreshSectionContent(Test.sectionTypes.setVariable, counter, vals);
}

Test.uiRemoveSetVarCondition = function(counter, index) {
    var vals = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter)));
    vals[1]--;
    vals.splice(7 + vals[0] + index * 4, 4);
    Test.uiRefreshSectionContent(Test.sectionTypes.setVariable, counter, vals);
}

Test.uiTablesChanged = function() {
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.setVariable || s.type == Test.sectionTypes.tableModification) {
            var value = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + s.counter)));
            Test.uiRefreshSectionContent(s.type, s.counter, value);
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

Test.uiSetVarNameChanged = function(obj) {
    if (obj != null) {
        var oldValue = obj.val();
        if (!Test.variableValidation(oldValue)) {
            var newValue = Test.convertVariable(oldValue);
            obj.val(newValue);
            Methods.alert(dictionary["s1"].format(oldValue, newValue), "info", dictionary["s2"]);
        }
    }

    Test.uiRefreshComboBoxes();

    var vars = Test.getSerializedLogicVars();

    $.post("view/Test_vars.php", {
        vars: vars
    }, function(data) {
        $("#divTestVarsDialog").html(data);
    });
}

Test.getLogicVars = function() {
    var vars = new Array();

    var svars = Test.getSetVars();
    vars = svars;

    var rvars = Test.getReturnVars();
    for (var i = 0; i < rvars.length; i++) {
        var exists = false;
        for (var a = 0; a < vars.length; a++) {
            if (rvars[i].name == vars[a].name) {
                vars[a].section = vars[a].section.concat(rvars[i].section);
                vars[a].type = vars[a].type.concat(rvars[i].type);
                exists = true;
                break;
            }
        }
        if (!exists) {
            vars.push(rvars[i]);
        }
    }
    ;

    var pvars = Test.getParameterVars();
    for (var i = 0; i < pvars.length; i++) {
        var exists = false;
        for (var a = 0; a < vars.length; a++) {
            if (pvars[i].name == vars[a].name) {
                vars[a].section = vars[a].section.concat(pvars[i].section);
                vars[a].type = vars[a].type.concat(pvars[i].type);
                exists = true;
                break;
            }
        }
        if (!exists) {
            vars.push(pvars[i]);
        }
    }
    ;

    vars.sort(Test.comparerVars);
    return vars;
};

Test.getSerializedLogicVars = function() {
    var vars = Test.getLogicVars();
    var result = new Array();
    for (var i = 0; i < vars.length; i++) {
        for (var a = 0; a < vars[i].section.length; a++) {
            vars[i].section[a]["name"] = Test.getSectionTypeName(vars[i].section[a].type);
        }
        result.push($.toJSON(vars[i]));
    }
    return result;
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

Test.getSetVars = function() {
    var vars = new Array();
    var sections = Test.getSections();
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        if (s.type == Test.sectionTypes.setVariable) {
            var value = Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + s.counter)));
            if (value[4] != "") {
                var v = {
                    name: value[4],
                    section: [s],
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
            }
        }
    });
    return vars;
}

Test.uiRefreshComboBoxes = function() {
    var sections = Test.getSections();
    var vars = [];
    var vs = Test.getLogicVars();
    for (var i = 0; i < vs.length; i++) {
        vars.push(vs[i].name);
    }
    sections.each(function() {
        var s = Test.sectionDivToObject($(this));
        $(this).find(".comboboxVars").each(function() {
            var val = $(this).val();
            var source = vars;
            $(this).autocomplete({
                source: source,
                minLength: 0
            }).click(function() {
                $(this).autocomplete("search", '');
            });
            $(this).val(val);
        });
    });
}

Test.changeSetVarType = function(counter) {
    Test.uiRefreshSectionContent(Test.sectionTypes.setVariable, counter, Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter))));
};

Test.uiRefreshSection = function(counter, type) {
    Test.uiRefreshSectionContent(type, counter, Test.getSectionValues(Test.sectionDivToObject($('#divSection_' + counter))));
}

Test.uiShowVarsDialog = function() {
    $("#divTestVarsDialog").dialog({
        title: dictionary["s65"],
        width: 600,
        close: function() {
            $(this).dialog("destroy");
        },
        buttons: [
            {
                text: dictionary["s64"],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
};

Test.comparerVars = function(a, b) {
    if (a == null || b == null)
        return 0;
    if (a != null && !a.name)
        return 0;
    if (b != null && !b.name)
        return 0;
    if (a.name.toString() < b.name.toString())
        return -1;
    if (a.name.toString() > b.name.toString())
        return 1;
    return 0;
}

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

Test.uiRefreshDebugCodeMirrors = function() {
    for (var i = 0; i < Test.debugCodeMirrors.length; i++) {
        try {
            Test.debugCodeMirrors[i].refresh();
        }
        catch (err) {

        }
    }
}

Test.uiIniDebug = function() {
    try {
        $("#divTestDebugDialog").dialog("close");
    } catch (err) {
    }

    $("#divTestDebugDialog").dialog({
        title: dictionary["s284"],
        width: 800,
        height: 600,
        resizeStop: function() {
            $("#divTestDebugAccordion").accordion("resize");
        },
        open: function() {
            Test.resetDebug();
            $("#divTestDebugAccordion").accordion({
                fillSpace: true,
                animated: false,
                change: function(event, ui) {
                    Test.uiRefreshDebugCodeMirrors();
                    $("#divTestDebugAccordion").accordion("resize");
                }
            });
        },
        beforeClose: function() {
            Test.stopRunTimeDebug();
        },
        buttons: [
            {
                text: dictionary["s323"],
                click: function() {
                    Test.startDebug();
                    $("#divTestDebugDialog").dialog("option", "buttons", [
                        {
                            text: dictionary["s324"],
                            click: function() {
                                Test.restartDebug();
                            }
                        }
                    ]);
                }
            }
        ]
    });
};

Test.setDebugStatus = function(data, error) {
    $("#divTestDebugDialog").dialog("option", "title", "<p style='margin:0px;' id='pTestDebugStatus'></p>");
    if (error != null && error) {
        $("#pTestDebugStatus").addClass("ui-state-error");
    } else {
        $("#pTestDebugStatus").removeClass("ui-state-error");
    }
    $("#pTestDebugStatus").html(data);
}

Test.appendDebugConsole = function(data, style, tag) {
    if (tag == null)
        tag = "p";
    if (style == null)
        style = "";
    $("#divTestDebugConsole").append("<" + tag + " class='" + style + "'>" + data + "</" + tag + ">").scrollTop($("#divTestDebugConsole")[0].scrollHeight);
}

Test.resetDebug = function() {
    Test.debugCodeMirrors = new Array();
    Test.setDebugStatus(dictionary["s285"].format(this.currentID));
    $("#divTestDebugConsole").html("");
    $("#divTestDebugTest").html("");
    Test.appendDebugConsole(dictionary["s286"].format(this.currentID), "ui-widget-header");
    Test.appendDebugConsole(dictionary["s287"], "ui-state-highlight");
    Test.appendDebugConsole(dictionary["s288"], "");
};

Test.startDebug = function() {
    Test.startSyntaxDebug();
}

Test.restartDebug = function() {
    Test.resetDebug();
    Test.startSyntaxDebug();
}

Test.debugSectionCode = {};
Test.startSyntaxDebug = function() {
    Test.debugSectionCode = new Array();

    Test.setDebugStatus(dictionary["s289"].format(this.currentID));
    Test.appendDebugConsole(dictionary["s290"], "ui-widget-header");

    Test.debugSectionSyntax(1);
}

Test.debugSectionSyntax = function(counter) {
    var thisClass = this;

    $.post("query/debug_syntax_validation.php", {
        Test_id: this.currentID,
        counter: counter
    }, function(data) {
        if (data.result == 0) {
            if (data.response.debug["return"] == 0) {
                Test.appendDebugConsole(dictionary["s291"].format(counter));
                Test.debugSectionCode["section" + counter] = data.response.debug.code;
                if (data.next_counter > 0)
                    Test.debugSectionSyntax(data.next_counter);
                else {
                    Test.setDebugStatus(dictionary["s293"].format(thisClass.currentID));
                    Test.appendDebugConsole(dictionary["s294"]);
                    Test.startRunTimeDebug();
                }
            }
            else {
                counterFailed = counter;
                Test.appendDebugConsole(dictionary["s292"].format(counter), "ui-state-error");
                Test.appendDebugConsole("<textarea id='textareaDebugSyntax_" + counter + "'>" + data.response.debug.code + "</textarea>", null, "div");
                Test.debugCodeMirrors.push(Methods.iniCodeMirror("textareaDebugSyntax_" + counter, "r", true));
                var output = data.response.debug.output.join("<br/>");
                Test.appendDebugConsole(output, "ui-state-highlight");

                Test.setDebugStatus(dictionary["s295"].format(thisClass.currentID, counterFailed), true);
                Test.appendDebugConsole(dictionary["s296"].format(counterFailed), "ui-state-error");
            }
        }
        if (data.result == -1) {
            Methods.alert(dictionary["s278"], "alert", dictionary["s274"]);
            location.reload();
        }
        if (data.result == -2) {
            Methods.alert(dictionary["s81"], "alert", dictionary["s274"]);
            $("#divTestDebugDialog").dialog("close");
        }
    }, "json");
}

Test.runTimeResponseIndex = 0;
Test.runTimeCurrentTemplateID = 0;
Test.debugCodeMirrors = new Array();
test = null;

Test.stopRunTimeDebug = function() {
    if (test != null) {
        test.stop();
        test = null;
    }
}

Test.startRunTimeDebug = function() {
    var thisClass = this;
    Test.appendDebugConsole(dictionary["s297"], "ui-widget-header");

    Test.appendDebugConsole(dictionary["s298"].format(this.currentID));
    Test.setDebugStatus(dictionary["s299"].format(this.currentID));
    Test.appendDebugConsole(dictionary["s300"]);
    Test.stopRunTimeDebug();
    test = new Concerto("#divTestDebugTest", null, null, this.currentID, "../query/", function(data) {
        Test.runTimeResponseIndex++;
        Test.appendDebugConsole(dictionary["s301"]);

        //code
        Test.appendDebugConsole("<textarea id='textareaDebugRun_" + Test.runTimeResponseIndex + "'>" + data.debug.code + "</textarea>", null, "div");
        Test.debugCodeMirrors.push(Methods.iniCodeMirror("textareaDebugRun_" + Test.runTimeResponseIndex, "r", true));

        //output
        var output = "";
        if (data.debug.output != "")
            output = data.debug.output.join("<br/>");
        Test.appendDebugConsole(output, "ui-state-highlight");

        //validation
        if (data.debug["return"] != 0) {
            Test.setDebugStatus(dictionary["s302"].format(thisClass.currentID, this.sessionID), true);
            Test.appendDebugConsole(dictionary["s303"], "ui-state-error");
        }
        else {
            Test.appendDebugConsole(dictionary["s304"]);
        }

        //HTML values
        /*
         var html = "<table><caption class='ui-widget-header'>"+dictionary["s305"]+"</caption><thead><tr><th class='ui-widget-header'>"+dictionary["s70"]+"</th><th class='ui-widget-header'>"+dictionary["s306"]+"</th></tr></thead><tbody>";
         for(var k in data["values"]){
         html+="<tr><td class='ui-widget-content'><b>"+k+"</b></td><td class='ui-widget-content'>"+data['values'][k]+"</td></tr>";
         }
         html+="</tbody></table>";
         Test.appendDebugConsole(html);
         */

        //template
        if (data.data.STATUS == Concerto.statusTypes.completed) {
            Test.setDebugStatus(dictionary["s307"].format(thisClass.currentID, this.sessionID));
            Test.appendDebugConsole(dictionary["s308"], "ui-state-highlight");
        } else {
            if (data.data.STATUS != Concerto.statusTypes.error) {
                Test.runTimeCurrentTemplateID = data.data.TEMPLATE_ID;
                Test.appendDebugConsole(dictionary["s309"].format(Test.runTimeCurrentTemplateID), "ui-widget-header");
                Test.appendDebugConsole(dictionary["s310"].format(Test.runTimeCurrentTemplateID, data.data.TIME_LIMIT == null || data.data.TIME_LIMIT == 0 ? dictionary["s311"] : data.data.TIME_LIMIT + " " + dictionary["s312"]));
                Test.appendDebugConsole(dictionary["s313"]);
                Test.setDebugStatus(dictionary["s314"].format(thisClass.currentID, this.sessionID, Test.runTimeCurrentTemplateID));
            }
        }
    }, function(btnName, vals) {
        //button clicked
        if (btnName == "NONE") {
            Test.appendDebugConsole(dictionary["s315"]);
        } else {
            Test.appendDebugConsole(dictionary["s315"].format(btnName));
        }

        //values send
        var html = "<table><caption class='ui-widget-header'>" + dictionary["s317"] + "</caption><thead><tr><th class='ui-widget-header'>" + dictionary["s70"] + "</th><th class='ui-widget-header'>" + dictionary["s306"] + "</th></tr></thead><tbody>";
        for (var i = 0; i < vals.length; i++) {
            var val = jQuery.parseJSON(vals[i]);
            html += "<tr><td class='ui-widget-content'>" + val.name + "</td><td class='ui-widget-content'>" + val.value + "</td></tr>";
        }
        html += "</tbody></table>";
        Test.appendDebugConsole(html);

        Test.setDebugStatus(dictionary["s299"].format(thisClass.currentID));
    }, true);
    test.run();
}

Test.uiAddProtectedVar = function(obj) {
    var grid = $("#div" + this.className + "GridProtectedVars").data('kendoGrid');
    grid.addRow();

    Methods.iniTooltips();
}

Test.uiRemoveProtectedVar = function(obj) {
    var thisClass = this;
    Methods.confirm(dictionary["s360"], dictionary["s361"], function() {
        var grid = $("#div" + thisClass.className + "GridProtectedVars").data('kendoGrid');
        var index = obj.closest('tr')[0].sectionRowIndex;
        grid.removeRow(grid.tbody.find("tr:eq(" + index + ")"));
    });
}

Test.getSerializedProtected = function() {
    var grid = $("#div" + this.className + "GridProtectedVars").data('kendoGrid');
    var prot = new Array();
    if (grid == null)
        return prot;
    var data = grid.dataSource.data();

    for (var i = 0; i < data.length; i++) {
        prot.push($.toJSON({
            name: data[i].name
        }));
    }
    return prot;
}

Test.uiVarNameChanged = function(obj) {
    if (obj != null) {
        var oldValue = obj.val();
        if (!Test.variableValidation(oldValue)) {
            var newValue = Test.convertVariable(oldValue);
            obj.val(newValue);
            Methods.alert(dictionary["s1"].format(oldValue, newValue), "info", dictionary["s2"]);
        }
    }

    Test.uiRefreshComboBoxes();
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

    $("#div" + Test.className + "Variables").mask(dictionary["s319"]);
    $.post("view/Test_variables.php", {
        oid: this.currentID,
        class_name: this.className,
        parameters: parameters,
        returns: returns
    }, function(data) {
        $("#div" + Test.className + "Variables").unmask();
        $("#div" + Test.className + "Variables").html(data);
    })
}

Test.uiRefreshLoader = function(oid) {

    $("#div" + Test.className + "Loader").mask(dictionary["s319"]);
    $.post("view/Test_loader.php", {
        oid: this.currentID,
        class_name: this.className,
        loader: oid
    }, function(data) {
        $("#div" + Test.className + "Loader").unmask();
        $("#div" + Test.className + "Loader").html(data);
    })
}