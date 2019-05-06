<?php


namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestsController
 *
 * @package AppBundle\Controller
 */
class TestsController
{

    public function testAction(Request $request)
    {


        return new Response('<body></body>');
    }
}
