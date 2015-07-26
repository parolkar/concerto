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

function Methods() {};

Methods.captionBtnEdit="";
Methods.captionBtnDelete="";
Methods.captionBtnSave="save";
Methods.captionBtnNew="";
Methods.captionBtnPreview="";
Methods.captionBtnCancel="";
Methods.captionBtnLogout="";
Methods.captionBtnImportHTML="";
Methods.captionBtnInfoRFunction="";
Methods.captionBtnInfoItemName="";
Methods.captionBtnInfoItemTemplate="";
Methods.captionBtnInfoItemHash="";
Methods.captionBtnInfoItemTimer="";
Methods.captionBtnInfoSubmitButtons="";
Methods.captionBtnInfoSentVariables="";
Methods.captionBtnInfoAcceptedVariables="";
Methods.captionBtnInfoItemTable="";
Methods.captionBtnInfoItemDefaultButton="";
Methods.captionBtnDebug="";
Methods.captionBtnRun="";
Methods.captionBtnExecute="";
Methods.captionBtnSessionVariables="";
Methods.captionBtnRVariables="";
Methods.captionBtnHomepage="";
Methods.captionBtnGoogleGroup="";
Methods.captionBtnBuiltInFunctionsDoc="";
Methods.captionBtnItemsSessionVariables="";
Methods.captionRequiredFields="";
Methods.captionNotSaved="";
Methods.captionSaved="";
Methods.captionDeleteFileConfirmation="";
Methods.captionDefaultAlertTitle="";
Methods.captionDefaultConfirmationTitle="";

Methods.currentVersion="";
Methods.latestVersion="";
Methods.latestVersionLink="";

Methods.iniIconButtons=function()
{
    $('.btnEdit').button({
        label:Methods.captionBtnEdit,
        icons:{
            primary: "ui-icon-pencil"
        }
    });

    $('.btnDelete').button({
        label:Methods.captionBtnDelete,
        icons:{
            primary: "ui-icon-trash"
        }
    });
	
    $('.btnSave').button({
        label:Methods.captionBtnSave,
        icons:{
            primary: "ui-icon-disk"
        }
    });
	
    $('.btnNew').button({
        label:Methods.captionBtnNew,
        icons:{
            primary: "ui-icon-document"
        }
    });
	
    $('.btnPreview').button({
        label:Methods.captionBtnPreview,
        icons:{
            primary: "ui-icon-search"
        }
    });
	
    $('.btnFirstOnly').button({
        icons:{
            primary: "ui-icon-seek-first"
        },
        text:false
    });
    $('.btnPrevOnly').button({
        icons:{
            primary: "ui-icon-seek-prev"
        },
        text:false
    });
    $('.btnNextOnly').button({
        icons:{
            primary: "ui-icon-seek-next"
        },
        text:false
    });
    $('.btnEndOnly').button({
        icons:{
            primary: "ui-icon-seek-end"
        },
        text:false
    });
    $('.btnCancel').button({
        label:Methods.captionBtnCancel,
        icons:{
            primary: "ui-icon-cancel"
        }
    });
    $(".btnLogout").button({
        icons: {
            primary: "ui-icon-person"
        },
        label:Methods.captionBtnLogout
    });
        
    $( ".btnImportHTML" ).button({
        icons: {
            primary: "ui-icon-link"
        },
        label:Methods.captionBtnImportHTML
    });
	    	
    $( ".btnInfoRFunction" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoRFunction").attr("title",
        Methods.captionBtnInfoRFunction);

    $( ".btnInfoItemName" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemName").attr("title",
        Methods.captionBtnInfoItemName);

    $( ".btnInfoItemTemplate" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemTemplate").attr("title",
        Methods.captionBtnInfoItemTemplate);
        
    $( ".btnInfoItemDefaultButton" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemDefaultButton").attr("title",
        Methods.captionBtnInfoItemDefaultButton);

    $( ".btnInfoItemHash" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemHash").attr("title",
        Methods.captionBtnInfoItemHash);

    $( ".btnInfoItemTimer" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemTimer").attr("title",
        Methods.captionBtnInfoItemTimer);

    $( ".btnInfoSubmitButtons" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoSubmitButtons").attr("title",
        Methods.captionBtnInfoSubmitButtons);

    $( ".btnInfoItemTable" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoItemTable").attr("title",
        Methods.captionBtnInfoItemTable);

    $( ".btnInfoSentVariables" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoSentVariables").attr("title",
        Methods.captionBtnInfoSentVariables);

    $( ".btnInfoAcceptedVariables" ).button({
        icons: {
            primary: "ui-icon-help"
        },
        text:false
    });
    $(".btnInfoAcceptedVariables").attr("title",
        Methods.captionBtnInfoAcceptedVariables);
        
    $('.btnDebug').button({
        label:Methods.captionBtnDebug,
        icons:{
            primary: "ui-icon-lightbulb"
        }
    });
    
    $('.btnRun').button({
        label:Methods.captionBtnRun,
        icons:{
            primary: "ui-icon-play"
        }
    });
    
    $('.btnExecute').button({
        label:Methods.captionBtnExecute,
        icons:{
            primary: "ui-icon-circle-arrow-w"
        }
    });
    
    $('.btnSessionVariables').button({
        label:Methods.captionBtnSessionVariables,
        icons:{
            primary: "ui-icon-star"
        }
    });
    
    $('.btnRVariables').button({
        label:Methods.captionBtnRVariables,
        icons:{
            primary: "ui-icon-star"
        }
    });
    
    $('.btnHomepage').button({
        label:Methods.captionBtnHomepage,
        icons:{
            primary: "ui-icon-link"
        }
    });
    
    $('.btnGoogleGroup').button({
        label:Methods.captionBtnGoogleGroup,
        icons:{
            primary: "ui-icon-link"
        }
    });
    
    $('.btnBuiltInFunctionsDoc').button({
        label:Methods.captionBtnBuiltInFunctionsDoc,
        icons:{
            primary: "ui-icon-help"
        }
    });
    
    $('.btnItemsSessionVariables').button({
        label:Methods.captionBtnItemsSessionVariables,
        icons:{
            primary: "ui-icon-star"
        }
    });
};

Methods.getTempID=function()
{
    var time = new Date().getTime();
    return User.sessionID+"_"+time;
};

Methods.iniListTableExtensions=function(className,pager,filter,cols,listLength)
{
    var ts = $("#table"+className+"List").tablesorter({
        fixedWidth:true
    });
    if(pager)
    {
        ts = ts.tablesorterPager({
            container: $("#table"+className+"ListPager"),
            positionFixed:false,
            size:listLength
        });
    }
    if(filter)
    {
        ts = ts.tablesorterFilter({
            filterContainer: $("#table"+className+"ListPagerFilter"),
            filterClearContainer: $("#table"+className+"ListPagerFilterReset"),
            filterColumns: cols,
            filterCaseSensitive: false
        });
    }
};

Methods.iniCKEditor=function(selector,externalPath)
{  
    var minHeight = 300;
    var clientHeight = $(window).height()-400;
    var height = minHeight;
    if(clientHeight>minHeight) height = clientHeight;
    
    var name = $(selector).attr("name");
    if(CKEDITOR.instances[name]!=null) 
    {
        CKEDITOR.remove(CKEDITOR.instances["htmlEditor"]);
    }
    CKEDITOR.replace(name,{
        height: height,
        filebrowserBrowseUrl : externalPath+'lib/ckeditor/plugins/pgrfilemanager/PGRFileManager.php?langCode=en&type=Link',
        filebrowserImageBrowseUrl : externalPath+'lib/ckeditor/plugins/pgrfilemanager/PGRFileManager.php?langCode=en&type=image',
        filebrowserFlashBrowseUrl : externalPath+'lib/ckeditor/plugins/pgrfilemanager/PGRFileManager.php?langCode=en&type=flash'		 
    });
        
    CKEDITOR.instances[name].on("dataReady",Item.editorHTMLChangedEvent);
    
    CKEDITOR.instances[name].on("blur",Item.editorHTMLChangedEvent);
};

Methods.getCKEditorData=function(selector)
{
    var name = $(selector).attr("name");
    var instance = CKEDITOR.instances[name];
    if(instance) return CKEDITOR.instances[name].getData();
    else return "";
};

Methods.iniCodeMirror=function(id,mode,readOnly)
{
    var obj = document.getElementById(id);
    var myCodeMirror = CodeMirror.fromTextArea(obj,{
        mode:mode,
        fixedGutter:true,
        theme:"night",
        lineNumbers:true,
        matchBrackets:true,
        "readOnly":(readOnly!=null&readOnly?true:false),
        onChange:function(instance){
            instance.save();
            instance.refresh();
        }
    });
    return myCodeMirror;
};

Methods.checkLatestVersion=function(currentVersion,callback,proxy)
{
    Methods.currentVersion = currentVersion;
    jQuery.getFeed({
        url: proxy==null?'../lib/jfeed/proxy.php':proxy,
        data: {
            url:"http://code.google.com/feeds/p/concerto-platform/downloads/basic"
        },
        success: function(feed) {
            var max=Methods.currentVersion;
            var link="";
            var isNewerVersion=false;
            for(var i=0;i<feed.items.length;i++) 
            {
                var desc = feed.items[i].description;
                var version = desc.substr(desc.indexOf("Source-Version:")+15);
                version = version.substr(0,version.indexOf("\n"));
                
                var amax = max.split(".");
                var avers = version.split(".");
                
                for(var a=0;a<3;a++)
                {
                    if(parseInt(amax[a])>parseInt(avers[a])) break;
                    if(parseInt(amax[a])<parseInt(avers[a])) 
                    {
                        max=version;
                        link = feed.items[i].link;
                        isNewerVersion = true;
                        break;
                    }
                }
            }
            
            Methods.latestVersion=max;
            Methods.latestVersionLink=link; 
            
            callback.call(this,isNewerVersion?1:0,Methods.latestVersion,Methods.latestVersionLink);
        }
    });  
};

Methods.confirm=function(message,title,callback)
{
    if(title==null) title=Methods.captionDefaultConfirmationTitle;
    $("#divGeneralDialog").html('<span class="ui-icon ui-icon-help" style="float:left; margin:0 7px 0px 0;"></span>'+message);
    $("#divGeneralDialog").dialog({
        title:title,
        minHeight:50,
        show:"fade",
        hide:"fade",
        resizable:false,
        modal:true,
        buttons:
        {
            no:function(){
                $(this).dialog("close");
            },
            yes:function(){
                callback.call(this);
                $(this).dialog("close");
            }
        }
    });
};

Methods.alert=function(message,icon,title)
{
    if(title==null) title=Methods.captionDefaultAlertTitle;
    $("#divGeneralDialog").html((icon!=null?'<span class="ui-icon ui-icon-'+icon+'" style="float:left; margin:0 7px 0px 0;"></span>':'')+message);
    $("#divGeneralDialog").dialog({
        title:title,
        minHeight:50,
        show:"fade",
        hide:"fade",
        resizable:false,
        modal:true,
        buttons:
        {
            ok:function(){
                $(this).dialog("close");
            }
        }
    });
}
