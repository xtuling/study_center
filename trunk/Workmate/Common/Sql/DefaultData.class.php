<?php
/**
 * 应用安装时的初始数据文件
 * data.php
 * $Author$
 */

namespace Common\Sql;

class DefaultData
{

    /**
     * 安装数据
     * @author
     */
    public static function installData()
    {

        return [
            'workmate_config_default' => [
                [
                    'key' => 'release',
                    'value' => 1,
                    'type' => 0,
                    'comment' => '开启同事圈发布审核，0关闭，1开启',
                ],
                [
                    'key' => 'comment',
                    'value' => 1,
                    'type' => 0,
                    'comment' => '开启同事圈评论审核，0关闭，1开启',
                ],
            ],
        ];
    }
}
