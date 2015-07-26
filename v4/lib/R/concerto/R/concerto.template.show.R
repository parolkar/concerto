concerto.template.show <-
function(templateID=-1, HTML="", head="", params=list(),timeLimit=0,finalize=F,workspaceID=concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default",effectHideOptions="default"){
  print(paste("showing template #",workspaceID,":",templateID,"...",sep=''))
  if(!is.list(params)) stop("'params' must be a list!")

  if(templateID==-1 && HTML=="") stop("templateID or HTML must be declared")
  
  template <- concerto.template.get(templateID,workspaceID=workspaceID)
  if(HTML!=""){
    concerto:::concerto.updateHead(concerto.template.fillHTML(head,params))
    concerto:::concerto.updateHTML(concerto.template.fillHTML(HTML,params))
  } else {
    if(dim(template)[1]==0) stop(paste("Template #",workspaceID,":",templateID," not found!",sep=''))
    concerto:::concerto.updateHead(concerto.template.fillHTML(template[1,"head"],params))
    concerto:::concerto.updateHTML(concerto.template.fillHTML(template[1,"HTML"],params))
  }
  concerto:::concerto.updateTemplateWorkspaceID(workspaceID)
  concerto:::concerto.updateTemplateID(templateID)
  concerto:::concerto.updateTimeLimit(timeLimit)

  if(effectShow=="default") {
    if(dim(template)[1]>0) effectShow <- template[1,"effect_show"]
    else effectShow <- "none"
  }
  if(effectHide=="default") {
    if(dim(template)[1]>0) effectHide <- template[1,"effect_hide"]
    else effectHide <- "none"
  }
  if(effectShowOptions=="default") {
    if(dim(template)[1]>0) effectShowOptions <- template[1,"effect_show_options"]
    else effectShowOptions <- ""
  }
  if(effectHideOptions=="default") {
    if(dim(template)[1]>0) effectHideOptions <- template[1,"effect_hide_options"]
    else effectHideOptions <- ""
  }
  concerto:::concerto.updateEffectShow(effectShow)  
  concerto:::concerto.updateEffectHide(effectHide)  
  concerto:::concerto.updateEffectShowOptions(effectShowOptions) 
  concerto:::concerto.updateEffectHideOptions(effectHideOptions)  
  
  if(finalize){
    concerto:::concerto.test.updateAllReturnVariables()
    concerto:::concerto.updateRelease(1)
  }
  concerto:::concerto.updateStatus(2)
  
  return(concerto:::concerto.interpretResponse())
}
