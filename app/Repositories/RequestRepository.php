<?php

namespace App\Repositories;


class RequestRepository extends Repository
{
    /**
     * @param string $url
     * @param array $params
     * @return bool|string
     */
    public function post(string $url, array $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, $url);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $url
     * @param $curlFile
     * @return bool|string
     */
    public function postFile(string $url, $curlFile)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $curlFile]);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }


    /**
     * @param string $url
     * @param string $curlFile
     * @param string $accessToken
     * @return bool|string
     */
    public function postDocs(string $url, string $curlFile, string $accessToken)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $curlFile, 'access_token' => $accessToken,
            'v' => 5.131]);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $url
     * @param $response
     * @return bool|string
     */
    function sendTelegram($url, $response)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function avi(string $url)
    {
        $ch = curl_init($url);
        sleep(1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        sleep(1);
        $res = curl_exec($ch);
        sleep(1);
        curl_close($ch);
        return $res;
    }
}
