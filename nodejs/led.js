const Gpio = require('pi-gpio');

class LED {
    constructor(options) {
        options = options || {};

        this.green = options.green || {pin: 3, state: 0};
        this.red = options.red || {pin: 5, state: 0};

        Gpio.open(this.green.pin, 'output');
        Gpio.open(this.red.pin, 'output');

        this.blink('green');
    }

    blink(led, interval) {
        interval = interval || 500;

        if (this[led] === undefined) {
            console.error(`Unable to get led ${led} in scope`);
            return;
        }

        setTimeout(() => {
            let currentLed = this[led];
            currentLed.state = (currentLed.state < 1) ? 1 : 0;
            Gpio.write(currentLed.pin, currentLed.state, () => {});
        }, interval);
    }

    switch(led, state) {
        let currentLed = this[led];

        if (currentLed !== undefined) {
            currentLed.state = state;
            Gpio.write(currentLed.pin, currentLed.state, function() {});
        }
    }
}

module.exports = LED;
