concerto.test.run <-
function(testID,params=list(),workspaceID=concerto$workspaceID){
  print(paste("running test #",workspaceID,":",testID,"...",sep=''))
  
  test <- concerto.test.get(testID,workspaceID=workspaceID)
  if(is.data.frame(test) && dim(test)[1]==0) stop(paste("Test #",workspaceID,":",testID," not found!",sep=''))
  
  if(length(params)>0){
    for(param in ls(params)){
      assign(param,params[[param]])
    }
  }
  
  eval(parse(text=test$code))
  
  r <- list()
  for(ret in test$returnVariables){
    if(exists(ret)) r[[ret]] <- get(ret)
  }
  return(r)
}
