concerto.updateTemplateID <-
function(templateID) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  templateID <- dbEscapeStrings(concerto$db$connection,toString(templateID))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_id` = '%s' WHERE `id`=%s",dbName,templateID,sessionID))
}
