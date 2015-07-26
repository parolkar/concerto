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

function OModule() {};

OModule.inheritance=function(obj)
{
    obj.isNewObjectSaved=false;
    obj.tempID='';
    obj.currentID=0;
    obj.listLength=10;
    
    obj.uiChangeListLength=function(length)
    {
        this.listLength=length;
    };
	
    obj.uiReload=function(oid)
    {
        this.uiEdit(oid);
        this.uiList();
    };
        
    obj.highlightCurrentElement=function()
    {
        $(".row"+this.className+" td").removeClass("ui-state-highlight");
        $("#row"+this.className+this.currentID+" td").addClass("ui-state-highlight");
    };
	
    obj.uiEdit=function(oid)
    {
        var thisClass = this;
        if(thisClass.extraBeforeEditCallback) thisClass.extraBeforeEditCallback();
        if(this.currentID==0 && !this.isNewObjectSaved) this.uiDeleteTempRecords();
        this.isNewObjectSaved=false;
        if(oid==0) this.tempID=this.uiRegenerateTempID();
		
        this.currentID=oid;
        $.post("view/"+this.className+"_form.php",
        {
            oid:oid, 
            temp_id:this.tempID
        },
        function(data){
            $("#div"+thisClass.className+"Form").html(data);
            thisClass.highlightCurrentElement();
            if(thisClass.extraEditCallback) thisClass.extraEditCallback();
        });
    };
	
    obj.uiRegenerateTempID=function()
    {
        this.tempID = this.className+"_"+Methods.getTempID();
    };
	
    obj.uiList=function()
    {
        var thisClass = this;
        $.post("view/"+this.className+"_list.php",{
            oid:thisClass.currentID
        },
        function(data)
        {
            $('#div'+thisClass.className+'List').html(data);
            thisClass.highlightCurrentElement();
        });
    };
	
    obj.uiDelete=function(oid)
    {
        var thisClass = this;
        Methods.confirm(this.captionDelete+" #"+oid+"?",null,function(){
            if(oid==thisClass.currentID) thisClass.uiEdit(0);
            $.post("query/delete_object.php",
            {
                class_name:thisClass.className,
                oid:oid
            },
            function(data)
            {
                thisClass.uiList();
                if(thisClass.extraDeleteCallback) thisClass.extraDeleteCallback();
            });
        });
    };
	
    obj.uiSave=function()
    {
        var thisClass = this;
		
        if(this.uiFormNotValidated)
        {
            var notValidated = this.uiFormNotValidated();
            if(notValidated) 
            {
                Methods.alert(notValidated,"alert");
                return;
            }
        }
		
        $.post("query/save_module.php",
            this.getSaveObject(),
            function(data)
            {
                if(data.oid!=0)
                {
                    if(thisClass.currentID!=0) thisClass.uiList();
                    else 
                    {
                        thisClass.isNewObjectSaved=true;
                        thisClass.uiReload(data.oid);
                    }
                    if(thisClass.extraSaveCallback) thisClass.extraSaveCallback();
                    Methods.alert(Methods.captionSaved,"info");
                }
                else Methods.alert(Methods.captionNotSaved,"alert");
            },"json");
    };
	
    obj.uiDeleteTempRecords=function()
    {
        $.post("query/delete_temp_files.php",
        {
            temp_id:this.tempID
        },
        function(data)
        {
            });
    };
	
    obj.uiDeleteFile=function(oid)
    {
        var thisClass=this;
        Methods.confirm(Methods.captionDeleteFileConfirmation+" #"+oid+"?",null,function(){
            $.post("query/delete_object.php",
            {
                class_name:"File",
                oid:oid
            },
            function(data)
            {
                thisClass.uiFilesList();
            });
        });
    };
	
    obj.uiFilesList=function()
    {
        var thisClass=this;
        $.post("view/files_list.php",
        {
            oid:this.currentID, 
            temp_id:this.tempID, 
            class_name:this.className
        },
        function(data)
        {
            $('#form'+thisClass.className+'UploadedFiles').html(data);
            Methods.iniIconButtons();
        });
    };
};