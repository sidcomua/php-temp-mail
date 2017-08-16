<?php

/**
 * API Client for https://temp-mail.org/
 */
class TempMail
{
    protected $email;
    protected $login;
    protected $domain;
    protected $format = 'json';
    protected $apiUrl = 'https://privatix-temp-mail-v1.p.mashape.com';
    protected $apiKey = '';
    protected $domains;

    public function __construct($login = null, $domain = null, $format = null)
    {
        if (null !== $login) {
            $this->setLogin($login);
        }
        if (null !== $domain) {
            $this->setDomain($domain);
        }
        if (null !== $format) {
            $this->setFormat($format);
        }

        $this->setApiKey(getenv('MASHAPE_API_KEY'));
    }

    public function deleteMail($unique_id)
    {
        $url = $this->apiUrl . '/request/delete/id/' . $this->getHash($unique_id)
            . '/format/' . $this->getFormat();
        return $this->request($url);
    }

    public function getMailBox($email = null, $source = false)
    {
        if (null === $email) {
            $email = $this->getEmail();
        }

        $url = $this->apiUrl . '/request/' . ($source ? 'source' : 'mail') . '/id/' . $this->getHash($email) . '/format/' . $this->getFormat();

        return $this->request($url);
    }

    public function getEmail()
    {
        if (null === $this->email) {
            $this->email = $this->getLogin() . $this->getDomain();
        }
        return $this->email;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getLogin()
    {
        if (null === $this->login) {
            $this->login = $this->generateLogin();
        }
        return $this->login;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function getDomain()
    {
        if (null === $this->domain) {
            $domains = $this->getDomains();

            if (!empty($domains)) {
                $this->domain = $domains[mt_rand(0, count($domains) - 1)];
            }
        }

        return $this->domain;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = (string) $apiKey;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getDomains()
    {
        if (empty($this->domains)) {
            $url = $this->apiUrl . '/request/domains/format/json/';
            $result = $this->request($url);

            if ($result) {
                $this->domains = json_decode($result, true);
            }
        }

        return $this->domains;
    }

    protected function generateLogin($min_length = 6, $max_length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';

        $lenght = mt_rand($min_length, $max_length);

        $login = '';
        for ($i = 0; $i < $lenght; $i++) {
            $login .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $login;
    }

    protected function request($url)
    {
        // Create a stream
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "X-Mashape-Key: " . $this->apiKey . "\r\n"
            ]
        ];

        $context = stream_context_create($opts);

        // Open the file using the HTTP headers set above
        return @file_get_contents($url, false, $context);
    }

    protected function getHash($email)
    {
        return md5($email);
    }
}
