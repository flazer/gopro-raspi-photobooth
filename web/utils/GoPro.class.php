<?php

class GoPro {

    private $_config = [];

    public function __construct() {
        $this->_config = require_once(_PROJECT_ROOT_PATH_ . '/config/system.conf.php');
    }

    public function getImageList() {
        $list = [];
        $url = 'http://%s:%s/gp/gpMediaList';
        $config = $this->_getConfig('gopro');
        $url = sprintf($url, $config['ip'], $config['port']);
        $res = file_get_contents($url);
        if (strlen($res) < 1) {
            return $list;
        }

        $raw = json_decode($res, true);
        if (isset($raw['media'])) {
            $raw = $raw['media'];
            foreach ($raw as $folder => $data) {
                if (!is_array($data['fs'])) continue;
                foreach ($data['fs'] AS $entry) {
                    if (!isset($entry['n'])) continue;
                    $type = strtolower(array_pop(explode('.', $entry['n'])));
                    if ($type != 'jpg') continue;
                    $entry['folder'] = $data['d'];
                    $list[] = $entry;

                }
            }
        }

        uasort($list, function($a, $b){
            return ($a['mod'] > $b['mod']) ? -1 : 1;
            return 0;
        });

        return $list;
    }

    public function shutter($mode) {
        $config = $this->_getConfig('gopro');
        switch ($mode) {
            case 'picture':
                //self::_setMode($mode);
                $res = @file_get_contents('http://' . $config['ip'] . '/gp/gpControl/command/shutter?p=1');
                if (strlen($res) <=3) {
                    return true;
                }
                return false;

            default:
                break;
        }

        return false;
    }


    private function _setMode($mode) {
        $config = $this->_getConfig('gopro');
        switch ($mode) {
            case 'picture':
                @file_get_contents('http://' . $config['ip'] . '/gp/gpControl/setting/21/1');
                return true;

            default:
                break;
        }

        return false;
    }


    public function syncImages() {
        $downloaded = $this->getDownloadedItemsList();
        $goProImages = $this->getImageList();

        foreach ($goProImages AS $photo) {
            if (in_array($photo['n'], $downloaded)) {
                continue;
            }
            $this->_downloadImage($photo['n'], $photo['folder']);
        }
    }

    public function getDownloadedItemsList() {
        $pathes = $this->_getPathes();
        $downloadFolder = _PROJECT_ROOT_PATH_ . $pathes['raw'];
        $folder = scandir($downloadFolder);
        $folder = array_diff($folder, array('.', '..'));

        return $folder;
    }

    public function getLastXImages($amount = 5, $type = 'raw') {
        $pathes = $this->_getPathes();
        $downloadFolder = _PROJECT_ROOT_PATH_ . $pathes[$type];
        $files = $this->getDownloadedItemsList();
        $ignored = array('.', '..', '.svn', '.htaccess');

        $list = array();
        foreach ($files as $file) {
            if (in_array($file, $ignored)) continue;
            $list[$file] = (int) @filemtime($downloadFolder . $file);
        }

        arsort($list);
        $list = array_keys($list);

        if (!is_array($list)) return [];
        if (count($list) <= $amount) return $list;
        return array_slice($list, 0, $amount);
    }

    private function _downloadImage($name, $folder) {
        $config = $this->_getConfig('gopro');
        $pathes = $this->_getPathes();
        $downloadFolder = _PROJECT_ROOT_PATH_ . $pathes['raw'];
        $imageUrl = 'http://%s:%s/videos/DCIM/%s/%s';
        $imageUrl = sprintf($imageUrl, $config['ip'], $config['port'], $folder, $name);
        $image = file_get_contents($imageUrl);
        $path = $downloadFolder . $name;
        file_put_contents($path, $image);

        self::_resizeImage('preview', $path);
    }

    private function _resizeImage($type, $path) {
        $pathes = $this->_getPathes();
        $dimension = $this->_getDimensions($type);
        $name = array_pop(explode('/', $path));

        $widthNew = $dimension['width'];
        list($width, $height) = getimagesize($path);
        $heightNew = $height * ($widthNew / $width);

        $newImage = imagecreatetruecolor($widthNew, $heightNew);
        $source = imagecreatefromjpeg($path);
        imagecopyresized($newImage, $source, 0, 0, 0, 0, $widthNew, $heightNew, $width, $height);
        imagejpeg($newImage, _PROJECT_ROOT_PATH_ . $pathes[$type] . $name, 99);
        imagedestroy($newImage);
    }

    private function _getDimensions($type) {
        $config = $this->_getConfig('photos');
        return $config['dimensions'][$type];
    }

    private function _getPathes() {
        $config = $this->_getConfig('photos');
        return $config['pathes'];
    }

    private function _getConfig($key = false) {
        $config = $this->_config;
        if ($key && isset($config[$key])) {
            return $config[$key];
        }

        return $config;
    }
}
