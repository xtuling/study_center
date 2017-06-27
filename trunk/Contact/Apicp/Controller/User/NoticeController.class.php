<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2016-11-24 16:57:03
 */
namespace Apicp\Controller\User;

use Common\Common\ShortUrl;
use Common\Common\Sms;
use Common\Common\User;
use Common\Service\NoticeService;
use VcySDK\Enterprise;
use VcySDK\Mail;
use VcySDK\Service;

class NoticeController extends AbstractController
{

    /**
     * 提醒未关注人员关注，发送短信和邮件
     * @author tony
     * @time 2016-11-24 16:57:53
     */
    public function Index_post()
    {
        $uids = I('post.uids');
        if (empty($uids)) {
            E('_ERR_PARAM_IS_NULL');
        }

        $this->sendNotice($uids);

        return true;
    }

    /**
     * 发送审核通知
     * @author tony
     * @param array $uids 需要发送提醒的用户uid
     * @return bool
     */
    private function sendNotice($uids)
    {
        // 获取企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $userServ = new User();
        $sms = &Sms::instance();
        $mailSdk = new Mail(Service::instance());
        $noticeServ = new NoticeService();

        $tplname = 'hr_subscribe_mail';
        $subject = '邀请加入企业号';

        foreach ($uids as $k => $uid) {
            $userInfo = $userServ->getByUid($uid);

            $gender = ['', '先生', '女士'];

            // 写入提醒关注表
            $data = [
                'eaid' => $this->_login->user['eaId'],
                'adminer_mobile' => $this->_login->user['eaMobile'],
                'uid' => $uid,
                'user_name' => $userInfo['memUsername'],
                'user_mobile' => $userInfo['memMobile'],
                'email' => $userInfo['memEmail'],
            ];
            $result = $noticeServ->insert($data);
            if ($result === false) {
                E('_ERR_INSERT_ERROR');
            }

            // 发送短信
            if (!empty($userInfo['memMobile'])) {
                $longUrl = oaUrl('Frontend/Index/NoticeFollow/Index', ['notice_id' => $result]);
                $url = ShortUrl::create($longUrl);
                // 最新修改，姓名可以为50个字符串，所以先拼装成一个完整的字符串，然后按照长度进行截断。
                $text = "{$userInfo['memUsername']}{$gender[$userInfo['memGender']]}，"
                    ."{$ep['corpName']}邀请您加入企业号，点击链接扫描二维码快速加入吧！{$url}";

                $len = 70 - mb_strlen(cfg('SMS_SIGN'));
                $array = null;
                while ($text) {
                    $sms->send($userInfo['memMobile'], mb_substr($text, 0, $len, "utf8"));
                    $text = mb_substr($text, $len, mb_strlen($text), "utf8");
                }
            }
            // 发送邮件
            if (!empty($userInfo['memEmail'])) {
                $params = [
                    '%qy_name%' => $ep['corpName'],
                    '%user_name%' => $userInfo['memUsername'],
                    '%adminer_mobile%' => $this->_login->user['eaMobile'],
                    '%qrcode%' => $ep['corpWxqrcode'],
                    '%date%' => rgmdate(NOW_TIME, 'Y-m-d'),
                ];
                $mailSdk->sendTemplateMail([
                    'mcTplName' => $tplname,
                    'mcEmails' => [$userInfo['memEmail']],
                    'mcSubject' => $subject,
                    'mcVars' => $params,
                ]);
            }
        }
        return true;
    }

}
