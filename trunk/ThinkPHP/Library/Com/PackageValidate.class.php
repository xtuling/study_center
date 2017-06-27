<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhoutao
 * Date: 2017/1/23
 * Time: 上午10:23
 *
 * 在初始化的时候 new PackageValidate(验证规则, 报错码, 接收的提交字段);
 * 当 "接收的提交字段" 不为空时, 会自动执行getParams获取提交数据, 在getParams里当rule不为空时, 自动执行验证器验证
 *
 * 也可以不自动处理, 实例化的时候不提供参数就行, 后面自行操作
 */

namespace Com;

class PackageValidate extends Validate
{
    /** 手机正则规则 */
    const PHONE_REGEX = '/(^0?1[2,3,5,6,7,8,9]\d{9}$)|(^(\d{3,4})-(\d{7,8})$)|(^(\d{7,8})$)|(^(\d{3,4})-(\d{7,8})-(\d{1,4})$)|(^(\d{7,8})-(\d{1,4})$)/';
    // TODO 根据需求 任意人 持续添加正则或者其他常用规则 2017-01-22 15:47:11 zhoutao

    /**
     * 提交的字段
     * @var array
     */
    public $postField = [];

    /**
     * 提交的数据
     * @var array
     */
    public $postData = [];

    /**
     * 实例化
     * PackageValidate constructor.
     * @param array $rules 验证规则
     * @param array $message 报错码
     * @param array $postField 接收的提交字段
     */
    public function __construct(array $rules = [], array $message = [], $postField = [])
    {
        parent::__construct($rules, $message);

        if (!empty($postField) && is_array($postField)) {
            $this->postField = $postField;
            $this->getParams();
        }
    }

    /**
     * set
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->name = $value;
    }

    /**
     * 获取提交数据
     * @param array $field 字段
     * @return bool
     */
    public function getParams(array $field = [])
    {
        $field = empty($field) ? $this->postField : $field;

        // 获取不为空的提交值
        foreach ($field as $name) {
            $tmp = I('post.' . $name);
            if (!empty($tmp) || $tmp == '0') {
                $this->postData[$name] = $tmp;
            }
        }

        // 如果存在规则数据, 则自动验证
        if (!empty($this->rule)) {
            return $this->validateParams();
        }

        return true;
    }

    /**
     * 验证数据
     * @param array $rule 验证规则
     * @param array $message 报错码
     * @return bool
     */
    public function validateParams(array $rule = [], array $message = [])
    {
        // 验证数据
        $validate = Validate::make(
            empty($this->rule) ? $rule : $this->rule,
            empty($this->message) ? $message : $this->message
        );
        $validateResult = $validate->check($this->postData);

        // 抛错
        if (!$validateResult) {
            E($validate->getError());
        }

        return true;
    }

}
