concerto.interpretResponse <-
function(){
  closeAllConnections()
  fifo_connection <- fifo(concerto$templateFIFOPath,"r",blocking=TRUE)
  response <- readLines(fifo_connection,warn=FALSE)
  closeAllConnections()
  if(response=="serialize"){
    concerto:::concerto.serialize()
  } else if(response=="close") {
    stop("close command recieved")
  } else {
    response <- rjson::fromJSON(response)
  }
  return(response)
}
