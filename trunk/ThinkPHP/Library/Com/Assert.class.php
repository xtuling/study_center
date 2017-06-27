<?php
/**
 * PHP 断言验证
 * author: zhoutao
 * 用法: use Com\Assert
 * Assert::isArray('要验证的值', '报错码');
 * 报错码会直接调用E方法抛错
 */

namespace Com;

class Assert
{
    const MB_STRLEN_TYPE = 'utf-8';

    /**
     * 字符串长度 等于 $length
     * @param $data
     * @param $errorMsg
     * @param $length
     */
    static public function length($data, $errorMsg, $length)
    {
        if (mb_strlen($data, self::MB_STRLEN_TYPE) !== $length) {
            E($errorMsg);
        }
    }

    /**
     * 字符串长度在 $from < 长度 < $to 范围内
     * @param $data
     * @param $errorMsg
     * @param $from
     * @param $to
     */
    static public function lengthBetween($data, $errorMsg, $from, $to)
    {
        $length = mb_strlen($data, self::MB_STRLEN_TYPE);

        if ($length < $from || $length > $to) {
            E($errorMsg);
        }
    }

    /**
     * 字符串长度小于 $length
     * @param $data
     * @param $errorMsg
     * @param $length
     */
    static public function lengthLess($data, $errorMsg, $length)
    {
        if (mb_strlen($data, self::MB_STRLEN_TYPE) > $length) {
            E($errorMsg);
        }
    }

    /**
     * 字符串长度大于 $length
     * @param $data
     * @param $errorMsg
     * @param $length
     */
    static public function lengthGreater($data, $errorMsg, $length)
    {
        if (mb_strlen($data, self::MB_STRLEN_TYPE) < $length) {
            E($errorMsg);
        }
    }

    /**
     * 在数组内
     * @param $data
     * @param $errorMsg
     * @param array $range
     */
    static public function inArray($data, $errorMsg, array $range)
    {
        if (!in_array($data, $range, true)) {
            E($errorMsg);
        }
    }

    /**
     * 是数组
     * @param $data
     * @param $errorMsg
     */
    static public function isArray($data, $errorMsg)
    {
        if (!is_array($data)) {
            E($errorMsg);
        }
    }

    /**
     * 数组有键值 $key
     * @param $data
     * @param $errorMsg
     * @param $key
     */
    static public function hasKey($data, $errorMsg, $key)
    {
        if (!array_key_exists($key, $data)) {
            E($errorMsg);
        }
    }

    /**
     * 数组的count等于参数$count
     * @param $data
     * @param $errorMsg
     * @param $count
     */
    static public function count($data, $errorMsg, $count)
    {
        if (count($data) !== $count) {
            E($errorMsg);
        }
    }

    /**
     * 数组的count小于参数$count
     * @param $data
     * @param $errorMsg
     * @param $count
     */
    static public function countLess($data, $errorMsg, $count)
    {
        if (count($data) > $count) {
            E($errorMsg);
        }
    }

    /**
     * 数组的count大于参数$count
     * @param $data
     * @param $errorMsg
     * @param $count
     */
    static public function countGreater($data, $errorMsg, $count)
    {
        if (count($data) < $count) {
            E($errorMsg);
        }
    }

    /**
     * 值的范围在 $from <= value <= $to 范围内
     * @param $data
     * @param $errorMsg
     * @param $from
     * @param $to
     */
    static public function between($data, $errorMsg, $from, $to)
    {
        if ($data < $from || $data > $to) {
            E($errorMsg);
        }
    }

    /**
     * 值的范围在 $from < value < $to 范围内
     * @param $data
     * @param $errorMsg
     * @param $from
     * @param $to
     */
    static public function betweenStrict($data, $errorMsg, $from, $to)
    {
        if ($data <= $from || $data >= $to) {
            E($errorMsg);
        }
    }

    /**
     * 是布尔值
     * @param $data
     * @param $errorMsg
     */
    static public function bool($data, $errorMsg)
    {
        if (!is_bool($data)) {
            E($errorMsg);
        }
    }

    /**
     * 是 true
     * @param $bool
     * @param $errorMsg
     */
    static public function isTrue($bool, $errorMsg)
    {
        if ($bool === false) {
            E($errorMsg);
        }
    }

    /**
     * 是 false
     * @param $bool
     * @param $errorMsg
     */
    static public function isFalse($bool, $errorMsg)
    {
        if ($bool === true) {
            E($errorMsg);
        }
    }

    /**
     * 字符串是十进制数字
     * @param $data
     * @param $errorMsg
     */
    static public function digit($data, $errorMsg)
    {
        if (!ctype_digit($data)) {
            E($errorMsg);
        }
    }

    /**
     * 是空值
     * @param $data
     * @param $errorMsg
     */
    static public function isEmpty($data, $errorMsg)
    {
        if (!empty($data)) {
            E($errorMsg);
        }
    }

    /**
     * 不是空值
     * @param $data
     * @param $errorMsg
     */
    static public function notEmpty($data, $errorMsg)
    {
        if (empty($data)) {
            E($errorMsg);
        }
    }

    /**
     * 是float值
     * @param $data
     * @param $errorMsg
     */
    static public function float($data, $errorMsg)
    {
        if (!is_float($data)) {
            E($errorMsg);
        }
    }

    /**
     * 是int值
     * @param $data
     * @param $errorMsg
     */
    static public function int($data, $errorMsg)
    {
        if (!is_int($data)) {
            E($errorMsg);
        }
    }

    /**
     * 值小于等于 $number
     * @param $data
     * @param $errorMsg
     * @param $number
     */
    static public function less($data, $errorMsg, $number)
    {
        if ($data > $number) {
            E($errorMsg);
        }
    }

    /**
     * 值大于等于 $number
     * @param $data
     * @param $errorMsg
     * @param $number
     */
    static public function greater($data, $errorMsg, $number)
    {
        if ($data < $number) {
            E($errorMsg);
        }
    }

    /**
     * 值小于 $number
     * @param $data
     * @param $errorMsg
     * @param $number
     */
    static public function lessStrict($data, $errorMsg, $number)
    {
        if ($data >= $number) {
            E($errorMsg);
        }
    }

    /**
     * 值大于 $number
     * @param $data
     * @param $errorMsg
     * @param $number
     */
    static public function greaterStrict($data, $errorMsg, $number)
    {
        if ($data <= $number) {
            E($errorMsg);
        }
    }

    /**
     * 符合正则匹配
     * @param $data
     * @param $errorMsg
     * @param $pattern
     * @return $this
     */
    static public function match($data, $errorMsg, $pattern)
    {
        $checkResult = @preg_match($pattern, $data);

        if ($checkResult === 0) {
            E($errorMsg);
        }
    }

    /**
     * 值符合模式匹配
     * @param $data
     * @param $errorMsg
     * @param $pattern
     */
    static public function glob($data, $errorMsg, $pattern)
    {
        if (!fnmatch($pattern, $data)) {
            E($errorMsg);
        }
    }

    /**
     * 值小于 0
     * @param $data
     * @param $errorMsg
     */
    static public function negative($data, $errorMsg)
    {
        if ($data >= 0) {
            E($errorMsg);
        }
    }

    /**
     * 值大于 0
     * @param $data
     * @param $errorMsg
     */
    static public function positive($data, $errorMsg)
    {
        if ($data <= 0) {
            E($errorMsg);
        }
    }

    /**
     * 值和 $anotherValue 相同
     * @param $data
     * @param $errorMsg
     * @param $anotherValue
     */
    static public function isSame($data, $errorMsg, $anotherValue)
    {
        if ($data !== $anotherValue) {
            E($errorMsg);
        }
    }

    /**
     * 值和 $anotherValue 相同
     * @param $data
     * @param $errorMsg
     * @param $anotherValue
     */
    static public function notSame($data, $errorMsg, $anotherValue)
    {
        if ($data === $anotherValue) {
            E($errorMsg);
        }
    }

    /**
     * 值是 null
     * @param $data
     * @param $errorMsg
     */
    static public function isNull($data, $errorMsg)
    {
        if (!is_null($data)) {
            E($errorMsg);
        }
    }

    /**
     * 值不为 null
     * @param $data
     * @param $errorMsg
     */
    static public function notNull($data, $errorMsg)
    {
        if (is_null($data)) {
            E($errorMsg);
        }
    }

    /**
     * 值是数字 或 数字字符串
     * @param $data
     * @param $errorMsg
     */
    static public function numeric($data, $errorMsg)
    {
        if (!is_numeric($data)) {
            E($errorMsg);
        }
    }

    /**
     * 值是资源类型
     * @param $data
     * @param $errorMsg
     */
    static public function resource($data, $errorMsg)
    {
        if (!is_resource($data)) {
            E($errorMsg);
        }
    }

    /**
     * 值是字符串类型
     * @param $data
     * @param $errorMsg
     */
    static public function string($data, $errorMsg)
    {
        if (!is_string($data)) {
            E($errorMsg, $data);
        }
    }
}
