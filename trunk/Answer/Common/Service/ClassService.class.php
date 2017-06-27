<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/21
 * Time: 17:14
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
            $conds = ['class_name' => $class_name];
        }
        $count = $this->count_by_conds($conds);
        if ($count > 0) {
            return false;
        }
        return true;
    }
}
