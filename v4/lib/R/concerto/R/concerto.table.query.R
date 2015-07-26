concerto.table.query <-
function(sql,params=list()){
  sql <- gsub("^[[:space:]]*","",sql)
  sql <- concerto.table.fillSQL(sql,params)
  result <- dbSendQuery(concerto$db$connection,sql)
  
  if(tolower(substr(gsub("^[[:space:]]*","",sql),1,6))=="select"){
    response <- fetch(result,n=-1)
    return(response)
  }
  return(NULL)
}
