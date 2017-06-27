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
            'sign_config_default' => [
                [
                    'cycle' => 7,
                    'integral_rules' => 'a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}',
                ],
            ],
        ];
    }
}
