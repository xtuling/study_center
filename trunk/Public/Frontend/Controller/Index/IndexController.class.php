<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/6/2
 * Time: 16:33
 */

namespace Frontend\Controller\Index;

class IndexController extends AbstractController
{
    /**
     * 应用首页
     * @author liyifei
     */
    public function Index()
    {
        redirectFront('/app/page/index/index/index');
    }
}
