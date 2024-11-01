<?php

/* Strava Class Definition */
class SimplyStrava
{
    private $strava_user_id = "";
    private $strava_auth = "";
    private $strava_units = "";
    private $debug = 0;

    // Class initiation
    function __construct($user_id = "", $auth = "", $units = "imperial") {
        $this->strava_user_id = $user_id;
        $this->strava_auth = $auth;
        $this->strava_units = $units;
    }

    // Obtain login id and token from email and password
    public function obtain_token($email = "", $pwd = "") {
       
        $id_token = $this->do_post_request("https://www.strava.com/api/v2/authentication/login", array("email" => $email, "password" => $pwd) );
        $id_token = json_decode ($id_token, true);

        if (isset($id_token['error'])) {
            return false;
        } else {
            return array("id" => $id_token['athlete']['id'], "token" => $id_token['token']);
        }

    }
 
    // Retrieve list of strava rides
    public function list_rides($user_id = "") {

        if(!$user_id || $trim($user_id) == "") {
            $user_id = $this->strava_user_id;
            if ($this->debug) echo "$user_id<br>\n";
            if (!$user_id || trim($user_id) == "") throw new Exception("Strava User ID requred.");
        }

        // Strava api call
        $json_ride_list = $this->get_url("http://app.strava.com/api/v1/rides?athleteId=".$user_id);
        $json_ride_list = json_decode($json_ride_list, true);

        if (isset($json_ride_list['error'])) {
            if ($this->debug) echo "failure retrieving rides<br>\n";
            return false;
        } else {
            if ($this->debug) echo "successfully retrieved rides<br>\n";
            return $json_ride_list['rides'];
        }
    }

    // Retrieve Ride Details
    public function ride_details($ride_id) {

        if (!$ride_id || trim($ride_id)=="") return false;

        // Strava api call
        $json_ride_detail = $this->get_url("http://www.strava.com/api/v2/rides/".$ride_id);
        $json_ride_detail = json_decode($json_ride_detail, true);

        if (isset($json_ride_detail['error'])) return false;

        $json_ride_detail['ride']['start_date_local'] = $this->strava_to_unix($json_ride_detail['ride']['start_date_local']);    
        if ($this->strava_units == "metric") {
            $json_ride_detail['ride']['distance'] = $this->meters_to_kilometers($json_ride_detail['ride']['distance']);
        } else {
            $json_ride_detail['ride']['distance'] = $this->meters_to_miles($json_ride_detail['ride']['distance']);
        }

        return $json_ride_detail['ride'];
    }

    // time conversion
    private function strava_to_unix($strdate) {

        $tempdate = str_replace("T", " ", $strdate);
        $tempdate = str_replace("Z", " ", $tempdate);
        date_default_timezone_set('UTC');
        $tempdate = strtotime($tempdate);
        return $tempdate;
    }

    // imperial distance conversion
    private function meters_to_miles($distance) {

        $miles = $distance * 0.000621371192;
        $miles = round($miles, 1);
        return $miles;
    }

    // metric distance conversion
    private function meters_to_kilometers($distance) {

        $km = $distance * 0.001;
        $km = round($km, 1);
        return $km;
    }

    // access web services
    private function get_url ($url) {

        $webserv = curl_init();
        $timeout = 0;
        curl_setopt ($webserv, CURLOPT_URL, $url);
        curl_setopt ($webserv, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($webserv, CURLOPT_FOLLOWLOCATION, true );

        ob_start();
        curl_exec($webserv);
        curl_close($webserv);
        $url_response = ob_get_contents();
        ob_end_clean();

        return $url_response;

    }

    private function do_post_request($url, $data) {
  
        $data=http_build_query($data);

        // post data with curl
        $ch = curl_init();
        $timeout = 60;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($ch,CURLOPT_POST, 2);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $url_response = ob_get_contents();
        ob_end_clean();
  
        return $url_response;
    }


    // clean up
    function __destruct() {
        unset($this->strava_user_id);
        unset($this->strava_units);
        unset($this->strava_auth);
    }

}

?>
