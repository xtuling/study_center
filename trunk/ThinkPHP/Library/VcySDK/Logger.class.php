<?php

/**
 * Logger.class.php
 * 日志记录
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK;

class Logger
{

    /**
     * 一般错误: 一般性错误
     *
     * @var string
     */
    const ERR = 'ERR';

    /**
     * 信息: 程序输出信息
     *
     * @var string
     */
    const INFO = 'INFO';

    /**
     * 调试: 调试信息
     *
     * @var string
     */
    const DEBUG = 'DEBUG';

    /**
     * 写操作日志
     *
     * @param string $log 日志内容字串
     * @param string $level 日志等级
     * @param string $file 日志文件路径
     * @return boolean
     */
    public static function write($log, $level = self::ERR, $file = '')
    {

        $now = rgmdate(time(), 'Y-m-d H:i');
        // 如果未指定日志文件, 则自动生成
        if (empty($file)) {
            $file = Config::instance()->logPath . rgmdate(time(), 'y_m_d') . '.log';
        }

        // 自动创建日志目录
        $log_dir = dirname($file);
        if (! is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($file) && Config::instance()->logSize <= filesize($file)) {
            rename($file, dirname($file) . '/' . time() . '-' . basename($file));
        }

        // 写入文件
        file_put_contents($file, "[{$now}] " . $_SERVER['REMOTE_ADDR'] . ' ' . BOARD_URL . "\r\n{$log}\r\n", FILE_APPEND);
        return true;
    }
}
