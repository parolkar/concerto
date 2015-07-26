concerto.qti.get <-
function(qtiID,workspaceID=concerto$workspaceID){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto.workspace.get(workspaceID))

  objField <- "id"
  if(is.character(qtiID)){
    objField <- "name"
  }

  qtiID <- dbEscapeStrings(concerto$db$connection,toString(qtiID))
  result <- dbSendQuery(concerto$db$connection,sprintf("SELECT `id`,`name`,`ini_r_code`,`response_proc_r_code` FROM `%s`.`QTIAssessmentItem` WHERE `%s`='%s'",dbName,objField,qtiID))
  response <- fetch(result,n=-1)
  return(response)
}
