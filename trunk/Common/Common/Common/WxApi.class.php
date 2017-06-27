<?php
/**
 * 微信API操作类
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/6/16
 * Time: 17:14
 */
namespace Common\Common;

use VcySDK\Service;
use VcySDK\WxQy\WebAuth;
use VcySDK\WxQy\Menu;
use VcySDK\WxQy\Pay;
use VcySDK\TemplateMsg;
use VcySDK\BizPay;

class WxApi
{

    /**
     * VcySDK 接口操作类
     */
    protected $_serv = null;

    /**
     * 构造方法
     */
    public function __construct()
    {

        $this->_serv = &Service::instance();
    }

    /**
     * 实例化
     *
     * @return WxApi
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 获取js签名
     *
     * @param string $url
     * @return array|bool
     */
    public function getJsSign($url)
    {

        $webauth = new WebAuth($this->_serv);

        return $webauth->jssignature($url);
    }

    /**
     * 获取支付参数
     *
     * @param array $data + memUid string uid
     *                    + worTotalFee double 金额
     *                    + worBody string 商品描述
     *                    + worIp string 用户IP
     *                    + worNotifyUrl string 通知url
     * @return array
     */
    public function getPayParams($data)
    {

        $data['worTradeType'] = 'JSAPI';
        $pay = new Pay($this->_serv);

        return $pay->prepay($data);
    }

    /**
     * 创建菜单
     *
     * @param array  $data        菜单数据
     * @param string $callbackUrl 创建成功后的回调地址
     * @return array
     */
    public function createMenu($data, $callbackUrl)
    {

        $menu = new Menu($this->_serv);

        return $menu->create($data, $callbackUrl);
    }

    /**
     * 获取菜单
     *
     * @return array
     */
    public function getMenu()
    {

        $menu = new Menu($this->_serv);

        return $menu->get();
    }

    /**
     * 发送模板消息
     *
     * @param array $data 模板数据
     * @return array
     */
    public function sendTplMsg($data)
    {

        $tplMsg = new TemplateMsg($this->_serv);

        return $tplMsg->send($data);
    }

    /**
     * 商家支付
     *
     * @param array $data + uid string
     *                    + platformOrderNo string 订单号，长度为32
     *                    + amount int 转账金额，单位分，最小金额为1元
     * @return array
     */
    public function bizPay($data)
    {

        $bizPay = new BizPay($this->_serv);

        return $bizPay->transfers($data);
    }
}
