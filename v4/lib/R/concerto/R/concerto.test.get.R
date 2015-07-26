concerto.test.get <-
function(testID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  objField <- "id"
  if(is.character(testID)){
    objField <- "name"
  }

  testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`code` FROM `%s`.`Test` WHERE `%s`='%s'",dbName,objField,testID))
  response <- fetch(result,n=-1)
  if(dim(response)[1] == 1) {
    response <- as.list(response[1,])
    testID <- response$id
    response$returnVariables <- concerto:::concerto.test.getReturnVariables(testID,workspaceID=workspaceID)
  }
  return(response)
}
