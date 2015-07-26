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

function User() { };
OModule.inheritance(User);

User.className="User";
User.sessionID="";

User.captionIncorrectLogin="login/password combination is incorrect!";
User.captionDelete="Are you sure you want to delete user";
User.captionPasswordsMismatch="Password and password confirmation doesn't match!";

User.extraDeleteCallback=function() { };

User.extraSaveCallback=function() 
{
    if(this.currentID!=0) 
    {
        Group.uiReload(Group.currentID);
    }
    else 
    {
        Group.uiEdit(Group.currentID);
    }
};

User.getSaveObject=function()
{
    return { 
        oid:this.currentID,
        class_name:this.className,
        login:$("#form"+this.className+"InputLogin").val(),
        firstname:$("#form"+this.className+"InputFirstname").val(),
        lastname:$("#form"+this.className+"InputLastname").val(),
        email:$("#form"+this.className+"InputEmail").val(),
        phone:$("#form"+this.className+"InputPhone").val(),
        Group_id:$("#form"+this.className+"SelectGroup").val(),
        modify_password:$("#form"+this.className+"CheckboxPassword").is(":checked")?1:0,
        password:$("#form"+this.className+"InputPassword").val(),
        superadmin:$("#form"+this.className+"CheckboxSuperadmin").is(":checked")?1:0,
        send_credentials:$("#form"+this.className+"CheckboxSendCredentials").is(":checked")?1:0,
        welcome:$("#form"+this.className+"TextareaWelcome").val()
    };
};

User.uiFormNotValidated=function()
{
    //required fields
    var result = true;
    var message = "";
    
    var login = $("#form"+this.className+"InputLogin");
    var password = $("#form"+this.className+"InputPassword");
    var confirmation = $("#form"+this.className+"InputPasswordConf");
    var password_mod = $("#form"+this.className+"CheckboxPassword").is(":checked");
    
    if(jQuery.trim(login.val())=="")
    {
        result = false;
        login.addClass("ui-state-error");
        message+=Methods.captionRequiredFields+"\n";
    }
    else login.removeClass("ui-state-error");
    
    if(User.currentID==0)
    {
        if(jQuery.trim(password.val())=="") 
        {
            result=false;
            if(message.indexOf(Methods.captionRequiredFields)==-1) message+=Methods.captionRequiredFields+"\n";
            password.addClass("ui-state-error");
        }
        else
        {
            password.removeClass("ui-state-error");
        }
    }
    else password.removeClass("ui-state-error");
    
    if(!password.hasClass("ui-state-error"))
    {
        if(password_mod&&password.val()!=confirmation.val())
        {
            result=false;
            password.addClass("ui-state-error");
            confirmation.addClass("ui-state-error");
            message+=User.captionPasswordsMismatch+"\n";
        }
        else
        {
            password.removeClass("ui-state-error"); 
            confirmation.removeClass("ui-state-error");
        }
    }
    
    if(result) return false;
    else return message;
};

User.uiLogOut=function()
{
    $.post("query/log_out.php",{},
        function(data){
            location.href="index.php";
        });
};

User.uiLogin=function()
{
    $.post(
        'query/login.php',
        {
            login:$('#login').val(), 
            password:$('#password').val()
        },
        function(data)
        {
            if(data.success=="1") location.reload(true);
            else Methods.alert(User.captionIncorrectLogin,"alert");
        },
        "json");		
};