<?php
/**
 * Created by PhpStorm.
 * User: Alexandr Kuziv
 * Date: 06.04.2017
 * Time: 16:02
 */

namespace Demos\TaskBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $ts = new TaskController();

        //$calendar = calendar();
        //$month_name = \TaskController::getMonthName();

        $this->assertEquals($ts);

    }
}
