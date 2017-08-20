<?php

namespace App\Controller;

use App\Controller\Traits\ProfileTrait;
use Cake\ORM\TableRegistry;
use CakeDC\Users\Controller\UsersController as BaseUsersController;
use Cake\Core\Configure;
use Firebase\JWT\JWT;
use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Utility\Security;
use CakeDC\Users\Controller\Component\UsersAuthComponent;

class UsersController extends BaseUsersController
{
    use ProfileTrait;

    public function initialize() {

        parent::initialize();

        $this->Auth->allow('login');
    }

    public function changePassword()
    {
        $id = basename($this->request->here);

        $usersTable = TableRegistry::get(Configure::read('Users.table'));
        $query = $usersTable->find('all')->where(['id' => $id])->limit(1);
        $user2 = $query->first();

        $this->set(compact('user2'));

        $this->set('title', 'Change User Password');

        parent::changePassword();

        $this->render('admin_change_password');
    }

    public function edit($id = null)
    {
        //$id = basename($this->request->here);

        $usersTable = TableRegistry::get(Configure::read('Users.table'));
        $query = $usersTable->find('all')->where(['id' => $id])->limit(1);
        $user2 = $query->first();

        $this->set(compact('user2'));

        $this->set('title', 'Change User Password');

        parent::edit($id);

        $this->render('admin_edit_user');
    }

    public function register() {

        $this->eventManager()->on(UsersAuthComponent::EVENT_AFTER_REGISTER, function ($event) {
            //the callback function should return the user data array to force register
             $event->data['user']->set('token', JWT::encode(
                [
                    'sub' => $event->subject->entity->id,
                    'exp' =>  time() + 604800
                ],
                Security::salt()));
            $usersTable = TableRegistry::get(Configure::read('Users.table'));
            $usersTable->save($event->data['user']);
        });

        $this->set('title', 'Register User');

        parent::register();
    }

    public function login() {

        $this->set('title', 'CakeAdminLTE Login');

        parent::login();
    }

    public function requestResetPassword() {

        $this->set('title', 'Reset Password');

        parent::requestResetPassword();
    }
}
