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

function Item(parameters,debug,client)
{
    this.variables = parameters;
    this.sessionID=parameters.SessionID;
    this.currentTemplateID=parameters.template_id;
	
    this.HTML;
    this.timer;
    this.timeLeft;
    this.defaultButtonName;
    this.timeout;
	
    this.getTimerObject = function()
    {
        return document.getElementById("hzn_timer");
    };
	
    this.isTimerSet = function()
    {
        if(this.timer>0) return true;
        else return false;
    };
	
    this.insertVariables=function(html)
    {
        //timer
        while(html.indexOf("{{timer}}")!=-1)
        {
            html = html.replace("{{timer}}", "<font id='hzn_timer'></font>");
        }
		
        //vars
        var loop = true;
        while(loop)
        {
            loop=false;
            for(var key in this.variables)
            {
                while(html.indexOf("{{"+key+"}}")!=-1)
                {
                    html = html.replace("{{"+key+"}}", (this.variables.hasOwnProperty(key)?this.variables[key]:""));
                    if(this.variables.hasOwnProperty(key) && html.indexOf("{{")!=-1) loop = true;
                }
            }
        }
        return html;
    };
	
    this.setHTML = function(html)
    {
        html = this.insertVariables(html);
        this.HTML = html;
        $("#item").html(html);
    };
	
    this.setTimer = function(timer,defaultButtonName)
    {
        this.timer = parseInt(timer);
        this.timeLeft=parseInt(timer);
        this.defaultButtonName = defaultButtonName;
        var timerObject = this.getTimerObject();
        if(timerObject) timerObject.innerHTML = this.timeLeft;
        this.timeout = setTimeout("Item.Current.timeTick();", "1000");
    };
    this.timeTick=function()
    {
        if(this.isTimeLeft()) 
        {
            this.timeLeft--;
            var timerObject = this.getTimerObject();
            if(timerObject) timerObject.innerHTML = this.timeLeft;
            this.timeout = setTimeout("Item.Current.timeTick();", "1000"); 
        }
        if(!this.isTimeLeft()) this.clickDefaultButton();
    };
    this.clickDefaultButton=function()
    {
        var button = this.getDefaultButton();
        if(button!=null) button.click();
    };
    this.isTimeLeft = function()
    {
        if(this.timeLeft>0 || this.timer==0) return true;
        else return false;
    };
	
    this.getControls = function()
    {
        var controls = new Array();
        $("#item :checkbox").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        $("#item :radio:checked").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        $("#item select").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        $("#item textarea").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        $("#item :text").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        $("#item :password").each(function(){
            if($(this).attr("name")!="") controls[$(this).attr("name")]=$(this);
        });
        return controls;
    };
	
    this.getButtons = function()
    {
        var buttons = new Array();
        $("#item :button").each(function(){
            buttons.push($(this));
        });
        $("#item :image").each(function(){
            buttons.push($(this));
        });
        return buttons;
    };
	
    this.getDefaultButton = function()
    {
        var buttons = this.getButtons();
        for(var i=0;i<buttons.length;i++)
        {
            if(buttons[i].attr("name")==this.defaultButtonName) return buttons[i];
        }
        return null;
    };
	
    this.initializeButtonsTrigger=function()
    {
        var thisClass = this;
        var buttons = this.getButtons();
        for(var i=0;i<buttons.length;i++)
        {
            var btn = buttons[i];
            if(btn.hasClass("noTrigger")) continue;
            btn.click(function()
            {
                if(Item.debug) Debug.Item.buttonClicked($(this).attr("name"));
                clearTimeout(thisClass.timeout);
                Item.Current.RCall($(this).attr("name"));
                $("#item").html("<div align='center' style='width:100%;'><img src='css/img/ajax-loader.gif' /></div>");
            });
        }
    };
	
    this.RCall=function(buttonName)
    {
        var ctrName=new Array();
        var ctrValue=new Array();
        var controls = this.getControls();
        var varsName=new Array();
        var varsValue=new Array();
        for(key in controls)
        {
            if(controls[key].is(":checkbox")&&!controls[key].is(":checked")) controls[key].val("NA");
            ctrName.push(key);
            ctrValue.push(controls[key].val());
            if(Item.debug) Debug.sessionVariableModified(key, controls[key].val(), Debug.Item.lastItemContainer);
        }
        if(Item.debug) Debug.sessionVariableModified("button_name", buttonName, Debug.Item.lastItemContainer);
        if(Item.debug) Debug.sessionVariableModified("time_left", this.timeLeft, Debug.Item.lastItemContainer);
        for(key in this.variables)
        {
            varsName.push(key);
            varsValue.push(this.variables[key]);
        }
        if(Item.debug) Debug.Item.initializeCallToR();
        
        var callback = function(data) {
            if(Item.debug) Debug.Item.RCallResult(data.debug_rcode,data.debug_return,data.debug_output);
                
            delete data.debug_rcode;
            delete data.debug_return;
            delete data.debug_output;
                
            Item.Current.variables = data;
            Item.Current.setCurrentItem(data.template_id);
        };
        
        var options = {
            "SessionID":this.sessionID,
            "template_id":this.currentTemplateID,
            "button_name":buttonName,
            "time_left":this.timeLeft,
            "ctr_name[]":ctrName,
            "ctr_value[]":ctrValue, 
            "var_name[]":varsName, 
            "var_value[]":varsValue
        };
        
        if(Item.client) Item.clientObject.rCall(options,function(data){
            callback.call(this,data)
        });
        else $.post("query/r_call.php",options,function(data){
            callback.call(this,data)
        },"json");
    };

    this.setCurrentItem=function(itemID)
    {
        if(Item.debug) Debug.Item.loadItem(itemID);
        clearTimeout(this.timeout);
        
        var callback = function(data){
            
            Item.Current.currentTemplateID=itemID;
            Item.Current.setHTML(data.HTML);
            Item.Current.setTimer(data.timer, data.default_button);
            Item.Current.initializeButtonsTrigger();
            if(Item.debug) Debug.Item.itemLoaded();
        };
        
        var sessionID = this.sessionID;
        var options = {
            "SessionID":sessionID,
            "template_id":itemID
        };
        
        if(Item.client) Item.clientObject.setItem(options,function(data){
            callback.call(this,data)
        });
        else $.post("query/set_item.php",options,function(data){
            callback.call(this,data)
        },"json");
    };
    
    Item.debug=(debug==1);
    Item.client=(client!=null&&client==1);
    this.setCurrentItem(parameters.template_id);
}
Item.debug=false;
Item.client = false;
Item.clientObject = null;
Item.Current = null;
