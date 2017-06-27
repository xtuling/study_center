<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/5
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

class AwardController extends AbstractController
{
    /**
     * 跳转至手机端测评页
     * @author zhonglei
     */
    public function Index()
    {
        redirectFront('/app/page/integral/medal-list', [], 'integral');
    }
}
