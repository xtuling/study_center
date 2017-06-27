<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Operate;

use Common\Service\ClassService;

class IconApiController extends \Apicp\Controller\AbstractController
{
    /**
     * 栏目接口
     * @desc 栏目接口
     * @return array(
     *  array(
     *      'id' => '分类 ID', // 分类 ID
     *      'name' => '分类名称', // 分类名称
     *      'url' => '分类链接，如果为空，则表明该链接不可直接访问', // 分类链接，如果为空，则表明该链接不可直接访问
     *      'upId' => '上级分类 ID，为 0 则表示顶级' // 上级分类 ID，为 0 则表示顶级
     *  ),
     *  array()
     * )
     */
    public function Index_post()
    {
        $classServ = new ClassService();
        $classList = $classServ->list_all();

        // 格式化数据
        $res = [
            [
                'id' => 0,
                'name' => '全部',
                'url' => 'News/Frontend/Index/NewsList/Index?class_id=0',
                'upId' => 0,
            ]
        ];
        if (!empty($classList)) {
            foreach ($classList as $k => $v) {
                $res[] = [
                    'id' => $v['class_id'],
                    'name' => $v['class_name'],
                    // 只有一级分类可以直接跳转
                    'url' => $v['parent_id'] == 0 ? 'News/Frontend/Index/NewsList/Index?class_id=' . $v['class_id'] : '',
                    'upId' => $v['parent_id'],
                ];
            }
        }

        $this->_result = $res;
    }
}
