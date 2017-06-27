<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:46
 */
namespace Common\Service;

use Common\Common\Constant;
use Common\Model\ClassModel;

class ClassService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new ClassModel();
    }

    /**
     * 唯一分类名检查
     * @author tangxingguo
     * @param string $class_name 分类名
     * @param int $class_id 分类ID
     * @return bool
     */
    public function uniqueName($class_name, $class_id = 0)
    {
        if ($class_id > 0) {
            $conds = [
                'class_name' => $class_name,
                'class_id != ?' => $class_id,
            ];
        } else {
            $conds = ['class_name' => $class_name];
        }
        $count = $this->count_by_conds($conds);
        if ($count > 0) {
            return false;
        }
        return true;
    }

    /**
     * 取分类等级
     * @author tangxingguo
     * @param int $classId 分类ID
     * @param int $level 分类等级（0=分类不存在；1=一级分类；2=二级分类；3=三级分类）
     */
    public function getLevel($classId, &$level = 0)
    {
        static $classList;
        if (empty($classList)) {
            $classList = $this->list_all();
            $classList = array_combine_by_key($classList, 'class_id');
        }
        $classInfo = isset($classList[$classId]) ? $classList[$classId] : [];
        if (empty($classInfo)) {
            return;
        }
        $level++;
        if ($classInfo['parent_id'] > 0) {
            $this->getLevel($classInfo['parent_id'], $level);
        } elseif ($classInfo['parent_id'] == 0) {
            return;
        }
    }

    /**
     * 根据层级格式化分类数据
     * @author tangxingguo
     * @param array $list 分类数据
     * @param int $parentId 父ID
     * @return array 格式化后的数据
     */
    public function formatClass($list, $parentId = 0)
    {
        $return = [];
        foreach ($list as $class) {
            if ($class['parent_id'] == $parentId) {
                foreach ($list as $child) {
                    if ($child['parent_id'] == $class['class_id']) {
                        $class['child'] = $this->formatClass($list, $class['class_id']);
                        break;
                    } else {
                        $class['child'] = [];
                    }
                }
                $return[] = $class;
            }
        }
        return $return;
    }

    /**
     * @desc 获取所有已启用的分类ID（父类禁用子类不显示）
     * @author tangxingguo
     */
    public function getOpenClassIds()
    {
        $classServ = new ClassService();
        $classList = $classServ->list_all();
        $classIds = [];
        if (!empty($classList)) {
            // 格式化分类
            $classList = array_combine_by_key($classList, 'class_id');
            foreach ($classList as $k => $v) {
                if ($v['parent_id'] != 0) {
                    $classList[$v['parent_id']]['child'][] = &$classList[$k];
                }
            }

            // 取已开启的二级分类以及其子分类
            foreach ($classList as $key => $value) {
                // 父级是否开启
                $parentIsOpen = isset($classList[$value['parent_id']]) ? ($classList[$value['parent_id']]['is_open'] != Constant::CLASS_IS_OPEN_FALSE) : false;
                // 非一级分类 + 当前分类已开启 + 父级分类已开启
                if ($value['parent_id'] != 0 && $value['is_open'] == Constant::CLASS_IS_OPEN_TRUE && $parentIsOpen) {
                    $classIds[] = $value['class_id'];
                }
            }
        }

        return $classIds;
    }

    /**
     * 获取顶级分类信息
     * @author liyifei
     * @param int $classId 子分类ID
     * @return array
     */
    public function getTopClass($classId)
    {
        // 获取分类
        $class_list = $this->list_all();
        $class_list = array_combine_by_key($class_list, 'class_id');

        // 获取一级分类
        while ($class_list[$classId]['parent_id'] > 0) {
            $classId = $class_list[$classId]['parent_id'];
        }

        return $class_list[$classId];
    }

    /**
     * 以树形结构格式化数据库数据
     * @author liyifei
     * @return array
     */
    public function formatDB()
    {
        // 所有分类列表
        $tree = $this->list_all();
        if (empty($tree)) {
            return [];
        }
        $tree = array_combine_by_key($tree, 'class_id');

        foreach ($tree as $k => $v) {
            if ($v['parent_id'] != 0) {
                $tree[$v['parent_id']]['child'][$v['class_id']] = &$tree[$k];
            }
        }

        return $tree;
    }

    /**
     * 获取当前分类及所有子分类ID
     * @author liyifei
     * @param int $classId 分类ID
     * @param array $tree 分类树形结构
     * @param array $childIds 收集子分类ID
     * @return array
     */
    public function getChildClassIds($classId, $tree = [], $childIds = [])
    {
        if (empty($tree)) {
            $tree = $this->formatDB();
        }

        $childIds[] = $classId;

        if (isset($tree[$classId]['child'])) {
            foreach ($tree[$classId]['child'] as $v) {
                $childIds = $this->getChildClassIds($v['class_id'], $tree[$classId]['child'], $childIds);
            }
        }

        return $childIds;
    }
}
