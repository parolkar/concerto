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

function UserType() { };
OModule.inheritance(UserType);

UserType.className="UserType";

UserType.reloadOnModification=true;
UserType.reloadHash="tnd_mainMenu-users";

UserType.onBeforeSave=function(isNew){
    if(isNew==null) isNew = false;
    Methods.confirmUnsavedLost(function(){
        UserType.uiSave(true,isNew);
    });
}

UserType.onBeforeDelete=function(oid){
    Methods.confirmUnsavedLost(function(){
        UserType.uiDelete(oid,true);
    });
}

UserType.onAfterEdit=function()
{
    User.uiReload(User.currentID);
    $("#divUsersAccordion").accordion("resize");
};

UserType.onAfterList=function(){
    }

UserType.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

UserType.getAddSaveObject=function()
{
    var rws = new Array();
    var ids = new Array();
    var values = new Array();
	
    $(".form"+this.className+"ModuleRights").each(function(index){
        var id = $(this).attr("id");
        id = id.substr(id.indexOf('_')+1);
        rws.push(id.substr(0,1));
        ids.push(id.substr(id.indexOf("_")+1));
        if($(this).is(":checkbox")) 
        {
            values.push($(this).is(":checked")?1:0);
        }
        else values.push($(this).val());
    });
    return { 
        "oid":this.currentID,
        "class_name":this.className,
        "name":$("#form"+this.className+"InputName").val(),
        "Sharing_id":$("#form"+this.className+"SelectSharing").val(),
        "rws[]":rws,
        "ids[]":ids,
        "values[]":values
    };
};

UserType.getFullSaveObject=function(){
    var obj = this.getAddSaveObject();
    if($("#form"+this.className+"SelectOwner").length==1) obj["Owner_id"]=$("#form"+this.className+"SelectOwner").val();
    return obj;
}

UserType.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputName").val()
    ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    UserType.uiSaveValidated(ignoreOnBefore,isNew);
}