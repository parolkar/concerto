concerto.qti.equal <-
function(arg1,arg2,baseType){
  if(length(arg1)!=length(arg2)) return(FALSE)
  if(baseType!='pair') return(all(arg1%in%arg2))
  i = 1
  for(a in arg1){
    v2 = unlist(strsplit(v," "))
    v2 = paste(v2[2]," ",v2[1],sep="")
    if(a != arg2[i] && v2 != arg2[i]) return(FALSE)
  }
  return(TRUE)
}
