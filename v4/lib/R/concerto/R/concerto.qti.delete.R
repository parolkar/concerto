concerto.qti.delete <-
function(exp1,exp2,baseType){
  if(baseType!="pair") return((exp2)[which(exp2!=exp1)])
  result = c()
  for(i in exp2){
    if(concerto:::concerto.qti.equal(i,exp1,"pair")) result = c(result,i)
  }
  return(result)
}
