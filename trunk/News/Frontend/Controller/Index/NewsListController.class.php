<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 17/4/18
 * Time: 14:31
 */
namespace Frontend\Controller\Index;

class NewsListController extends AbstractController
{
    /**
     * 应用列表
     * @author tangxingguo
     */
    public function Index()
    {
        $class_id = I('get.class_id', 0, 'intval');

        redirectFront('/app/page/news/list/list', ['class_id' => $class_id]);
    }
}
