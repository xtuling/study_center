<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/19
 * Time: 19:44
 */

namespace Frontend\Controller\Index;

use Common\Common\Cache;

class ClearCacheController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 清除缓存
     * @author zhonglei
     */
    public function Index()
    {

        $cache = &Cache::instance();
        $cache->get('Common.Department', null);
        $cache->get('Common.User', null);
        exit('SUCCESS');
    }
}
