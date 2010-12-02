<?php

    include 'rest_helper.php';

    openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);

    class WatershetUstreamWebService {

        private $app_url = "http://www.alosrhmc.com/";

        public function validateBroadcasterSession($brandId, $channelCode, $sessionId) {
            syslog(LOG_DEBUG, "Validate Broadcaster Session: ${brandId} | ${channelCode} | ${sessionId}");
            $response = rest_helper($this->app_url . "admin/broadcaster/validate/${brandId}/${channelCode}/${sessionId}.json");

            if ($response->authStatus == 1) {
                return new SoapParam(true, 'authStatus');
            } else {
                return array(new SoapParam(false, 'authStatus'), new SoapParam($response->authMessage, 'authMessage'));
            }
        }

        public function notifySystemMessage($brandId, $message, $priority) {
            syslog(LOG_DEBUG, "SYSTEM_MESSAGE: ${brandId} | ${message} | ${priority}");
            return true;
        }

        public function loginBroadcaster($brandId, $userName, $password) {
            return array(
                "SessionID", #sessionId, authMessage if error
                array(# channels
                    'title' => 'test',
                    'channelCode' => 'channelCode'
                )
            );
        }

        public function loginBroadcasterByChannelToken($brandId, $channelCode, $channelToken) {
            return array(
                "SessionId" # sessionId, authMessage if error
            );
        }

        public function notifyChannelStatusChanged($brandId, $channelCode, $status, $changedAt) {
            syslog(LOG_DEBUG, "Channel Status Changed: ${brandId} | ${channelCode} | ${status}  | ${changedAt}");

            # POST
            $params = array(
                'brandId' => $brandId,
                'channelCode' => $channelCode,
                'changedAt' => $changedAt,
                'status' => $status
            );
            $response = do_post_request($this->app_url . "admin/channels/update_status.json", $params);

            if ($response->acknowledged == 1) {
                return new SoapParam(true, 'acknowledged');
            } else {
                return new SoapParam(false, 'acknowledged');
            }
        }

        public function validateViewerSession($brandId, $channelCode, $sessionId) {
            sys_log(LOG_DEBUG, "Validate user session: ${sessionId} | ${channelCode}");

            $response = rest_helper($this->app_url . "lectures/validate/${brandId}/${channelCode}/${sessionId}.json");

            if ($response->authStatus == 1) {
                return new SoapParam(true, 'authStatus');
            } else {
                return array(new SoapParam(false, 'authStatus'), new SoapParam($response->authMessage, 'authMessage'));
            }
        }

        public function notifyRecordingStarted($brandId, $channelCode, $startedAt) {
            syslog(LOG_DEBUG, "Recording started: ${brandId} | ${channelCode} | ${startedAt}");
            # POST
            $params = array(
                'brandId' => $brandId,
                'channelCode' => $channelCode,
                'startedAt' => $startedAt
            );
//            $response = rest_helper($this->app_url . "admin/channels/update_status.json", $params, 'POST');
            $response = new stdClass();
            $response->acknowledged = 1;

            if ($response->acknowledged == 1) {
                return new SoapParam(true, 'acknowledged');
            } else {
                return new SoapParam(false, 'acknowledged');
            }
        }

        public function notifyRecordingCompleted($brandId, $channelCode, $videoId, $createdAt, $videoAttributes) {
            syslog(LOG_DEBUG, "Recording Completed: ${brandId} | ${channelCode} | ${videoId}  | ${createdAt}");

	   				ob_start();
						var_dump($videoAttributes);
						$out = ob_get_contents();
						ob_end_clean();

						syslog(LOG_DEBUG, "ATTR: $out");							    

            # POST
            $params = array(
                'brandId' => $brandId,
                'channelCode' => $channelCode,
                'videoId' => $videoId,
                'createdAt' => $createdAt,
                'videoAttributes' => $videoAttributes
            );
            $response = do_post_request($this->app_url . "admin/lectures/create_recorded_lecture.json", $params);

            if ($response->acknowledged == 1) {
                return new SoapParam(true, 'acknowledged');
            } else {
                return new SoapParam(false, 'acknowledged');
            }
        }

        public function checkStreamUserTimeLimit($brandId, $channelCode, $sessionId) {
            return array(
                60, #userTimeLeft
                true); #shouldCheckAgain
        }

    }

    ini_set("soap.wsdl_cache_enabled", "0");

    $uri = "http://www.alosrhmc.com:81/webservice/soap_service.php";
    $wsdl = "http://localhost:81/webservice/webservice.xml";
    $server = new SoapServer(null, array(
                'uri' => $uri,
                'location' => $uri,
                'soap_version' => SOAP_1_2));
    $server->setClass("WatershetUstreamWebService");
    $server->handle();

    closelog();
?>
