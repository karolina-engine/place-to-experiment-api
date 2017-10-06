<?php

namespace Communicator;

use Communicator\SignedJwt as Message;

use Aws\Sns\SnsClient;

class Event
{
    private $eventKey;
    private $subject;
    private $data;

    public function __construct($eventKey, $subject, $data)
    {
        $this->eventKey = $eventKey;
        $this->subject = $subject;
        $this->data = $data;
    }

    public function publish()
    {
        $client = SnsClient::factory(array(
            'region'  => 'eu-west-1',
            'version' => 'latest'
        ));


        $TopicArn = getenv('platform_topic');

        $result = $client->publish(array(
            'TopicArn' => $TopicArn,
            // Message is required
            'Message' => $this->message(),
            'Subject' => $this->subject
        ));
    }

    private function message()
    {
        $message = new Message(getenv('platform_secret_key'), 3600);


        $message->write('event_key', $this->eventKey);
        
        foreach ($this->data as $claim => $value) {
            $message->write($claim, $value);
        }

        $token_string = $message->getTokenString();
        return $token_string;
    }
}
