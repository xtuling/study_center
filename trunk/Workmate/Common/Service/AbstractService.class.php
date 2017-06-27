<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author: zhuxun37
 * @version: $Id$
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Common\Msg;
use Common\Common\User;
use VcySDK\Service;

abstract class AbstractService extends \Com\Service
{
    // 默认显示条数
    const DEFAULT_LIMIT = 15;

    // 【同事圈】成功发表话题
    const MSG_CIRCLE_PUBLISH = 1;

    // 【同事圈】话题通过审核
    const MSG_CIRCLE_ADOPT = 2;

    // 【同事圈】话题未通过审核
    const MSG_CIRCLE_REFUSE = 3;

    // 【同事圈】成功发表评论
    const MSG_COMMENT_PUBLISH = 4;

    // 【同事圈】评论通过审核
    const MSG_COMMENT_ADOPT = 5;

    // 【同事圈】评论未通过审核
    const MSG_COMMENT_REFUSE = 6;

    // 【同事圈】收到评论消息
    const MSG_COMMENT = 7;

    // 我的评论类型
    const MY_COMMENT_INFO = 1;

    // 我的话题详情类型
    const MY_CIRCLE_INFO = 2;

    // 我的评论详情类型
    const CIRCLE_INFO = 3;


    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 即时消息通知方法 【发消息统一写这里，方便维护】
     * @param $params -通知参数
     *          + user     -审核人
     *          + name     -评论人
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + cd_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + id   -话题ID
     *          + description -摘要
     * @param $type -通知文案类型
     * @return bool
     */
    public function send_msg($params, $type)
    {
        // 初始化
        $data = array();

        // 初始化审核人信息
        $reviewer = '';

        // 如果审核人存在
        if (!empty($params['user'])) {

            $reviewer = "\r\n审核人：" . $params['user'];
        }

        // 转换格式
        $params['description'] = $this->DeleteHtml($params['description']);

        // 获取应用名称
        $application_name = cfg('APPLICATION_NAME');

        // 本方法根据类型区分拼接不通的文案
        switch ($type) {
            case self::MSG_CIRCLE_PUBLISH:

                $data = array(
                    'title' => '【' . $application_name. '】您已成功发表话题，请耐心等待审核',
                    'description' => '话题内容：' . $this->cutstr($params['description'], 0,
                            20) . "\r\n发表时间：" . rgmdate(MILLI_TIME,
                            'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::MY_CIRCLE_INFO
                );
                break;
            case self::MSG_CIRCLE_ADOPT:

                $data = array(
                    'title' => '【' . $application_name. '】恭喜您，您的话题已通过审核，现已正式发布',
                    'description' => '话题内容：' . $this->cutstr($params['description'], 0,
                            20) . $reviewer . "\r\n审核时间：" . rgmdate(strval(MILLI_TIME), 'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::CIRCLE_INFO
                );

                break;
            case self::MSG_CIRCLE_REFUSE:

                $data = array(
                    'title' => '【' . $application_name. '】抱歉，您的话题未通过审核，无法发布',
                    'description' => '话题内容：' . $this->cutstr($params['description'], 0,
                            20) . $reviewer . "\r\n审核时间：" . rgmdate(strval(MILLI_TIME), 'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::MY_CIRCLE_INFO
                );

                break;
            case self::MSG_COMMENT_PUBLISH:

                $data = array(
                    'title' => '【' . $application_name. '】您已成功发表评论，请耐心等待审核',
                    'description' => '评论内容：' . $this->cutstr($params['description'], 0,
                            20) . "\r\n评论时间：" . rgmdate(MILLI_TIME,
                            'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::MY_COMMENT_INFO
                );

                break;

            case self::MSG_COMMENT_ADOPT:
                $data = array(
                    'title' => '【' . $application_name. '】恭喜您，您的评论已通过审核，现已正式发布',
                    'description' => '评论内容：' . $this->cutstr($params['description'], 0,
                            20) . $reviewer . "\r\n评论时间：" . rgmdate(strval(MILLI_TIME), 'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::CIRCLE_INFO
                );

                break;
            case self::MSG_COMMENT_REFUSE:
                $data = array(
                    'title' => '【' . $application_name. '】抱歉，您的评论未通过审核，无法发布',
                    'description' => '评论内容：' . $this->cutstr($params['description'], 0,
                            20) . $reviewer . "\r\n评论时间：" . rgmdate(strval(MILLI_TIME), 'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::MY_COMMENT_INFO
                );
                break;
            case self::MSG_COMMENT:

                $data = array(
                    'title' => '【' . $application_name. '】您发布的话题有一条新评论',
                    'description' => '评论人：' . $params['name'] . "\r\n评论内容：" . $this->cutstr($params['description'], 0,
                            20) . "\r\n评论时间：" . rgmdate(strval(MILLI_TIME), 'Y-m-d H:i:s'),
                    'picUrl' => '',
                    'type' => self::CIRCLE_INFO
                );
                break;

            default:

                return true;
        }

        $this->send($params, $data);

        return true;
    }


    /**
     * 发送消息
     * @param $params -通知参数
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + cd_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + ac_id   -ID
     * @param array $condition 消息参数
     *          + title  -标题
     *          + description -内容
     *          + picUrl -图片URL
     * @return bool
     */
    private function send($params = array(), $condition = array())
    {
        // 发送消息接收人
        $toUser = array_unique(array_filter(array_column($params['uids'], 'memID')));
        if (empty($toUser)) {
            $toUser = $params['uids'];
        }

        $msgUser = implode('|', $toUser);

        // 发送消息部门
        $toParty = array_unique(array_filter(array_column($params['cd_ids'], 'dpID')));
        if (empty($toParty)) {
            $toParty = $params['cd_ids'];
        }
        // 发送消息标签
        $toTag = array_unique(array_filter(array_column($params['tag_ids'], 'tagID')));
        if (empty($toTag)) {
            $toTag = $params['tag_ids'];
        }

        // 实例化发消息SDK
        $sdk_msg = Msg::instance();
        $msg['toUser'] = !empty($msgUser) ? $msgUser : ''; // 接收人
        $msg['toParty'] = !empty($toParty) ? implode('|', $toParty) : '';
        $msg['toTag'] = !empty($toTag) ? implode('|', $toTag) : '';
        $msg['articles'] = array(
            array(
                'title' => $condition['title'],
                'description' => $condition['description'],
                'url' => oaUrl('Frontend/Index/Msg/Index',
                    array('type' => $condition['type'], 'id' => $params['id'])
                ),
                'picUrl' => '',
            ),
        );

        $sdk_msg->sendNews($msg['toUser'], $msg['toParty'], $msg['toTag'], $msg['articles']);

        return true;
    }

    /**
     * 转换时间格式
     * 时间显示规则：1小时内显示XX分钟前，1天以内的显示XX个小时前，1~7天显示XX天前，超过7天显示具体年-月-日 时-分
     * @param string $time 当前时间戳
     * @return bool
     */
    public function get_time($time = '')
    {
        // 获取剩余秒数
        $way = NOW_TIME - ($time / 1000);

        // 一分钟之内
        if ($way < 60) {

            return '刚刚';
        }

        // 如果是一小时之内
        if ($way < 3600) {

            return intval($way / 60) . '分钟前';
        }

        // 如果是一小时之内
        if ($way < 3600) {

            return intval($way / 60) . '分钟前';
        }

        // 如果是一天之内
        if ($way < 86400) {

            return intval($way / 3600) . '小时前';
        }

        // 如果是一周之内
        if ($way < 604800) {

            return intval($way / 86400) . '天前';
        }

        return rgmdate(strval($time));
    }

    /**
     * 格式化图片地址
     * @param array $images_list 附件列表
     * @return array
     */
    public function format_att_images($images_list = array())
    {
        // 初始化
        $covers = array();

        // 如果附件ID为空
        if (empty($images_list)) {

            return $covers;
        }

        // 遍历数据
        foreach ($images_list as $key => $v) {

            $covers[$v['cid']][] = array(
                'atId' => $v['atid'],
                'imgUrl' => imgUrl($v['atid'])
            );

        }

        return $covers;
    }

    /**
     * 格式化人员信息
     * @param array $mem_uids 人员UIDS
     * @return array
     */
    public function format_user($mem_uids = array())
    {
        // 初始化
        $users = array();

        // 如果附件ID为空
        if (empty($mem_uids)) {

            return $users;
        }

        // 获取人员列表
        $user_list = User::instance()->listByConds(array('memUids' => $mem_uids), 1, count($mem_uids));

        // 获取已查到人员UID集合
        $uid_list = array_column($user_list['list'], 'memUid');

        // 获取全部用户列表
        $this->user_list($user_list['list'], $mem_uids, $uid_list);

        // 遍历数据
        foreach ($user_list['list'] as $key => $v) {

            $users[$v['memUid']] = array(
                'username' => strval($v['memUsername']),
                'avatar' => strval($v['memFace']),
                'uid' => strval($v['memUid'])
            );
        }

        return $users;
    }

    /**
     * 【微信端】去除字符串html标签方法
     * @param  string $str 字符串
     * @return string
     */
    public function DeleteHtml($str = '')
    {
        $str = trim($str); //清除字符串两边的空格

        $str = strip_tags($str, ""); //利用php自带的函数清除html格式

        $replace = array("\t", "\n", "\r");

        return trim(str_replace($replace, '', $str)); //返回字符串
    }

    /**
     * 替换字符串中的/r/n为回车
     * @param string $str 字符串
     * @return string
     */
    public function Enter($str = '')
    {

        $str = trim($str); //清除字符串两边的空格

        $replace = array("\r\n", "\n", "\r", "&crarr;", "&#8629;");

        return trim(str_replace($replace, "<br/>", $str)); //返回字符串

    }

    /**
     * 【同事圈】获取用户缩略图像
     * @param string $memFace 原图地址
     * @return string
     */
    public function memFace($memFace = '')
    {

        // 如果头像信息存在
        if (empty($memFace)) {
            return '';
        }

        if ('//' == substr($memFace, -2)) {
            // 如果后两个字符为 // 则重新取
            $avatar_url = substr($memFace, 0, -1) . '64';
        } elseif ('/' == substr($memFace, -1)) {
            // 以 / 结尾时
            $avatar_url = $memFace . '64';
        } elseif ('/64' != substr($memFace, -3)) {
            // 如果不是以 /64 结尾
            $avatar_url = $memFace . '/64';
        } else {
            $avatar_url = $memFace;
        }

        return $avatar_url;
    }

    /**
     * 获取已删除用户信息
     * @param array &$user_list 用户列表
     * @param array $uids 全部用户UID
     * @param array $uid_list 已查询到用户UID
     * @return array
     */
    public function user_list(&$user_list = array(), $uids = array(), $uid_list = array())
    {

        // 获取被删除的用户UID
        $un_uid = array_diff($uids, $uid_list);

        // 实例化
        $user = User::instance();

        // 遍历数据
        foreach ($un_uid as $k => $v) {

            // 如果UID不为空
            if (!empty($v)) {

                $user_list[] = $user->getByUid($v);
            }
        }

        return $uid_list;
    }

    /**
     * 字符串截取，支持中文和其他编码
     * static
     * access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * return string
     */
    public function cutstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }

        if ($this->utf8_strlen($str) > $length && $slice) {

            $slice = $slice . '...';
        }

        return $suffix ? $slice : $slice;
    }

    /**
     * 获取字符串长度
     * @param string $str 字符串
     * @return int
     */
    protected function utf8_strlen($str = '')
    {
        $count = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $value = ord($str[$i]);
            if ($value > 127) {
                $count++;
                if ($value >= 192 && $value <= 223) {
                    $i++;
                } elseif ($value >= 224 && $value <= 239) {
                    $i = $i + 2;
                } elseif ($value >= 240 && $value <= 247) {
                    $i = $i + 3;
                } else {
                    die('Not a UTF-8 compatible string');
                }
            }
            $count++;
        }

        return $count;
    }
}

