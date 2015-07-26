concerto.updateHTML <-
function(html){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  html <- dbEscapeStrings(concerto$db$connection,toString(html))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `HTML` = '%s' WHERE `id`=%s",dbName,html,sessionID))
}
