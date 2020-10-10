<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\I18n\I18n;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    private $_user = null;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);

        $this->loadComponent('Auth', [
            'authorize' => ['Controller'], 
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'username' => 'email',
                        'password' => 'senha'
                    ]
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'userModel' => 'Users',
                    'fields' => [
                        'username' => 'id',
                    ],

                    'parameter' => 'token',
                    'queryDatasource' => false,
                ]
            ],

            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => false
        ]);

        I18n::locale('pt_BR');
    }
    public function isAuthorized($userAuth)
    {
        if (!isset($userAuth['user_id'])) {
            return false;
        }

        $tableUsers = TableRegistry::get('Users');
        $user = $tableUsers->get($userAuth['user_id']);

        if (empty($user)) {
            return false;
        }

        if (!$user['ativo']) {
            return false;
        }

        $this->setUser($user);

        return true;
    }
    public function beforeRender(Event $event)
    {
         // Setando extensão .json
         $this->RequestHandler->renderAs($this, 'json');
        $this->set('_serialize', true);
    }

    private function setUser($user)
    {
        $this->_user = $user;
    }

    protected function getUser()
    {
        return $this->_user;
    }
}
