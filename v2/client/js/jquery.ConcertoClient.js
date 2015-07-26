/*
    Concerto Testing Platform,
    Web based adaptive testing platform utilizing R language for computing purposes.
    
    Copyright (C) 2011  Psychometrics Centre, Cambridge University

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function($){  
    $.fn.ConcertoClient = function(clientPath,serverPath,opts) {  
        var thisClass = this;
        var serverPath = serverPath;
        var clientPath = clientPath;
        
        var defaults = {
        };  
        var options = $.extend(defaults, opts);  
        
        this.sessionData=function(){
            var queryExt = {
                query:"query/session_data.php",
                server_path:serverPath
            };
            
            thisClass.clientCall($.extend(options,queryExt),function(data){
                Item.Current = new Item(data,0,1);
            });
        };
        
        this.rCall=function(opts,callback){
            var queryExt = {
                query:"query/r_call.php",
                server_path:serverPath
            };
            
            thisClass.clientCall($.extend(opts,queryExt),function(data){
                callback.call(this,data);
            });
        };
        
        this.setItem=function(opts,callback){
            var queryExt = {
                query:"query/set_item.php",
                server_path:serverPath
            };
            
            thisClass.clientCall($.extend(opts,queryExt),function(data){
                callback.call(this,data);
            });
        };
        
        this.clientCall = function(opts,callback)
        {
            $.post(
                clientPath,
                $.extend(options,opts),
                function(data){
                    callback.call(this,data);
                },
                "json");
        };
        
        
        this.each(function() {  
            $(this).html("<div align='center' id='item' style='width:100%;'><div align='center' style='width:100%;'><img src='css/img/ajax-loader.gif' /></div></div>");
            thisClass.sessionData();
        }); 
        Item.clientObject = this;
    };  
})(jQuery);  