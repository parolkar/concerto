<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Przemyslaw Lis" />
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="Expires" content="-1"/>
        <title>Concerto client test</title>

        <link rel="stylesheet" href="css/QTI.css?timestamp=<?= time() ?>" />
        <link rel="stylesheet" href="css/jQueryUI/cupertino/jquery-ui-1.10.1.custom.min.css" />

        <script type="text/javascript" src="jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="jquery.json-2.3.min.js"></script>
        <script type="text/javascript" src="jquery-ui-1.10.1.custom.min.js"></script>

        <script type="text/javascript" src="Compatibility.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="Concerto.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="QTI.js?timestamp=<?= time() ?>"></script>
        <script type="text/javascript" src="concerto.jquery.js?timestamp=<?= time() ?>"></script>
        <script>
            $(function() {
                $("#divConcertoClient").concerto({
                    WSPath:"concerto_client.php",
                    workspaceID:1,
                    testID:1,
                    callback:function(data){
                        if(data.sessionStatus==2){
                            $.post("concerto_client.php",{
                                method:"get_last_html",
                                sid:data.sessionID,
                                hash:data.sessionHash,
                                wid:data.workspaceID
                            },function(data2){
                                console.log(data2);
                            },"json");
                            $.post("concerto_client.php",{
                                method:"get_returns",
                                sid:data.sessionID,
                                hash:data.sessionHash,
                                wid:data.workspaceID
                            },function(data2){
                                console.log(data2);
                            },"json");
                        }
                    }
                });
                console.log(test);
            });
        </script>
    </head>
    <body>
        <h1>Concerto v4 client test</h1>
        <div id="divConcertoClient"></div>
    </body>
</body>