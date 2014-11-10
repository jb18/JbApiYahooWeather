<?php

namespace Jb\ApiYahooWeather\Lib;

use Goutte\Client;

/**
 * Core class from Api Yahoo Weather library
 */
class ApiYahooWeather {
    
    /** BASE URL to call for API */
    const URL_BASE = 'http://query.yahooapis.com/v1/public/yql?format=json&q=';
    
    /** YQL Query to call for get datas on API */
    const URL_QUERY = 'select * from weather.forecast where woeid="%s" and u="%s"';

    /** @var Goutte\Client $client Goutte client*/
    protected $client;
    
    /** @var array $lastResponse Last response from API*/
    protected $lastResponse;

    public function __construct() {
        $this->client = new Client();
    }

    /**
     * Method to call Yahoo Api
     * @param string $woeid woeid which correspond to the city you want
     * @param string $unit c or f for celsius or fahreneit
     * @return string representation of api response
     * @throws \Exception
     */
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
    
    /**
     * Get lastResponse
     * @param boolean $toJson choose format for the return value (array or json)
     * @return array|string
     */
    public function getLastResponse($toJson=false) {
        return ($toJson)?json_encode($this->lastResponse):$this->lastResponse;
    }
    
    /**
     * Set lastResponse
     * @param array $data data from json_encode
     * @return void
     */
    public function setLastResponse($data){
        $this->lastResponse = $data;
    }

    /**
     * Get current temperature
     * @param boolean $with_unit return or not unit
     * @return string
     */
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
    
    /**
     * Get Location
     * @return string
     */
    public function getLocation(){
        if (!$this->lastResponse || !isset($this->lastResponse["location"]["city"])) {
            return "";
        }
        
        return $this->lastResponse["location"]["city"];
    }
    
    /**
     * get Forecast
     * @return array
     */
    public function getForecast(){
        if (!$this->lastResponse || !isset($this->lastResponse["item"]["forecast"])) {
            return array();
        }
        
        return $this->lastResponse["item"]["forecast"];
    }
    
}
