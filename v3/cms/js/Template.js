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

function Template() { };
OModule.inheritance(Template);

Template.className="Template";

Template.onAfterEdit=function()
{
    };

Template.onAfterSave=function(isNewObject)
{
    Test.uiTemplatesChanged();
};

Template.onAfterDelete=function(){
    Test.uiTemplatesChanged();
}

Template.onAfterImport=function(){
    Test.uiTemplatesChanged();
}

Template.onAfterAdd=function(){
    }
    
Template.uiRefreshCodeMirrors=function(){
    if(Template.formCodeMirror!=null) Template.formCodeMirror.refresh();
}

Template.formCodeMirror=null;
Template.getAddSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        HTML:Methods.getCKEditorData("#form"+this.className+"TextareaHTML"),
        head:$("#form"+this.className+"TextareaHead").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};

Template.getFullSaveObject = function(){
    var obj = this.getAddSaveObject();
    obj["description"]=$("#form"+this.className+"TextareaDescription").val();
    obj["effect_show"]=$("#form"+this.className+"SelectEffectShow").val();
    obj["effect_hide"]=$("#form"+this.className+"SelectEffectHide").val();
    obj["effect_show_options"]=this.getEffectOptions(true);
    obj["effect_hide_options"]=this.getEffectOptions(false);
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}

Template.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    Template.uiSaveValidated(ignoreOnBefore,isNew);
}

Template.setEffectOptions=function(isShow,optionsJSON){
    var effect = $("#form"+this.className+"SelectEffect"+(isShow?"Show":"Hide"));
    if(effect.val()=="none" || optionsJSON.trim() == "") return;
    
    var options = $.parseJSON(optionsJSON);
    
    switch(effect.val()){
        case "blind":{
            if(options["direction"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"BlindDirection").val(options.direction);
            break;
        }
        case "clip":{
            if(options["direction"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"ClipDirection").val(options.direction);
            break;
        }
        case "drop":{
            if(options["direction"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"DropDirection").val(options.direction);
            break;
        }
        case "explode":{
            if(options["pieces"]!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"ExplodePieces").val(options.pieces);
            break;
        }
        case "fold":{
            if(options["horizFirst"]!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"FoldHorizFirst").attr("checked",options.horizFirst);
            if(options["size"]!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"FoldSize").val(options.size);
            break;
        }
        case "puff":{
            if(options["percent"]!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"PuffPercent").val(options.percent);
            break;
        }
        case "slide":{
            if(options["direction"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"SlideDirection").val(options.direction);
            break;
        }
        case "scale":{
            if(options["direction"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"ScaleDirection").val(options.direction);
            if(options["origin"]!=null) $("#select"+this.className+(isShow?"Show":"Hide")+"ScaleOrigin").val(options.origin);
            if(options["percent"]!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"ScalePercent").val(options.percent);
            break;
        }
    }
    if(options.duration!=null) $("#input"+this.className+(isShow?"Show":"Hide")+"Duration").val(options.duration);
}

Template.getEffectOptions=function(isShow){
    var effect = $("#form"+this.className+"SelectEffect"+(isShow?"Show":"Hide"));
    if(effect.val()=="none") return "";
    
    var options = {};
    switch(effect.val()){
        case "blind":{
            options["direction"]=$("#select"+this.className+(isShow?"Show":"Hide")+"BlindDirection").val();
            break;
        }
        case "clip":{
            options["direction"]=$("#select"+this.className+(isShow?"Show":"Hide")+"ClipDirection").val();
            break;
        }
        case "drop":{
            options["direction"]=$("#select"+this.className+(isShow?"Show":"Hide")+"DropDirection").val();
            break;
        }
        case "explode":{
            options["pieces"]=$("#input"+this.className+(isShow?"Show":"Hide")+"ExplodePieces").val();
            break;
        }
        case "fold":{
            options["horizFirst"]=$("#input"+this.className+(isShow?"Show":"Hide")+"FoldHorizFirst").is(":checked");
            options["size"]=$("#input"+this.className+(isShow?"Show":"Hide")+"FoldSize").val();
            break;
        }
        case "puff":{
            options["percent"]=$("#input"+this.className+(isShow?"Show":"Hide")+"PuffPercent").val();
            break;
        }
        case "slide":{
            options["direction"]=$("#select"+this.className+(isShow?"Show":"Hide")+"SlideDirection").val();
            break;
        }
        case "scale":{
            options["direction"]=$("#select"+this.className+(isShow?"Show":"Hide")+"ScaleDirection").val();
            options["origin"]=$("#select"+this.className+(isShow?"Show":"Hide")+"ScaleOrigin").val();
            options["percent"]=$("#input"+this.className+(isShow?"Show":"Hide")+"ScalePercent").val();
            break;
        }
    }
    options["duration"]=$("#input"+this.className+(isShow?"Show":"Hide")+"Duration").val();
    return $.toJSON(options);
}

Template.uiChangeEffect=function(isShow){
    var thisClass = this;
    var options = this.getEffectOptions(!isShow);
    var showEffect = $("#form"+this.className+"SelectEffectShow").val();
    var hideEffect = $("#form"+this.className+"SelectEffectHide").val();
    
    var object = {
        oid:thisClass.currentID,
        class_name:thisClass.className,
        effect_show:showEffect,
        effect_hide:hideEffect
    };
    
    if(isShow){
        object["effect_hide_options"] = options;
    } else {
        object["effect_show_options"] = options;
    }
    
    $("#div"+this.className+"Transitions").mask(dictionary["s319"]);
    $.post("view/Template_transitions.php",object,function(data){
        $("#div"+thisClass.className+"Transitions").unmask();
        $("#div"+thisClass.className+"Transitions").html(data);
    });
}