<?php

namespace Medico\Service;

use Mailgun\Mailgun;
use Mailgun\HttpClient\HttpClientConfigurator;

class MailerService
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send($to, $subject, $message)
    {
        $configurator = new HttpClientConfigurator();
        $configurator->setEndpoint($this->config->endpoint);
        $configurator->setApiKey($this->config->apiKey);

        $mgClient = new Mailgun($configurator);

        # Make the call to the client.
        $result = $mgClient->messages()->send($this->config->domain, array(
            'from'    => $this->config->from,
            'to'    => $to,
            'subject' => $subject,
            'text'    => $message
        ));
    }
}
