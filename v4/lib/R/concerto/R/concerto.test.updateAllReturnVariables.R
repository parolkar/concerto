concerto.test.updateAllReturnVariables <-
function() {
  print("updating all return variables...")
  
  test <- concerto.test.get(concerto$testID)
  for(ret in test$returnVariables){
    concerto:::concerto.test.updateReturnVariable(ret)
  }
}
