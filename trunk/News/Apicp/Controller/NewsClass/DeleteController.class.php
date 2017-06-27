<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\NewsClass;

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
     * @return null
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

        $classServ = new ClassService();
        $classInfo = $classServ->get($postData['class_id']);
        if (empty($classInfo)) {
            E('_ERR_CLASS_DATA_NOT_FOUND');
        }

        // 一级分类检查有无子分类
        if ($classInfo['parent_id'] == 0) {
            $child = $classServ->get_by_conds(['parent_id' => $classInfo['class_id']]);
            if ($child) {
                E('_ERR_CLASS_CONTAIN_CHILD');
            }
        }

        // 检查分类下有无新闻
        $articleServ = new ArticleService();
        $articleCount = $articleServ->count_by_conds(['class_id' => $postData['class_id']]);
        if ($articleCount > 0) {
            E('_ERR_CLASS_CONTAIN_ARTICLE');
        }

        // 删除分类
        $classServ->delete($postData['class_id']);
    }
}
