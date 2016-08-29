<?php

require_once('config/consts.conf.php');
$config = require_once('config/system.conf.php');

?>
<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15">

        <title><?=_PROJECT_NAME_?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <link href='/css/3rdparty/uikit/uikit.css' rel='stylesheet' type='text/css'>
        <link href='/css/styles.css' rel='stylesheet' type='text/css'>

        <script language="JavaScript" type="text/javascript" src="/js/3rdparty/jsmpg.js"></script>
        <script language="JavaScript" type="text/javascript" src="/js/3rdparty/socketio.js"></script>
        <script language="JavaScript" type="text/javascript" src="/js/3rdparty/jquery.js"></script>
        <script language="JavaScript" type="text/javascript" src="/js/engine.js"></script>
    </head>

    <body>

        <div id="page">
            <div class='flex'>
                <div id="secretShutter"></div>
                <div id="previewStream">
                    <canvas id="videoCanvas" width="640" height="480"></canvas>
                </div>

                <div id="image-collection">
                    <div class="bggrey"></div>
                    <div class="wall"></div>
                    <div class="settings hidden">
                        <input type="hidden" class="imageFolder" value="<?=$config['photos']['pathes']['preview']?>"/>
                    </div>
                </div>

                <div id="overlay" class="hidden">
                    <h1 class="smile hidden"></h1>
                    <h1 class="saving hidden">Saving image.</h1>
                    <input type="hidden" class="shutterTexts" value ="<?=implode('|', $config['shutterTexts'])?>"/>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            var client = new WebSocket( 'ws://<?=$config['stream']['ip']?>:<?=$config['stream']['port']?>/' );
            var canvas = document.getElementById('videoCanvas');
            var player = new jsmpeg(client, {canvas:canvas});
        </script>
        <div id="streamConfig" class="hidden">
            <input type="hidden" class="ip" value="<?=$config['socket']['ip']?>"/>
            <input type="hidden" class="port" value="<?=$config['socket']['port']?>"/>
        </div>
    </body>
</html>
