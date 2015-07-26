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

function OModule() {};

OModule.inheritance=function(obj)
{
    obj.currentID=0;
    obj.listLength=20;
    obj.reloadOnModification=false;
    obj.reloadHash="";
    obj.currentPanel = "list";
    
    obj.uiChangeListLength=function(length)
    {
        this.listLength=length;
    };
	
    obj.uiReload=function(oid)
    {
        this.uiEdit(oid);
        this.uiList();
    };
    
    obj.checkRequiredFields=function(fields){
        for(var i=0;i<fields.length;i++){
            if(fields[i]=="") return false;
        }
        return true;
    }
    
    obj.download=function(oid){
        var thisClass = this;
        $.post("query/download_object.php",{
            class_name:this.className,
            oid:oid
        },function(data){
            switch(data.result){
                case OModule.queryResults.OK:{
                    $("#divDialogDownload").dialog("close");
                    Methods.alert(dictionary["s388"], "info", dictionary["s387"]);
                    if(thisClass.onAfterImport) thisClass.onAfterImport();
                    thisClass.uiReload(data.oid);
                    break;
                }
                case OModule.queryResults.notLoggedIn:{
                    thisClass.onNotLoggedIn(dictionary["s387"]);
                    break;
                }
                case -2:{
                    Methods.alert(dictionary["s389"], "alert", dictionary["s387"]);
                    break;
                }
            }
        },"json");
    }
    
    obj.crudUpdate = function(collection,id){
    
        var found = false;
        for(var i=0;i<collection.length;i++){
            if(collection[i]==id){
                found = true;
                break;
            }
        }
        if(!found){
            collection.push(id);
        }
    }
    
    obj.uiDownload=function(){
        var thisClass = this;
        
        $("#divDialogDownload").dialog({
            modal:true,
            resizable:false,
            title:dictionary["s387"],
            width:950,
            open:function(){
                $('.ui-widget-overlay').css('position', 'fixed');
                $("#divDialogDownload").html("<fieldset class='padding ui-widget-content ui-corner-all margin'><legend><table><tr><td><span class='tooltip spanIcon ui-icon ui-icon-help' title='"+dictionary["s498"]+"'></span></td><td class=''><b>"+dictionary["s497"]+"</b></td></tr></table></legend><div id='divDialogDownloadGrid'></div></fieldset>");
                $("#divDialogDownloadGrid").kendoGrid({
                    dataBound:function(e){
                        Methods.iniTooltips();
                        if(this.dataSource.group().length == 0) {
                            setTimeout( function() {
                                $(".k-grouping-header").html(dictionary["s339"]);
                            });
                        }
                    },
                    dataSource: {
                        transport:{
                            read: {
                                url:"query/get_library_list.php?class_name="+thisClass.className,
                                dataType:"json"
                            }
                        },
                        schema:{
                            model:{
                                id: "id",
                                fields:{
                                    id: {
                                        type: "number"
                                    },
                                    description: {
                                        type: "string"
                                    },
                                    name: {
                                        type:"string"
                                    },
                                    author: {
                                        type:"string"
                                    },
                                    revision: {
                                        type:"string"
                                    },
                                    uploaded: {
                                        type:"string"
                                    },
                                    count: {
                                        type:"number"
                                    }
                                }
                            }
                        },
                        pageSize:10
                    },
                    filterable:{
                        messages: {
                            info: dictionary["s340"],
                            filter: dictionary["s341"],
                            clear: dictionary["s342"],
                            and: dictionary["s227"],
                            or: dictionary["s228"]
                        },
                        operators: {
                            string: {
                                contains: dictionary["s344"],
                                eq: dictionary["s222"],
                                neq: dictionary["s221"],
                                startswith: dictionary["s343"],
                                endswith: dictionary["s345"]
                            },
                            number: {
                                eq: dictionary["s222"],
                                neq: dictionary["s221"],
                                gte: dictionary["s224"],
                                gt: dictionary["s223"],
                                lte: dictionary["s226"],
                                lt: dictionary["s225"]
                            }
                        }
                    },
                    columnMenu:{
                        messages: {
                            filter: dictionary["s341"],
                            columns: dictionary["s533"],
                            sortAscending: dictionary["s534"],
                            sortDescending: dictionary["s535"]
                        }  
                    },
                    sortable:true,
                    pageable: {
                        refresh:true,
                        pageSizes:true,
                        messages: {
                            display: dictionary["s527"],
                            empty: dictionary["s528"],
                            page: dictionary["s529"],
                            of: dictionary["s530"],
                            itemsPerPage: dictionary["s531"],
                            first: dictionary["s523"],
                            previous: dictionary["s524"],
                            next: dictionary["s525"],
                            last: dictionary["s526"],
                            refresh: dictionary["s532"]
                        }
                    },
                    groupable:true,
                    scrollable:false,
                    resizable: true,
                    columns:[
                    {
                        title: dictionary["s371"],
                        width: 60,
                        template: "<span class='spanIcon ui-icon ui-icon-help tooltip' title='${description}'></span>",
                        field: "description",
                        filterable: false,
                        sortable: false,
                        groupable: false
                    },
                    {
                        title: dictionary["s69"],
                        width: 60,
                        field: "id",
                        filterable: true,
                        sortable: true,
                        groupable: false
                    },
                    {
                        title: dictionary["s70"],
                        field: "name",
                        filterable: true,
                        sortable: true,
                        groupable: false
                    },
                    {
                        title: dictionary["s378"],
                        field: "author",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s379"],
                        field: "revision",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s385"],
                        field: "uploaded",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title: dictionary["s386"],
                        field: "count",
                        filterable: true,
                        sortable: true,
                        groupable: true
                    },
                    {
                        title:'', 
                        width:30, 
                        filterable: false, 
                        sortable: false, 
                        groupable: false, 
                        template: "<span style='display:inline-block;' class='spanIcon tooltip ui-icon ui-icon-gear' onclick='"+thisClass.className+".download(${ id })' title='"+dictionary["s374"]+"'></span>"
                    }
                    ]
                });
            
                Methods.iniIconButton(".btnDownload","gear");
                
                $("#divDialogDownload").dialog("option","position","center"); 
            },
            close:function(){
            //$('.ui-widget-overlay').css('position', 'absolute');
            },
            buttons:[
            {
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }
            ]
        })
    };
    
    obj.upload=function(oid){
        var thisClass = this;
        $.post("query/upload_object.php",{
            class_name:this.className,
            oid:oid,
            name:$("#inputDialogUploadName").val(),
            description:Methods.getCKEditorData("#textareaDialogUploadDescription"),
            author:$("#inputDialogUploadAuthor").val(),
            revision:$("#inputDialogUploadRevision").val()
        },function(data){
            switch(parseInt(data.result)){
                case OModule.queryResults.OK:{
                    $("#divDialogUpload").dialog("close");
                    Methods.alert(dictionary["s384"], "info", dictionary["s382"]);
                    break;
                }
                case OModule.queryResults.notLoggedIn:{
                    thisClass.onNotLoggedIn(dictionary["s382"]);
                    break;
                }
                default: {
                    Methods.alert(dictionary["s444"], "alert", dictionary["s382"]);
                    break; 
                }
            }
        },"json");
    }
    
    obj.uiUpload=function(oid){
        var thisClass = this;
        $.post("view/upload_form.php",{
            class_name:thisClass.className,
            oid:oid
        },function(data){
            $("#divDialogUpload").html(data);
            $("#divDialogUpload").dialog({
                modal:true,
                resizable:false,
                title:dictionary["s382"],
                width:950,
                open:function(){
                    $('.ui-widget-overlay').css('position', 'fixed');
                    Methods.iniCKEditor("#textareaDialogUploadDescription", function(){
                        $("#divDialogUpload").dialog("option","position","center"); 
                    },800)
                },
                close:function(){
                //$('.ui-widget-overlay').css('position', 'absolute');
                },
                buttons:[
                {
                    text:dictionary["s383"],
                    click:function(){
                        thisClass.upload(oid);
                    }
                },
                {
                    text:dictionary["s23"],
                    click:function(){
                        $(this).dialog("close");
                    }
                }
                ]
            })
        })
    };
    
    obj.uiAdd=function(ignoreOnBefore)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        if(thisClass.onBeforeAdd && !ignoreOnBefore) {
            if(!thisClass.onBeforeAdd()) return;
        }
        
        if(this.currentID!=0) this.uiEdit(0,null,function(){
            obj.uiShowAddDialog();
        });
        else obj.uiShowAddDialog();
    }
    
    obj.uiShowAddDialog=function(){
        var thisClass = this;
        
        $.post("view/"+this.className+"_form.php",{
            oid:-1
        },function(data){
            $("#divAddFormDialog").html(data);
            $("#divAddFormDialog").dialog({
                modal:true,
                resizable:false,
                title:dictionary["s7"],
                width:925,
                open:function(){
                    $('.ui-widget-overlay').css('position', 'fixed');
                    if(thisClass.onAfterAdd) thisClass.onAfterAdd();
                },
                close:function(){
                //$('.ui-widget-overlay').css('position', 'absolute');
                },
                buttons:[
                {
                    text:dictionary["s95"],
                    click:function(){
                        thisClass.uiSave();
                    }
                },
                {
                    text:dictionary["s23"],
                    click:function(){
                        $(this).dialog("close");
                    }
                }
                ]
            })
        });
    }
    
    obj.uiShowForm=function(){
        if(this.currentPanel=="form") return;
        $("#div"+this.className+"List").hide();
        $("#div"+this.className+"Form").show();
        $("#radio"+this.className+"List").removeAttr("checked");
        $("#radio"+this.className+"Form").attr("checked","checked");
        $( "#div"+this.className+"RadioMenu" ).buttonset("refresh"); 
        this.currentPanel="form";
    }
    
    obj.uiShowList=function(){
        if(this.currentPanel=="list") return;
        $("#div"+this.className+"Form").hide();
        $("#div"+this.className+"List").show();
        $("#radio"+this.className+"Form").removeAttr("checked");
        $("#radio"+this.className+"List").attr("checked","checked");
        $( "#div"+this.className+"RadioMenu" ).buttonset("refresh"); 
        this.currentPanel="list";
    }
	
    obj.uiEdit=function(oid,ignoreOnBefore,callback)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        if(thisClass.onBeforeEdit && !ignoreOnBefore) {
            if(!thisClass.onBeforeEdit()) return;
        }
		
        this.currentID=oid;
        
        if(this.currentID>0) {
            $("#radio"+this.className+"Form").button("enable");
            $("#radio"+this.className+"Form").button("option","label",dictionary["s338"]+" #"+this.currentID);
            this.uiShowForm();
        }
        else {
            $("#radio"+this.className+"Form").button("disable");
            $("#radio"+this.className+"Form").button("option","label",dictionary["s338"]+" "+dictionary["s73"]);
            this.uiShowList();
        }
        
        Methods.uiBlockModule(thisClass.className);
        $.post("view/"+this.className+"_form.php",
        {
            oid:oid
        },
        function(data){
            Methods.uiUnblockModule(thisClass.className);
            $("#div"+thisClass.className+"Form").html(data);
            if(thisClass.onAfterEdit) thisClass.onAfterEdit();
            if(callback!=null) callback.call(thisClass);
        });
    };
	
    obj.uiList=function()
    {
        var thisClass = this;
        Methods.uiBlock("#div"+thisClass.className+"List");
        var grid = $("#div"+thisClass.className+"Grid").data("kendoGrid");
        grid.dataSource.read(); 
        grid.refresh();
        Methods.uiUnblock("#div"+thisClass.className+"List");
    };
	
    obj.uiDelete=function(oid,ignoreOnBefore)
    {
        var isArray = false;
        if(oid instanceof Array) isArray = true;
        
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        
        if(thisClass.onBeforeDelete && !ignoreOnBefore) {
            if(!thisClass.onBeforeDelete(oid)) return;
        }
        
        var question = dictionary["s8"].format(oid);
        if(isArray) question = dictionary["s517"];
        
        Methods.confirm(question,null,function(){
            if(thisClass.reloadOnModification) { 
                Methods.uiBlockAll();
            }
            
            var objEdited = false;
            if(isArray){
                if(thisClass.isCheckedList(thisClass.currentID)) objEdited = true;
            } else {
                if(oid==thisClass.currentID) objEdited = true;
            }
            
            if(objEdited && !thisClass.reloadOnModification) thisClass.uiEdit(0);
            $.post("query/delete_object.php",
            {
                class_name:thisClass.className,
                oid:oid
            },
            function(data)
            {
                switch(data.result){
                    case OModule.queryResults.OK:{
                        if(!isArray) thisClass.uiListCheckRemove(oid);
                        else {
                            thisClass.checkedList = [];
                            thisClass.uiRefreshCheckedList();
                        }
                        if(!thisClass.reloadOnModification) {
                            thisClass.uiList();
                            if(thisClass.onAfterDelete) thisClass.onAfterDelete();
                        }
                        else {
                            Methods.reload(thisClass.reloadHash);
                        }
                        break;
                    }
                    case OModule.queryResults.notLoggedIn:{
                        thisClass.onNotLoggedIn(dictionary["s273"]);
                        break;
                    }
                }
            },"json");
        });
    };
    
    obj.uiImport=function(){
        var thisClass = this;
        $("#div"+this.className+"DialogImport").dialog({
            title:dictionary["s268"],
            modal:true,
            resizable:false,
            minHeight: 50,
            close:function(){
            //$('.ui-widget-overlay').css('position', 'absolute');
            },
            beforeClose:function(){
            
            },
            open:function(){
                $('.ui-widget-overlay').css('position', 'fixed');
                $('#file'+thisClass.className+'Import').fileupload({
                    dataType: 'json',
                    //maxChunkSize: 1000000,
                    url: 'js/lib/fileupload/php/index.php',
                    formData:function(form){
                        return [{
                            name:"class_name",
                            value:thisClass.className
                        }]  
                    },
                    send: function (e, data) {
                        Methods.modalProgress();
                    },
                    progress: function(e,data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        Methods.changeProgress(progress);
                    },
                    done: function (e, data) {
                        $.each(data.result, function (index, file) {
                            Table.isFileUploaded = true;
                            Methods.confirm(dictionary["s269"], dictionary["s29"], function(){
                                Methods.uiBlock($("#div"+thisClass.className+"DialogImport").parent());
                                $.post("query/import_object.php",{
                                    class_name:thisClass.className,
                                    file:file.name
                                },function(data){
                                    Methods.uiUnblock($("#div"+thisClass.className+"DialogImport").parent());
                                    $("#div"+thisClass.className+"DialogImport").dialog("close");
                                    switch(data.result){
                                        case OModule.queryResults.OK:{
                                            Methods.alert(dictionary["s270"], "info", dictionary["s268"]);
                                            thisClass.uiReload(data.oid);
                                            if(thisClass.onAfterImport) thisClass.onAfterImport();
                                            break;
                                        }
                                        case OModule.queryResults.notLoggedIn:{
                                            thisClass.onNotLoggedIn(dictionary["s268"]);
                                            break;
                                        }
                                        case -3:{
                                            Methods.alert(dictionary["s271"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                        //incorrect file
                                        case -4:{
                                            Methods.alert(dictionary["s333"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                        //version mismatch
                                        case -5:{
                                            Methods.alert(dictionary["s370"], "alert", dictionary["s268"]);
                                            break;
                                        }
                                        //transaction error
                                        case OModule.queryResults.transactionError:{
                                            Methods.alert(dictionary["s616"]+data.message, "alert", dictionary["s268"]);  
                                            break;
                                        }
                                    }
                                },"json");
                            });
                        });
                    }
                });
            },
            buttons:[{
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }]
        }); 
    }
    
    obj.uiExport=function(oid){
        var param = oid;
        if(oid instanceof Array){
            param = "";
            for(var i=0;i<oid.length;i++){
                param+="&oid[]="+oid[i];
            }
        } else {
            param="&oid="+oid;
        }
        location.href="query/export_object.php?class_name="+this.className+param;
    };
    
    obj.getMessageSuccessfulSave = function(){
        return dictionary["s9"];
    }
    obj.uiSaveValidated=function(ignoreOnBefore,isNew){
        var thisClass = this;
            
        if(thisClass.onBeforeSave && !ignoreOnBefore) {
            if(!thisClass.onBeforeSave(isNew)) return;
        }
		
        if(thisClass.reloadOnModification) { 
            //Methods.uiBlockAll();
        } else {
        
            if(isNew) Methods.uiBlock($("#divAddFormDialog").parent());
            else Methods.uiBlockModule(thisClass.className);
        }
        
        var params = {};
        if(this.currentID==0&&!isNew) params = this.getAddSaveObject();
        else params = this.getFullSaveObject(isNew);
        if(isNew) params['oid']=0;
        
        $.post("query/save_object.php",
            params,
            function(data)
            {
                if(isNew) Methods.uiUnblock($("#divAddFormDialog").parent());
                else Methods.uiUnblockModule(thisClass.className);
                if(thisClass.currentID==0) $("#divAddFormDialog").dialog("close");
                
                switch(data.result){
                    case OModule.queryResults.OK:{
                        if(data.oid!=0)
                        {
                            var isNewObject = false;
                            if(thisClass.currentID==0||isNew) isNewObject = true;
                            if(!thisClass.reloadOnModification) { 
                                if(thisClass.currentID!=0&&!isNew) thisClass.uiList();
                                else thisClass.uiReload(data.oid);
                            }
                            Methods.alert(thisClass.getMessageSuccessfulSave(isNewObject),"info", dictionary["s274"],function(){
                                if(thisClass.reloadOnModification) {
                                    Methods.reload(thisClass.reloadHash);
                                }
                                if(thisClass.onAfterSave) thisClass.onAfterSave(isNewObject);
                            });
                        }
                        else {
                            Methods.alert(dictionary["s10"],"alert", dictionary["s274"]);
                        }
                        break;
                    }
                    case OModule.queryResults.notLoggedIn:{
                        thisClass.onNotLoggedIn(dictionary["s274"]);
                        break;     
                    }
                    case OModule.queryResults.transactionError:{
                        if(thisClass.reloadOnModification) Methods.uiUnblockAll();
                        Methods.alert(dictionary["s616"]+data.message, "alert", dictionary["s274"]);  
                        break;
                    }
                }
            },"json");
    }
	
    obj.uiSave=function(ignoreOnBefore,isNew)
    {
        if(ignoreOnBefore==null) ignoreOnBefore=false;
        var thisClass = this;
        
        if(isNew==null) isNew = false;
        
        if(thisClass.uiSaveValidate) thisClass.uiSaveValidate(ignoreOnBefore, isNew);
        else thisClass.uiSaveValidated(ignoreOnBefore,isNew);
    };
    
    obj.onNotLoggedIn = function(title){
        var thisClass = this;
        Methods.alert(dictionary["s278"], "alert", title,function(){
            Methods.reload(thisClass.reloadHash); 
        });
    }
    
    obj.checkedList = [];
    obj.uiListCheckAll=function(){
        $(".chk"+obj.className+"List").each(function(){
            if(!$(this).is(":checked")) {
                $(this).attr("checked",true);
                obj.uiListCheckAdd(parseInt($(this).val()));
            }
        });
    }
    
    obj.uiListUncheckAll=function(){
        $(".chk"+obj.className+"List").each(function(){
            if($(this).is(":checked")) {
                $(this).attr("checked",false);
                obj.uiListCheckRemove(parseInt($(this).val()));
            }
        });
    }
    
    obj.uiListCheckToggle=function(o,id){
        if($(o).is(":checked")){
            obj.uiListCheckAdd(id);
        }
        else {
            obj.uiListCheckRemove(id);
        }
    }
    
    obj.uiListCheckAdd=function(id){
        if(!obj.isCheckedList(id)) obj.checkedList.push(id);
        obj.uiRefreshCheckedList();
    }
    
    obj.uiListCheckRemove=function(id){
        if(obj.isCheckedList(id)) obj.checkedList.splice(obj.checkedList.indexOf(id),1);
        obj.uiRefreshCheckedList();
    }
    
    obj.isCheckedList=function(id){
        if(obj.checkedList.indexOf(id)!=-1) return true;
        else return false;
    }
    
    obj.uiRefreshCheckedList=function(){
        var fontCount = $("#font"+obj.className+"CheckedCount");
        fontCount.html(obj.checkedList.length);
        
        $(".chk"+obj.className+"List").each(function(){
            if(obj.isCheckedList(parseInt($(this).val()))) $(this).attr("checked",true);
            else $(this).attr("checked",false);
        })
    }
    
    obj.uiEditDescription=function(o){
        var thisClass = this;
        $("#dialog"+this.className+"TextareaDescription").val(o.val());
        $("#div"+this.className+"DialogDescription").dialog({
            title:dictionary["s3"],
            modal:true,
            resizable:false,
            width:840,
            open:function(){
                $('.ui-widget-overlay').css('position', 'fixed');
            },
            close:function(){
            //$('.ui-widget-overlay').css('position', 'absolute');
            },
            create:function(){
                var thisDialog = $("#div"+thisClass.className+"DialogDescription");
                Methods.iniCKEditor($("#dialog"+thisClass.className+"TextareaDescription"),function(){
                    thisDialog.dialog("option","position","center");
                },800);
            },
            buttons:[
            {
                text:dictionary["s38"],
                click:function(){
                    o.val(Methods.getCKEditorData($("#dialog"+thisClass.className+"TextareaDescription")));
                    $(this).dialog("close");
                }
            },
            {
                text:dictionary["s23"],
                click:function(){
                    $(this).dialog("close");
                }
            }
            ]
        }); 
    }
};

OModule.queryResults = {
    OK:0,
    notLoggedIn:-1,
    accessDenied:-2,
    transactionError:-6
}