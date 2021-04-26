<?php

namespace NetSuite;

class Logger
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var string
     */
    const DEFAULT_LOG_FORMAT = 'netsuite-php-%date-%operation';

    /**
     * @var string
     */
    const DEFAULT_DATE_FORMAT = 'Ymd.His.u';

    /**
     * Construct a new Logger object.
     *
     * @param string $path
     * @param string $format
     * @param string $dateFormat
     */
    public function __construct(
        string $path = null,
        string $format = self::DEFAULT_LOG_FORMAT,
        string $dateFormat = self::DEFAULT_DATE_FORMAT
    ) {
        $this->path = $path ?: __DIR__ . '/../logs';
        $this->format = $format;
        $this->dateFormat = $dateFormat;
    }

    /**
     * Update the logging path in the Logger object
     *
     * @param string $path
     * @return void
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * Log the last soap call as request and response XML files.
     *
     * @param \SoapClient $client
     * @param string $operation
     * @return void
     */
    public function logSoapCall(\SoapClient $client, string $operation): void
    {
        if (file_exists($this->path)) {
            $fileName = strtr($this->format, [
                '%date' => (new \DateTime())->format($this->dateFormat),
                '%operation' => $operation,
            ]);
            $logFile = $this->path . '/' . $fileName;

            // REQUEST
            $request = $logFile . '-request.xml';
            $handle = fopen($request, 'w');
            $data = $client->__getLastRequest();
            $data = cleanUpNamespaces($data);

            $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);

            $privateFieldXpaths = [
                '//password',
                '//password2',
                '//currentPassword',
                '//newPassword',
                '//newPassword2',
                '//ccNumber',
                '//ccSecurityCode',
                '//socialSecurityNumber',
            ];

            $privateFields = $xml->xpath(implode(' | ', $privateFieldXpaths));

            foreach ($privateFields as &$field) {
                $field[0] = '[Content Removed for Security Reasons]';
            }

            $stringCustomFields = $xml->xpath("//customField[@xsitype='StringCustomFieldRef']");

            foreach ($stringCustomFields as $field) {
                $field->value = '[Content Removed for Security Reasons]';
            }

            $xml_string = str_replace('xsitype', 'xsi:type', $xml->asXML());

            fwrite($handle, $xml_string);
            fclose($handle);

            // RESPONSE
            $response = $logFile . '-response.xml';
            $handle = fopen($response, 'w');
            $data = $client->__getLastResponse();

            fwrite($handle, $data);
            fclose($handle);
        }
    }
}
