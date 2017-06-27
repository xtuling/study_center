<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/23
 * Time: 下午12:34
 */

namespace Common\Common;

use VcySDK\Message;
use VcySDK\Service;

class Msg
{
    /**
     * 发送消息时，消息接受者单次最大总数
     */
    const USER_MAX_COUNT = 1000;

    /**
     * 发送消息时，消息接受部门单次最大总数
     */
    const DEPT_MAX_COUNT = 100;

    // VcySDK对象
    protected $messageSDK = null;

    /**
     * 单例实例化
     *
     * @return Msg
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {

        $this->messageSDK = new Message(Service::instance());

        return true;
    }

    /**
     * 将应用 token 参数注入到变量参数内
     * @param array $params
     * @return array
     */
    protected function _wxworkAccessToken(array $params)
    {
        $wxworkAuth = new \Com\WxworkAuth();
        if (!$wxworkAuth->useWXWorkGetUserInfo()) {
            return $params;
        }

        $params['accessToken'] = $wxworkAuth->getAccessToken();

        return $params;
    }

    /**
     * 发送文本消息
     * @param string|array $toUser  消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     * @param string|array $toParty 消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     * @param string|array $toTag   消息接收标签, 多个接收标签用 | 分割, toUser为@all时忽略此参数
     * @param string       $content 消息内容, 最多2048字节
     * @param int          $safe    是否保密消息 1:是, 0:不是 默认: 0 不是
     * @return array
     */
    public function sendText($toUser, $toParty, $toTag, $content, $safe = 0)
    {

        if (is_array($toUser)) {
            $toUser = implode('|', $toUser);
        }
        if (is_array($toParty)) {
            $toParty = implode('|', $toParty);
        }
        if (is_array($toTag)) {
            $toTag = implode('|', $toTag);
        }

        $params = [
            'toUser' => $toUser,
            'toParty' => $toParty,
            'toTag' => $toTag,
            'content' => $content,
            'safe' => $safe
        ];

        return $this->messageSDK->sendText($this->_wxworkAccessToken($params));
    }

    /**
     * 发送图文消息
     * @param string|array $toUser   消息接受者, 多个接收者用 | 分割, 最多1000人, @all 为向关注企业号应用的所有人发送
     * @param string|array $toParty  消息接收部门, 多个接收部门用 | 分割, 最多100个, toUser为@all时忽略此参数
     * @param string|array $toTag    消息接收标签, 多个接收标签用 | 分割, toUser为@all时忽略此参数
     * @param array        $articles 图文消息, 支持1~8个图文消息
     *                      + title       标题 不超过128字节, 超过自动截断
     *                      + description 描述 不超过512字节, 超过自动截断
     *                      + url         点击跳转URL
     *                      + picUrl      图片URL 支持JPG、PNG 大图640320，小图8080。如不填，在客户端不显示图片
     * @param int          $safe     是否保密消息 1:是, 0:不是 默认: 0 不是
     * @return array
     */
    public function sendNews($toUser, $toParty, $toTag, $articles, $safe = 0)
    {

        if (is_array($toUser)) {
            $toUser = implode('|', $toUser);
        }
        if (is_array($toParty)) {
            $toParty = implode('|', $toParty);
        }
        if (is_array($toTag)) {
            $toTag = implode('|', $toTag);
        }

        $params = [
            'toUser' => $toUser,
            'toParty' => $toParty,
            'toTag' => $toTag,
            'articles' => $articles,
            'safe' => $safe,
        ];

        return $this->messageSDK->sendNews($this->_wxworkAccessToken($params));
    }
}
