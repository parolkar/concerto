<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!isset($ini)) {
    require_once __DIR__ . '/../Ini.php';
    $ini = new Ini();
}

$path = Ini::$path_data . session_id() . ".Rc";

$sql = sprintf("TRUNCATE TABLE `%s`.`%s`", Ini::$db_master_name, RDoc::get_mysql_table());
mysql_query($sql);
$sql = sprintf("TRUNCATE TABLE `%s`.`%s`", Ini::$db_master_name, RDocLibrary::get_mysql_table());
mysql_query($sql);
$sql = sprintf("TRUNCATE TABLE `%s`.`%s`", Ini::$db_master_name, RDocFunction::get_mysql_table());
mysql_query($sql);

include __DIR__ . "/../SETTINGS.php";

$code = "
        library(RMySQL)
        library(tools)
        unlink('$path')
        drv <- dbDriver('MySQL')
        con <- dbConnect(drv, user = '$db_master_user', password = '$db_master_password', dbname = '$db_master_name', host = '$db_host', port = $db_port, client.flag=CLIENT_MULTI_STATEMENTS)
        dbSendQuery(con,statement = 'SET NAMES \"utf8\";')
        dbSendQuery(con,statement = 'SET time_zone=\"$mysql_timezone\";')
            
        adm <- c()
        for(package in sort(.packages(T))){

            dbSendQuery(con,paste('INSERT INTO `RDocLibrary` SET `name`=\"',package,'\" ; SELECT last_insert_id();',sep=''))
            rs1 <- dbNextResult(con)
            lid <- fetch(rs1, n=-1)[1,1]
            library(package,character.only=T)
            db <- Rd_db(package)

            for(doc in db){
                fileConn<-file('$path',open='a+')
                tools::Rd2HTML(doc,out=fileConn)
                HTML <- dbEscapeStrings(con,paste(readLines(fileConn),collapse='\n'))
                dbSendQuery(con,paste('INSERT INTO `RDoc` SET `HTML`=\"',HTML,'\"; SELECT last_insert_id();',sep=''))
                rs2 <- dbNextResult(con)
                did <- fetch(rs2, n=-1)[1,1]
                unlink('$path')

                aliases <- tools:::.Rd_get_metadata(x=doc,kind='alias')
                sql <- 'INSERT INTO `RDocFunction` (`name`,`RDocLibrary_id`,`RDoc_id`) VALUES '
                first <- T
                insert <- F
                for(alias in aliases) {
                    if(!grepl('^[a-zA-Z0-9_.]*$',alias,perl=T) || alias %in% adm || !exists(alias) || !is.function(get(alias))){
                        next
                    }
                    adm <- c(adm,alias)
                    if(!first) {
                        sql <- paste(sql,',',sep='')
                    }
                    alias <- dbEscapeStrings(con,alias)
                    sql <- paste(sql,'(\"',alias,'\", \"',lid,'\", \"',did,'\") ',sep='')
                    first <- F
                    insert <- T
                }
                if(insert){
                    dbSendQuery(con,sql)
                }
            }
        }
        ";

$fh = fopen($path, "w");
fwrite($fh, $code);
fclose($fh);

$rscript_path = Ini::$path_r_script;

`$rscript_path $path`;

if (file_exists($path))
    unlink($path);
?>