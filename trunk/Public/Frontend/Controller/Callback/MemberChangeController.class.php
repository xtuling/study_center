<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/8/15
 * Time: 上午9:48
 */

namespace Frontend\Controller\Callback;

class MemberChangeController extends AbstractController
{

    public function Index()
    {

        // 更新所有公共缓存
        clear_sys_cache(cfg('CACHE_COMMON_FIELD'));

        exit('SUCCESS');
    }
}
