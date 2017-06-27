<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/8/12
 * Time: 下午2:51
 */

namespace Rpc\Controller\Cache;

class ClearController extends AbstractController
{

    public function Index()
    {

        clear_cache();

        return true;
    }
}
