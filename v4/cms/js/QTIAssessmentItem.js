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

function QTIAssessmentItem() {
}
;
OModule.inheritance(QTIAssessmentItem);

QTIAssessmentItem.className = "QTIAssessmentItem";

QTIAssessmentItem.onAfterEdit = function()
{
};

QTIAssessmentItem.onAfterSave = function(isNewObject)
{
    Test.uiQTIAssessmentItemsChanged();
};

QTIAssessmentItem.onAfterDelete = function() {
    Test.uiQTIAssessmentItemsChanged();
}

QTIAssessmentItem.onAfterImport = function() {
    Test.uiQTIAssessmentItemsChanged();
}

QTIAssessmentItem.onAfterAdd = function() {
    if (QTIAssessmentItem.formCodeMirror != null)
        QTIAssessmentItem.formCodeMirror.refresh();
}

QTIAssessmentItem.formCodeMirror = null;
QTIAssessmentItem.getAddSaveObject = function()
{
    return {
        oid: this.currentID,
        class_name: this.className,
        name: $("#form" + this.className + "InputName").val(),
        XML: $("#form" + this.className + "TextareaXML").val()
    };
};

QTIAssessmentItem.getFullSaveObject = function(isNew) {
    if (isNew == null) {
        isNew = false;
    }

    var obj = this.getAddSaveObject();
    obj["description"] = $("#form" + this.className + "TextareaDescription").val();
    return obj;
}

QTIAssessmentItem.uiSaveValidate = function(ignoreOnBefore, isNew) {
    var thisClass = this;
    
    if (!this.checkRequiredFields([
        $("#form" + this.className + "InputName").val()
    ])) {
        Methods.alert(dictionary["s415"], "alert");
        return false;
    }

    $.post("query/check_module_unique_fields.php", {
        "class_name": this.className,
        "oid": this.currentID,
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

QTIAssessmentItem.uiRevalidate = function() {
    var thisClass = this;
    var xml = $("#form" + this.className + "TextareaXML").val();
    Methods.uiBlock("#div" + thisClass.className + "Validation");
    $.post("query/QTIAssessmentItem_revalidate.php", {
        xml: xml
    }, function(data) {
        Methods.uiUnblock("#div" + thisClass.className + "Validation");
        if (data.result == 0) {
            $("#div" + thisClass.className + "Validation").removeClass("ui-state-error");
            $("#div" + thisClass.className + "Validation").addClass("ui-state-highlight");
        } else {
            $("#div" + thisClass.className + "Validation").removeClass("ui-state-highlight");
            $("#div" + thisClass.className + "Validation").addClass("ui-state-error");
        }
        switch (data.result) {
            case OModule.queryResults.notLoggedIn:
                {
                    thisClass.onNotLoggedIn(dictionary["s633"]);
                    break;
                }
            case OModule.queryResults.OK:
                {
                    $("#div" + thisClass.className + "Validation").html("<b>" + dictionary["s470"] + "</b>");
                    break;
                }
        }
        if (data.result > 0) {
            $("#div" + thisClass.className + "Validation").html("<b>" + dictionary["s471"] + "</b><br/>" + dictionary["s472"] + "<b>");
            switch (data.result) {
                case 1:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["s475"]);
                        break;
                    }
                case 2:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["s476"]);
                        break;
                    }
                case 4:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["s478"]);
                        break;
                    }
                case 3:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["s477"]);
                        break;
                    }
                case 5:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["479"]);
                        break;
                    }
                case 6:
                    {
                        $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + dictionary["s480"]);
                        break;
                    }
            }
            $("#div" + thisClass.className + "Validation").html($("#div" + thisClass.className + "Validation").html() + "</b>, " + dictionary["s473"] + "<b>" + data.section + "</b>, " + dictionary["s474"] + "<b>" + data.target + "</b>");
        }
    }, "json");
}