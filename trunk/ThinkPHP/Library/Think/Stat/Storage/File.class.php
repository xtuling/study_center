<?php
/**
 * File.class.php
 * 使用文本格式存储统计日志
 * @author Deepseath
 * @version $Id$
 */
namespace Think\Stat\Storage;

use Think\Stat;

/**
 * 使用文本格式存储统计日志
 */
class File extends Stat
{

    /**
     * 存储配置信息
     */
    private $_options = [];

    /**
     * 定义存储换行符（禁止修改）
     */
    private $_newLine = "\n";

    /**
     * 数据存储分隔符（禁止修改）
     */
    private $_comma = "\t";

    /**
     * 构造函数
     */
    public function __construct($options = array())
    {
        $this->_options = $options;
    }

    /**
     * 日志存储
     */
    public function save()
    {
        // 获取统计字段
        parent::$statInfo = parent::statInfo();
        // 获取存储文件名
        $filename = $this->_logFilename(parent::$statInfo['domain'], parent::$statInfo['timestamp']);
        // 检查目录是否存在
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // 将数组值序列化存储
        $statInfo = self::$statInfo;
        foreach ($statInfo as &$_value) {
            if (is_array($_value)) {
                $_value = serialize($_value);
            }
        }
        // 存储数据
        $data = rgmdate(parent::$statInfo['timestamp'], 'Y-m-d H:i:s')
                    . $this->_comma . json_encode($statInfo, JSON_UNESCAPED_UNICODE)
                    . $this->_newLine;
        if (false === file_put_contents($filename, $data, FILE_APPEND)) {
            E(L('_STORAGE_WRITE_ERROR_') . ':' . $filename);
        } else {
            return true;
        }
    }

    /**
     * 构造存储文件路径
     * @param string $domain 企业标识符（域名）
     * @param integer $timestamp 存储时间戳
     * @return string
     */
    protected function _logFilename($domain, $timestamp)
    {
        return $this->_logBaseDirname($domain)
            . D_S . rgmdate($timestamp, 'Ym')
            . D_S . rgmdate($timestamp, 'Ymd')
            . D_S . rgmdate($timestamp, 'YmdH') . '.log';
    }

    /**
     * 构造日志存储基础目录
     * @param string $domain 企业标识符（域名）
     * @return string
     */
    protected function _logBaseDirname($domain)
    {
        return CODE_ROOT . D_S . 'Common' . D_S . 'Runtime'
            . D_S . $this->_options['path']
            . D_S . substr($domain, 0, 1)
            . D_S . substr($domain, -1)
            . D_S . $domain;
    }
}
