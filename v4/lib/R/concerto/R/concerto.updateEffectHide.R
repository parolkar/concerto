concerto.updateEffectHide <-
function(effectHide) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  effectHide <- dbEscapeStrings(concerto$db$connection,toString(effectHide))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `effect_hide` = '%s' WHERE `id`=%s",dbName,effectHide,sessionID))
}
