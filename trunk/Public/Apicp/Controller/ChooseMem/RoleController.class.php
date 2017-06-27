<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/26
 * Time: 上午11:29
 */

namespace Apicp\Controller\ChooseMem;

use VcySDK\Role;
use VcySDK\Service;

class RoleController extends AbstractController
{

    public function Index_post()
    {

        $condition = array();
        $page = (int)I('post.page');
        $limit = (int)I('post.limit');
        $keyword = (string)I('post.keyword');
        if (!empty($keyword)) {
            $condition['keyword'] = $keyword;
        }

        $job = new Role(Service::instance());
        list(, $limit, $page) = page_limit($page, $limit);
        $sdkResult = $job->listAll($condition, $page, $limit);

        $this->_result = array(
            'list' => $sdkResult['list'],
            'page' => $sdkResult['pageNum'],
            'limit' => $sdkResult['pageSize'],
            'total' => $sdkResult['total']
        );

        return true;
    }

}