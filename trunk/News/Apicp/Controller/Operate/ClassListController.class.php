<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Operate;

use Common\Service\ClassService;

class ClassListController extends \Apicp\Controller\AbstractController
{
    /**
     * Banner 分类选择接口
     * @desc 用于首页 Banner 展示的接口
     * @return array(
     *  array(
     *      'id' => '分类 ID', // 分类 ID
     *      'name' => '分类名称', // 分类名称
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
        $res = [];
        if (!empty($classList)) {
            foreach ($classList as $k => $v) {
                $res[] = [
                    'id' => $v['class_id'],
                    'name' => $v['class_name'],
                    'upId' => $v['parent_id'],
                ];
            }
        }

        $this->_result = $res;
    }
}
