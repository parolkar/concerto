/*
Concerto Platform - Online Adaptive Testing Platform
Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University

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

test = null;
(function( $ ){
    $.fn.concerto = function( options ) {  

        var settings = $.extend( {
            'WSPath': 'client/concerto_client.php',
            'testID': null,
            'sessionID': null,
            'sessionHash': null,
            'params':{},
            'loadingImageSource':null,
            'callback':function(sessionID,sessionHash, status, finished){},
            'resumeFromLastTemplate':false
        }, options);

        this.each(function() {    
            test = new Concerto(
                $(this), 
                settings.sessionHash,
                settings.sessionID, 
                settings.testID,
                settings.WSPath, 
                function(data) { 
                    settings.callback.call(this, test.sessionID, test.hash, test.status, test.finished); 
                }, 
                null, 
                null, 
                true,
                settings.loadingImageSource,
                settings.resumeFromLastTemplate
                );      
            var values = new Array();
            for(var key in settings.params){
                values.push($.toJSON({
                    name:key,
                    value:settings.params[key]
                }));
            }
            test.run(null,values);
        });

    };
})( jQuery );