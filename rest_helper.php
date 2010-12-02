<?php


    function rest_helper($url, $params = null, $verb = 'GET', $format = 'json') {
        $post_params = false;

        if ($params !== null) {
            $params = http_build_query($params);
            switch($verb) {
                case 'POST':
                    $post_params = true;
                    break;
                case 'GET':
                    $url .= "?" . $params;
                    break;
            }
        }

        $headers = array(
            'Accept: application/json',
//            'Content-Type: application/json',
        );

        # init session
        $session = curl_init($url);

        # get HTTP HEADERS and result
//        curl_setopt($session, CURLOPT_HEADER, true);
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        switch ($verb) {
            case 'POST':
                curl_setopt($session, CURLOPT_POST, true);
                break;

           case 'PUT':
               curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'PUT');
               break;

           case 'DELETE':
               curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'DELETE');
               break;
        }

        if (in_array($verb, array('POST', 'PUT', 'DELETE')) && $post_params) {
            curl_setopt($session, CURLOPT_POSTFIELDS, $params);
        }

        # Close Session
        $response = curl_exec($session);

//        $status_code = array();
//        preg_match('/\d\d\d/', $response, $status_code);
        $status_code = curl_getinfo($session, CURLINFO_HTTP_CODE);
        curl_close($session);

        switch( $status_code ) {
           case 200:
            return handle_format($response, $format);
           break;
           case 503:
            die('Your call failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
           break;
           case 403:
             die('Your call failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
           break;
           case 400:
             die('Your call failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
           break;
           default:
             die('Your call returned an unexpected HTTP status of:' . $status_code);
        }

    }


    function handle_format($reponse, $format) {
        switch ($format) {
            case 'json':
              $r = json_decode($reponse);
              if ($r === null) {
                throw new Exception("failed to decode $reponse as json");
              }
              return $r;

            case 'xml':
              $r = simplexml_load_string($reponse);
              if ($r === null) {
                throw new Exception("failed to decode $reponse as xml");
              }
              return $r;
          }
    }

    function do_post_request($url, $params = null, $verb = 'POST', $format = 'json') {
        return rest_helper($url, $params, $verb, $format);
    }

    function do_get_request($url, $params = null, $verb = 'GET', $format = 'json') {
        return rest_helper($url, $params, $verb, $format);
    }

?>
