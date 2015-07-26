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
        head:$("#form"+this.className+"TextareaHead").val()
    };
};

Template.getFullSaveObject = function(isNew){
    if(isNew==null){
        isNew = false;
    }
    
    var obj = this.getAddSaveObject();
    obj["description"]=$("#form"+this.className+"TextareaDescription").val();
    obj["effect_show"]=$("#form"+this.className+"SelectEffectShow").val();
    obj["effect_hide"]=$("#form"+this.className+"SelectEffectHide").val();
    obj["effect_show_options"]=this.getEffectOptions(true);
    obj["effect_hide_options"]=this.getEffectOptions(false);
    return obj;
}

Template.uiSaveValidate=function(ignoreOnBefore,isNew){
    var thisClass = this;
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
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
    
    Methods.uiBlock("#div"+this.className+"Transitions");
    $.post("view/Template_transitions.php",object,function(data){
        Methods.uiUnblock("#div"+thisClass.className+"Transitions");
        $("#div"+thisClass.className+"Transitions").html(data);
    });
}