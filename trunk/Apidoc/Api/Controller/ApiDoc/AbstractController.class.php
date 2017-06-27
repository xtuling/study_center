<?php
/**
 * Created by PhpStorm.
 *
 * User: zhoutao
 * Date: 16/7/14
 * Time: 下午2:56
 */

namespace Api\Controller\ApiDoc;

abstract class AbstractController extends \Api\Controller\AbstractController
{

    /**
     * Apidoc 不需要任何数据库支持, 也不需要登录, 所以不执行基类操作
     * @param string $action
     * @return bool
     */
    public function before_action($action = '')
    {

        return true;
    }

}
