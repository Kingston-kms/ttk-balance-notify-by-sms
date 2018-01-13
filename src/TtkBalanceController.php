<?php

namespace console\controllers;

use yii\console\Controller;
use yii\httpclient\Client;

class TtkBalanceController extends Controller
{
    const LOGIN_URL = 'https://lk.ttk.ru/mpo/login.ttk';
    const POST_URL = 'https://lk.ttk.ru/mpo/login';

    const ACC_LOGIN = '123456789';
    const ACC_PASSWORD = 'password';
    const ACC_URL = 'https://lk.ttk.ru/mpo/pages/contract.ttk?id=0000000000000000000000';

    const SMSRU_URL = 'https://sms.ru/sms/send';
    const SMS_API = 'put here api_id';
    const SMS_NUM = '79991112233';

    const BALANCE_LIMIT = 50;

    public function actionGetBalance()
    {

        $loginGet = $this->loginForm();

        $loginPost = $this->postForm($loginGet->cookies);

        $balance = $this->getBalance($loginPost->cookies);

        preg_match_all('/<span.*?>(.*?)<\/span>.*?/s',$balance->content,$match);
        preg_match('/\d*+/',$match[1][0],$value);

        if (intval($value[0]) < self::BALANCE_LIMIT) echo $this->sendNotify($value[0]);

    }

    public function loginForm()
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        return $client->createRequest()
            ->setUrl(self::LOGIN_URL)->send();

    }

    public function postForm($cookies)
    {

        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        return $client->createRequest()->setMethod('post')
            ->setUrl(self::POST_URL)
            ->setHeaders(['content-type' => 'application/x-www-form-urlencoded'])
            ->setData([
                'username' => self::ACC_LOGIN,
                'password' => self::ACC_PASSWORD
            ])->setCookies($cookies)->send();

    }

    public function getBalance($cookies)
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        return $client->createRequest()->setMethod('get')
            ->setUrl(self::ACC_URL)
            ->setCookies($cookies)
            ->send();

    }

    public function sendNotify($value)
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        $client->createRequest()
            ->setUrl(self::SMSRU_URL)
            ->setMethod('post')
            ->setData([
                'api_id' => self::SMS_API,
                'to' => self::SMS_NUM,
                'msg' => 'Low balance TTK: ' . $value
            ])->send();

    }
}
