<?php

class RequestsController extends Controller {
    /*
     *  A controller to make external requests
     *  allowing access only from localhost. It speaks
     *  JSON.
     *
     *  We make these requests using ajax from the application.
     *  PHP dependencies:
     *      - curl
     *      - json
     *
     */
    public function beforeFilter() {
        $this->autoLayout = false;
        $this->autoRender = false;

        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'])  {
            // you should not be here, bail hard
            exit;
        }
    }

    public function index() {
        // safequard - unconditional gtfo
        exit;
    }

    public function coordinates() {
        // request coordinates from Nominatim service
        if (! isset($_POST['address'])) {
            return json_encode(array('error' => 'bad parameters'));
        }

        // prepare request parameters
        $url = "http://nominatim.openstreetmap.org/search.php?q=";
        $q = urlencode($_POST['address']) . '&format=json';

        // prepare curl
        $c = curl_init($url.$q);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_TIMEOUT, '5');

        // make request and handle response
        $res = curl_exec($c);
        if (curl_errno($c)) {
            curl_close($c);
            return json_encode(array('error' => 'request failed'));
        }
        curl_close($c);

        // build response
        $res = json_decode($res, true);
        if (empty($res)) {
            $res = array(
                'lng' => null,
                'lat' => null);
        } else {
            $res = array(
                'lng' => $res[0]['lon'],
                'lat' => $res[0]['lat']);
        }
        return json_encode($res);
    }
}
