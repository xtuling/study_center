<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/4/18
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

class CourseIndexController extends AbstractController
{
    /**
     * 应用首页
     * @author zhonglei
     */
    public function Index()
    {
        redirectFront('/app/page/course/list/list');
    }
}
