<?php

namespace StopForumSpam;

use \GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;

class Api {

    /**
     * @var string
     */
    protected $apiBaseUrl = 'http://api.stopforumspam.org/api?json';

    /**
     * @var string
     */
    protected $apiVersion = '1.6';

    /**
     * @var string
     */
    protected $apiParams = [];

    /**
     * @var string
     */
    protected $site;
    
    /**
     * @var string 
     */
    protected $key;

    /**
     * @var
     */
    protected $ip;

    /**
     * @var
     */
    protected $email;

    /**
     * @var
     */
    protected $username;

    /**
     * @var
     */
    protected $emailHash;

    /**
     * Initialize
     */
    public function __constructor($site, $key) {
        if ($site && $key) {
            // Set API Credentials
            $this->site = $site;
            $this->key = $key;
        } else 
            throw new Exception('You must pass both your SITE & Key into the Constructor.');
    }

    public function check() {

        if (isset($this->ip)) 
            $this->apiParams['ip'] = $this->ip;

        if (isset($this->email))
            $this->apiParams['email'] = $this->email;

        if (isset($this->username))
            $this->apiParams['username'] = $this->username;
    
        if (isset($this->emailHash))
            $this->apiParams['emailhash'] = $this->emailHash;

        if ($this->apiParams) {
            try {
                $client = new \GuzzleHttp\Client();
            
                $response = $client->request('POST', $this->apiBaseUrl, [
                    'form_params' => $this->apiParams
                ]);

                return $response;

            } catch (ClientException $e) {
                if ($e->hasResponse())
                    return Psr7\str($e->getResponse());
                
                return Psr7\str($e->getRequest());
            }
        }
        
        return new Exception('You must set atleast 1 parameter.');
    }

    /**
     * Set IP Address
     */
    public function setIp($ip = null) {
        $this->ip = is_null($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }

    /**
     * Set Email Address
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Set Username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Set Email Hash
     */
    public function setEmailHash($email) {
        $this->emailHash = md5($email);
    }
}