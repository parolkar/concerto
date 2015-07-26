concerto.updateLoaderEffectShowOptions <-
function(effectShowOptions) {
  if(is.list(effectShowOptions)){
    effectShowOptions <- toJSON(effectShowOptions)
  }

  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  effectShowOptions <- dbEscapeStrings(concerto$db$connection,toString(effectShowOptions))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `loader_effect_show_options` = '%s' WHERE `id`=%s",dbName,effectShowOptions,sessionID))
}
