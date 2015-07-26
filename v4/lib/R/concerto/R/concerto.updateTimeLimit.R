concerto.updateTimeLimit <-
function(timeLimit) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  timeLimit <- dbEscapeStrings(concerto$db$connection,toString(timeLimit))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `time_limit` = '%s' WHERE `id`=%s",dbName,timeLimit,sessionID))
}
