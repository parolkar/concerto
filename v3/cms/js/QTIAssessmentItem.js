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

function QTIAssessmentItem() { };
OModule.inheritance(QTIAssessmentItem);

QTIAssessmentItem.className="QTIAssessmentItem";

QTIAssessmentItem.onAfterEdit=function()
{
    };

QTIAssessmentItem.onAfterSave=function(isNewObject)
{
    Test.uiQTIAssessmentItemsChanged();
};

QTIAssessmentItem.onAfterDelete=function(){
    Test.uiQTIAssessmentItemsChanged();
}

QTIAssessmentItem.onAfterImport=function(){
    Test.uiQTIAssessmentItemsChanged();
}

QTIAssessmentItem.onAfterAdd=function(){
    if(QTIAssessmentItem.formCodeMirror!=null) QTIAssessmentItem.formCodeMirror.refresh();
}

QTIAssessmentItem.formCodeMirror=null;
QTIAssessmentItem.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        XML:$("#form"+this.className+"TextareaXML").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

QTIAssessmentItem.getFullSaveObject = function(){
    var obj = this.getAddSaveObject();
    obj["description"]=$("#form"+this.className+"TextareaDescription").val();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}

QTIAssessmentItem.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    QTIAssessmentItem.uiSaveValidated(ignoreOnBefore,isNew);
}

QTIAssessmentItem.uiRevalidate=function(){
    var thisClass = this;
    var xml = $("#form"+this.className+"TextareaXML").val();
    $("#div"+thisClass.className+"Validation").mask(dictionary["s319"]);
    $.post("query/QTIAssessmentItem_revalidate.php",{
        xml:xml
    },function(data){
        $("#div"+thisClass.className+"Validation").unmask();
        if(data.result==0){
            $("#div"+thisClass.className+"Validation").removeClass("ui-state-error");
            $("#div"+thisClass.className+"Validation").addClass("ui-state-highlight");
        } else {
            $("#div"+thisClass.className+"Validation").removeClass("ui-state-highlight");
            $("#div"+thisClass.className+"Validation").addClass("ui-state-error");
        }
        switch(data.result){
            case -1:{
                location.reload();
                break;
            }
            case 0:{
                $("#div"+thisClass.className+"Validation").html("<b>" + dictionary["s470"] + "</b>");
                break;
            }
        }
        if(data.result>0){
            $("#div"+thisClass.className+"Validation").html("<b>" + dictionary["s471"] + "</b><br/>"+dictionary["s472"]+"<b>");
            switch(data.result){
                case 1:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["s475"]);
                    break;
                }
                case 2:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["s476"]);
                    break;
                }
                case 4:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["s478"]);
                    break;
                }
                case 3:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["s477"]);
                    break;
                }
                case 5:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["479"]);
                    break;
                }
                case 6:{
                    $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+dictionary["s480"]);
                    break;
                }
            }
            $("#div"+thisClass.className+"Validation").html($("#div"+thisClass.className+"Validation").html()+"</b>, " +dictionary["s473"] + "<b>" +data.section+ "</b>, " +dictionary["s474"] + "<b>" +data.target+ "</b>");
        }
    },"json");
}