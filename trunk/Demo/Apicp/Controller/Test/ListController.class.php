<?php
/**
 * ListController.class.php
 * $author$
 */

namespace Apicp\Controller\Test;

class ListController extends AbstractController
{

    public function Index()
    {

        $this->_result = array(
            'total' => 0,
            'list' => ['test cp' => 'demo/success'],
            'title' => L('DEFAULT_TITLE')
        );

        return true;
    }
}
