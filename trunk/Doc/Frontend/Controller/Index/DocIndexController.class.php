<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/26
 * Time: 15:19
 */

namespace Frontend\Controller\Index;

class DocIndexController extends AbstractController
{
    /**
     * 应用首页
     * @author liyifei
     */
    public function Index()
    {
        redirectFront('/app/page/doc-library/list');
    }
}
