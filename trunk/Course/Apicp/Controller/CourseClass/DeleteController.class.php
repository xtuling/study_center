<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\CourseClass;

use Com\PackageValidate;
use Common\Service\ArticleService;
use Common\Service\ClassService;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author tangxingguo
     * @desc 删除分类
     * @param int    class_id:true 分类ID
     */

    public function Index_post()
    {
        // 验证规则
        $rules = [
            'class_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 取分类等级
        $classServ = new ClassService();
        $classServ->getLevel($postData['class_id'], $level);
        if (empty($level)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }

        // 一级、二级分类下有子分类不允许删除
        if (in_array($level, [1, 2])) {
            $child = $classServ->get_by_conds(['parent_id' => $postData['class_id']]);
            if ($child) {
                E('_ERR_CLASS_CONTAIN_CHILD');
            }
        }

        // 二级、三级分类下有课程不允许删除
        if (in_array($level, [2, 3])) {
            $articleServ = new ArticleService();
            $articleCount = $articleServ->count_by_conds(['class_id' => $postData['class_id']]);
            if ($articleCount > 0) {
                E('_ERR_CLASS_CONTAIN_ARTICLE');
            }
        }

        // 删除分类
        $classServ->delete($postData['class_id']);
    }
}
