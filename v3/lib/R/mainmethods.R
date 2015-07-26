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
 
setwd(CONCERTO_TEMP_PATH)
library(session)
library(catR)

update.session.counter <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `counter` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.status <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `status` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.release <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `release` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.time_limit <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `time_limit` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.template_testsection_id <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_TestSection_id` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.template_id <- function(CONCERTO_PARAM){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `Template_id` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.effects <- function(CONCERTO_PARAM1, CONCERTO_PARAM2, CONCERTO_PARAM3, CONCERTO_PARAM4){
   CONCERTO_PARAM1 <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM1))
   CONCERTO_PARAM2 <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM2))
   CONCERTO_PARAM3 <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM3))
   CONCERTO_PARAM4 <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM4))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `effect_show` ='%s', `effect_hide` ='%s', `effect_show_options` ='%s', `effect_hide_options` ='%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM1, CONCERTO_PARAM2, CONCERTO_PARAM3, CONCERTO_PARAM4, dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

update.session.HTML <- function(CONCERTO_PARAM1, CONCERTO_PARAM2, CONCERTO_PARAM3){
   CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(fill.session.HTML(get.template.HTML(CONCERTO_PARAM1,CONCERTO_PARAM2,CONCERTO_PARAM3))))
   dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("UPDATE `%s`.`TestSession` SET `HTML` = '%s' WHERE `id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),CONCERTO_PARAM,dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID))))
}

get.template.HTML <- function(CONCERTO_PARAM1,CONCERTO_PARAM2,CONCERTO_PARAM3) {
    CONCERTO_SQL <- sprintf("SELECT `HTML` FROM `TestTemplate` WHERE `Test_id`=%s AND `TestSection_id`=%s AND `Template_id`=%s",dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM1)),dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM2)),dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM3)))
    CONCERTO_SQL_RESULT <- dbSendQuery(CONCERTO_DB_CONNECTION,CONCERTO_SQL)
    CONCERTO_SQL_RESULT <- fetch(CONCERTO_SQL_RESULT,n=-1)
    return(CONCERTO_SQL_RESULT[1,1])
}

update.session.return <- function(CONCERTO_PARAM) {
    if(exists(CONCERTO_PARAM)) {
        CONCERTO_VALUE <- toString(get(CONCERTO_PARAM))
        CONCERTO_PARAM <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_PARAM))
        CONCERTO_VALUE <- dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_VALUE))
        dbSendQuery(CONCERTO_DB_CONNECTION, statement = sprintf("REPLACE INTO `%s`.`TestSessionReturn` SET `TestSession_id` ='%s', `name`='%s', `value`='%s'",dbEscapeStrings(CONCERTO_DB_CONNECTION,CONCERTO_DB_NAME),dbEscapeStrings(CONCERTO_DB_CONNECTION,toString(CONCERTO_TEST_SESSION_ID)),CONCERTO_PARAM, CONCERTO_VALUE))
    }
}

fill.session.HTML <- function(CONCERTO_PARAM){
    CONCERTO_HTML_MATCHES <- unlist(regmatches(CONCERTO_PARAM,gregexpr("\\{\\{[^\\}\\}]*\\}\\}",CONCERTO_PARAM)))
    CONCERTO_HTML_MATCHES <- CONCERTO_HTML_MATCHES[!CONCERTO_HTML_MATCHES == '{{TIME_LEFT}}'] 
    while(length(CONCERTO_HTML_MATCHES)>0){
        CONCERTO_HTML_MATCHES_INDEX <- 1
        while(CONCERTO_HTML_MATCHES_INDEX<=length(CONCERTO_HTML_MATCHES)){
            CONCERTO_HTML_MATCH_VALUE <- gsub("\\{\\{","",CONCERTO_HTML_MATCHES[CONCERTO_HTML_MATCHES_INDEX])
            CONCERTO_HTML_MATCH_VALUE <- gsub("\\}\\}","",CONCERTO_HTML_MATCH_VALUE)
            if(exists(CONCERTO_HTML_MATCH_VALUE)){
                CONCERTO_PARAM <- gsub(CONCERTO_HTML_MATCHES[CONCERTO_HTML_MATCHES_INDEX],toString(get(CONCERTO_HTML_MATCH_VALUE)),CONCERTO_PARAM, fixed=TRUE)
            }
            else {
                CONCERTO_PARAM <- gsub(CONCERTO_HTML_MATCHES[CONCERTO_HTML_MATCHES_INDEX],"",CONCERTO_PARAM, fixed=TRUE)
            }
            CONCERTO_HTML_MATCHES_INDEX=CONCERTO_HTML_MATCHES_INDEX+1
        }
        CONCERTO_HTML_MATCHES <- unlist(regmatches(CONCERTO_PARAM,gregexpr("\\{\\{[^\\}\\}]*\\}\\}",CONCERTO_PARAM)))
        CONCERTO_HTML_MATCHES <- CONCERTO_HTML_MATCHES[!CONCERTO_HTML_MATCHES == '{{TIME_LEFT}}'] 
    }
    return(CONCERTO_PARAM)
}

evalWithTimeout <- function (..., envir = parent.frame(), timeout, cpu = timeout, elapsed = timeout, onTimeout = c("error", "warning", "silent"))
{
    onTimeout <- match.arg(onTimeout)
    res <- invisible()
    setTimeLimit(cpu = cpu, elapsed = elapsed, transient = TRUE)
    tryCatch({
        res <- eval(..., envir = envir)
    }, error = function(ex) {
        msg <- ex$message
        pattern <- gettext("reached elapsed time limit")
        if (regexpr(pattern, msg) != -1) {
            if (onTimeout == "error") {
                stop("Timeout!")
            }
            else if (onTimeout == "warning") {
                warning("Timeout!")
            }
            else if (onTimeout == "silent") {
            }
        }
        else {
            stop(msg)
        }
    })
    res
}

convertVariable <- function(var){
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

containsOrderedVector <- function(subject,search){
    j = 1;
    for(i in subject){
        if(search[j]==i){
            if(length(search)==j) return(TRUE)
            j=j+1
        } else {
            j = 1
        }
    }
    return(FALSE)
}

library(RMySQL)
CONCERTO_DB_DRIVER <- dbDriver('MySQL')
CONCERTO_DB_CONNECTION <- dbConnect(CONCERTO_DB_DRIVER, user = CONCERTO_DB_LOGIN, password = CONCERTO_DB_PASSWORD, dbname = CONCERTO_DB_NAME, host = CONCERTO_DB_HOST, port = CONCERTO_DB_PORT, client.flag=CLIENT_MULTI_STATEMENTS)
dbSendQuery(CONCERTO_DB_CONNECTION,statement = "SET NAMES 'utf8';")
dbSendQuery(CONCERTO_DB_CONNECTION,statement = paste("SET time_zone='",CONCERTO_DB_TIMEZONE,"';",sep=''))

rm(CONCERTO_DB_HOST)
rm(CONCERTO_DB_PORT)
rm(CONCERTO_DB_LOGIN)
rm(CONCERTO_DB_PASSWORD)
rm(CONCERTO_DB_TIMEZONE)