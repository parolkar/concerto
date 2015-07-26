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

function QTI(){};

QTI.maxChoicesCheck=function(tsid,obj,name,maxChoices){
    if($(".QTIitemBody_"+tsid+" input:checkbox:checked[name='"+name+"']").length>maxChoices) $(obj).attr("checked",false);
}

QTI.initializeOrderInteraction = function(tsid){
    $('.QTIitemBody_'+tsid+' .QTIorderContainer').sortable();
}

QTI.initializeAssociateInteraction=function(tsid,name,maxAssociations){
    $('.QTIitemBody_'+tsid+' .QTIassociateDraggable').draggable({
        revert:'invalid'
    });
    $('.QTIitemBody_'+tsid+' .QTIasssociateAddAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.addAssociation(tsid,name,ui.draggable,maxAssociations)
        }
    });
    $('.QTIitemBody_'+tsid+' .QTIasssociateRemoveAssociationDropable').droppable({
        drop:function(event,ui){
            QTI.removeAssociation(tsid,name,ui.draggable)
        }
    });
}
QTI.refreshAssociationOptions = function(tsid, name){
    if($(".QTIitemBody_"+tsid+" .QTIdraggableOptionsContainer").children().length==0) $(".QTIitemBody_"+tsid+" .QTIdraggableOptionsContainer").hide(0);
    else $(".QTIitemBody_"+tsid+" .QTIdraggableOptionsContainer").show(0);
    }
    QTI.refreshAssociations = function(tsid, name){
    $(".QTIitemBody_"+tsid+" .QTIassociation").each(function(){
        if($(this).html()=="") {
        $(this).remove();
        return;
        }
        $(this).children("input").remove();
        if($(this).children(".QTIitemBody_"+tsid+" .QTIassociateDraggable").length==2){
        var value = "";
        $(this).children(".QTIitemBody_"+tsid+" .QTIassociateDraggable").each(function(){
            if(value!="") value+=" ";
            value+=$(this).attr("identifier");
            });
        $(this).append("<input type='hidden' name='"+name+"' value='"+value+"' />");
        }
        });
    }
    QTI.removeAssociation=function(tsid, name,obj){
    obj.css("top","0px");
    obj.css("left","0px");
    $(".QTIitemBody_"+tsid+" .QTIdraggableOptionsContainer").append(obj);
    QTI.refreshAssociations(tsid, name);
    QTI.refreshAssociationOptions(tsid, name);
    }
    QTI.addAssociation=function(tsid, name,obj,maxAssociations){
    var currentAssociations = $('.QTIitemBody_'+tsid+' .QTIassociation').length;
    obj.css("top","0px");
    obj.css("left","0px");
    if(currentAssociations<maxAssociations || maxAssociations==0){
    var identifier = obj.attr("identifier");
    if(obj.attr("matchmax")==0 || obj.attr("matchmax")>$(".QTIitemBody_"+tsid+" .QTIassociateDraggable[identifier='"+identifier+"']").length) obj = obj.clone();
    $("<div class='QTIassociation QTIdisplayTable'></div>").append(obj).appendTo($(".QTIitemBody_"+tsid+" .QTIassociationsContainer"));
    }
    $('.QTIitemBody_'+tsid+' .QTIassociateDraggable').draggable({
        revert:'invalid'
        });
    $(".QTIitemBody_"+tsid+" .QTIassociation").droppable({
        drop:function(event,ui){
        ui.draggable.css("top","0px");
        ui.draggable.css("left","0px");
        if($(this).children("div").length==2) return;
        if(ui.draggable.attr("matchGroup")!=undefined && ui.draggable.attr("matchGroup")!="" && $(this).children("div").length==1) {
        var groups = ui.draggable.attr("matchGroup").split(" ");
        var otherIdentifier = $(this).children("div").attr("identifier");
        var found = false;
        for(var i=0;i<groups.length;i++){
        if(otherIdentifier == groups[i]){
        found = true;
        break;
        }
        }
        if(!found) return;
        }
        $(this).append(ui.draggable);
            
        QTI.refreshAssociations(tsid, name);
        QTI.refreshAssociationOptions(tsid, name);
        }
        });
    QTI.refreshAssociations(tsid, name);
    QTI.refreshAssociationOptions(tsid, name);
    }

    QTI.matchInteractionCheck=function(tsid, name,obj,maxAssociations){
    var hi = $(obj).attr("hi");
    var vi = $(obj).attr("vi");
    var hmm = $(obj).attr("hmm");
    var vmm = $(obj).attr("vmm");
    if($(".QTIitemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"']").length>maxAssociations) $(obj).attr("checked",false);
    if($(".QTIitemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"'][hi='"+hi+"']").length>hmm) $(obj).attr("checked",false);
    if($(".QTIitemBody_"+tsid+" .QTImatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length>vmm) $(obj).attr("checked",false);
    }

    QTI.gapMatchInteractionCheck=function(tsid, name,obj){
    var hi = $(obj).attr("hi");
    var vi = $(obj).attr("vi");
    var hmm = $(obj).attr("hmm");
    if($(".QTIitemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][hi='"+hi+"']").length>hmm) $(obj).attr("checked",false);
    if($(".QTIitemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length>1) $(obj).attr("checked",false);
    
    var elem = $(".QTIitemBody_"+tsid+" font[identifier='"+vi+"']");
    var content = $(".QTIitemBody_"+tsid+" .choiceContent_"+hi).html();
    if($(obj).is(":checked")) elem.html(content);
    else {
    if($(".QTIitemBody_"+tsid+" .QTIgapMatchInteractionCheckbox:checked[name='"+name+"'][vi='"+vi+"']").length==0) elem.html("___"+vi+"___");
}
}

QTI.initializeSliderInteraction=function(tsid,min,max,step,orientation){
    $(".QTIitemBody_"+tsid+" .QTIsliderInteraction").slider({
        min:min,
        max:max,
        step:step,
        orientation:orientation,
        stop:function(event,ui){
            $(".QTIitemBody_"+tsid+" .QTIsliderInteractionInput").val($(this).slider("option","value"));
        }
    });
}