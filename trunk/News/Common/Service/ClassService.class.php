<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/12
 * Time: 10:35
 */
namespace Common\Service;

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
            $conds = [
                'class_name' => $class_name,
            ];
        }
        $count = $this->_d->count_by_conds($conds);
        if ($count > 0) {
            return false;
        }
        return true;
    }

    /**
     * 取分类等级
     * @author tangxingguo
     * @param int $classId 分类ID
     * @return int 分类等级（0=不存在，1=一级分类，2=二级分类）
     */
    public function classLevel($classId)
    {
        $classInfo = $this->get($classId);
        if (empty($classId)) {
            return 0;
        }
        if ($classInfo['parent_id'] == 0) {
            return 1;
        }
        if ($classInfo['parent_id'] > 0) {
            return 2;
        }
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
}
