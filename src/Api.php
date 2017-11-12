<?php
namespace StopForumSpam;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Psr7;

class Api
{

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
     * @var int
     */
    protected $maxFrequency = 25;

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
    public function __construct($apiKey)
    {
        if (!$apiKey) {
            throw new \Exception('You must pass your API Key into the Constructor.');
        }
        /**
         * Set Api Key
         */
        $this->apiKey = $apiKey;
    }

    /**
     * Report a Spammer
     * IP, Username, and Email are required!
     * @return \Exception|string
     */
    public function reportSpammer()
    {
        /**
         * Setup Params
         */
        $this->setApiParams(true);

        if ($this->getApiParams()) {
            try {
                $client = new Client();
                $response = $client->request('POST', $this->apiAddUrl, ['form_params' => $this->apiParams]);
                $parsedResponse = $response->getBody();

                if ($parsedResponse) {
                    return $parsedResponse;
                }

            } catch (ClientException $e) {
                return ($e->hasResponse()) ? Psr7\str($e->getResponse()) : Psr7\str($e->getRequest());
            }
        }
        return new \Exception('You must set atleast 1 parameter before running reportSpammer()');
    }

    /**
     * Check if the specified type value has a confidence level less than the set maximum confidence (NON-BULK)
     * @return \Exception|boolean
     */
    public function setIsConfidence($type = 'ip', $confidence = null, $return = true)
    {
        if (!is_null($confidence)) {
            $this->maxConfidence = $confidence;
        }

        if (is_null($this->resultData)) {
            return new \Exception('You must run setResultData() first!');
        }

        if (!isset($this->resultData[$type])) {
            return new \Exception('Type was not found in the Result Data!');
        }
        if (isset($this->resultData[$type]['confidence'])) {
            $this->isConfidence = ($this->resultData[$type]['confidence'] < $this->maxConfidence);
            return ($return) ? $this->isConfidence : true;
        }
        if (isset($this->resultData[$type]['frequency'])) {
            $this->isConfidence = ($this->resultData[$type]['frequency'] < $this->maxFrequency);
            if ($return) {
                return $this->isConfidence;
            }
        } else {
            $this->isConfidence = true; // Not spammer based on no data

            if ($return) {
                return $this->isConfidence;
            }
        }
    }

    /**
     * Check if the specified type values have a confidence level less than the set maximum confidence (BULK)
     * @return \Exception|array|boolean
     */
    public function setConfidenceData($type = 'ip', $confidence = null, $return = true)
    {
        if (!is_null($confidence))
            $this->maxConfidence = $confidence;

        if (is_null($this->resultData))
            return new \Exception('You must run setResultData() first!');

        if (!isset($this->resultData[$type])) {
            return new \Exception('Type was not found in the Result Data!');
        }
        foreach($this->resultData[$type] as $row) {
            if (isset($row['confidence'])) {
                $this->confidenceData[$type][$row['value']] = ($row['confidence'] < $this->maxConfidence);
            } elseif (isset($row['frequency'])) {
                $this->confidenceData[$type][$row['value']] = ($row['frequency'] < $this->maxFrequency);
            } else {
                $this->confidenceData[$type][$row['value']] = true; // Can't find any data that says this row is spam.
            }
        }

        return ($return) ? $this->confidenceData : true;
    }

    /**
     * Return Raw Result if successful (BULK & NON-BULK)
     * @return \Exception|array|string
     */
    public function setResultData($return = false)
    {
        /**
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

                    return ($return) ? $this->resultData : true;
                }

                return new \Exception("Error: ". $parsedResponse['error']);
            } catch (ClientException $e) {
                return ($e->hasResponse()) ? Psr7\str($e->getResponse()) : Psr7\str($e->getRequest());
            }
        }
        return new \Exception('You must set atleast 1 parameter before running reportData()');
    }

    /**
     * Set API Params
     */
    protected function setApiParams($noEvidence = false)
    {
        if (isset($this->ip)) {
            $this->apiParams['ip'] = $this->ip;
        }
        if (isset($this->email)) {
            $this->apiParams['email'] = $this->email;
        }
        if (isset($this->username)) {
            $this->apiParams['username'] = $this->username;
        }
        if (isset($this->evidence) && $noEvidence) {
            $this->apiParams['evidence'] = $this->evidence;
        }
        if (is_null($this->apiParams)) {
            return new \Exception('You must set atleast 1 parameter before running setApiParams().');
        }
        if ($noEvidence && !isset($this->apiParams['email']) && !isset($this->apiParams['username']) && !isset($this->apiParams['ip'])) {
            return new \Exception('When reporting, you must set: Email, Username, and the IP Address.');
        }

        /**
         * Set Api Key
         */
        $this->apiParams['api_key'] = $this->apiKey;
        return $this->apiParams;
    }

    /**
     * Get API Params
     */
    protected function getApiParams()
    {
        if (!is_null($this->apiParams)) {
            return $this->apiParams;
        }

        return new \Exception('You must run setApiParams() first!');
    }

    /**
     * Get Result Data (BULK & NON-BULK)
     */
    public function getResultData()
    {
        if (!is_null($this->resultData)) {
            return $this->resultData;
        }

        return new \Exception('You must run setResultData() first.');
    }

    /**
     * Get Confidence Data (BULK)
     */
    public function getConfidenceData()
    {
        if (!is_null($this->confidenceData)) {
            return $this->confidenceData;
        }

        return new \Exception('You must run setConfidenceData() first.');
    }

    /**
     * Get isConfident (NON-BULK)
     */
    public function getIsConfidence()
    {
        if (!is_null($this->isConfidence)) {
            return $this->isConfidence;
        }

        return new \Exception('You must run setIsConfidence() first.');
    }

    /**
     * Set Confidence Level
     */
    public function setMaxConfidence($confidence)
    {
        $this->maxConfidence = $confidence;
    }

    /**
     * Set Max Frequency
     */
    public function setMaxFrequency($frequency)
    {
        $this->maxFrequency = $frequency;
    }

    /**
     * Set IP Address (BULK & NON-BULK)
     */
    public function setIp($ip = null)
    {
        $this->ip = is_null($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }

    /**
     * Set Email Address (BULK & NON-BULK)
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Set Username (BULK & NON-BULK)
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set Evidence
     */
    public function setEvidence($evidence)
    {
        $this->evidence = $evidence;
    }
}
