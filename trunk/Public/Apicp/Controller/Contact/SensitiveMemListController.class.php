<?php
/**
 * 敏感成员列表接口
 *
 */
namespace Apicp\Controller\Contact;


class SensitiveMemListController extends AbstractController
{
    public function Index()
    {


        return $this->_result = array();
    }


    public function Test()
    {

        // 返回值
        $result = $this->result();

        return $this->_result = $result;
    }

    public function result()
    {


        // 构造返回值
        $result = array(
            'list' => array(
                array(
                    'sensitiveId' => "72A15A2A7F0000016C05A24F56B28853",
                    'tags' => array(
                        array(
                            'tagId' => '78D54AFE7F00000162CAAD4E37AC9624',
                            'TagName' => '总部'
                        ),
                        array(
                            'tagId' => '78D54AFE7F00000162CAAD4E37AC9624',
                            'TagName' => '技术部'
                        ),
                    ),
                    'dps' => array(
                        array(
                            'id' => '78D54AFE7F00000162CAAD4E37AC9624',
                            'name' => '技术部',
                            'isChecked' => true
                        ),
                        array(
                            'id' => '78D54AFE7F00000162CAAD4E37AC9624',
                            'name' => '技术部1',
                            'isChecked' => true
                        )
                    ),
                    'mems' => array(
                        array(
                            'm_uid' => '78D54AFE7F00000162CAAD4E37AC9624',
                            'm_username' => '张总',
                            'selected' => true
                        )

                    ),
                    'data' => array(
                        array(
                            'fieldName' => "address",
                            'level' => 3,
                            'name' => "地址",
                            'number' => 1,
                            'open' => 1,
                            'required' => 0,
                            'type' => 0,
                            'view' => 0
                        ),
                        array(
                            'fieldName' => "leader",
                            'level' => 2,
                            'name' => "直属领导",
                            'number' => 2,
                            'open' => 1,
                            'required' => 0,
                            'type' => 0,
                            'view' => 0
                        ),

                    )

                )

            )
        );

        return $result;
    }


}
