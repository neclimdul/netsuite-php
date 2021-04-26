<?php
/**
 * This file is part of the netsuitephp/netsuite-php library.
 *
 * @package    ryanwinchester/netsuite-php
 * @author     Ryan Winchester <fungku@gmail.com>
 * @copyright  Copyright (c) Ryan Winchester
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @link       https://github.com/netsuitephp/netsuite-php
 * created:    2015-01-22  1:04 PM
 */

namespace NetSuite;

use NetSuite\Classes\ApplicationInfo;
use NetSuite\Classes\GetDataCenterUrlsRequest;
use NetSuite\Classes\Passport;
use NetSuite\Classes\Preferences;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\SearchPreferences;
use NetSuite\Classes\TokenPassport;
use NetSuite\Classes\TokenPassportSignature;
use SoapClient;
use SoapHeader;

class NetSuiteClient
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var SoapClient
     */
    private $client;
    /**
     * @var array
     */
    private $clientOptions = [];
    /**
     * @var array
     */
    private $soapHeaders = [];
    /**
     * @var \NetSuite\Logger
     */
    private $logger;

    /**
     * @param array $config
     * @param array $options
     * @param \SoapClient $client
     * @param \NetSuite\Logger $logger
     */
    public function __construct(
        array $config = null,
        array $options = [],
        \SoapClient $client = null,
        Logger $logger = null
    ) {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = self::getEnvConfig();
        }
        $this->validateConfig($this->config);
        $this->clientOptions = $options;
        if ($client) {
          $this->client = $client;
        }
        if ($logger) {
            $this->logger = $logger;
        }
    }

    /**
     * Set the data center URL for the configured NetSuite account
     *
     * @param array $config
     *
     * @return void
     */
    public function setDataCenterUrl(array $config)
    {
        $params = new GetDataCenterUrlsRequest();
        $params->account = $config['account'];
        $result = $this->getDataCenterUrls($params)->getDataCenterUrlsResult;
        $domain = $result->dataCenterUrls->webservicesDomain;
        $dataCenterUrl = $domain.'/services/NetSuitePort_'.$config['endpoint'];
        $this->getClient()->__setLocation($dataCenterUrl);
    }

    /**
     * Create a configuration array by inspecting the $_ENV superglobal.
     *
     * @return array
     */
    public static function getEnvConfig()
    {
        $config = [
            'endpoint'          => getenv('NETSUITE_ENDPOINT') ?: '2019_1',
            'host'              => getenv('NETSUITE_HOST') ?: 'https://webservices.sandbox.netsuite.com',
            'email'             => getenv('NETSUITE_EMAIL'),
            'password'          => getenv('NETSUITE_PASSWORD'),
            'role'              => getenv('NETSUITE_ROLE') ?: '3',
            'account'           => getenv('NETSUITE_ACCOUNT'),
            'app_id'            => getenv('NETSUITE_APP_ID') ?: '4AD027CA-88B3-46EC-9D3E-41C6E6A325E2',
            'logging'           => getenv('NETSUITE_LOGGING'),
            'log_path'          => getenv('NETSUITE_LOG_PATH'),
            'log_format'        => getenv('NETSUITE_LOG_FORMAT'),
            'log_dateformat'    => getenv('NETSUITE_LOG_DATEFORMAT'),
        ];

        // These config keys aren't required by all users, but if they are
        // defined in the config array, then they must be correct, thus we
        // will omit ones that have been left empty in the .env file.
        $optKeys = [
            'NETSUITE_CONSUMER_KEY'    => 'consumerKey',
            'NETSUITE_CONSUMER_SECRET' => 'consumerSecret',
            'NETSUITE_TOKEN_KEY'       => 'token',
            'NETSUITE_TOKEN_SECRET'    => 'tokenSecret',
            'NETSUITE_HASH_TYPE'       => 'signatureAlgorithm'
        ];
        foreach ($optKeys as $optKey => $cfgKey) {
            if ($optVal = getenv($optKey)) {
                $config[$cfgKey] = $optVal;
            }
        }

        return $config;
    }

    /**
     * Make sure that this client object has at least the basic required
     * configuration values defined or else throw a runtime exception.
     *
     * @param array $config
     *
     * @return void
     */
    public function validateConfig(array $config)
    {
        $requiredParams = [
            'endpoint',
            'host',
            'account',
        ];
        foreach ($requiredParams as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                throw new \RuntimeException('Config key missing: '.$key);
            }
        }
    }

    /**
     * Alternate way to instantiate the NetSuiteClient. This method is
     * superfluous now that the constructor will intelligently look for ENV
     * configuration when it isn't given explicit configuration. This static
     * method is retained for compatibility with those users who might
     * currently be using this method.
     *
     * This method will be removed in some future version.
     *
     * @deprecated
     *
     * @param array $options
     * @param \SoapClient $client
     *
     * @return \NetSuite\NetSuiteClient
     */
    public static function createFromEnv(
        array $options = [],
        \SoapClient $client = null
    ) {
        $config = self::getEnvConfig();

        return new static($config, $options, $client);
    }

    /**
     * Make the SOAP call!
     *
     * @param string $operation
     * @param mixed $parameter
     * @return mixed
     */
    protected function makeSoapCall($operation, $parameter)
    {
        $this->fixWtfCookieBug();

        if (isset($this->config['token'])) {
            $this->addHeader('tokenPassport', $this->createTokenPassportFromConfig($this->config));
        } else {
            $this->setApplicationInfo($this->config['app_id']);
            $this->addHeader("passport", $this->createPassportFromConfig($this->config));
        }

        try {
            $response = $this->getClient()->__soapCall($operation, [$parameter], null, $this->soapHeaders);
            $this->logSoapCall($operation);
            return $response;
        } catch (\Exception $e) {
            $this->logSoapCall($operation);
            throw $e;
        }
    }

    /**
     * Create the options array.
     *
     * @param array $config
     * @param array $overrides
     * @return array
     */
    private function createOptions($config, $overrides = [])
    {
        return array_merge([
            'classmap' => require __DIR__."/includes/classmap.php",
            'trace' => 1,
            'connection_timeout' => 5,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'location' => $config['host']."/services/NetSuitePort_".$config['endpoint'],
            'keep_alive' => false,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'user_agent' => "PHP-SOAP/".phpversion()." + ryanwinchester/netsuite-php",
        ], $overrides);
    }

    /**
     * Build the WSDL address from the config.
     *
     * @param array $config
     * @return string
     */
    private function createWsdl($config)
    {
        return $config['host'].'/wsdl/v'.$config['endpoint'].'_0/netsuite.wsdl';
    }

    /**
     * Create the Passport.
     *
     * @param array $config
     * @return Passport
     */
    private function createPassportFromConfig($config)
    {
        $passport = new Passport();
        $passport->account = $config['account'];
        $passport->email = $config['email'];
        $passport->password = $config['password'];
        $passport->role = new RecordRef();
        $passport->role->internalId = $config['role'];

        return $passport;
    }

    /**
     * Create the TokenPassport.
     *
     * @param array $config
     * @return TokenPassport
     */
    private function createTokenPassportFromConfig($config)
    {
        $tokenPassport = new TokenPassport();
        $tokenPassport->account = $config['account'];
        $tokenPassport->consumerKey = $config['consumerKey'];
        $tokenPassport->token = $config['token'];
        $tokenPassport->nonce = $this->generateTokenPassportNonce();
        $tokenPassport->timestamp = time();

        $signatureAlgorithm = isset($config['signatureAlgorithm']) ? $config['signatureAlgorithm'] : 'sha256';

        $tokenSignature = new TokenPassportSignature();
        $tokenSignature->_ = $this->computeTokenPassportSignature(
            $config['account'],
            $config['consumerKey'],
            $config['consumerSecret'],
            $config['token'],
            $config['tokenSecret'],
            $tokenPassport->nonce,
            $tokenPassport->timestamp,
            $signatureAlgorithm
        );
        $tokenSignature->algorithm = 'HMAC_' . strtoupper($signatureAlgorithm);
        $tokenPassport->signature = $tokenSignature;

        return $tokenPassport;
    }

    /**
     * Add a header by name.
     *
     * @param string $header
     * @param mixed $value
     */
    public function addHeader($header, $value)
    {
        $this->soapHeaders[$header] = new SoapHeader("ns", $header, $value);
    }

    /**
     * Remove a header by name.
     *
     * @param string $header
     */
    public function clearHeader($header)
    {
        unset($this->soapHeaders[$header]);
    }

    /**
     * Set the application id.
     *
     * @param string $appId
     */
    public function setApplicationInfo($appId = null)
    {
        $applicationInfo = new ApplicationInfo();
        $applicationInfo->applicationId = $appId;
        $this->addHeader("applicationInfo", $applicationInfo);
    }

    /**
     * Set preferences header.
     *
     * @param bool $warningAsError
     * @param bool $disableMandatoryCustomFieldValidation
     * @param bool $disableSystemNotesForCustomFields
     * @param bool $ignoreReadOnlyFields
     */
    public function setPreferences(
        $warningAsError = false,
        $disableMandatoryCustomFieldValidation = false,
        $disableSystemNotesForCustomFields = false,
        $ignoreReadOnlyFields = false
    ) {
        $preferences = new Preferences();
        $preferences->warningAsError = $warningAsError;
        $preferences->disableMandatoryCustomFieldValidation = $disableMandatoryCustomFieldValidation;
        $preferences->disableSystemNotesForCustomFields = $disableSystemNotesForCustomFields;
        $preferences->ignoreReadOnlyFields = $ignoreReadOnlyFields;
        $this->addHeader("preferences", $preferences);
    }

    /**
     * Clear preferences header.
     */
    public function clearPreferences()
    {
        $this->clearHeader("preferences");
    }

    /**
     * Set the search preferences header.
     *
     * @param bool $bodyFieldsOnly
     * @param int $pageSize
     * @param bool $returnSearchColumns
     */
    public function setSearchPreferences($bodyFieldsOnly = true, $pageSize = 50, $returnSearchColumns = true)
    {
        $preferences = new SearchPreferences();
        $preferences->bodyFieldsOnly = $bodyFieldsOnly;
        $preferences->pageSize = $pageSize;
        $preferences->returnSearchColumns = $returnSearchColumns;

        $this->addHeader("searchPreferences", $preferences);
    }

    /**
     * Clear the search preferences.
     */
    public function clearSearchPreferences()
    {
        $this->clearHeader("searchPreferences");
    }

    /**
     * SoapClient apparently always sends the JSESSIONID cookie.
     * So we'll just un-set it to prevent this.
     */
    private function fixWtfCookieBug()
    {
        $this->getClient()->__setCookie('JSESSIONID');
    }

    /**
     * Inject a SoapClient object.
     *
     * @param \SoapClient $client
     * @return void
     */
    public function setClient(\SoapClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Get the current soap client.
     *
     * @return \SoapClient
     * @throws \SoapFault
     */
    public function getClient(): \SoapClient
    {
        if (!isset($this->client)) {
            $options = $this->createOptions($this->config, $this->clientOptions);
            $wsdl = $this->createWsdl($this->config);
            $this->client = new SoapClient($wsdl, $options);
        }
        if (isset($this->config['host']) && $this->config['host'] == 'https://webservices.netsuite.com') {
            // Fetch the data center URL for this account because the user
            // provided the legacy webservices URL.
            unset($this->config['host']);
            $this->setDataCenterUrl($this->config);
        }

        return $this->client;
    }

    /**
     * Inject a logger object into the client instance.
     *
     * @param Logger $logger
     * @return void
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Get or create an instance of the Logger.
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        if (!$this->logger) {
            $this->logger = new Logger(
                empty($this->config['log_path']) ? $this->config['log_path'] : null,
                empty($this->config['log_format']) ? $this->config['log_format'] : Logger::DEFAULT_LOG_FORMAT,
                empty($this->config['log_dateformat']) ? $this->config['log_dateformat'] : Logger::DEFAULT_DATE_FORMAT
            );
        }

        return $this->logger;
    }

    /**
     * Turn request logging on or off.
     *
     * @param bool $on
     * @return void
     */
    public function logRequests($on = true): void
    {
        $this->config['logging'] = $on;
    }

    /**
     * Set the logging path.
     *
     * @param string $logPath
     * @return void
     */
    public function setLogPath(string $logPath): void
    {
        $this->config['log_path'] = $logPath;
        $this->getLogger()->setPath($logPath);
    }

    /**
     * Log the last SOAP call.
     *
     * @param string $operation
     * @return void
     */
    private function logSoapCall(string $operation): void
    {
        if (isset($this->config['logging']) && $this->config['logging']) {
            $this->getLogger()->logSoapCall($this->getClient(), $operation);
        }
    }

    /**
     * Compute TokenPassport signature
     *
     * @param int|string $account
     * @param string $consumerKey
     * @param string $consumerKey
     * @param string $token
     * @param string $tokenSecret
     * @param string $nonce
     * @param int|string $timestamp
     * @param string $signatureAlgorithm
     * @return string
     */
    private function computeTokenPassportSignature($account, $consumerKey, $consumerSecret, $token, $tokenSecret, $nonce, $timestamp, $signatureAlgorithm)
    {
        $baseString = implode('&', [$account, $consumerKey, $token, $nonce, $timestamp]);
        $key = $consumerSecret . '&' . $tokenSecret;
        return base64_encode(hash_hmac($signatureAlgorithm, $baseString, $key, true));
    }

    /**
     * Generate random (or sufficiently enough so) string of characters
     */
    private function generateTokenPassportNonce($length = 32)
    {
        $noncePool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $noncePool[mt_rand(0, 61)];
        }
        return $key;
    }
}
