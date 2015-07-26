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

function Group() { };
OModule.inheritance(Group);

Group.className="Group";
Group.captionDelete="Are you sure you want to delete group";

Group.extraDeleteCallback=function()
{
    User.uiReload(User.currentID);
};

Group.extraSaveCallback=function()
{
    if(this.currentID!=0) User.uiReload(User.currentID);
    else User.uiEdit(User.currentID);
};

Group.uiFormNotValidated=function()
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

Group.getSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        name:$("#form"+this.className+"InputName").val(),
        Sharing_id:$("#form"+this.className+"SelectSharing").val()
    };
};