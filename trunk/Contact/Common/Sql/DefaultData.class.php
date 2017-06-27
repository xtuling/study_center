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
     * @author zhonglei
     */
    public static function installData()
    {

        return [
            'setting' => array(
                array(
                    'key' => 'manageAuths',
                    'value' => 'a:2:{s:12:"selectedList";a:0:{}s:5:"auths";a:0:{}}',
                    'type' => '1',
                    'comment' => '微信端管理权限'
                ),
                array(
                    'key' => 'jobMode',
                    'value' => 'input',
                    'type' => '0',
                    'comment' => '岗位输入方式'
                ),
                array(
                    'key' => 'roleMode',
                    'value' => 'input',
                    'type' => '0',
                    'comment' => '角色输入方式'
                ),
                array(
                    'key' => 'synctime',
                    'value' => '0',
                    'type' => '0',
                    'comment' => '最后同步时间'
                )
            ),
            'invite_setting' => [
                'content' => '',
                'share_content' => '',
                'type' => 1,
                'invite_udpids' => '',
                'check_type' => '3',
                'check_udpids' => '',
                'departments' => '',
                'inviter_write' => '',
                'form' => '',
                'qrcode_expire' => 0,
            ],
            'attr' => [
                [
                    'field_name' => 'memUsername',
                    'attr_name' => '姓名',
                    'postion' => 1,
                    'type' => 1,
                    'option' => '',
                    'order' => 1,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 0,
                    'is_required' => 1,
                    'is_required_edit' => 0,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 0,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 0,
                    'is_show' => 1,
                    'is_show_edit' => 0,
                ],
                [
                    'field_name' => 'memGender',
                    'attr_name' => '性别',
                    'postion' => 1,
                    'type' => 7,
                    'option' => serialize(
                        [
                            [
                                'name' => '男',
                                'value' => 1
                            ],
                            [
                                'name' => '女',
                                'value' => 2
                            ]
                        ]
                    ),
                    'order' => 2,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 0,
                    'is_required' => 1,
                    'is_required_edit' => 0,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 0,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 0,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'memMobile',
                    'attr_name' => '手机号',
                    'postion' => 2,
                    'type' => 1,
                    'option' => '',
                    'order' => 3,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 0,
                    'is_required' => 1,
                    'is_required_edit' => 0,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 0,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 0,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'memWeixin',
                    'attr_name' => '微信号',
                    'postion' => 2,
                    'type' => 1,
                    'option' => '',
                    'order' => 4,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 1,
                    'is_required' => 0,
                    'is_required_edit' => 1,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 1,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 1,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'memEmail',
                    'attr_name' => '邮箱',
                    'postion' => 2,
                    'type' => 1,
                    'option' => '',
                    'order' => 5,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 1,
                    'is_required' => 0,
                    'is_required_edit' => 1,
                    'is_show' => 1,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 1,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'dpName',
                    'attr_name' => '组织',
                    'postion' => 3,
                    'type' => 999,
                    'option' => '',
                    'order' => 6,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 0,
                    'is_required' => 1,
                    'is_required_edit' => 0,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 0,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 0,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'memJob',
                    'attr_name' => '岗位',
                    'postion' => 3,
                    'type' => 1,
                    'option' => '',
                    'order' => 8,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 1,
                    'is_required' => 1,
                    'is_required_edit' => 1,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 1,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 1,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ],
                [
                    'field_name' => 'memRole',
                    'attr_name' => '角色',
                    'postion' => 3,
                    'type' => 1,
                    'option' => '',
                    'order' => 9,
                    'is_system' => 1,
                    'is_open' => 1,
                    'is_open_edit' => 1,
                    'is_required' => 1,
                    'is_required_edit' => 1,
                    'is_open_cp' => 1,
                    'is_open_cp_edit' => 1,
                    'is_required_cp' => 1,
                    'is_required_cp_edit' => 1,
                    'is_show' => 1,
                    'is_show_edit' => 1,
                ]
            ]
        ];
    }
}
