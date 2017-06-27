<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 20:44
 */

namespace Common\Service;

use Common\Common\ShortUrl;
use Common\Common\Sms;
use Common\Model\AttrModel;
use VcySDK\Service;
use VcySDK\Enterprise;
use VcySDK\Mail;
use Common\Common\User;
use Common\Model\InviteUserModel;

class InviteUserService extends AbstractService
{
    /**
     * 审批状态,等待审批
     */
    const CHECK_STATUS_WAIT = 1;

    /**
     * 审批状态,审批通过
     */
    const CHECK_STATUS_PASS = 2;

    /**
     * 审批状态,审批驳回
     */
    const CHECK_STATUS_FAIL = 3;

    /**
     * 列表类型,我的邀请列表
     */
    const MY_INVITE_LIST = 1;

    /**
     * 列表类型,我的审核列表
     */
    const MY_CHECK_LIST = 2;

    /**
     * 用户是否已关注,是
     */
    const USER_IS_FOLLOW_TRUE = 1;

    /**
     * 用户是否已关注,否
     */
    const USER_IS_FOLLOW_FALSE = 0;

    /**
     * 是否已通知邀请人,是
     */
    const INVITER_IS_NOTICE_TRUE = 1;

    /**
     * 是否已通知邀请人,否
     */
    const INVITER_IS_NOTICE_FALSE = 0;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new InviteUserModel();
    }

    /**
     * 根据表单数据中的手机号、微信号、邮箱获取邀请数据
     * @author zhonglei
     * @param array $form
     * @param bool  $ignoreRefuse 忽略拒绝
     * @return array
     */
    public function getInviteUser($form, $ignoreRefuse = false)
    {

        $dict = ['memMobile' => 'mobile', 'memWeixin' => 'weixin', 'memEmail' => 'email'];
        $data = [];

        foreach ($dict as $k => $v) {
            if (isset($form[$k]['attr_value']) && $form[$k]['attr_value']) {
                $data[$v] = $form[$k]['attr_value'];
            }
        }

        return $this->_d->getByAccount($data, $ignoreRefuse);
    }

    /**
     * 发送审核结果通知
     * @author zhonglei
     * @param array $inviteData   邀请数据
     * @param int   $check_status 审核结果
     * @return void
     */
    public function sendNotice($inviteData, $check_status)
    {

        // 获取企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        // 发送短信
        if ($inviteData['mobile']) {
            // 获取短连接
            $longUrl = oaUrl('Frontend/Index/InviteSuccess/Index', ['invite_id' => $inviteData['invite_id']]);
            $url = ShortUrl::create($longUrl);

            $texts = [
                self::CHECK_STATUS_PASS => "您加入{$ep['corpName']}申请已通过审核，请您点击链接扫描二维码快速加入吧！{$url}",
                self::CHECK_STATUS_FAIL => "很抱歉，您加入{$ep['corpName']}的申请审核未通过。",
            ];
            $sms = &Sms::instance();
            $sms->send($inviteData['mobile'], $texts[$check_status]);
        }

        // 发送邮件
        if ($inviteData['email']) {
            $userServ = new User();
            $inviter = $userServ->getByUid($inviteData['invite_uid']);

            $tplnames = [
                self::CHECK_STATUS_PASS => 'hr_apply_to_join_enterprise_succeed',
                self::CHECK_STATUS_FAIL => 'hr_apply_to_join_enterprise_failed',
            ];

            $subjects = [
                self::CHECK_STATUS_PASS => '申请加入企业号成功',
                self::CHECK_STATUS_FAIL => '申请加入企业号失败',
            ];

            $params = [
                '%qy_name%' => $ep['corpName'],
                '%inviter%' => $inviter['memUsername'],
                '%qrcode%' => $ep['corpWxqrcode'],
                '%date%' => rgmdate(NOW_TIME, 'Y-m-d'),
            ];

            $mailSdk = new Mail(Service::instance());
            $mailSdk->sendTemplateMail([
                'mcTplName' => $tplnames[$check_status],
                'mcEmails' => [$inviteData['email']],
                'mcSubject' => $subjects[$check_status],
                'mcVars' => $params,
            ]);
        }
    }

    /**
     * 检查是否有等待审核的邀请人员
     * @author tony
     * @return bool
     */
    public function haveInviteUserWait()
    {

        $count = $this->_d->count_by_conds(['check_status' => InviteUserService::CHECK_STATUS_WAIT]);

        return $count > 0 ? true : false;
    }

    /**
     * 检查邀请表单数据
     * @author tony
     * @param array $form     表单数据
     * @param array $attrList 属性数据
     * @return bool
     */
    public function checkInviteData($form, $attrList)
    {

//        $data_keys = ['type', 'field_name', 'attr_name', 'option'];
//        // 遍历表单数据，检查数据结构
//        foreach ($form as $v) {
//            $keys = array_keys($v);
//            if (!empty(array_diff($data_keys, $keys))) {
//                return false;
//            }
//        }

        // 遍历表单设置，检查必填项
        foreach ($attrList as $fieldSetting) {
            // 跳过非必填项(跳过直属上级,不给被邀请人选择直属上级的权限)
            if ($fieldSetting['is_required'] == AttrModel::ATTR_IS_REQUIRED_FALSE || $fieldSetting['type'] == AttrModel::ATTR_TYPE_LEADER) {
                continue;
            }

            $is_exist = false;
            // 检查必填项是否存在，且值不能为空
            if (isset($form[$fieldSetting['field_name']])) {
                $attr_value = $form[$fieldSetting['field_name']]['attr_value'];
                if (is_array($attr_value)) {
                    $is_exist = !empty($attr_value);
                } else {
                    $is_exist = strlen(trim($attr_value)) > 0;
                }
            }
            if (!$is_exist) {
                E('1009:' . $fieldSetting['attr_name'] . '不能为空');
            }
        }

        return true;
    }

    /**
     * 搜索邀请信息邀请ID列表
     * @param array  $condition
     * @param mixed  $page_option
     * @param array  $order_option
     * @param string $field
     * @return array|bool
     */
    public function listByRight($condition, $page_option = null, $order_option = array(), $field = '*')
    {

        try {
            return $this->_d->listByRight($condition, $page_option, $order_option, $field);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }


    /**
     * 删除手机号,微信号,邮箱关联的邀请记录
     * @param $mobileArr
     * @param $weixinArr
     * @param $emailArr
     * @return bool
     */
    public function delInviteUserRecord($mobileArr = [], $weixinArr = [], $emailArr = []) {

        if (count($mobileArr) == 0) {
            E("_ERR_WHERE_MOBILE_NOT_EMPTY");
        }

        return $this->_d->delInviteUserRecord([
            'mobile' => implode(",", $this->formatWhereParams($mobileArr)),
            'weixin' => count($weixinArr) > 0 ? implode(",", $this->formatWhereParams($weixinArr)) : "",
            'email' => count($emailArr) > 0 ? implode(",", $this->formatWhereParams($emailArr)) : ""
        ]);
    }

    /**
     * 格式化SQL条件
     * @param $arr
     * @return mixed
     */
    private function formatWhereParams($arr)
    {
        foreach ($arr as $key => &$item) {
            
            if (empty($item)) {
                unset($arr[$key]);
                continue;
            }

            $item = "'" . $item . "'";
        }

        return $arr;
    }

}
