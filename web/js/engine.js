$(document).ready(function() {
    Engine.init();
});


var Engine =
{
    init: function() {
        Images.init();
        Socket.init();
    },

    api: function(action, params, callback){
        var response = '';
        if (typeof params == 'object') {
            params = btoa(JSON.stringify(params));
        }

        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/api/",
            async: true,
            data : {
                action : action,
                param  : params
            },
            success: function(json){
                response = json;
                if (typeof callback == 'function') {
                    callback(json);
                }
            }
        });
        return response;
    }
};

var Socket = {
    socket : null,
    host : null,
    port: null,

    init: function() {
        this.socket = io.connect('http://' + this.getHost() + ':' + this.getPort());
        this.socket.on('shutter', function(msg){
            Images.shutter();
        });
    },

    getHost: function() {
        if (this.host == null) {
            if ($('#streamConfig .ip').length) {
                this.host = $('#streamConfig .ip').val();
            }
        }
        return this.host;
    },

    getPort: function() {
        if (this.port == null) {
            if ($('#streamConfig .port').length) {
                this.port = $('#streamConfig .port').val();
            }
        }
        return this.port;
    },

    send: function(type, msg) {
        Socket.socket.emit(type, msg)
    }
};

var Images = {
    checkCount: 0,
    checkIntervalTimes: [2,5,10],
    shutterTexts : [
        'Hahahaha!'
    ],

    init: function() {
        this.query();
        this.loadShutterTexts();

        $('#secretShutter').click(function(e) {
           Images.shutter();
        });
    },

    loadShutterTexts: function() {
        if ($('#overlay .shutterTexts').length) {
            var val = $('#overlay .shutterTexts').val();
            Images.shutterTexts = val.split('|');

        }
    },

    randomShutterText: function() {
        return Images.shutterTexts[Math.floor(Math.random() * Images.shutterTexts.length)];
    },

    checkNew: function() {
        var interval = Images.checkIntervalTimes[Images.checkCount] * 1000;
        setTimeout(function(){
            Images.query();
            Images.checkCount += 1;
            if (Images.checkCount < Images.checkIntervalTimes.length) {
                Images.checkNew();
            } else {
                Images.checkCount = 0
            }
        }, interval);
    },

    shutter: function() {
        $('#overlay .smile').html(Images.randomShutterText());
        Overlays.show('smile');
        window.setTimeout(function(e) {
            Overlays.hide();
            Overlays.show('saving');
        },2000);

        window.setTimeout(function(e) {
            Overlays.hide();
        }, 4000);

        Images.checkNew();

        Engine.api(
            'shutter',
            {
                mode: 'picture'
            },
            function(data) {
                Socket.send('shuttered', false);
                //Update
                Images.parseResponse(data);
            }
        );
    },

    query: function(amount) {
        if (typeof amount == 'undefined') {
            amount = 7;
        }
        Engine.api(
            'collection',
            {
                amount: amount
            },
            function(data) {
                Images.parseResponse(data);
            }
        )
    },

    parseResponse: function(data) {
        if (data.result != typeof 'undefined') {
            if (data.result != 'ok') {
                if (Debug.is()) {
                    console.log('ERROR:');
                    console.log(data.payload);
                }
            } else if (!$.isEmptyObject(data.payload.data)) {
                Images.draw(data.payload);
            }
        }
    },

    draw: function(list) {
        var folder = $('#image-collection .settings .imageFolder').val();
        var html = '';
        $.each(list.data, function( k, name ) {
            html += '<img data-name="' + name + '" src="' + folder + name + '"/>'
        })

        $('#image-collection .wall').html(html);
    }
};

var Overlays = {

    show: function(type) {
        $('#overlay h1.' + type).removeClass('hidden');
        $('#overlay').removeClass('hidden');
    },

    hide: function() {
        $('#overlay').addClass('hidden');
        $('#overlay h1').addClass('hidden');
    }
}

var Debug = {
    status: false,

    is: function() {
      return this.status;
    },

    set: function(val) {
        if (val == 1) {
            this.status = true;
            return true;
        }

        this.status = false;
        return false;
  }
};
