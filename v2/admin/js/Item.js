/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function Item() { };
OModule.inheritance(Item);

Item.className="Item";
Item.listLength=25;

Item.captionDelete="";
Item.captionImportHTMLConfirm="";

Item.RCodeMirrorsIds=new Array();
Item.RCodeMirrors=new Array();

Item.editorHTMLChangedEvent=function(){
    Item.onEditorHTMLChange();
}

Item.extraDeleteCallback=function()
{
    };

Item.extraSaveCallback=function()
{
    };

Item.extraBeforeEditCallback=function()
{
    if(CKEDITOR.instances["htmlEditor"]!=null) CKEDITOR.instances["htmlEditor"].removeListener("blur",Item.editorHTMLChangedEvent);
};

Item.extraEditCallback=function()
{
    Item.refreshPresentation();
};

Item.refreshDefaultButtonSelect=function()
{
    var buttons = Item.getAllButtons();
    var selection = $("#formItemSelectDefault").val();
    $.post("view/tab_item_presentation_default.php",
    {
        "buttons[]":buttons,
        selection:selection,
        oid:Item.currentID
    },
    function(data){
        $("#tdDefaultButton").html(data);
    })
};

Item.onEditorHTMLChange=function()
{
    Item.refreshInteraction();
    Item.refreshDefaultButtonSelect();
};

Item.refreshHTML=function(oid)
{
    $.post(
        "query/get_item_html.php", {
            "item_id":oid
        },
        function(data) 
        { 
            //$("#htmlEditor").val(data);
            //Methods.iniCKEditor("#htmlEditor")
            CKEDITOR.instances["htmlEditor"].setData(data);
        });
};

Item.importHTML=function(oid)
{
    Methods.confirm(Item.captionImportHTMLConfirm,null,function(){
        Item.refreshHTML(oid);
    });
};

Item.refreshPresentation=function()
{
    $.post(
        "view/tab_item_presentation.php", {
            "oid":Item.currentID
        },
        function(data) 
        { 
            $("#tabItemPresentation").html(data);
            Item.onEditorHTMLChange();
            
            CKEDITOR.instances["htmlEditor"].on("blur",Item.editorHTMLChangedEvent);
        }		
        );
};

Item.refreshInteraction=function()
{
    var sentNames = new Array();
    var sentSources = new Array();

    var controls = Item.getAllControls();
    for(var i=0;i<controls.length;i++)
    {
        sentNames.push(controls[i]["name"]);
        sentSources.push(controls[i]["source"]);
    }
    
    var buttons = Item.getAllButtons();
    var vars = Item.getAcceptedVars();
    
    Item.refreshAcceptedVars(vars);
    Item.refreshSentVars(sentNames, sentSources);
    
    $.post("view/tab_item_interaction.php",
    {
        oid:Item.currentID,
        "buttons[]":buttons
    },function(data){
        Item.RCodeMirrorsIds = new Array();
        Item.RCodeMirrors = new Array();
        $("#tabItemInteraction").html(data); 
        
        for(var i=0;i<buttons.length;i++)
        {
            if($("#divItemTabs").tabs("option","selected")==1)
            {
                var cm = Methods.iniCodeMirror("button_function_"+buttons[i],"r");
                Item.RCodeMirrors.push(cm);
            }
            Item.RCodeMirrorsIds.push("button_function_"+buttons[i]);
        }
    });	
};

Item.refreshAcceptedVars=function(vars)
{
    $.post("view/tab_item_interaction_avars.php",{
        "vars[]":vars
    },function(data){
        $("#itemAcceptedVariables").html(data);
    },"html");
};

Item.refreshSentVars=function(names,sources)
{
    $.post("view/tab_item_interaction_svars.php",{
        "names[]":names,
        "sources[]":sources
    },function(data){
        $("#itemSentVariables").html(data);
    },"html");
};

Item.getAllControls=function()
{
    var html = Methods.getCKEditorData("#htmlEditor");
    var controls = new Array();

    //checkboxes, texts, radios
    var inputHTML = html;
    while(inputHTML.toLowerCase().indexOf("<input")!=-1)
    {
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<input"));
        var startIndex = inputHTML.toLowerCase().indexOf("<input")+6;
        var input = inputHTML.substring(startIndex,inputHTML.indexOf(">"));
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<input")+1);

        var inputName=input;
        if(input.toLowerCase().indexOf(" name=")==-1) continue;
        startIndex = input.toLowerCase().indexOf(" name=")+6;
        var quote = input.substr(startIndex,1);
        inputName = input.substr(startIndex+1);
        var name = inputName.substring(0,inputName.indexOf(quote));

        var inputType=input;
        if(input.toLowerCase().indexOf(" type=")==-1) continue;
        startIndex = input.toLowerCase().indexOf(" type=")+6;
        var quote = input.substr(startIndex,1);
        inputType = input.substr(startIndex+1);
        var type = inputType.substring(0,inputType.indexOf(quote));
			
        var ctr = new Array();
        ctr["name"]=name;
        ctr["source"]=type;
        var lcType = type.toLowerCase();
        if(name=="") continue;
        if(lcType=="text" || lcType=="password" || lcType=="checkbox" || lcType=="radio") 
        {
            var present = false;
            for(var i=0;i<controls.length;i++)
            {
                if(controls[i]["name"]==name && controls[i]["source"]==type)
                {
                    present = true;
                    break;
                }
            }
            if(!present) controls.push(ctr);
        }
    }

    //select
    inputHTML = html;
    while(inputHTML.toLowerCase().indexOf("<select")!=-1)
    {
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<select"));
        var startIndex = inputHTML.toLowerCase().indexOf("<select")+7;
        var input = inputHTML.substring(startIndex,inputHTML.indexOf(">"));
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<select")+1);

        var inputName=input;
        if(input.toLowerCase().indexOf(" name=")==-1) continue;
        startIndex = input.toLowerCase().indexOf(" name=")+6;
        var quote = input.substr(startIndex,1);
        inputName = input.substr(startIndex+1);
        var name = inputName.substring(0,inputName.indexOf(quote));

        var type="select";
			
        var ctr = new Array();
        ctr["name"]=name;
        ctr["source"]=type;
        var lcType = type.toLowerCase();

        if(name=="") continue;

        var present = false;
        for(var i=0;i<controls.length;i++)
        {
            if(controls[i]["name"]==name && controls[i]["source"]==type)
            {
                present = true;
                break;
            }
        }
        if(!present) controls.push(ctr);
    }

    //textarea
    inputHTML = html;
    while(inputHTML.toLowerCase().indexOf("<textarea")!=-1)
    {
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<textarea"));
        var startIndex = inputHTML.toLowerCase().indexOf("<textarea")+9;
        var input = inputHTML.substring(startIndex,inputHTML.indexOf(">"));
        inputHTML = inputHTML.substr(inputHTML.toLowerCase().indexOf("<textarea")+1);

        var inputName=input;
        if(input.toLowerCase().indexOf(" name=")==-1) continue;
        startIndex = input.toLowerCase().indexOf(" name=")+6;
        var quote = input.substr(startIndex,1);
        inputName = input.substr(startIndex+1);
        var name = inputName.substring(0,inputName.indexOf(quote));

        var type="textarea";
			
        var ctr = new Array();
        ctr["name"]=name;
        ctr["source"]=type;
        var lcType = type.toLowerCase();

        if(name=="") continue;

        var present = false;
        for(var i=0;i<controls.length;i++)
        {
            if(controls[i]["name"]==name && controls[i]["source"]==type)
            {
                present = true;
                break;
            }
        }
        if(!present) controls.push(ctr);
    }
		
    return controls;
};

Item.getAllButtons=function()
{
    var buttons = new Array();
    var html = Methods.getCKEditorData("#htmlEditor");
    while(html.indexOf('<input')!=-1)
    {
        i = html.indexOf('<input ');
        html = html.substr(i+1);
        var input = html.substring(0,html.indexOf('>'));

        if(input.indexOf(" type=")==-1) continue;

        var inputTypeQuote=input.substr(input.indexOf(" type=")+6,1);
        if(inputTypeQuote!="'"&&inputTypeQuote!='"') continue;

        var inputType = input.substr(input.indexOf(" type=")+7);
        inputType = inputType.substr(0,inputType.indexOf(inputTypeQuote));
        if(inputType!="button" && inputType!="image") continue;
			
        if(input.indexOf(" name=")==-1) continue;

        var inputNameQuote=input.substr(input.indexOf(" name=")+6,1);
        if(inputNameQuote!="'"&&inputNameQuote!='"') continue;

        var inputName = input.substr(input.indexOf(" name=")+7);
        inputName = inputName.substr(0,inputName.indexOf(inputNameQuote));
        if(inputName!="" && buttons.indexOf(inputName)==-1) buttons.push(inputName);
    }
    return buttons;
};

Item.getButtonsFunctions=function(buttons)
{
    var functions = Array();
    for(var i=0;i<buttons.length;i++)
    {
        functions.push($("#button_function_"+buttons[i]).val());
    }
    return functions;
};

Item.getButtonsDefaults=function(buttons)
{
    var defaults = Array();
    for(var i=0;i<buttons.length;i++)
    {
        defaults.push($("#button_default_"+buttons[i]).attr("checked")?1:0);
    }
    return defaults;
};

Item.getAcceptedVars=function()
{
    var html = Methods.getCKEditorData("#htmlEditor");
    var vars = new Array();
		
    while(html.indexOf("{{")!=-1)
    {
        html = html.substr(html.indexOf("{{")+2);
        vars.push(html.substr(0,html.indexOf("}}"))); 
    }	
    return vars;
};

Item.uiFormNotValidated=function()
{
    //required fields
    var result = true;
    var name = $("#form"+this.className+"InputName");
    if(jQuery.trim(name.val())=="") 
    {
        result = false;
        name.addClass("ui-state-error");
    }
    else name.removeClass("ui-state-error");
    
   if(result) return false;
   else return Methods.captionRequiredFields;
};

Item.getSaveObject=function()
{
    var buttons = Item.getAllButtons();
    var functions = Item.getButtonsFunctions(buttons);
    var timerNumber = parseInt($("#form"+this.className+"InputTimer").val());
    
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val(),
        HTML:Methods.getCKEditorData("#htmlEditor"),
        default_btn_name:$("#form"+this.className+"SelectDefault").val(),
        timer:(isNaN(timerNumber)?0:timerNumber),
        "buttons[]":buttons,
        "functions[]":functions
    };
};

Item.showBuiltInRFunctionsDocDialog=function()
{
    $("#divBuiltInRFunctionsDocDialog").dialog({
        show:"fade",
        hide:"fade",
        width:600
    });
};

Item.showItemsSessionVariablesDialog=function()
{
    $("#divItemsSessionVariablesDialog").dialog({
        show:"fade",
        hide:"fade",
        width:600
    });
};