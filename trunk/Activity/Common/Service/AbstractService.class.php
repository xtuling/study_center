<?php
/**
 * AbstractService.class.php
 * Service 层基类
 * @author: houyingcai
 * @copyright: vchangyi.com
 */
namespace Common\Service;

use Common\Common\Msg;
use Common\Common\User;

abstract class AbstractService extends \Com\Service
{
    // 默认页码
    const DEFAULT_PAGE = 1;
    // 默认每页条数
    const DEFAULT_LIMIT = 15;

    //【活动中心】成功发布活动
    const MSG_ACTIVITY_PUBLISH = 1;

    //【活动中心】活动被编辑
    const MSG_ACTIVITY_UPDATE = 2;

    //【活动中心】评论被回复
    const MSG_COMMENT_REPLY = 3;

    // 消息ID类型为活动
    const ACTIVITY = 1;

    // 消息ID类型为回复
    const REPLY = 2;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * 即时消息通知方法 【发消息统一写这里，方便维护】
     * @param $params -通知参数
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + dp_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + cid   -活动ID或者回复ID
     *          + description -摘要
     * @param $type -通知文案类型
     * @return bool
     */
    public function send_msg($params, $type)
    {
        // 获取应用名称
        $application_name = cfg('APPLICATION_NAME');
        
        // 本方法根据类型区分拼接不通的文案
        switch ($type) {
            case self::MSG_ACTIVITY_PUBLISH:

                $data = array(
                    'title' => '【' . $application_name. '】您收到一个活动邀请，快来参与吧',
                    'description' => '活动标题：' . $this->cutstr($params['subject'], 0, 20) . "\r\n活动时间：" .
                    rgmdate((string) $params['begin_time'], 'Y-m-d H:i') . '~' .
                    ($params['end_time'] ? rgmdate((string) $params['end_time'], 'Y-m-d H:i') : '无') . "\r\n发布时间：" .
                    rgmdate((string) $params['publish_time'], 'Y-m-d H:i'),
                    'picUrl' => '',
                    'type' => self::ACTIVITY,
                );
                break;
            case self::MSG_ACTIVITY_UPDATE:

                $data = array(
                    'title' => '【' . $application_name. '】您被邀请的活动信息有更新',
                    'description' => '活动标题：' . $this->cutstr($params['subject'], 0, 20) . "\r\n活动时间：" .
                    rgmdate((string) $params['begin_time'], 'Y-m-d H:i') . '~' .
                    ($params['end_time'] ? rgmdate((string) $params['end_time'], 'Y-m-d H:i') : '无') . "\r\n发布时间：" .
                    rgmdate((string) $params['publish_time'], 'Y-m-d H:i'),
                    'picUrl' => '',
                    'type' => self::ACTIVITY,
                );

                break;
            case self::MSG_COMMENT_REPLY:

                $data = array(
                    'title' => '【' . $application_name. '】您的评论有人回复了',
                    'description' => '活动标题：' . $this->cutstr($params['subject'], 0, 20) . "\r\n回复信息：" .
                    $this->cutstr($params['reply_content'], 0, 10) . "\r\n回复时间：" .
                    rgmdate((string) $params['publish_time'], 'Y-m-d H:i'),
                    'picUrl' => '',
                    'type' => self::REPLY,
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
     *          + is_all    -全公司
     *          + uids      -用户uid数组
     *              + memID     -用户uid
     *          + dp_ids    -部门id数组
     *              + dpID      -部门id
     *          + tag_ids   -标签id数组
     *              + tagID     -标签id
     *          + cid   -ID
     * @param array $condition 消息参数
     *          + title  -标题
     *          + description -内容
     *          + picUrl -图片URL
     * @return bool
     */
    private function send($params = array(), $condition = array())
    {
        // 发送消息接收人
        $msgUser = !empty($params['uids']) ? $params['uids'] : array();
        $toUser = isset($params['is_all']) && !empty($params['is_all']) ? '@all' : $msgUser;

        // 发送消息部门
        $toParty = !empty($params['dp_ids']) ? $params['dp_ids'] : array();

        // 发送消息标签
        // $toTag = !empty($params['tag_ids']) ? $params['tag_ids'] : array();

        // TODO::以后有岗位后还需再添加岗位

        $articles = array(
            array(
                'title' => $condition['title'],
                'description' => $condition['description'],
                'url' => oaUrl('Frontend/Index/Msg/Index',
                    array('type' => $condition['type'], 'id' => $params['cid'])
                ),
                'picUrl' => '',
            ),
        );

        $msgServ = &Msg::instance();
        $msgServ->sendNews($toUser, $toParty, $toTag, $articles);
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
     * @param string $url 生成缩略图
     * @return string
     */
    public function pic_thumbs($url = '')
    {
        $size = '64';

        if (empty($url)) {
            return '';
        }
        if ('//' == substr($url, -2)) {
            // 如果后两个字符为 // 则重新取
            $pic_url = substr($url, 0, -1) . $size;
        } elseif ('/' == substr($url, -1)) {
            // 以 / 结尾时
            $pic_url = $url . $size;
        } elseif ('/' . $size != substr($url, -3)) {
            // 如果不是以 /64 结尾
            $pic_url = $url . '/' . $size;
        } else {
            $pic_url = $url;
        }
        return $pic_url;
    }

    /**
     * 获取已删除用户信息
     * @param array &$user_list 用户列表
     * @param array $uids 全部用户UID
     * @return bool
     */
    public function user_list(&$user_list = array(), $uids = array())
    {
        // 获取未删除的用户UID
        $_uids = array_column($user_list, 'memUid');

        // 获取被删除的用户UID
        $un_uid = array_diff($uids, $_uids);

        // 实例化
        $user = User::instance();

        // 遍历数据
        foreach ($un_uid as $k => $v) {

            // 如果UID不为空
            if (!empty($v)) {

                $user_list[] = $user->getByUid($v);
            }
        }

        return true;
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
