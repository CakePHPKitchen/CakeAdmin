<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\View\Exception\MissingTemplateException;
use App\Utility\Generator;
use Cake\Utility\Text;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class SupportController extends AppController
{

    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow(['tickets', 'fetch', 'support', 'contact']);
    }

    public function tickets()
    {

    }

    public function fetch()
    {
        print_r($this->request->getQueryParams());die();
    }

    public function support()
    {
        $formSubmitted = false;

        if($this->request->getMethod() == 'POST') {

            $formSubmitted = true;

            $data = $this->request->getData();

            $subject = $data['subject'];
            $message = $data['message'];
            $topicId = $data['topic'];
            $priority = $data['priority'];

            $usersTable = TableRegistry::get(Configure::read('Users.table'));
            // $query = $usersTable->find('all')->where(['users.id' => $this->Auth->user('id')])->limit(1);
            // $user = $query->first();

            // $user_id = $user->get('id');
            $user_id = $this->Auth->user('id');

            $contactTable = TableRegistry::get('Messages');
            $contactEntity = $contactTable->newEntity([
                'user_id'  => $user_id,
                'subject'  => $subject,
                'message'  => $message,
                'closed'   => 0,
                'topic'    => $topicId,
                'priority' => $priority,
                'created'  => new \DateTime('now'),
                'modified' => new \DateTime('now')
            ]);
            $result = $contactTable->save($contactEntity);

            $message_id = $result->id;

            $support_username = 'admin'; // Configure::read('Users.support_username');

            $query = $usersTable->find('all')->where(['users.username' => $support_username])->limit(1);
            $admin = $query->first();

            $recipientsTable = TableRegistry::get('Recipients');
            $recipientsEntity = $recipientsTable->newEntity([
                'message_id'  => $message_id,
                'user_id'     => $admin->get('id'),
                'created'     => new \DateTime('now'),
                'modified'    => new \DateTime('now')
            ]);
            $recipientsTable->save($recipientsEntity);

            $this->Flash->success('Support Ticket Created Successfully, We will be in touch shortly...');
        }

        $supportTable = TableRegistry::get('Messages');
        $supportEntity = $supportTable->newEntity();
        $this->set(compact('supportEntity', 'formSubmitted'));
    }

    public function contact()
    {
        $formSubmitted = false;

        if($this->request->getMethod() == 'POST') {

            $formSubmitted = true;

            // Array ( [email] => Jeffrey.l.roberts@gmail.com [subject] => asdgfasdg [message] => asdgffasdgdsfg )
            $data = $this->request->getData();

            $email = $data['email'];
            $subject = $data['subject'];
            $message = $data['message'];
            $user_id = 0;

            $usersTable = TableRegistry::get(Configure::read('Users.table'));
            $query = $usersTable->find('all')->where(['users.email' => $email])->limit(1);
            $user = $query->first();

            if(!$user) {

                $username = explode('@', $email);
                $username = $username[0];
                $password = Generator::alphanumeric(8);

                $user = [
                    'id' => Text::uuid(),
                    'username' => $username,
                    'email' => $email,
                    'is_superuser' => '0',
                    'role' => 'user',
                    'active' => 1,
                    'created' => new \DateTime('now'),
                    'modified' => new \DateTime('now')
                ];

                $user = $usersTable->newEntity($user);
                $hashedPassword = $user->hashPassword($password);
                $user->set('password', $hashedPassword);
                $userResult = $usersTable->save($user);

                $user_id = $userResult->id;
            }
            else {

                $user_id = $user->id;
            }

            if($user_id > 0) {

                $user_id = $user->get('id');
                $contactTable = TableRegistry::get('Messages');
                $contactEntity = $contactTable->newEntity([
                    'user_id' => $user_id,
                    'subject' => $subject,
                    'message' => $message,
                    'closed'  => 1,
                    'created' => new \DateTime('now'),
                    'modified' => new \DateTime('now')
                ]);
                $result = $contactTable->save($contactEntity);

                $message_id = $result->id;

                $support_username = 'admin'; // Configure::read('Users.support_username');

                $query = $usersTable->find('all')->where(['users.username' => $support_username])->limit(1);
                $admin = $query->first();

                $recipientsTable = TableRegistry::get('Recipients');
                $recipientsEntity = $recipientsTable->newEntity([
                    'message_id' => $message_id,
                    'user_id' => $admin->get('id'),
                    'created' => new \DateTime('now'),
                    'modified' => new \DateTime('now')
                ]);
                $recipientsTable->save($recipientsEntity);

                $this->Flash->success('Contact Message Successfully Sent... Thank You, We will be in touch shortly...');
            }
            else {

                $this->Flash->error('Invalid User ID');
            }
            // @todo send email to user confirming contact message sent...
        }

        $contactTable = TableRegistry::get('Messages');
        $contactEntity = $contactTable->newEntity();
        $this->set(compact('contactEntity', 'formSubmitted'));

    }
}