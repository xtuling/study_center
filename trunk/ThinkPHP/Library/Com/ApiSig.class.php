<?php
/**
 * ApiSig.class.php
 * 全局 Api 签名验证算法
 * $Author$
 */
namespace Com;

class ApiSig
{
    /** 秘钥 */
    protected $_secret = '';
    /** 签名有效时间(单位: 秒) */
    protected $_expire = 600;

    /**
     * 实例化
     *
     * @param string $secret
     *            秘钥
     * @param number $expire
     *            有效时间
     * @return \Com\ApiSig
     */
    public static function &instance($secret = '', $expire = 600)
    {
        static $instance;
        // 有效时间
        $expire = empty($expire) ? cfg('API_SIG_EXPIRE') : $expire;
        // 秘钥
        $secret = empty($secret) ? cfg('API_SECRET') : $secret;
        // 根据秘钥初始化
        $md5 = md5($secret);
        if (empty($instance[$md5])) {
            $instance[$md5] = new self($secret, $expire);
        }

        return $instance[$md5];
    }

    public function __construct($secret, $expire)
    {
        $this->_secret = $secret;
        $this->_expire = $expire;
    }

    /**
     * 生成 sig 值, 需要注意的是, $source 里面不要加时间戳和秘钥, 不能使用 ts 和 sig 键值
     *
     * @param mixed $source
     *            源字串
     * @param int $timestamp
     *            时间戳
     * @return string
     */
    public function create($source, $timestamp = 0, $authkey = '')
    {
        // 强制转换成数组
        $source = (array) $source;
        if (! empty($source['ts']) && 0 >= $timestamp) {
            $timestamp = $source['ts'];
        }
        unset($source['ts'], $source['sig']);
        // 参数数组
        $source[] = 0 >= $timestamp ? NOW_TIME : $timestamp;
        if (empty($authkey)) {
            $source[] = $this->_secret;
        } else {
            $source[] = $authkey;
        }
        // 排序
        sort($source, SORT_STRING);

        return sha1(implode($source));
    }

    /**
     * 检查默认 sig
     *
     * @param array $params
     *            外部参数
     * @return boolean
     */
    public function check($params, $throw_expire = false)
    {
        // 取出时间戳和 sig
        $ts = (int) $params['ts'];
        $sig = (string) $params['sig'];
        // 删除键值
        unset($params['ts'], $params['sig']);
        // 检查时间
        if ($ts + $this->_expire < NOW_TIME) {
            // 如果需要抛错, 则直接向上抛
            if ($throw_expire) {
                E('_ERR_SIGNATURE_EXPIRED');

                return false;
            }

            return false;
        }
        // 如果 sig 不相等
        if ($sig != $this->create($params, $ts)) {
            return false;
        }

        return true;
    }
}
