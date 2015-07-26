concerto.qti.initialize <-
function(qtiID,params=list(),workspaceID=concerto$workspaceID){
  print(paste("initializing QTI #",workspaceID,":",qtiID,"...",sep=''))
  if(!is.list(params)) stop("'params' must be a list!")
  
  qti <- concerto.qti.get(qtiID,workspaceID=workspaceID)
  if(dim(qti)[1]==0) stop(paste("QTI #",workspaceID,":",qtiID," not found!",sep=''))
  
  #create 'result' list
  result <- list()
  eval(parse(text=qti[1,"ini_r_code"]))
  if(length(params)>0){
    for(i in ls(params)){
      result[[i]] <- params[[i]]
    }
  }
  result$QTI_HTML <- concerto.template.fillHTML(result$QTI_HTML,result)
  return(result)
}
