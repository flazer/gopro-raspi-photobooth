const EventEmitter = require('events').EventEmitter;
const Util = require('util');
const Gpio = require('pi-gpio');

class Button {
    constructor(led, options) {
        options = options || {};
        this.led = led;
        this.pin = options.pin || 7;
        this.state = options.state || 1;

        Gpio.open(this.pin, "input");
    }

    check(interval) {
        interval = interval || 50;

        setTimeout(() => {
            Gpio.read(this.pin, (err, value) => {
                if(value != this.state) {
                    this.state = value;

                    if (value === 0) {
                        this.emit('shutter');
                        this.led.switch('red', 1);
                    }
                }
                this.check();
            });
        }, interval);
    }
}
Util.inherits(Button, EventEmitter);

module.exports = Button;
