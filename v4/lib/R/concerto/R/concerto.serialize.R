concerto.serialize <-
function(){
  print("serializing session...")
  closeAllConnections()
  if(exists("onSerialize")) do.call("onSerialize",list(),envir=.GlobalEnv);
  save.session(concerto$sessionPath)
  concerto:::concerto.updateStatus(7)
  dbDisconnect(concerto$db$connection)
  print("serialization finished")
  #stop("done")
  Sys.sleep(3600)
}
