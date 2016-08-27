<?php

    require_once('../config/consts.conf.php');
    require_once(_PROJECT_ROOT_PATH_ . '/utils/GoPro.class.php');

    header("Content-type: application/json; charset=utf-8");

    $params = $_GET;
    if(!isset($params['action'])) {
        return unknownError();
    }

    if(isset($params['param'])) {
        $params['param'] = json_decode(
            base64_decode($params['param']),
            true
        );
    }

    $GoPro = new GoPro();
    switch ($params['action']) {

        case 'collection':
            $amount = 7;
            if (isset($params['param']['amount'])) {
                $amount = $params['param']['amount'];
            }
            $data = $GoPro->getLastXImages($amount);
            echo result($data);

            break;

        case 'shutter':
            $mode = 'picture';
            if (isset($params['param']['mode'])) {
                $mode = $params['param']['mode'];
            }
            if($GoPro->shutter($mode)) {
                sleep(2); //nasty hack, because there is no response for shutter finished
                $GoPro->syncImages();
                $data = $GoPro->getLastXImages(7);
                echo result($data);
            } else {
                return unknownError();
            }

            break;

        default:
            return unknownError();
    }

    function result($data) {
        return json_encode(
            [
                'result' => 'ok',
                'payload' => [
                    'data' => $data
                ]
            ]
        );
    }

    function unknownError($payload = true) {
        echo json_encode(
            [
                'result' => 'error',
                'payload' => (!$payload) ? '¯\_(ツ)_/¯' : $payload
            ]
        );
    }
