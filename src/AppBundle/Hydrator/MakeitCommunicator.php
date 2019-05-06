<?php

namespace AppBundle\Hydrator;

/**
 * Class MakeitCommunicator
 *
 * @package AppBundle\Hydrator
 */
class MakeitCommunicator
{

    /**
     * @var string
     * Absolute url to makeit.software services endpoint.
     */
    private $endpoint;

    /**
     * @var string
     */
    private $apiKey;

    private static $curlOptions = [
      CURLOPT_TIMEOUT => 5,
      CURLOPT_CONNECTTIMEOUT => 2,
      CURLOPT_RETURNTRANSFER => TRUE,
    ];

    /**
     * MakeitCommunicator constructor.
     *
     * @param array $apiConfig
     */
    public function __construct(array $apiConfig) {
        $this->endpoint = $apiConfig['endpoint'];
        $this->apiKey = $apiConfig['api_key'];
    }

    public function getUserImagePath($employeeId)
    {
        $query = ['parameters' => ['title' => $employeeId]];
        $users = $this->request('node', $query);
        $nid = $users[0]['nid'] ?? false;
        if (!$nid) {
            return false;
        }
        $userData = $this->request('node/' . $nid);
        if (empty($userData['field_team_photo']['und'][0]['fid'])) {
            return false;
        }
        $fid = $userData['field_team_photo']['und'][0]['fid'];
        $file = $this->request('file/' . $fid);

        //return $file['uri_full']
        return $file['file'];
    }

    private function request($path = 'node', $query = [], $postData = NULL, $curlOptions = NULL) {
        $query['api-key'] = $this->apiKey;
        $url = $this->endpoint . "/$path?" . http_build_query($query);

        $ch = curl_init($url);
        curl_setopt_array($ch, self::$curlOptions);
        if ($postData) {
            $json = json_encode($postData);
            curl_setopt_array($ch, [
              CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
              ],
              CURLOPT_POST => TRUE,
              CURLOPT_POSTFIELDS => $json,
            ]);
        }
        if ($curlOptions) {
            curl_setopt_array($ch, $curlOptions);
        }
        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            throw new \Exception("MiS endpoint returned http code $httpCode to request to url $url.");
        }

        if ($response !== FALSE) {
            curl_close($ch);
            return json_decode($response, TRUE);
        }
        else {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }
    }
}