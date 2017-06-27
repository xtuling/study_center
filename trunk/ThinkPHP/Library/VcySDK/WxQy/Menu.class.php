<?php

/**
 * Menu.class.php
 * 菜单接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK\WxQy;

class Menu
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 创建服务号菜单
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CREATE_URL = '%s/menu/create';

    /**
     * 获取服务号菜单
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const GET_URL = '%s/menu/get';

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {
        $this->serv = $serv;
    }

    /**
     * 创建菜单接口
     * @param $menu
     * + buttons array	一级菜单数组，个数应为1~3个
     *   + type String 菜单的响应动作类型1-6,详细类型请查看微信企业号接口文档
     *   + name String 菜单标题,一级菜单不超过16个字节，二级菜单不超过40个字节，一级菜单最多5个汉字，二级菜单最多13个汉字
     *   + key String 菜单KEY值，用于消息接口推送，不超过128字节
     *   + url String 网页链接，用户点击菜单可打开链接，不超过256字节
     * + subButtons array  二级菜单数组，个数应为1~5个
     *   + 同 buttons
     * + callbackUrl 创建菜单成功后回调地址(POST)
     *
     * @return array|bool
     */
    public function create($menu)
    {

        return $this->serv->postSDK(self::CREATE_URL, $menu, 'generateApiUrlA');
    }

    /**
     * 获取服务号菜单
     *
     * @return array
     */
    public function get()
    {
        return $this->serv->postSDK(self::GET_URL, [], 'generateApiUrlA');
    }
}
