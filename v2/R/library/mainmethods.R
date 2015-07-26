###  Concerto Testing Platform,
###  Web based adaptive testing platform utilizing R language for computing purposes.
###
###  Copyright (C) 2011  Psychometrics Centre, Cambridge University
###
###  This program is free software: you can redistribute it and/or modify
###  it under the terms of the GNU General Public License as published by
###  the Free Software Foundation, either version 3 of the License, or
###  (at your option) any later version.
###
###  This program is distributed in the hope that it will be useful,
###  but WITHOUT ANY WARRANTY; without even the implied warranty of
###  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
###  GNU General Public License for more details.
###
###  You should have received a copy of the GNU General Public License
###  along with this program.  If not, see <http://www.gnu.org/licenses/>.

args <- commandArgs(T)
db_host <- args[2]
db_port <- as.numeric(args[3])
db_login <- args[4]
db_password <- args[5]
db_name <- args[6]
 
setwd(temp_path)
library(catR)
options(digits=3)

set.var <- function(variable, value, sid=SessionID, dbn=db_name){
   values<- paste("('",paste(c(sid, variable, value), sep=",", collapse="','"),"')", sep="")
   query <- paste("REPLACE INTO `",dbn,"`.`r_out` (`Session_ID`,`Variable`,`Value`) VALUES", values, sep = "")
   dbSendQuery(con, statement = query)
   print(paste("Session variable modification: '",variable,"'='",value,"'",sep =""))
}

set.next.template<-function(template_id){
    print(paste("Setting next item template: '",template_id,"'",sep =""))
    set.var("template_id", template_id, sid=SessionID, dbn=db_name)
}

library(RMySQL)