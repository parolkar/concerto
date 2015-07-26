concerto.workspace.get <-
function(workspaceID){
  if(!is.numeric(workspaceID)) {
    stop("workspaceID must be of numeric type")
  }

  return(paste(concerto$workspacePrefix,workspaceID,sep=''))
}
