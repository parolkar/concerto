concerto.updateLoaderHTML <-
function(loaderHTML){
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  loaderHTML <- dbEscapeStrings(concerto$db$connection,toString(loaderHTML))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `loader_HTML` = '%s' WHERE `id`=%s",dbName,loaderHTML,sessionID))
}
