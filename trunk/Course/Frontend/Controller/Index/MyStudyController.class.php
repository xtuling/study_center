<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/6/5
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

class MyStudyController extends AbstractController
{
    /**
     * 我的学习
     * @author zhonglei
     */
    public function Index()
    {
        redirectFront('/app/page/course/my/my');
    }
}
