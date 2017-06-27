<?php
/**
 * ListController.class.php
 * $author$
 */

namespace Api\Controller\Test;

class ListController extends AbstractController
{

    public function Index_get()
    {

        $this->_result = array(
            'total' => 0,
            'list' => ['test' => 'demo/success'],
            'title' => L('DEFAULT_TITLE')
        );

        return true;
    }
}
