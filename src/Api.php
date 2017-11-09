<?php

namespace StopForumSpam;

use \GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;

class Api {

    /**
     * @var string
     */
    protected $apiBaseUrl = 'https://api.stopforumspam.org/api?json';

    /**
     * @var string
     */
    protected $apiAddUrl = 'https://www.stopforumspam.com/add';

    /**
     * @var string
     */
    protected $apiVersion = '1.6';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $apiParams = [];

    /**
     * @var float
     */
    protected $maxConfidence = 25.0;

    /**
     * @var array
     */
    protected $resultData = null;

    /**
     * @var array
     */
    protected $confidenceData = null;

    /**
     * @var boolean
     */
    protected $isConfidence = null;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $evidence;

    /**
     * Initialization Constructor
     */
    public function __construct($apiKey) {
        if ($apiKey) {
            /*
             * Set Api Key
             */
            $this->apiKey = $apiKey;
        } else
            throw new \Exception('You must pass your API Key into the Constructor.');
    }

    /**
     * Report a Spammer
     * IP, Username, and Email are required!
     * @return \Exception|string
     */
    public function reportSpammer() {
        /*
         * Setup Params
         */
        $this->setApiParams(true);

        if ($this->getApiParams()) {
            try {
                $client = new Client();
                $response = $client->request('POST', $this->apiAddUrl, ['form_params' => $this->apiParams]);
                $parsedResponse = $response->getBody();

                if ($parsedResponse)
                    return $parsedResponse;

            } catch (ClientException $e) {
                if ($e->hasResponse())
                    return Psr7\str($e->getResponse());

                return Psr7\str($e->getRequest());
            }
        }
        return new \Exception('You must set atleast 1 parameter before running reportSpammer()');
    }

    /**
     * Check if the specified type value has a confidence level less than the set maximum confidence (NON-BULK)
     * @return \Exception|boolean
     */
    public function setIsConfidence($type = 'ip', $confidence = null, $return = true) {
        if (!is_null($confidence))
            $this->maxConfidence = $confidence;

        if (is_null($this->resultData))
            return new \Exception('You must run setResultData() first!');

        if (isset($this->resultData[$type])) {
            if (isset($this->resultData[$type]['confidence'])) {
                if ($this->resultData[$type]['confidence'] < $this->maxConfidence)
                    $this->isConfidence = true;
                else
                    $this->isConfidence = false;

                if ($return)
                    return $this->isConfidence;

                return true;
            } else
                return new \Exception('The "confidence" key was not found in the Type: '. $type);
        } else
            return new \Exception('Type was not found in the Result Data!');
    }

    /**
     * Check if the specified type values have a confidence level less than the set maximum confidence (BULK)
     * @return \Exception|array|boolean
     */
    public function setConfidenceData($type = 'ip', $confidence = null, $return = true) {
        if (!is_null($confidence))
            $this->maxConfidence = $confidence;

        if (is_null($this->resultData))
            return new \Exception('You must run setResultData() first!');

        if (isset($this->resultData[$type])) {
            if (!isset($this->resultData[$type]['confidence'])) {
                foreach($this->resultData[$type] as $row) {
                    if ($row['confidence'] < $this->maxConfidence)
                        $this->confidenceData[$type][$row['value']] = true;
                    else
                        $this->confidenceData[$type][$row['value']] = false;
                }

                if ($return)
                    return $this->confidenceData;

                return true;
            } else
                return new \Exception('The "confidence" key was found in the Type: '. $type);
        } else
            return new \Exception('Type was not found in the Result Data!');
    }

    /**
     * Return Raw Result if successful (BULK & NON-BULK)
     * @return \Exception|array|string
     */
    public function setResultData($return = false) {
        /*
         * Setup Params
         */
        $this->setApiParams();

        if ($this->getApiParams()) {
            try {
                $client = new Client();
                $response = $client->request('POST', $this->apiBaseUrl, ['form_params' => $this->apiParams]);
                $parsedResponse = \GuzzleHttp\json_decode($response->getBody(), true);

                if ($parsedResponse['success']) {
                    $this->resultData = $parsedResponse;

                    if ($return)
                        return $this->resultData;

                    return true;
                }

                return new \Exception("Error: ". $parsedResponse['error']);
            } catch (ClientException $e) {
                if ($e->hasResponse())
                    return Psr7\str($e->getResponse());

                return Psr7\str($e->getRequest());
            }
        }
        return new \Exception('You must set atleast 1 parameter before running reportData()');
    }

    /**
     * Set API Params
     */
    protected function setApiParams($noEvidence = false) {
        if (isset($this->ip))
            $this->apiParams['ip'] = $this->ip;

        if (isset($this->email))
            $this->apiParams['email'] = $this->email;

        if (isset($this->username))
            $this->apiParams['username'] = $this->username;

        if (isset($this->evidence) && $noEvidence)
            $this->apiParams['evidence'] = $this->evidence;

        if (is_null($this->apiParams))
            return new \Exception('You must set atleast 1 parameter before running setApiParams().');

        if ($noEvidence && !isset($this->apiParams['email']) && !isset($this->apiParams['username']) && !isset($this->apiParams['ip']))
            return new \Exception('When reporting, you must set: Email, Username, and the IP Address.');

        /*
         * Set Api Key
         */
        $this->apiParams['api_key'] = $this->apiKey;
        return $this->apiParams;
    }

    /**
     * Get API Params
     */
    protected function getApiParams() {
        if (!is_null($this->apiParams))
            return $this->apiParams;

        return new \Exception('You must run setApiParams() first!');
    }

    /**
     * Get Result Data (BULK & NON-BULK)
     */
    public function getResultData() {
        if (!is_null($this->resultData))
            return $this->resultData;

        return new \Exception('You must run setResultData() first.');
    }

    /**
     * Get Confidence Data (BULK)
     */
    public function getConfidenceData() {
        if (!is_null($this->confidenceData))
            return $this->confidenceData;

        return new \Exception('You must run setConfidenceData() first.');
    }

    /**
     * Get isConfident (NON-BULK)
     */
    public function getIsConfidence() {
        if (!is_null($this->isConfidence))
            return $this->isConfidence;

        return new \Exception('You must run setIsConfidence() first.');
    }

    /**
     * Set Confidence Level
     */
    public function setMaxConfidence($confidence) {
        $this->maxConfidence = $confidence;
    }

    /**
     * Set IP Address (BULK & NON-BULK)
     */
    public function setIp($ip = null) {
        $this->ip = is_null($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }

    /**
     * Set Email Address (BULK & NON-BULK)
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Set Username (BULK & NON-BULK)
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Set Evidence
     */
    public function setEvidence($evidence) {
        $this->evidence = $evidence;
    }
}