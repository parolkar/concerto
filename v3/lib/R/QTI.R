## 
## Concerto Platform - Online Adaptive Testing Platform
## Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; version 2
## of the License, and not any of the later versions.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
## You should have received a copy of the GNU General Public License
## along with this program; if not, write to the Free Software
## Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
##

QTImapResponse <- function(variableName){
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

QTIequal <-function(arg1,arg2,baseType){
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

QTIcontains <- function(exp1,exp2,baseType,cardinality){
    if(cardinality=='ordered') {
        if(baseType!='pair') {
            containsOrderedVector(exp1,exp2) 
        } else {
            j = 1;
            for(i in exp1){
                v2 = unlist(strsplit(i," "))
                v2 = paste(v2[2]," ",v2[1],sep="")
                if(exp2[j]==i || exp2[j]==v2){
                    if(length(exp2)==j) return(TRUE)
                    j=j+1
                } else {
                    j = 1
                }
            }
            return(FALSE)
        }
    } else {
        if(baseType!='pair') {
            all(exp2 %in% exp1)
        } else {
            for(i in exp2){
                v2 = unlist(strsplit(i," "))
                v2 = paste(v2[2]," ",v2[1],sep="")
                if(!i%in%exp1 && !v2%in%exp1) return(FALSE)
            }
            return(TRUE)
        }
    }
}

QTIdelete <- function(exp1,exp2,baseType){
    if(baseType!="pair") return((exp2)[which(exp2!=exp1)])
    result = c()
    for(i in exp2){
        if(QTIequal(i,exp1,"pair")) result = c(result,i)
    }
    return(result)
}