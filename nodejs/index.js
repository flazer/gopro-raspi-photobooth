const GoPro = require('goproh4');
const Express = require('express');
const SocketIO = require('socket.io');
const LED = require('./led');
const Button = require('./button');

const cam = new GoPro.Camera();

const ioServer = SocketIO.listen(1337);
const led = new LED();
const button = new Button(led);


var sequence = 1,
    clients = [];

// Event fired every time a new client connects:
ioServer.on('connection', function(socket) {
    console.info(`New client connected (id=${socket.id})`);
    clients.push(socket);

    // When socket disconnects, remove it from the list:
    socket.on('disconnect', function() {
        var index = clients.indexOf(socket);
        if (index != -1) {
            clients.splice(index, 1);
            console.info(`Client gone (id=${socket.id})`);
        }
    });

    button.on ('shutter', function (msg) {
        led.switch('red', 0);
    });
});

cam.restartStream().then(function () {
    console.log('[livestream]', 'started');

    var STREAM_PORT =           8082;
    var WEBSOCKET_PORT =        8084;
    var STREAM_MAGIC_BYTES =    'jsmp';
    var width =                 320;
    var height =                240;

    var socketServer = new (require('ws').Server)({port: WEBSOCKET_PORT});

    socketServer.on('connection', function(socket) {
        var streamHeader = new Buffer(8);
        streamHeader.write(STREAM_MAGIC_BYTES);
        streamHeader.writeUInt16BE(width, 4);
        streamHeader.writeUInt16BE(height, 6);
        socket.send(streamHeader, {binary:true});

        console.log( 'New WebSocket Connection ('+socketServer.clients.length+' total)' );

        socket.on('close', function(code, message){
            console.log( 'Disconnected WebSocket ('+socketServer.clients.length+' total)' );
        });
    });

    socketServer.broadcast = function(data, opts) {
        for( var i in this.clients ) {
            if (this.clients[i].readyState == 1) {
                this.clients[i].send(data, opts);
            }
            else {
                console.log( 'Error: Client ('+i+') not connected.' );
            }
        }
    };

    var app = Express();

    app.post('/publish', function (req, res) {
        console.log(
            'Stream Connected: ' + req.socket.remoteAddress +
            ':' + req.socket.remotePort + ' size: ' + width + 'x' + height
        );
        req.socket.setTimeout(0);
        req.on('data', function(data){
            socketServer.broadcast(data, {binary:true});
        });
    });

    app.use('/index', Express.static(__dirname + '/client'));

    app.listen(STREAM_PORT);

    var spawn_process = function () {
        var ffmpeg = require('child_process').spawn("ffmpeg", [
        "-f",
        "mpegts",
        "-i",
        "udp://" + cam._ip + ":8554",
        "-f",
        "mpeg1video",
        "-probesize",
        "8192",
        "-b",
        "800k",
        "-r",
        "30",
        "http://localhost:8082/publish"
        ]);

        ffmpeg.stdout.pipe(process.stdout);
        ffmpeg.stderr.pipe(process.stdout);
        ffmpeg.on('exit', function () {
            spawn_process();
        });
    };
    spawn_process();
});
