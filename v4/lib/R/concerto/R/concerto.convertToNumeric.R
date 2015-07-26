concerto.convertToNumeric <-
function(var){
  result <- tryCatch({
    if(is.character(var)) var <- as.numeric(var)
    return(var)
  }, warning = function(w) {
    return(var)
  }, error = function(e) {
    return(var)
  }, finally = function(){
    return(var)
  })
  return(result)
}
