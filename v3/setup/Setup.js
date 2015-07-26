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

function Setup() { };
Setup.path_external = "";

Setup.steps=[];

Setup.continueSteps = true;
Setup.currentStep=-1;
Setup.maxStep = 0;

Setup.dbBackupReminderShown = false;
Setup.dbPhaseReached = false;
Setup.continueDBSteps = true;
Setup.currentDBStep = -1;
Setup.maxDBStep = 0;
    
Setup.initialize=function(){
    $("#divSetupProgressBar").progressbar();
    $("#divSetupDBProgressBar").progressbar();
}
    
Setup.run=function(){
    if(!Setup.continueSteps) {
        Setup.failure();
        return;
    }
    Setup.currentStep++;
    if(Setup.currentStep==Setup.maxStep) {
        Setup.success();
        return;
    }
    Setup.steps[Setup.currentStep].check();
}

Setup.updateProgressBar=function(title,db){
    if(db==null) db=false;
    var value = (db?Setup.currentDBStep:Setup.currentStep)+1;
    var maxValue = (db?Setup.maxDBStep:Setup.maxStep);
    $("#divSetup"+(db?"DB":"")+"ProgressBar").progressbar("value",Math.floor(value/maxValue*100));
    $("#tdCurrent"+(db?"DB":"")+"Step").html(title);
}

Setup.failure=function(){
    $("body").append('<h1 class="ui-state-error" align="center">Please correct your problems using recommendations and run the test again.</h1>');
    
    $("#tdLoadingStep").css("visibility","hidden");
    $("#tdCurrentStep").html("<font style='color:red'><b>failed to finish</b></font>");
    
    if(!Setup.dbPhaseReached) {
        $("#tdLoadingDBStep").css("visibility","hidden");
        $("#tdCurrentDBStep").html("<font style='color:red'><b>not started</b></font>");
    }
}

Setup.success=function(){
    $("body").append('<h1 class="" align="center" style="color:green;">Test completed. Every item passed correctly.</h1>');
    $("body").append("<h1 class='ui-state-highlight' align='center' style='color:blue;'>IT IS STRONGLY RECOMMENDED TO DELETE THIS <b>/setup</b> DIRECTORY NOW!</h1>");
    $("body").append('<h2 class="" align="center"><a href="'+Setup.path_external+'cms/index.php">click here to launch Concerto Platform panel</a> - if this is fresh installation of Concerto then default admin account is <b>login:admin/password:admin</b></h2>');
    
    $("#tdLoadingStep").css("visibility","hidden");
    $("#tdCurrentStep").html("<font style='color:green'><b>finished successfuly</b></font>");
}
    
Setup.insertCheckRow=function(title,db){
    if(db==null) db=false;
    var row = "<tr id='row"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"'>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-0'>"+title+"</td>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-1'>please wait...</td>"+
    "<td class='ui-widget-content' id='col"+(db?"DB":"")+"-"+(db?Setup.currentDBStep:Setup.currentStep)+"-2'>-</td>"+
    "</tr>";
    if(db){
        $(row).insertBefore("#row-7");
    } else {
        $("#tbodySetup").append(row);
    }
}

Setup.check=function(obj,check,success,failure){
    if(check=="concerto_version"){
        Methods.checkLatestVersion(function(isNewerVersion,version){
            if(isNewerVersion==1) failure.call(obj,version);
            else success.call(obj,version);
        },"../cms/lib/jfeed/proxy.php");
        return;
    }
    
    if(check=="getDBSteps"){
        Setup.dbPhaseReached = true;
        Setup.getDBSteps(obj,success,failure);
        return;
    }
    
    var jqXHR = $.post("Setup.php",{
        check:check
    },function(data){
        if(data.result==undefined){
            obj.error(jqXHR.responseText);
            return;
        }
        switch(data.result){
            case 0:{
                success.call(obj,data.param);
                break;
            }
            default:{
                failure.call(obj,data.param);
                break;
            }
        }
    },"json").error(function(data){
        obj.error(jqXHR.responseText);
    });
}

Setup.versions = [];
Setup.create_db = false;
Setup.validate_column_names = false;
Setup.repopulate_TestTemplate = false;
Setup.recalculate_hash = false;
Setup.getDBSteps=function(obj,success,failure){
    if(obj!=null) SetupStep.parentDBObj = obj;
    if(success!=null) SetupStep.parentDBSuccess = success;
    if(failure!=null) SetupStep.parentDBFailure = failure;
    
    $.post("Setup.php",{
        check:"get_db_update_steps_count"
    },function(data){
        var count = 0;
        if(data.create_db) count++;
        count+=data.versions.length;
        if(data.validate_column_names) count++;
        if(data.repopulate_TestTemplate) count++;
        if(data.recalculate_hash) count++;
        Setup.maxDBStep = count;
        Setup.create_db = data.create_db;
        Setup.versions = data.versions;
        Setup.validate_column_names = data.validate_column_names;
        Setup.repopulate_TestTemplate = data.repopulate_TestTemplate;
        Setup.recalculate_hash = data.recalculate_hash;
        
        Setup.runDB();
    },"json");
}

Setup.runDB=function(){

    if(!Setup.continueDBSteps) {
        Setup.failureDB();
        return;
    }
    Setup.currentDBStep++;
    if(Setup.currentDBStep==Setup.maxDBStep) {
        Setup.successDB();
        return;
    }
    
    var offset = 0;
    if(Setup.create_db){
        offset = 1;
        if(Setup.currentDBStep==0) {
            Setup.createDBStep.check();
            return;
        }
    }
    
    if(Setup.currentDBStep<Setup.versions.length+offset) {
        if(!Setup.dbBackupReminderShown){
            Methods.alert("Database update will start shortly. Please backup your database before continuing.", "alert","database update", function(){
                Setup.dbBackupReminderShown = true;
                Setup.updateDBStep.check(Setup.versions[Setup.currentDBStep-offset]);
            });
        } else Setup.updateDBStep.check(Setup.versions[Setup.currentDBStep-offset]);
    }
    else {
        if(Setup.validate_column_names){
            Setup.validate_column_names=false;
            Setup.validateColumnsDBStep.check();
            return;
        }
        if(Setup.repopulate_TestTemplate){
            Setup.repopulate_TestTemplate=false;
            Setup.repopulateTestTemplateDBStep.check();
            return;
        }
        if(Setup.recalculate_hash){
            Setup.recalculate_hash=false;
            Setup.recalculateHashDBStep.check();
            return;
        }
    };
}

Setup.failureDB=function(){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:red'><b>failed to finish</b></font>");
    
    SetupStep.parentDBFailure.call(SetupStep.parentDBObj);
}

Setup.successDB=function(){
    $("#tdLoadingDBStep").css("visibility","hidden");
    $("#tdCurrentDBStep").html("<font style='color:green'><b>finished successfuly</b></font>");
    
    SetupStep.parentDBSuccess.call(SetupStep.parentDBObj);
}

function SetupStep(db,title,method,successCaption,failureCaption,failureReccomendation,required,successCallback,failureCallback){
    this.db = false;
    if(db!=null) this.db = db;
    this.title = "";
    if(title!=null) this.title = title;
    this.method = "";
    if(method!=null) this.method = method;
    this.successCaption = "";
    if(successCaption!=null) this.successCaption = successCaption;
    this.failureCaption = "";
    if(failureCaption!=null) this.failureCaption = failureCaption;
    this.failureReccomendation = "";
    if(failureReccomendation!=null) this.failureReccomendation = failureReccomendation;
    this.required = true;
    if(required!=null) this.required = required;
    this.successCallback = function(){};
    if(successCallback!=null) this.successCallback = successCallback;
    this.failureCallback = function(){}
    if(failureCallback!=null) this.failureCallback = failureCallback;
    
    this.check=function(param){
        if(param==null) param="";
        Setup.insertCheckRow(this.title.format(param),this.db);
        Setup.updateProgressBar(this.title.format(param),this.db);
        
        Setup.check(this,this.method, this.success, this.failure)
    }
    
    this.success=function(param){
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").html(this.successCaption.format(param));
        if(this.db){
            Setup.runDB();
        } else {
            Setup.run();
        }
        this.successCallback();
    }
    
    this.error=function(param){
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").removeClass("ui-state-highlight");
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").addClass("ui-state-error");
    
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").html("Error encountered: {0}".format(param));
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-2").html("Please contact us about this problem.");
    
        if(this.db){
            Setup.continueDBSteps = !this.required;
            Setup.runDB();
        } else {
            Setup.continueSteps = !this.required;
            Setup.run();
        }
        this.failureCallback();
    }
    
    this.failure=function(param){
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").removeClass("ui-state-highlight");
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").addClass("ui-state-error");
    
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-1").html(this.failureCaption.format(param));
        $("#col"+(this.db?"DB":"")+"-"+(this.db?Setup.currentDBStep:Setup.currentStep)+"-2").html(this.failureReccomendation.format(param));
    
        if(this.db){
            Setup.continueDBSteps = !this.required;
            Setup.runDB();
        } else {
            Setup.continueSteps = !this.required;
            Setup.run();
        }
        this.failureCallback();
    }
}
SetupStep.parentDBObj=null;
SetupStep.parentDBSuccess=null;
SetupStep.parentDBFailure=null;

Setup.steps = [
    new SetupStep(
        false,
        "Check for the latest <b>Concerto Platform</b> version",
        "concerto_version",
        "your current version: <b>v{0}</b> <b style='color:green;'>IS UP TO DATE</b>",
        "newer version is available: <b>v{0}</b>. Your current version <b style='color:red;'>IS OUTDATED</b>",
        "You can find the latest version at the link below:<br/><a href='http://code.google.com/p/concerto-platform'>http://code.google.com/p/concerto-platform</a>",
        false
        ),
    new SetupStep(
        false,
        "PHP version at least <b>v5.3</b>",
        "php_version_check",
        "your PHP version: <b>{0}</b> - <b style='color:green;'>PASSED</b>",
        "your PHP version: <b>{0}</b> - <b style='color:red;'>FAILED</b>",
        "Update your PHP to v5.3 or higher.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'safe mode'</b> must be turned <b>OFF</b>",
        "php_safe_mode_check",
        "your PHP <b>'safe mode'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'safe mode'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'safe mode' OFF.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'magic quotes'</b> must be turned <b>OFF</b>",
        "php_magic_quotes_check",
        "your PHP <b>'magic quotes'</b> is turned <b>OFF</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'magic quotes'</b> is turned <b>ON</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'magic quotes' OFF.",
        true     
        ),
    new SetupStep(
        false,
        "PHP <b>'short open tags'</b> must be turned <b>ON</b>",
        "php_short_open_tag_check",
        "your PHP <b>'short open tags'</b> is turned <b>ON</b> - <b style='color:green;'>PASSED</b>",
        "your PHP <b>'short open tags'</b> is turned <b>OFF</b> - <b style='color:red;'>FAILED</b>",
        "Ask your server administrator to turn PHP 'short open tags' ON.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> connection test",
        "mysql_connection_check",
        "{0} <b>CONNECTED</b> - <b style='color:green;'>PASSED</b>",
        "{0} <b>CAN'T CONNECT</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>db_host, db_port, db_user, db_password</b> in /SETTINGS.php file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> database connection test",
        "mysql_select_db_check",
        "<b>MySQL</b> database <b>{0}</b> <b>IS CONNECTABLE</b> - <b style='color:green;'>PASSED</b>",
        "<b>MySQL</b> database <b>{0}</b> <b>IS NOT CONNECTABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>db_name</b> in <b>/SETTINGS.php</b> file. Check if database name is correct and if it is - check if MySQL user has required permissions to access this database.",
        true     
        ),
    new SetupStep(
        false,
        "<b>MySQL</b> database tables structure test",
        "getDBSteps",
        "<b>MySQL</b> database tables structure <b>IS CORRECT</b> - <b style='color:green;'>PASSED</b>",
        "<b>MySQL</b> database tables structure <b>IS NOT CORRECT</b> - <b style='color:red;'>FAILED</b>",
        "Setup application was unable to create valid database structure. Please restore database from the backup and revert Concerto to previous version.",
        true     
        ),
    new SetupStep(
        false,
        "<b>Rscript</b> file path must be set.",
        "rscript_check",
        "your <b>Rscript</b> file path: <b>{0}</b> <b>EXISTS</b> - <b style='color:green;'>PASSED</b>",
        "your <b>Rscript</b> file path: <b>{0}</b> <b>DOESN'T EXISTS</b> - <b style='color:red;'>FAILED</b>",
        "Rscript file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the Rscript file path is <b>/usr/bin/Rscript</b>. Set your Rscript path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>PHP</b> executable file path must be set.",
        "php_exe_path_check",
        "your <b>PHP</b> executable file path: <b>{0}</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>",
        "your <b>PHP</b> executable file path: <b>{0}</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>",
        "PHP executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the PHP executable file path is <b>/usr/bin/php</b>. Set your PHP executable path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "<b>R</b> executable file path must be set.",
        "R_exe_path_check",
        "your <b>R</b> executable file path: <b>{0}</b> <b>EXIST</b> - <b style='color:green;'>PASSED</b>",
        "your <b>R</b> executable file path: <b>{0}</b> <b>DOESN'T EXIST</b> - <b style='color:red;'>FAILED</b>",
        "R executable file path not set, set incorrectly or unaccesible to PHP.<br/>Usually the R executable file path is <b>/usr/bin/R</b>. Set your R executable path in <b>/SETTINGS.php</b> file.",
        true     
        ),
    new SetupStep(
        false,
        "R version at least <b>v2.15</b>",
        "r_version_check",
        "your R version: <b>{0}</b> - <b style='color:green;'>PASSED</b>",
        "your R version: <b>{0}</b> - <b style='color:red;'>FAILED</b>",
        "Update your R to v2.15 or higher.",
        true     
        ),
    new SetupStep(
        false,
        "<b>media</b> directory path must be writable",
        "media_directory_writable_check",
        "your <b>media</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>media</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>media</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>socks</b> directory path must be writable",
        "socks_directory_writable_check",
        "your <b>socks</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>socks</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>socks</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>temp</b> directory path must be writable",
        "temp_directory_writable_check",
        "your <b>temp</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>temp</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>temp</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>files</b> directory path must be writable",
        "files_directory_writable_check",
        "your <b>files</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>files</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>files</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>thumbnails</b> directory path must be writable",
        "thumbnails_directory_writable_check",
        "your <b>thumbnails</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>thumbnails</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>thumbnails</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>cache</b> directory path must be writable",
        "cache_directory_writable_check",
        "your <b>cache</b> directory: <b>{0}</b> <b>IS WRITABLE</b> - <b style='color:green;'>PASSED</b>",
        "your <b>cache</b> directory: <b>{0}</b> <b>IS NOT WRITABLE</b> - <b style='color:red;'>FAILED</b>",
        "Set <b>cache</b> directory rigths to 0777.",
        true     
        ),
    new SetupStep(
        false,
        "<b>catR</b> R package must be installed.",
        "catR_r_package_check",
        "<b>catR</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>catR</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>catR</b> package to main R library directory.",
        true     
        ),
    new SetupStep(
        false,
        "<b>session</b> R package must be installed.",
        "session_r_package_check",
        "<b>session</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>session</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>session</b> package to main R library directory.",
        true     
        ),
    new SetupStep(
        false,
        "<b>RMySQL</b> R package must be installed.",
        "RMySQL_r_package_check",
        "<b>RMySQL</b> package <b>IS INSTALLED</b> - <b style='color:green;'>PASSED</b>",
        "<b>RMySQL</b> package <b>IS NOT INSTALLED</b> - <b style='color:red;'>FAILED</b>",
        "Install <b>RMySQL</b> package to main R library directory.",
        true     
        )
    ];
Setup.maxStep = Setup.steps.length;

Setup.createDBStep = new SetupStep(
    true,
    "<b>MySQL</b> database update - create missing tables",
    "create_db",
    "<b>MySQL</b> database update - create  missing tables - <b style='color:green;'>PASSED</b>",
    "<b>MySQL</b> database update - create missing tables - <b style='color:red;'>FAILED</b>",
    "Setup application was unable to create missing database tables.",
    true
    );

Setup.updateDBStep = new SetupStep(
    true,
    "<b>MySQL</b> database update to <b>v{0}</b>",
    "update_db",
    "<b>MySQL</b> database update to <b>v{0}</b> - <b style='color:green;'>PASSED</b>",
    "<b>MySQL</b> database update failed with '<b>{0}</b>' - <b style='color:red;'>FAILED</b>",
    "Setup application was unable to create valid database structure.",
    true
    );

Setup.validateColumnsDBStep = new SetupStep(
    true,
    "<b>MySQL</b> database - validate column names",
    "update_db_validate_column_names",
    "<b>MySQL</b> database update - validate column names - <b style='color:green;'>PASSED</b>",
    "<b>MySQL</b> database - validate column names - <b style='color:red;'>FAILED</b>",
    "Setup application was unable to validate column names.",
    true
    );

Setup.repopulateTestTemplateDBStep = new SetupStep(
    true,
    "<b>MySQL</b> database - repopulate TestTemplate",
    "update_db_repopulate_TestTemplate",
    "<b>MySQL</b> database update - repopulate TestTemplate - <b style='color:green;'>PASSED</b>",
    "<b>MySQL</b> database - repopulate TestTemplate - <b style='color:red;'>FAILED</b>",
    "Setup application was unable to repopulate TestTemplate.",
    true
    );

Setup.recalculateHashDBStep = new SetupStep(
    true,
    "<b>MySQL</b> database - recalculate hash",
    "update_db_recalculate_hash",
    "<b>MySQL</b> database update - recalculate hash - <b style='color:green;'>PASSED</b>",
    "<b>MySQL</b> database update - recalculate hash - <b style='color:red;'>FAILED</b>",
    "Setup application was unable to recalculate hash.",
    true
    );
