concerto.qti.mapResponse <-
function(variableName){
  variable <- get(variableName)
  mapEntry <- get(paste(variableName,".mapping.mapEntry",sep=''))
  defaultValue <- get(paste(variableName,".mapping.defaultValue",sep=''))
  
  result <- 0
  for(v in unique(variable)){
    v <- as.character(v)
    if(get(paste(variableName,".baseType",sep=""))=="pair"){
      v2 = unlist(strsplit(v," "))
      v2 = paste(v2[2]," ",v2[1],sep="")
      
      if(!is.na(mapEntry[v])) result <- result + mapEntry[v]
      else if(!is.na(mapEntry[v2])) result <- result + mapEntry[v2]
      else result <- result + defaultValue
    } else {
      if(!is.na(mapEntry[v])) result <- result + mapEntry[v]
      else result <- result + defaultValue
    }
  }
  if(exists(paste(variableName,".mapping.lowerBound",sep=''))){
    lowerBound <- get(paste(variableName,".mapping.lowerBound",sep=''))
    if(result<lowerBound) result <- lowerBound
  }
  if(exists(paste(variableName,".mapping.upperBound",sep=''))){
    upperBound <- get(paste(variableName,".mapping.upperBound",sep=''))
    if(result>upperBound) result <- upperBound
  }
  return(result)
}
