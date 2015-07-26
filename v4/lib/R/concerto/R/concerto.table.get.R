concerto.table.get <-
function(tableID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  objField <- "id"
  if(is.character(tableID)){
    objField <- "name"
  }

  tableID <- dbEscapeStrings(concerto$db$connection,toString(tableID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name` FROM `%s`.`Table` WHERE `%s`='%s'",dbName,objField,tableID))
  response <- fetch(result,n=-1)
  return(response)
}
