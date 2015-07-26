concerto.updateEffectShow <-
function(effectShow) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  effectShow <- dbEscapeStrings(concerto$db$connection,toString(effectShow))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `effect_show` = '%s' WHERE `id`=%s",dbName,effectShow,sessionID))
}
