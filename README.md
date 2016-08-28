GoPro RasPi Photobooth
=============

**GoPro RasPi Photobooth: Use a GoPro connected to a Raspberry Pi as a photobooth.**  
  
This project uses ffmpeg, quick2wire-gpio-admin, nodejs, nginx and php.  
You will see a live preview of your GoPro in your browserwindow.  
Add a Pulled-Up Button to RasPi's pin 7 (GPIO4) to use it as remote shutter.  

##How to use: 

* install FFmpeg (source: https://github.com/FFmpeg/FFmpeg)
* install nodeJS >=6.0.0
* add GoPro's WiFi to your wpa_supplicant.conf
* install quick2wire-gpio-admin (source: https://github.com/flazer/quick2wire-gpio-admin.git)
* install nginx, php5-fpm, php5-gd
* change ip in web/config/system.conf.php to RasPi's ip (stream, socket)  
  
  

Thanks to [motammi](https://github.com/motammi) for cleaning up my messy nodeJs.
  
Based on the work of konradIT:
https://github.com/KonradIT/goprowifihack
