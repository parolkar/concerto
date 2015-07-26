concerto.updateLoaderTemplateWorkspaceID <-
function(workspaceID) {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  workspaceID <- dbEscapeStrings(concerto$db$connection,toString(workspaceID))
  dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `loader_UserWorkspace_id` = '%s' WHERE `id`=%s",dbName,workspaceID,sessionID))
}
