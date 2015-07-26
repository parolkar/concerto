concerto.initialize <-
function(testID,sessionID,workspaceID,workspacePrefix,user,password,dbName,host='localhost',port=3306,tempPath,mediaPath,dbTimezone,dbConnect,userIP,mediaURL){
  print("initialization...") 
  
  options(encoding='UTF-8')
  concerto <<- list()
  concerto$testID <<- testID
  concerto$sessionID <<- sessionID
  concerto$workspaceID <<- workspaceID
  concerto$workspacePrefix <<- workspacePrefix
  concerto$templateFIFOPath <<- paste(tempPath,"/fifo_",sessionID,sep='')
  concerto$sessionPath <<- paste(tempPath,"/session_",sessionID,".Rs",sep='')
  concerto$mediaPath <<- mediaPath
  concerto$userIP <<- userIP
  concerto$mediaURL <<- mediaURL
  
  setwd(tempPath)
  print(paste("working directory set to:",tempPath))
  
  if(dbConnect) concerto:::concerto.db.connect(user,password,dbName,host,port,dbTimezone)
}
