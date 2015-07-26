concerto.updateLoaderHead <-
function(loaderHead){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  loaderHead <- dbEscapeStrings(concerto$db$connection,toString(loaderHead))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `loader_head` = '%s' WHERE `id`=%s",dbName,loaderHead,sessionID))
}
