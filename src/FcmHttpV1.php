<?php

namespace Edujugon\PushNotification;

use GuzzleHttp\Client;

class FcmHttpV1 extends Fcm
{

    /**
     * Fcm constructor.
     * Override parent constructor.
     */
    public function __construct()
    {
        $this->url = 'https://fcm.googleapis.com/v1/projects/{projectId}/messages:send';

        $this->config = $this->initializeConfig('fcm');

        $this->client = new Client();
    }

    /**
     * Send notification by topic.
     * if isCondition is true, $topic will be treated as an expression
     *
     * @param $topic
     * @param $message
     * @param bool $isCondition
     * @return object
     */
    public function sendByTopic($topic, $message, $isCondition = false)
    {
        $headers = $this->addRequestHeaders();
        $data = $this->buildData($topic, $message, $isCondition);

        try {
            $result = $this->client->post(
                    $this->buildUrl(), [
                'headers' => $headers,
                'json' => $data,
                    ]
            );

            $json = $result->getBody();

            $this->setFeedback(json_decode($json));
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];

            $this->setFeedback(json_decode(json_encode($response)));
        } finally {
            return $this->feedback;
        }
    }

    /**
     * Prepare the data to be sent
     *
     * @param $topic
     * @param $message
     * @param $isCondition
     * @return array
     */
    protected function buildData($topic, $message, $isCondition)
    {
        if ($isCondition) {
            $message['condition'] = $topic;
        } else {
            $message['topic'] = $topic;
        }
        return $this->buildMessage($message);
    }

    /**
     * Wraps the message into a message key.
     * 
     * @param $message
     * @return array
     */
    protected function buildMessage($message)
    {
        return [
            'message' => parent::buildMessage($message)
        ];
    }

    /**
     * Gets the url.
     * 
     * @return string
     */
    protected function buildUrl()
    {
        return str_replace('{projectId}', $this->config['projectId'], $this->url);
    }

    /**
     * Set the needed headers for the push notification.
     *
     * @return array
     */
    protected function addRequestHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->config['accessToken'],
            'Content-Type:' => 'application/json'
        ];
    }

}
