concerto.test.updateReturnVariable <-
function(variable){
  if(exists(variable)) {
    dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
    sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
    value <- dbEscapeStrings(concerto$db$connection,toString(get(variable)))
    variable <- dbEscapeStrings(concerto$db$connection,toString(variable))
    value <- dbEscapeStrings(concerto$db$connection,toString(value))
    dbSendQuery(concerto$db$connection, statement = sprintf("REPLACE INTO `%s`.`TestSessionReturn` SET `TestSession_id` ='%s', `name`='%s', `value`='%s'",dbName,sessionID,variable, value))
  }
}
