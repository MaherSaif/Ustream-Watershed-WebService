<?php

    include 'rest_helper.php';

    openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);

    class WatershetUstreamWebService {

        private $app_url = "APP_URL";

        public function validateBroadcasterSession($brandId, $channelCode, $sessionId) {
            syslog(LOG_DEBUG, "Validate Broadcaster Session: ${brandId} | ${channelCode} | ${sessionId}");
            $response = rest_helper($this->app_url . "/validate_broadcaster/${brandId}/${channelCode}/${sessionId}.json");

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
            $response = do_post_request($this->app_url . "/channel_status/update.json", $params);

            if ($response->acknowledged == 1) {
                return new SoapParam(true, 'acknowledged');
            } else {
                return new SoapParam(false, 'acknowledged');
            }
        }

        public function validateViewerSession($brandId, $channelCode, $sessionId) {
            sys_log(LOG_DEBUG, "Validate user session: ${sessionId} | ${channelCode}");

            $response = rest_helper($this->app_url . "/validate_lecture/${brandId}/${channelCode}/${sessionId}.json");

            if ($response->authStatus == 1) {
                return new SoapParam(true, 'authStatus');
            } else {
                return array(new SoapParam(false, 'authStatus'), new SoapParam($response->authMessage, 'authMessage'));
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
            $response = do_post_request($this->app_url . "/lecture/create.json", $params);

            if ($response->acknowledged == 1) {
                return new SoapParam(true, 'acknowledged');
            } else {
                return new SoapParam(false, 'acknowledged');
            }
        }

    }

    ini_set("soap.wsdl_cache_enabled", "0");

    $uri = "WEB_SERVICE_URI";
    $wsdl = "";
    $server = new SoapServer(null, array(
                'uri' => $uri,
                'location' => $uri,
                'soap_version' => SOAP_1_2));
    $server->setClass("WatershetUstreamWebService");
    $server->handle();

    closelog();
?>
