<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/22
 * Time: 下午6:40
 */

namespace Apicp\Controller\Debug;

class UserController extends AbstractController
{

    /**
     * Data
     * @desc 获取已登录的用户信息
     *
     * @return array 用户信息
     *               array(
     *               'eaId' => '', // 用户ID
     *               'eaRealname' => '', // 用户名
     *               'eaMobile' => '', // 用户手机号
     *               'eaEmail' => '', // 用户邮箱
     *               'memFace' => '' // 用户头像
     *               )
     */
    public function Data_get()
    {

        $this->_result = $this->_login->user;

        return true;
    }
}
