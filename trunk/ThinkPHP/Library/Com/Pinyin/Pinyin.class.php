<?php
/**
 * 汉字拼音类
 */
namespace Com\Pinyin;

class Pinyin
{

    /**
     * utf-8 编码的汉字库
     *
     * @var string
     */
    private $_chinese_haracters_data;

    /**
     * 编码
     *
     * @var string
     */
    private $_charset = 'utf-8';

    /**
     * 构造字符串转拼音类
     * <p>自动载入汉字拼音库</p>
     */
    public function __construct()
    {
        if (empty($this->_chinese_haracters_data)) {
            $this->_chinese_haracters_data = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ChineseCharacters.dat');
        }
    }

    /**
     * 提取字符串最多前 $first_count 个首字母
     *
     * @param string $string
     * @param number $first_count
     * @return string
     */
    public function to_ucwords_first($string = '', $first_count = 4)
    {
        // 先转换为不带声调的拼音
        $string_without_tone = $this->to_without_tone($string, ' ', false);
        // 移除非字母、数字以及空格的字符
        $string_without_tone = preg_replace('/[^a-z0-9 ]/i', '', trim($string_without_tone));
        if (count(explode(' ', trim($string_without_tone))) > 1) {
            // 如果2个“词”以上则取每个词的首字母
            return substr($this->to_ucwords($string, ''), 0, $first_count);
        } else {
            // 如果是1个“词”，则取整词的前 $first_count 位
            return substr(strtoupper($string_without_tone), 0, $first_count);
        }
    }

    /**
     * 转成带有声调的拼音字符串
     *
     * @param string $string
     *            需要进行转换的字符串
     * @param string $delimiter
     *            转换后的拼音之间分隔符，默认为：半角空格
     * @param boolean $ignore_other
     *            是否忽略非汉字内容，默认为：false
     * @return string
     */
    public function to_with_tone($string, $delimiter = ' ', $ignore_other = false)
    {
        $length = mb_strlen($string, $this->_charset);
        $output = '';
        for ($i = 0; $i < $length; $i ++) {
            $word = mb_substr($string, $i, 1, $this->_charset);
            if (preg_match('/^[\x{4e00}-\x{9fa5}]$/u', $word) && preg_match('/\,' . preg_quote($word) . '(.*?)\,/', $this->_chinese_haracters_data, $matches)) {
                $output .= $matches[1] . $delimiter;
            } elseif (! $ignore_other) {
                $output .= $word;
            }
        }

        return $output;
    }

    /**
     * 转换为无声调的拼音字符串
     *
     * @param string $string
     *            需要进行转换的字符串
     * @param string $delimiter
     *            转换后的拼音之间的分隔符
     * @param boolean $ignore_other
     *            是否忽略非汉字内容
     * @return string
     */
    public function to_without_tone($string, $delimiter = ' ', $ignore_other = true)
    {
        // 先转换为带声调的字符串
        $string_with_tone = $this->to_with_tone($string, $delimiter, $ignore_other);

        // 替换声调字符并输出
        return str_replace(array(
            'ā',
            'á',
            'ǎ',
            'à',
            'ō',
            'ó',
            'ǒ',
            'ò',
            'ē',
            'é',
            'ě',
            'è',
            'ī',
            'í',
            'ǐ',
            'ì',
            'ū',
            'ú',
            'ǔ',
            'ù',
            'ǖ',
            'ǘ',
            'ǚ',
            'ǜ',
            'ü'
        ), array(
            'a',
            'a',
            'a',
            'a',
            'o',
            'o',
            'o',
            'o',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'u',
            'u',
            'u',
            'u',
            'v',
            'v',
            'v',
            'v',
            'v'
        ), $string_with_tone);
    }

    /**
     * 转换为拼音首字母组合
     *
     * @param string $string
     *            待转换的字符串
     * @param string $delimiter
     *            字母之间的分隔符号
     * @return string
     */
    public function to_ucwords($string, $delimiter = '')
    {
        $string_without_tone = ucwords($this->to_without_tone($string, ' ', false));
        $ucwords = preg_replace('/[^A-Z]/', '', $string_without_tone);
        if (! empty($delimiter)) {
            $ucwords = implode($delimiter, str_split($ucwords));
        }

        return $ucwords;
    }
}
