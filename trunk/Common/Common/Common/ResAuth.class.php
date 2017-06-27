<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/22
 * Time: 17:31
 */

namespace Common\Common;

use Com\Cookie;
use VcySDK\Service;
use VcySDK\Adminer;

class ResAuth
{
    /**
     * 用户类型：后台管理员
     */
    const USER_TYPE_ADMIN = 1;

    /**
     * 用户类型：手机端用户
     */
    const USER_TYPE_MOBILE = 2;

    /**
     * 实例化
     *
     * @return ResAuth
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
     * 创建资源鉴权数据
     * @param int $user_type 用户类型
     * @param string $uid 用户ID
     * @return string
     */
    public function buildAuthData($user_type, $uid)
    {
        $secret_key = cfg('RES_AUTH_SECRET');

        if (!empty($secret_key)) {
            // 密文=企业ID:用户类型:用户ID
            $secret_value = base64_encode(sprintf('%s:%s:%s', QY_DOMAIN, $user_type, $uid));
            // 签名=密文+密钥
            $sign = md5($secret_value . $secret_key);
            // 鉴权数据=密文:签名
            return sprintf('%s:%s', $secret_value, $sign);
        }

        return '';
    }

    /**
     * 解析密文数据
     * @param string $secret 密文（企业ID:用户类型:用户ID）
     * @return array
     *          + int   user_type   用户类型
     *          + array user        用户数据
     */
    public function parseSecret($secret)
    {
        $data = [];

        if (empty($secret)) {
            return $data;
        }

        // 解析数据
        $secret_datas = explode(':', $secret);

        if (!is_array($secret_datas) || count($secret_datas) !== 3) {
            return $data;
        }

        list($qy_domain, $user_type, $uid) = $secret_datas;

        // 域名、用户类型不匹配或用户ID为空
        if ($qy_domain !== QY_DOMAIN || !in_array($user_type, [self::USER_TYPE_ADMIN, self::USER_TYPE_MOBILE]) || empty($uid)) {
            $this->clearCookie();
            return $data;
        }

        // 根据用户类型获取用户数据
        switch ($user_type) {
            // 管理员
            case self::USER_TYPE_ADMIN:
                $adminerSdk = new Adminer(Service::instance());
                $user = $adminerSdk->fetch(['eaId' => $uid]);

                // 未找到管理员
                if (empty($user)) {
                    $this->clearCookie();
                    return $data;
                }

                break;
            case self::USER_TYPE_MOBILE:
                $userServ = &User::instance();
                $user = $userServ->getByUid($uid);

                // 未找到用户或用户已删除
                if (empty($user) || !$userServ->isNormal($user)) {
                    $this->clearCookie();
                    return $data;
                }

                break;
        }

        $data = [
            'user_type' => $user_type,
            'user' => $user,
        ];

        return $data;
    }

    /**
     * 将资源鉴权数据写入 Cookie
     * @param int $user_type 用户类型
     * @param string $uid 用户ID
     * @return bool
     */
    public function writeCookie($user_type, $uid)
    {
        $cookie_name = cfg('RES_AUTH_COOKIE_NAME');

        if (empty($cookie_name)) {
            return false;
        }

        $cookie_value = $this->buildAuthData($user_type, $uid);

        if (empty($cookie_value)) {
            return false;
        }

        $cookie = &Cookie::instance();
        $cookie->setx($cookie_name, $cookie_value);
        return true;
    }

    /**
     * 清除 Cookie 中的资源鉴权数据
     * @return bool
     */
    public function clearCookie()
    {
        $cookie_name = cfg('RES_AUTH_COOKIE_NAME');

        if (empty($cookie_name)) {
            return false;
        }

        $cookie = &Cookie::instance();
        $cookie->remove($cookie_name);
        return true;
    }
}
