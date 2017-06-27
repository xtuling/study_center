<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/22
 * Time: 下午6:40
 */

namespace Api\Controller\Debug;

use Com\Cookie;
use Common\Common\User;

class UserController extends AbstractController
{


    /**
     * Data
     * @desc 获取已登录的用户信息
     *
     * @return array 用户信息
     *               array(
     *               'memUid' => '', // 用户UID
     *               'memUsername' => '', // 用户名
     *               'memUserid' => '', // 用户在微信端的唯一标识
     *               'dpName' => array(), // 所在部门列表
     *               'tagName' => array(), // 所在标签列表
     *               'memMobile' => '', // 用户手机号
     *               'memFace' => '' // 用户头像
     *               )
     */
    public function Data_get()
    {

        $this->_result = $this->_login->user;

        return true;
    }
}
