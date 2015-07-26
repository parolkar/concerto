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

function User() { };
OModule.inheritance(User);

User.className="User";
User.sessionID="";
User.reloadOnModification=true;
User.reloadHash="tnd_mainMenu-users";

User.onBeforeSave=function(isNew){
    if(isNew==null) isNew = false;
    Methods.confirmUnsavedLost(function(){
        User.uiSave(true,isNew);
    });
}

User.onBeforeDelete=function(oid){
    Methods.confirmUnsavedLost(function(){
        User.uiDelete(oid,true);
    });
}

User.onAfterEdit=function() {
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterChangeListLength=function(){
    $("#divUsersAccordion").accordion("resize");
};

User.onAfterList=function(){
    }

User.getAddSaveObject=function()
{
    var login = $("#form"+this.className+"InputLogin").val();
    var password = $("#form"+this.className+"InputPassword").val();
    var hash = User.getClientHash(login, password);
    
    return { 
        oid:this.currentID,
        class_name:this.className,
        login:login,
        firstname:$("#form"+this.className+"InputFirstname").val(),
        lastname:$("#form"+this.className+"InputLastname").val(),
        email:$("#form"+this.className+"InputEmail").val(),
        phone:$("#form"+this.className+"InputPhone").val(),
        UserGroup_id:$("#form"+this.className+"SelectUserGroup").val(),
        UserInstitutionType_id:$("#form"+this.className+"SelectInstitutionType").val(),
        institution_name:$("#form"+this.className+"InputInstitutionName").val(),
        modify_password:$("#form"+this.className+"CheckboxPassword").is(":checked")?1:0,
        password_hash:hash,
        UserType_id:$("#form"+this.className+"SelectUserType").val()
    };
};

User.getFullSaveObject=function()
{
    var obj = this.getAddSaveObject();
    
    return obj;
}

User.uiSaveValidate=function(ignoreOnBefore,isNew){
    if(!this.checkRequiredFields([
        $("#form"+this.className+"InputLogin").val(),
        $("#form"+this.className+"InputFirstname").val(),
        $("#form"+this.className+"InputLastname").val(),
        $("#form"+this.className+"InputEmail").val(),
        $("#form"+this.className+"SelectInstitutionType").val(),
        $("#form"+this.className+"InputInstitutionName").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return false;
    }
    
    if($("#form"+this.className+"CheckboxPassword").is(":checked")&&$("#form"+this.className+"InputPassword").val()!=$("#form"+this.className+"InputPasswordConf").val())
    {
        Methods.alert(dictionary["s66"],"alert");
        return false;
    }
    
    $.post("query/check_module_unique_fields.php", {
        "class_name":this.className,
        "oid":this.currentID,
        "fields[]":[$.toJSON({
            name:"login",
            value:$("#form"+this.className+"InputLogin").val()
        })]
    },function(data){
        switch(data.result){
            case 0: {
                User.uiSaveValidated(ignoreOnBefore,isNew);
                break;
            }
            case 1:{
                Methods.alert(dictionary["s336"],"alert",dictionary["s274"]);
                return false;    
            }
            case -1:{
                Methods.alert(dictionary["s278"], "alert", dictionary["s274"]);
                return false;
            }
        }
    },"json");
}

User.register = function(){
    var login = $("#dd_register_inp_login").val();
    var firstname = $("#dd_register_inp_first_name").val();
    var lastname = $("#dd_register_inp_last_name").val();
    var email = $("#dd_register_inp_last_email").val();
    var phone = $("#dd_register_inp_last_phone").val();
    var password = $("#dd_register_inp_password").val();
    var password_conf = $("#dd_register_inp_password_conf").val();
    var institution_type = $("#dd_register_select_institution_type").val();
    var institution_name = $("#dd_register_inp_institution_name").val();
    
    if(!this.checkRequiredFields([
        login,firstname,lastname,email,institution_type,institution_name
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return;
    }
    
    if(password!=password_conf)
    {
        Methods.alert(dictionary["s66"],"alert",dictionary["s410"]);
        return false;
    }
    
    $.post("query/check_module_unique_fields.php", {
        "class_name":this.className,
        "oid":0,
        "fields[]":[$.toJSON({
            name:"login",
            value:login
        })]
    },function(data){
        switch(data.result){
            case 0: {
                var hash = User.getClientHash(login,password);
                $.post("query/register.php",{
                    login:login,
                    password_hash:hash,
                    firstname:firstname,
                    lastname:lastname,
                    email:email,
                    phone:phone,
                    UserInstitutionType_id:institution_type,
                    institution_name:institution_name
                },function(data){
                    switch(data.result){
                        case 0:{
                            Methods.alert(dictionary["s414"], "alert", dictionary["s410"],function(){
                                location.href='index.php';
                            });
                            break;
                        }
                        case -1:{
                            Methods.alert(dictionary["s81"], "alert", dictionary["s410"]);
                            break;
                        }
                    }
                },"json");
                
                break;
            }
            case 1:{
                Methods.alert(dictionary["s336"],"alert",dictionary["s410"]);
                return;    
            }
            case -1:{
                Methods.alert(dictionary["s278"], "alert", dictionary["s410"]);
                return;
            }
        }
    },"json");
}

User.uiPasswordRecovery=function(){
    var login = $("#dd_login_inp_login").val();
    if(!this.checkRequiredFields([
        login
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return;
    }
    
    $.post("query/recover_password.php",{
        login:login
    },function(data){
        switch(data.result){
            case 0:{
                Methods.alert(dictionary["s432"],"info",dictionary["s427"]);
                break;
            }
            case -1:{
                Methods.alert(dictionary["s426"],"alert",dictionary["s427"]);
                break;
            }
        }
    },"json");
}

User.uiRegister=function(){
    $("#dd_login").dialog("close");
    $("#dd_register").dialog({
        modal:true,
        width:400,
        title:dictionary["s410"],
        resizeable:false,
        closeOnEscape:false,
        dialogClass:"no-close",
        open:function(){
            $('.ui-widget-overlay').css('position', 'fixed');
            Methods.iniTooltips();
        },
        close:function(){
        //$('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons:[
        {
            text:dictionary["s412"],
            click:function(){
                User.register();
            }
        },
        {
            text:dictionary["s23"],
            click:function(){
                location.href = "index.php";
            }
        }
        ]
    });
}

User.getClientHash = function(login,password){
    var hash = password;
    for (var i = 0; i < 10; i++) {
        var shaObj = new jsSHA(login+"-"+hash, "ASCII");
        hash = shaObj.getHash("SHA-512", "HEX");
    }
    return hash;
}

User.uiLogIn=function()
{
    var thisClass=this;
    
    if(!this.checkRequiredFields([
        $("#dd_login_inp_login").val()
        ])) {
        Methods.alert(dictionary["s415"],"alert");
        return;
    }
    
    var login = $("#dd_login_inp_login").val();
    var password = $("#dd_login_inp_password").val();
    var hash = User.getClientHash(login,password);
    
    $("#dd_login").parent().mask(dictionary["s319"]);
    $.post("query/log_in.php",
    {
        login:login,
        password:hash
    },
    function(data){
        $("#dd_login").parent().unmask();
        if(data.success==1)
        {
            $("#dd_login").dialog("close");
            Methods.modalLoading();
            $.post("view/layout.php",{},
                function(data){
                    Methods.stopModalLoading();
                    $("#content").html(data);
                });
        }
        else Methods.alert(dictionary["s67"],"alert");
    },"json");
};

User.uiLogOut=function()
{
    Methods.modalLoading();
    $.post("query/log_out.php",{},
        function(data){
            location.href="index.php";
        });
};

User.sessionKeepAlive=function(interval){
    setTimeout(function(){
        $.post("query/session_keep_alive.php",{},function(data){
            User.sessionKeepAlive(interval);
        });
    },interval);
}