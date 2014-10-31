<?php

namespace Jb\ApiYahooWeather\Lib;

use Goutte\Client;

class ApiYahooWeather {
    
    const URL_BASE = 'http://query.yahooapis.com/v1/public/yql?format=json&q=';
    const URL_QUERY = 'select * from weather.forecast where woeid="%s" and u="%s"';

    protected $client;
    protected $lastResponse;

    public function __construct() {
        $this->client = new Client();
    }

    public function callApi($woeid = null,$unit="f") {
        $woeidUse = ($woeid !== null) ? $woeid : $this->woeid;
        
        if($woeidUse === null){
            throw new \Exception("Please provide a woeid code", 1);
        }
        
        $url = self::URL_BASE . urlencode(sprintf(self::URL_QUERY, $woeidUse, $unit));
        
        try {
            $response = $this->client->getClient()->get($url)->json();    
            if (!isset($response['query']['results']['channel']['item']['condition'])) {
                $this->lastResponse = false;
            } else {
                $this->lastResponse = $response['query']['results']['channel'];
            }
        } catch (\Exception $e) {
            $this->lastResponse = false;
        }
       
        return $this->lastResponse;
    }

    public function getLastResponse($toJson=false) {
        return ($toJson)?json_encode($this->lastResponse):$this->lastResponse;
    }
    
    public function setLastResponse($data){
        $this->lastResponse = $data;
    }

    public function getTemperature($with_unit = false) {
        if (!$this->lastResponse || !isset($this->lastResponse['item']['condition']['temp'])) {
            return "";
        }
        $return = $this->lastResponse['item']['condition']['temp'];
        if ($with_unit) {
            $return.=" " . $this->lastResponse["units"]["temperature"];
        }

        return $return;
    }
    
    public function getLocation(){
        if (!$this->lastResponse || !isset($this->lastResponse["location"]["city"])) {
            return "";
        }
        
        return $this->lastResponse["location"]["city"];
    }
    
     public function getForecast(){
        if (!$this->lastResponse || !isset($this->lastResponse["item"]["forecast"])) {
            return array();
        }
        
        return $this->lastResponse["item"]["forecast"];
    }
    
}
