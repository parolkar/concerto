<!--
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
-->

<html>
    <head>
        <script src="js/lib/jquery-1.6.2.min.js"></script>
        <script src="js/jquery.ConcertoClient.js"></script>
        <script src="js/Item.js"></script>
    </head>

    <body>
        <script>
            $(function(){
                $("#testContainer").ConcertoClient("query/ws_client.php","http://dev.myiqtest.org/concerto2/query/ws_server.php", { hash: "84eb16f4c3ea8ed2f7060bd0d72445f2"}); 
            });
        </script>

        <div id="testContainer" style="width:100%; border: dotted 1px blue;">

        </div>
    </body>
</html>