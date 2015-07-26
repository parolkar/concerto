concerto.updateEffectHideOptions <-
function(effectHideOptions) {
  if(is.list(effectHideOptions)){
    effectHideOptions <- toJSON(effectHideOptions)
  }

  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  effectHideOptions <- dbEscapeStrings(concerto$db$connection,toString(effectHideOptions))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `effect_hide_options` = '%s' WHERE `id`=%s",dbName,effectHideOptions,sessionID))
}
