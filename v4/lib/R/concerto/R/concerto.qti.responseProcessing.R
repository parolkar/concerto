concerto.qti.responseProcessing <-
function(qtiID,ini,userResponse,workspaceID=concerto$workspaceID){
  print(paste("response processing of QTI #",workspaceID,":",qtiID,"...",sep=''))
  if(!is.list(ini)) stop("'initialization variable' must be a list!")
  
  if(!is.list(userResponse)) stop("'user response variable' must be a list!")
  
  qti <- concerto.qti.get(qtiID,workspaceID=workspaceID)
  if(dim(qti)[1]==0) stop(paste("QTI #",workspaceID,":",qtiID," not found!",sep=''))
  
  result <- ini
  if(length(userResponse)>0){
    for(i in ls(userResponse)){
      result[[i]] <- userResponse[[i]]
    }
  }
  eval(parse(text=qti[1,"response_proc_r_code"]))
  
  return(result)
}
