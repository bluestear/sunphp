<?php

declare(strict_types=1);

namespace sunphp\sms;

defined('SUN_IN') or exit('Sunphp Access Denied');

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;


class AliSms
{

    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Dysmsapi Client
     */
    public function createClient($accessKeyId, $accessKeySecret)
    {
        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }

    public function send($args=[])
    {
        $ali = $this->config;

        $client = $this->createClient($ali['sms_keyid'], $ali['sms_secret']);
        // 签名
        if (empty($args['signName'])) {
            $args['signName'] = $ali['sms_sign1'];
        }
        $sendSmsRequest = new SendSmsRequest($args);

        // $sendSmsRequest = new SendSmsRequest([
        //     "phoneNumbers" => "18866668888",
        //     "signName" => "阿里云短信签名",
        //     "templateCode" => "SMS_140665220",
        //     "templateParam" => "{\"code\":\"1234\"}"
        // ]);

        $runtime = new RuntimeOptions([
            "ignoreSSL" => true
        ]);

        try {
            // 复制代码运行请自行打印 API 的返回值
            $result = $client->sendSmsWithOptions($sendSmsRequest, $runtime)->body;
            // dump($result);
            if ($result->code == "OK") {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }

            // dump(($error->message));

            // 如有需要，请打印 error
            // Utils::assertAsString($error->message);
            return false;
        }
    }
}
