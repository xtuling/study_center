<?php
/**
 * 标签列表接口
 *
 */
namespace Apicp\Controller\Contact;


class TagListController extends AbstractController
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
                    array(
                        'tagId' => '78D54AFE7F00000162CAAD4E37AC9624',
                        'TagName' => '总部',
                        'tagDisplayorder' => 10
                    ),
                    array(
                        'tagId' => '78D54AFE7F00000162CAAD4E37AC9624',
                        'TagName' => '技术部',
                        'tagDisplayorder' => 11
                    ),

                )

            )
        );

        return $result;
    }


}
