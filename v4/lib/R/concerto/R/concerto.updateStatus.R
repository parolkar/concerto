concerto.updateStatus <-
function(status) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  status <- dbEscapeStrings(concerto$db$connection,toString(status))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `status` = '%s' WHERE `id`=%s",dbName,status,sessionID))
}
