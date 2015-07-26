concerto.db.connect <-
function(user,password,dbName,host='localhost',port=3306,dbTimezone){
  print("connecting to database...")
  
  drv <- dbDriver('MySQL')
  
  con <- dbConnect(drv, user = user, password = password, dbname = dbName, host = host, port = port, client.flag=CLIENT_MULTI_STATEMENTS)
  dbSendQuery(con,statement = "SET NAMES 'utf8';")
  dbSendQuery(con,statement = paste("SET time_zone='",dbTimezone,"';",sep=''))
  
  concerto$db$connection <<- con
  concerto$db$name <<- dbName
}
