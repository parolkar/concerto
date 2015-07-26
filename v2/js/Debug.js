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

function Debug(){};

Debug.Session=function(){};
Debug.Session.sessionContainer;
Debug.Session.currentSessionID=0;

Debug.Session.createSession=function()
{
    Debug.Session.sessionContainer=$("<div/>",{
        id:"divHistorySessionContainer" ,
        "class":"ui-widget-content ui-corner-all fullWidth"
    }).html(Debug.getDateTime()+" - "+ "Session initialization...<br/>").appendTo("#history");
};

Debug.Session.sessionCreated=function(sid)
{
    Debug.Session.currentSessionID=sid;
    $("#thSessionHistory").html("History of session id: "+sid+" <button class='btnSessionVariables' onclick='Debug.showSessionVariables()' /> <button class='btnRVariables' onclick='Debug.showRVariables()'/>");
    
    Debug.Session.sessionContainer.append(Debug.getDateTime()+" - "+ "Session id: <b>"+sid+"</b> initialized.<br/>");
    Methods.iniIconButtons();
};

Debug.sessionVariableModified=function(name,value,container)
{
    $(container).append(Debug.getDateTime()+" - "+ "Session variable modification: '<b>"+name+"</b>' = '<b>"+value+"</b>'.</br>");
    
    if(Item.Current!=null)
    {
        $("#hzn_sessionVariables").html("");
        for(var key in Item.Current.variables)
        {
            $("#hzn_sessionVariables").append("'<b>"+key+"</b>' = '"+Item.Current.variables[key]+"'<br/>");
        }
    }
};

Debug.Item=function(){};
Debug.Item.lastItemContainer;
Debug.Item.lastItemIndex=0;

Debug.Item.loadItem=function(iid)
{
    Debug.Session.sessionContainer.prepend("<br/><br/>");
    
    Debug.Item.lastItemIndex++;
    Debug.Item.lastItemContainer=$("<div/>",{
        id:"divHistoryItemContainer"+Debug.Item.lastItemIndex, 
        "class":"ui-widget-content ui-state-focus ui-corner-all fullWidth"
    }).html("<div align='center' class='ui-widget-header ui-corner-all fullWidth' style='font-size:1.5em;'>Item template id: "+iid+"</div>"+Debug.getDateTime()+" - " + "Loading new item template id: <b>"+iid+"</b>...<br/>").prependTo(Debug.Session.sessionContainer);
    $("#thSessionItem").html("Item template id: <b>"+iid+"</b>");
    
    if(Item.Current!=null)
    {
        $("#hzn_sessionVariables").html("");
        for(var key in Item.Current.variables)
        {
            $("#hzn_sessionVariables").append("'<b>"+key+"</b>' = '"+Item.Current.variables[key]+"'<br/>");
        }
    }
};

Debug.Item.itemLoaded=function()
{
    Debug.Item.appendToLastItemContainer(Debug.getDateTime()+" - "+ "Item loaded.</br>");
};

Debug.Item.appendToLastItemContainer=function(html)
{
    Debug.Item.lastItemContainer.append(html);
};

Debug.Item.buttonClicked=function(name)
{
    Debug.Item.appendToLastItemContainer(Debug.getDateTime()+" - "+ "Button with name: '<b>"+name+"</b>' clicked.</br>");
};

Debug.Item.initializeCallToR=function()
{
    Debug.Item.appendToLastItemContainer(Debug.getDateTime()+" - "+ "Initializing call to <b>R</b>...</br>");
}

Debug.Item.RCallResult=function(code,exit,output)
{
    Debug.Item.appendToLastItemContainer("<br/><b>R code</b> to execute:</br>");
    var textarea = $("<textarea/>",{
        id:"rcode"+Debug.Item.lastItemIndex,
        style:"width:100%; height:200px;"
    }).html(code).appendTo(Debug.Item.lastItemContainer);
    Methods.iniCodeMirror("rcode"+Debug.Item.lastItemIndex, "r",true);
    Debug.Item.appendToLastItemContainer(exit==0?"<div style='color:green; font-size:1.5em;' align='center' class='fullWidth'>R code validation <b>PASSED.</b></div><br/>":"<div style='color:red; font-size:12px;' class='ui-state-error fullWidth' align='center'>R code validation <b>FAILED!</b></div><br/>");
    Debug.Item.appendToLastItemContainer("R code <b>output</b>:<br/>");
    Debug.Item.appendToLastItemContainer("<div class='fullWidth ui-state-highlight' style='font-size:1.2em;'>"+output+"</div>");
    
    $.post("query/r_variables.php",{
        oid:Debug.Session.currentSessionID
    },function(data){
        if(data.exists==1)
        {
            $("#hzn_rVariables").html(data.result);
        }
    },"json");
};

Debug.getDateTime=function()
{
    var date = new Date();
    return date.getDate()+"/"+date.getMonth()+"/"+date.getFullYear()+" "+(date.getHours()<10?"0"+date.getHours():date.getHours())+":"+(date.getMinutes()<10?"0"+date.getMinutes():date.getMinutes())+":"+(date.getSeconds()<10?"0"+date.getSeconds():date.getSeconds());
};

Debug.showSessionVariables=function()
{   
    $("#hzn_sessionVariables").dialog({
        
        });
};

Debug.showRVariables=function()
{
    $("#hzn_rVariables").dialog({
        
        });
};