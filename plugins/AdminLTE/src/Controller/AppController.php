<?php

namespace AdminLTE\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub

        $this->loadComponent('CakeDC/Users.UsersAuth');
    }

}
