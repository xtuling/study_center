<?php
/**
 * menu.php
 * 菜单配置
 *
 * $Author$
 */

/**
 * 分级的应用后台菜单配置：
 * 第一级为顶部菜单，
 * 第二级为左边的菜单，
 * 第三级为右边顶部的菜单，
 * 第四级为右边内容中的操作。
 *    + key 唯一的菜单标识
 *    + name 菜单项的名称，建议使用8个字符以内
 *  + auth 是否有权限
 *  + order 排序，按照顺序排序
 *  + parentKey 上级菜单的key
 *  + actionPath 操作路径，格式为：应用/模块/控制器/操作
 *    + subMenu 子菜单或操作
 */

// 应用中心菜单配置数组
$Conf_AppCenter = array(
    'key' => 'Top_AppCenter',
    'name' => '应用中心',
    'auth' => 0,
    'order' => 10,
    'parentKey' => '',
    'actionPath' => '',
    'subMenu' => array(
        array(
            'key' => 'AppCenter',
            'name' => '应用中心',
            'auth' => 0,
            'order' => 10,
            'parentKey' => 'Top_AppCenter',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'AppCenter_List',
                    'name' => '应用列表',
                    'auth' => 0,
                    'order' => 10,
                    'parentKey' => 'AppCenter',
                    'actionPath' => 'Common/Apicp/AppCenter/AppList/Index',
                    'subMenu' => array(
                        array(
                            'key' => 'AppCenter_Delete',
                            'name' => '删除应用及其数据',
                            'auth' => 0,
                            'order' => 10,
                            'parentKey' => 'AppCenter_List',
                            'actionPath' => 'Common/Apicp/AppCenter/DeleteApp/Index',
                            'subMenu' => array()
                        ),
                        array(
                            'key' => 'AppCenter_Install',
                            'name' => '安装应用',
                            'auth' => 0,
                            'order' => 10,
                            'parentKey' => 'AppCenter_List',
                            'actionPath' => 'Common/Apicp/AppCenter/InstallApp/Index',
                            'subMenu' => array()
                        )
                    )
                )
            )
        )
    )
);

// 人员管理菜单配置
$Conf_MemManage = array(
    'key' => 'Top_MemManage',
    'name' => '人员管理',
    'auth' => 0,
    'order' => 101,
    'parentKey' => '',
    'actionPath' => '',
    'subMenu' => array(
        array(
            'key' => 'MemManage',
            'name' => '员工管理',
            'auth' => 0,
            'order' => 101,
            'parentKey' => 'Top_MemManage',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'MemManage_List',
                    'name' => '员工列表',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'MemManage',
                    'actionPath' => '',
                    'subMenu' => array(
                        array(
                            'key' => 'MemManage_Import',
                            'name' => '批量导入',
                            'auth' => 0,
                            'order' => 101,
                            'parentKey' => 'MemManage_List',
                            'actionPath' => '',
                            'subMenu' => array()
                        ),
                        array(
                            'key' => 'MemManage_Async',
                            'name' => '同步通讯录',
                            'auth' => 0,
                            'order' => 102,
                            'parentKey' => 'MemManage_List',
                            'actionPath' => '',
                            'subMenu' => array()
                        )
                    )
                )
            )
        )
    )
);

// 系统设置菜单配置
$Conf_SysSetting = array(
    'key' => 'Top_System',
    'name' => '系统设置',
    'auth' => 0,
    'order' => 101,
    'parentKey' => '',
    'actionPath' => '',
    'subMenu' => array(
        array(
            'key' => 'Account',
            'name' => '账号信息',
            'auth' => 0,
            'order' => 102,
            'parentKey' => 'Top_System',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'Account_Account',
                    'name' => '获取账号信息',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'Account',
                    'actionPath' => 'Common/Apicp/SysSetting/Account/Index',
                    'subMenu' => array(
                        array(
                            'key' => 'Edit_Account',
                            'name' => '完善账号信息',
                            'auth' => 0,
                            'order' => 101,
                            'parentKey' => 'Account_Account',
                            'actionPath' => 'Common/Apicp/SysSetting/EditEnterprise/Index',
                            'subMenu' => array()
                        )
                    )
                )
            )
        ),
        array(
            'key' => 'EditPassword',
            'name' => '修改密码',
            'auth' => 0,
            'order' => 101,
            'parentKey' => 'Top_System',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'EditPassword_Edit',
                    'name' => '修改密码',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'EditPassword',
                    'actionPath' => 'Common/Apicp/SysSetting/EditPassword',
                    'subMenu' => array()
                )
            )
        ),
        array(
            'key' => 'Adminier',
            'name' => '管理员',
            'auth' => 0,
            'order' => 101,
            'parentKey' => 'Top_System',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'Adminier_List',
                    'name' => '管理员列表',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'Adminier',
                    'actionPath' => 'Common/Apicp/Manager/List',
                    'subMenu' => array(
                        array(
                            'key' => 'Adminier_Add',
                            'name' => '添加管理员',
                            'auth' => 0,
                            'order' => 101,
                            'parentKey' => 'Adminier_List',
                            'actionPath' => 'Common/Apicp/Manager/Add/Index',
                            'subMenu' => array()
                        ),
                        array(
                            'key' => 'Adminier_Delete',
                            'name' => '删除管理员',
                            'auth' => 0,
                            'order' => 102,
                            'parentKey' => 'Adminier_List',
                            'actionPath' => 'Common/Apicp/Manager/Delete/Index',
                            'subMenu' => array()
                        ),
                        array(
                            'key' => 'Adminier_Edit',
                            'name' => '编辑管理员',
                            'auth' => 0,
                            'order' => 103,
                            'parentKey' => 'Adminier_List',
                            'actionPath' => 'Common/Apicp/Manager/Edit/Index',
                            'subMenu' => array()
                        )
                    )
                )
            )
        ),
        array(
            'key' => 'RefreshCache',
            'name' => '缓存更新',
            'auth' => 0,
            'order' => 102,
            'parentKey' => 'Top_System',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'RefreshCache_Refresh',
                    'name' => '更新缓存',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'RefreshCache',
                    'actionPath' => 'Common/Apicp/SysSetting/RefreshCache/Index',
                    'subMenu' => array()
                )
            )
        ),
        array(
            'key' => 'SetEnv',
            'name' => '环境设置',
            'auth' => 0,
            'order' => 102,
            'parentKey' => 'Top_System',
            'actionPath' => '',
            'subMenu' => array(
                array(
                    'key' => 'SetEnv_SetEnv',
                    'name' => '环境设置',
                    'auth' => 0,
                    'order' => 101,
                    'parentKey' => 'SetEnv',
                    'actionPath' => 'Common/Apicp/SysSetting/SetEnv/Index',
                    'subMenu' => array()
                )
            )
        )
    )
);

// 循环每个应用获取应用菜单配置
$AppMenu = array();
foreach (glob(__DIR__ . '/Application/*.php') as $conf_file) {
    $config = require($conf_file);

    // 如果后台菜单存在，加入到配置数组，文件名作为键名
    if (isset($config['ManageMenu'])) {
        $AppMenu[] = $config['ManageMenu'];
    }
}

$Conf_AppData = array(
    'key' => 'Top_AppData',
    'name' => '应用数据',
    'auth' => 0,
    'parentKey' => '',
    'actionPath' => '',
    'subMenu' => $AppMenu
);

// 返回配置数组
return array(
    'MENU' => array(
        $Conf_AppCenter,
        $Conf_AppData,
        $Conf_MemManage,
        $Conf_SysSetting
    )
);
