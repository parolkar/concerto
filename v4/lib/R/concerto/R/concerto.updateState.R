concerto.updateState <-
function() {
  dbName <- dbEscapeStrings(concerto$db$connection,concerto$db$name)
  sessionID <- dbEscapeStrings(concerto$db$connection,toString(concerto$sessionID))
  state <- list()
  for(var in ls(envir=.GlobalEnv)){
    try({
        if(!is.function(get(var))) state[[var]] <- toString(get(var))
        if(nchar(state[[var]])>100) state[[var]] <- paste(substr(state[[var]],0,100),"...",sep='')
        },silent=T)
  }
  state <- rjson::toJSON(state)
  state <- dbEscapeStrings(concerto$db$connection,toString(state))
  result <- dbSendQuery(concerto$db$connection, statement = sprintf("UPDATE `%s`.`TestSession` SET `state` = '%s' WHERE `id`=%s",dbName,state,sessionID))
}
