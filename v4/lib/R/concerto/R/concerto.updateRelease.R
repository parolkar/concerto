concerto.updateRelease <-
function(release) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  release <- dbEscapeStrings(concerto$db$connection,toString(release))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `release` = '%s' WHERE `id`=%s",dbName,release,sessionID))
}
