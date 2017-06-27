<?php
/**
 * Validator.class.php
 * $Author$
 * $Id$
 */
namespace Com;

class Validator
{

    /**
     * 检查Email格式是否有效。
     *
     * @param string $email
     *
     * @return boolean
     */
    public static function is_email($email)
    {

        return $email && ! isset($email{40}) && preg_match('/^[_\\.0-9a-z-]+@([0-9a-z][0-9a-z-]*\\.)+[a-z0-9]{2,}$/i', $email);
    }

    /**
     * 检查QQ格式是否有效
     *
     * @param string $qq
     *
     * @return boolean
     */
    public static function is_qq($qq)
    {

        return $qq && preg_match('/^\d{5,11}$/', $qq);
    }

    /**
     * 检查Realname是否有效
     *
     * @param string $realname 真实名称
     * @param int    $min      最小长度
     * @param int    $max      最大长度
     *
     * @return boolean
     */
    public static function is_realname($realname, $min = 3, $max = 20)
    {

        return strlen($realname) > $min && strlen($realname) < $max && htmlspecialchars($realname) == $realname;
    }

    /**
     * 检查密码格式是否有效。
     * 规则：长度大于5小于31，并且在可见的半角字符内(包含空格)
     *
     * @param string $password
     *
     * @return boolean
     */
    public static function is_password($password)
    {

        return (32 == strlen($password) && preg_match('/^[0-9a-zA-Z]+$/', $password));
    }

    /**
     * 检查用户名是否有效。
     *
     * @param string $username
     * @param int    $max
     * @param int    $min
     *
     * @return boolean
     */
    public static function is_username($username, $max, $min = 2)
    {

        if (! preg_match('/^[^\'\,"%*\n\r\t?<>\\/\\\\ ]+$/', $username)) {
            return false;
        }
        $len = strlen($username);
        // 判断最小长度
        $min = 0 < $min ? $min : 2;
        if ($min > $len) {
            return false;
        }
        // 判断最大长度
        $max = $min < $max ? $max : $min;
        if ($max < $len) {
            return false;
        }

        return true;
    }

    /**
     * 检查是否为整数。
     *
     * @param int $int
     *
     * @return boolean
     */
    public static function is_int($int)
    {

        return preg_match('/^\d+$/', $int);
    }

    /**
     * 检查图片文件名
     *
     * @param string $filename
     *
     * @return boolean
     */
    public static function is_image($filename)
    {

        switch (strtolower(substr(strrchr($filename, '.'), 1))) {
            case 'gif':
            case 'jpg':
            case 'png':
            case 'bmp':
                return true;
            default:
                return false;
        }
    }

    /**
     * 检查电话号码格式是否正确
     *
     * @param string $str
     *
     * @return boolean
     */
    public static function is_phone($str)
    {

        return preg_match('/(^0?1[2,3,5,6,7,8,9]\d{9}$)|(^(\d{3,4})-(\d{7,8})$)|(^(\d{7,8})$)|(^(\d{3,4})-(\d{7,8})-(\d{1,4})$)|(^(\d{7,8})-(\d{1,4})$)/', $str);
    }

    /**
     * 手机号码是否有效
     *
     * @param string $mobile
     *
     * @return boolean
     */
    public static function is_mobile($mobile)
    {

        return preg_match('/^\d{11}$/', $mobile);
    }

    /**
     * 电话号码是否为400电话
     *
     * @param string $str
     *
     * @return boolean
     */
    public static function is_400_phone($str)
    {

        return preg_match('/^400\d{7}$/', $str);
    }

    /**
     * 电话号码是否为800电话
     *
     * @param string $str
     *
     * @return boolean
     */
    public static function is_800_phone($str)
    {

        return preg_match('/^800\d{7}$/', $str);
    }

    /**
     * 检查住址
     *
     * @param string $str
     *
     * @return boolean
     */
    public static function is_addr($str)
    {

        return strlen($str) > 7 && htmlspecialchars($str) == $str;
    }

    /**
     * 检查邮政编码格式是否正确
     *
     * @param string $str
     *
     * @return boolean
     */
    public static function is_postalcode($str)
    {

        return preg_match('/^\d{6}$/', $str);
    }

    /**
     * 检查一个数值是否在两个数值之间
     *
     * @param mixed $x
     * @param mixed $min
     * @param mixed $max
     *
     * @return boolean
     */
    public static function is_in_range($x, $min, $max)
    {

        return $x > $min && $x < $max;
    }

    /**
     * 判别是否相等
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return boolean
     */
    public static function is_equal($a, $b = null)
    {

        return $a == $b;
    }

    /**
     * isForbiddenWord 判断是否含有违禁词
     *
     * @param mixed $content
     * @param array $forbidden_word
     *
     * @return bool
     */
    public static function is_forbidden_word($content, $forbidden_word = array())
    {

        $content = strtolower($content);
        $len = strlen($content);
        foreach ($forbidden_word as $_word) {
            $tmpLen = strlen(str_replace(strtolower($_word), '', $content));
            if ($tmpLen != $len) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断字符串长度是否在两个数值之间
     *
     * @param string  $str      字符串
     * @param integer $min      最小长度
     * @param integer $max      最大长度
     * @param string  $encoding 编码
     *
     * @return boolean
     */
    public static function is_len_in_range($str, $min, $max, $encoding = null)
    {

        $len = $encoding ? mb_strlen($str, $encoding) : mb_strlen($str);

        return ($len >= $min && $len <= $max);
    }

    /**
     * 判断一个数组中的所有值是否为整数。
     *
     * @param array $arr
     *
     * @return boolean
     */
    public static function is_int_array($arr)
    {

        if (! is_array($arr)) {
            return false;
        }
        foreach ($arr as $v) {
            if (! Validator::is_int($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判别传入的第一个参数是否在剩下的几个参数中。
     *
     * @return boolean
     */
    public static function not_in()
    {

        $params = func_get_args();
        $key = array_shift($params);
        if (count($params) == 1) {
            return in_array($key, $params[0]);
        } else {
            return in_array($key, $params);
        }
    }

    /**
     * is_id_card
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public static function is_id_card($id)
    {

        $len = strlen($id);
        if ($len == 15) {
            return preg_match("/^[0-9]{15}$/", $id);
        } else if ($len != 18) {
            return false;
        }

        $id = strtoupper($id);
        $total = 0;
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $vercode = '10X98765432';
        for ($i = 0; $i < 17; $i ++) {
            $total += intval($id{$i}) * $factor[$i];
        }
        $mod = $total % 11;
        if ($id{17} == $vercode{$mod}) {
            return true;
        }

        return false;
    }

    /**
     * is_ip
     * 是否为IP地址
     *
     * @param string $ips
     * @param string $delimiter
     *
     * @return boolean
     */
    public static function is_ip($ips, $delimiter = '.')
    {

        $result = array();
        $ipArr = explode($delimiter, $ips);
        foreach ($ipArr as $ip) {
            $ip = trim($ip);
            if ($ip < 0 || $ip > 255) {
                return false;
            }
            $result[] = $ip;
        }

        return join($delimiter, $result);
    }

    /**
     * is_domain
     * 判断是否域名地址
     *
     * @param string $domain 域名地址
     *
     * @return boolean
     */
    public static function is_domain($domain)
    {

        if (preg_match('/^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/i', $domain)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * is_url
     *
     * @param mixed $url
     *
     * @return boolean
     */
    public static function is_url($url)
    {

        if (! $url) {
            return false;
        }

        return strlen($url) < 4096 && preg_match('/(http:\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/', $url); // by zhuchenguang
    }

    /**
     * is_required
     * 是否是必须的
     *
     * @param mixed $data
     *
     * @return boolean
     */
    public static function is_required($data)
    {

        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * is_date
     *
     * @param mixed $date_time
     *
     * @access public
     * @return bool
     */
    public static function is_date($date_time)
    {

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})( ([01]?[0-9]|2[0-3]):([0-5]?[0-9])(:([0-5]?[0-9]))?)?$/', $date_time, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断字符串长度是否在两个数值之间(中文算作2个字节)
     *
     * @param string $str
     * @param mixed  $min
     * @param mixed  $max
     *
     * @return boolean
     */
    public static function is_chinese_len_in_range($str, $min, $max)
    {

        preg_match_all("/[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}/", $str, $arr);
        preg_match_all("/[\x20-\x7E]/", $str, $arr2);
        $len = count($arr[0]) * 2 + count($arr2[0]);

        return $len >= $min && $len <= $max;
    }

    /**
     * is_lan_ip
     * 是否是局域网IP
     *
     * @param mixed $ip
     *
     * @return bool
     */
    public static function is_lan_ip($ip)
    {

        if ($ip >= ip2long('10.0.0.0') && $ip <= ip2long('10.255.255.255')) {
            return true;
        }
        if ($ip >= ip2long('127.0.0.0') && $ip <= ip2long('127.255.255.255')) {
            return true;
        }
        if ($ip >= ip2long('172.16.0.0') && $ip <= ip2long('172.16.255.255')) {
            return true;
        }
        if ($ip >= ip2long('192.168.0.0') && $ip <= ip2long('192.168.255.255')) {
            return true;
        }
        if ($ip >= ip2long('244.0.0.0') && $ip <= ip2long('244.255.255.255')) {
            return true;
        }
        if ($ip == ip2long('255.255.255.255')) {
            return true;
        }

        return false;
    }

    /**
     * 计算字符串字符个数是否在两值之间（含）
     * 无论任何字符均按一个来计算同mb_strlen
     *
     * @param string $string
     * @param number $min
     * @param number $max
     * @param string $charset
     *
     * @return boolean
     */
    public static function is_string_count_in_range($string, $min, $max, $charset = 'utf-8')
    {

        $length = mb_strlen($string, $charset);

        return $length >= $min && $length <= $max;
    }

    /**
     * 检查一个字符串是否为32位md5值
     *
     * @param string $string
     *
     * @return boolean
     */
    public static function is_md5($string = '')
    {

        $string = rstrtolower($string);

        return preg_match('/^[0-9a-f]{32}$/', $string) ? true : false;
    }

    /**
     * 验证微信 suiteid 格式
     *
     * @param string $suiteid 套件ID
     *
     * @return boolean
     */
    public static function is_suiteid($suiteid = '')
    {

        return preg_match('/^tj[0-9a-z]{16,30}/', $suiteid) ? true : false;
    }

    /**
     * 验证微信 corpid 格式
     *
     * @param string $corpid 企业corpID
     *
     * @return boolean
     */
    public static function is_corpid($corpid = '')
    {

        return preg_match('/^wx[0-9a-z]{16,30}/', $corpid) ? true : false;
    }

    /**
     * verify
     *
     * @param array $errors
     * @param array $data
     * @param mixed $options
     *            + fieldNmae(post Field name)
     *            + rules
     *            + required => true
     *            + email => true
     *            + realname => true
     *            + password => true
     *            + userName => true
     *            + int => true
     *            + image => true
     *            + phone => true
     *            + mobile => true
     *            + addr => true
     *            + postalcode => true
     *            + equal => 21
     *            + lenInRange => array(min, max)
     *            + inRange => array(min, max)
     *            + idCard => true
     *            + ip => true
     *            + url => true
     *            + fieldNmae(post Field name)
     *            + messages => msg
     *
     * @return array $error
     *         <code>
     *         $options = array();
     *         $options['email']['required']['rule'] = true;
     *         $options['email']['required']['message'] = 'email 不能为空';
     *         $options['password']['required']['rule'] = true;
     *         $options['password']['required']['message'] = '密码不能为空';
     *         $options['password']['password']['rule'] = true;
     *         $options['password']['password']['message'] = '密码必须6个字符以上';
     *         $options['age']['int']['rule'] = true;
     *         $options['age']['int']['message'] = 'int';
     *         $options['age']['in_range']['rule'] = array(10, 100);
     *         $options['age']['in_range']['message'] = '10-100 ';
     *         $options['url']['url_return']['rule'] = array('ValidatorTest::url_return');
     *         $options['url']['url_return']['message'] = 'url地址不正确';
     *         $data = array();
     *         $data['email'] = 'snowrui@yeah.net';
     *         $data['password'] = '1et';
     *         $data['age'] = '1';
     *         Validator::verify($error, $data, $options);
     *         </code>
     */
    public static function verify(&$errors, $data, $options)
    {

        $errors = array();
        // 如果数据为空或者不是数组
        if (! $data || ! is_array($data) || ! is_array($options)) {
            return false;
        }
        // 遍历验证规则, $field_name: 键值; $rules: 规则(验证方法)信息
        foreach ($options as $field_name => $rules) {
            // 遍历所有规则, $method: 方法名; $value: 值
            foreach ($rules as $method => $value) {
                // 验证方法名
                $m = 'is_' . $method;
                settype($value['rule'], 'array');
                // 如果是当前的验证方法
                if (method_exists('\Com\Validator', $m)) {
                    // 把待验证字串推入规则数组的起始位置
                    array_unshift($value['rule'], $data[$field_name]);
                    // 方法
                    $m = array(
                        '\Com\Validator',
                        $m
                    );
                } else { // 自定义方法
                    // 自定义方法, 第一参数肯定是方法名, 所以推出作为 $m
                    $m = array_shift($value['rule']);
                    // 把待验证字串推入规则数组的起始位置
                    array_unshift($value['rule'], $data[$field_name]);
                }
                // 调用验证方法
                if (! call_user_func_array($m, $value['rule'])) {
                    // 记录错误信息
                    $errors[$field_name] = $options[$field_name][$method]['message'];
                }
            }
        }

        return empty($errors) ? true : false;
    }
}
