<?php
/**
 * Created by PhpStorm.
 * User: kingston
 * Date: 13.01.2018
 * Time: 11:54
 */

namespace console\controllers;

use yii\console\Controller;
use yii\httpclient\Client;

class TtkBalanceController extends Controller
{
    const LOGIN_URL = 'https://lk.ttk.ru/mpo/login.ttk';
    const POST_URL = 'https://lk.ttk.ru/mpo/login';

    const ACC_LOGIN = '272001422';
    const ACC_PASSWORD = 'qdtlmudn';
    const ACC_URL_1 = 'https://lk.ttk.ru/mpo/pages/contract.ttk?id=7100910000000000630229';
    const ACC_URL_2 = 'https://lk.ttk.ru/mpo/pages/contract.ttk?id=7100910000000000638559';

    const SMSRU_URL = 'https://sms.ru/sms/send';
    const SMS_API = 'EF19A561-6882-CA54-BE8D-BFCD3139CAB4';
    const SMS_NUM = '79244032692';

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
            ->setUrl(self::ACC_URL_1)
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