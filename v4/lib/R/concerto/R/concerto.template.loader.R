concerto.template.loader <-
function(templateID=-1, HTML="", head="", params=list(),workspaceID=concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default",effectHideOptions="default"){
  print(paste("setting loader template #",workspaceID,":",templateID,"...",sep=''))

  if(templateID==0){
    concerto:::concerto.updateLoaderHTML("")
    return
  }

  if(templateID==-1 && HTML=="") stop("templateID or HTML must be declared")

  if(!is.list(params)) stop("'params' must be a list!")
  
  template <- concerto.template.get(templateID,workspaceID=workspaceID)

  if(HTML!=""){
    concerto:::concerto.updateLoaderHead(concerto.template.fillHTML(head,params))
    concerto:::concerto.updateLoaderHTML(concerto.template.fillHTML(HTML,params))
  } else {
    if(dim(template)[1]==0) stop(paste("Template #",workspaceID,":",templateID," not found!",sep=''))
    concerto:::concerto.updateLoaderHead(concerto.template.fillHTML(template[1,"head"],params))
    concerto:::concerto.updateLoaderHTML(concerto.template.fillHTML(template[1,"HTML"],params))
  }

  concerto:::concerto.updateLoaderTemplateWorkspaceID(workspaceID)
  concerto:::concerto.updateLoaderTemplateID(templateID)

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
  concerto:::concerto.updateLoaderEffectShow(effectShow)  
  concerto:::concerto.updateLoaderEffectHide(effectHide)  
  concerto:::concerto.updateLoaderEffectShowOptions(effectShowOptions) 
  concerto:::concerto.updateLoaderEffectHideOptions(effectHideOptions)  
}
