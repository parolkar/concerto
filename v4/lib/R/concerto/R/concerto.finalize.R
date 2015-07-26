concerto.finalize <-
function(){
  print("finalizing...")
  
  closeAllConnections()
  
  concerto:::concerto.test.updateAllReturnVariables()
  concerto:::concerto.updateStatus(3)
  dbDisconnect(concerto$db$connection)
}
