concerto.test.getReturnVariables <-
function(testID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  objField <- "Test_id"

  testID <- dbEscapeStrings(concerto$db$connection,toString(testID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `name` FROM `%s`.`TestVariable` WHERE `%s`='%s' AND `type`=1",dbName,objField,testID))
  response <- fetch(result,n=-1)
  
  result <- c()
  for(i in 1:dim(response)[1]){
    result <- c(result,response[i,"name"])
  }
  return(result)
}
