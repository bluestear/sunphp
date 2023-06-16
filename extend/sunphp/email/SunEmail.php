<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-05 13:35:13
 * @LastEditors: light
 * @LastEditTime: 2023-05-05 14:00:19
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\email;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreEmail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class SunEmail
{


    public static function send($args = [])
    {

        $get = request()->get();
        if (empty($get['i'])) {
            $mail_info = CoreEmail::where('acid', 0)->find();
        } else {
            $mail_info = CoreEmail::where('acid', $get['i'])->find();
            if (empty($mail_info) || empty($mail_info['email_name'])) {
                $mail_info = CoreEmail::where('acid', 0)->find();
            }
        }

        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        try {
            //服务器配置
            $mail->CharSet = "UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = $mail_info['email_smtp'];          // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $mail->Username = $mail_info['email_sender'];                // SMTP 用户名  即邮箱的用户名
            $mail->Password = $mail_info['email_code'];             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
            $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom($mail_info['email_sender'], $mail_info['email_name']);  //发件人
            $mail->addAddress($args['email'], $args['name']);  // 收件人，昵称可以省略
            //$mail->addAddress('ellen@example.com');  // 可添加多个收件人

            $mail->addReplyTo($mail_info['email_sender'], $mail_info['email_name']); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = $mail_info['email_name'] . "    " . $args['title'];
            $mail->Body    = '<h2>' . $args['content'] . '</h2>' . '<br>' . $mail_info['email_sign'];

            // 邮件正文不支持HTML的备用显示
            $mail->AltBody = $args['content'] . "    " . $mail_info['email_sign'];

            $mail->send();
            // dump($res);
            return true;
        } catch (Exception $e) {
            // echo '邮件发送失败: ', $mail->ErrorInfo;
            return false;
        }
    }
}
