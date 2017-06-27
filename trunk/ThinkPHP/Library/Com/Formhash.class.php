<?php
/**
 * Formhash.class.php
 * $Author$
 * $Id$
 */
namespace Com;

class Formhash
{

    /**
     * __secret
     * 加密用的 secret
     * 
     * @var string
     */
    private $__secret = 'vchangyi, to be best.';
    
    // 实例化
    public static function &instance()
    {
        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    // 构造方法
    public function __construct()
    {
        $secret = cfg('FORMHASH_SECRET');
        if (! empty($secret)) {
            $this->__secret = $secret;
        }
    }

    /**
     * generate
     * 生成hash
     * 
     * @param string $hash
     *            hash 字串
     * @param int $timestamp
     *            时间
     * @param string $random
     *            8位随机字串
     * @return boolean
     */
    public function generate(&$hash, $timestamp = null, $random = '')
    {
        // 时间戳为空
        if (! $timestamp) {
            $timestamp = NOW_TIME;
        }
        // 8位随机值
        if (empty($random)) {
            $random = random(8);
        }
        $ymdhis = rgmdate($timestamp, 'YmdHis');
        $md5 = md5(md5($ymdhis . $random . $this->__secret) . $this->__secret);
        $hash = substr($md5, 0, 2) . substr($md5, - 8) . $ymdhis . $random;
        
        return true;
    }

    /**
     * check
     * 检查hash是否正确
     * 
     * @param string $hash
     *            HASH串
     * @param int $timestmap
     *            时间戳
     * @return boolean
     */
    public function check($hash, &$timestamp)
    {
        $timestamp = rstrtotime(preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1-\2-\3 \4:\5:\6', substr($hash, 10, 14)));
        $random = substr($hash, - 8);
        // 生成hash字串
        $tmp_hash = '';
        $this->generate($tmp_hash, $timestamp, $random);
        
        return $tmp_hash == $hash;
    }
}
