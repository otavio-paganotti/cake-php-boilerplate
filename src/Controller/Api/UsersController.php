<?php
namespace App\Controller\Api;

use Cake\Utility\Security;
use Cake\I18n\Time;
use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Firebase\JWT\JWT;
/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['ativar','add','login','recuperarSenha']);
    }

    public function isAuthorized($userAuth)
    {
        $authorized = parent::isAuthorized($userAuth);
        $user = $this->getUser();

        if (empty($user)) {
            return false;
        }

        return $authorized;
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $result = [
            'success'=> true,
            'message'=> ''
        ];
        
        $result['usuarios'] = $this->paginate($this->Users);

        $this->set($result);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $result = [
            'success'=> true,
            'message'=> ''
        ];

        $result['usuario'] = $this->Users->get($id, [
            'contain' => ['Vagas', 'UsersDados'],
        ]);

        $this->set($result);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {

        $result = [
            'success'=> false,
            'message'=> ''
        ];

        $user = $this->Users->newEntity();
        
        if ($this->request->is('post')) {

            $user = $this->Users->patchEntity($user, $this->request->getData());

            $user->chave = Security::hash(Time::now() . $user->email, 'md5', true);
            $user->ativo = true;
             
            if ($this->Users->save($user)) {
                $result['success'] = true;
                $result['message'] = __('Usuário cadastro com sucesso');
                $result['cadastro'] = $user;
            }else{
                $result['errors'] = $user->errors();
                $result['message'] = __('Não foi possivel salvar usuário, tente novamente.');
            }
        }
        $this->set($result);
    }

    public function ativar($chave = null)
    {
        $result = [
            'success'=> false,
            'message'=> ''
        ];

        $user = $this->Users->findByChave($chave);

        if (!isset($user)) {
            $result['message'] = __('Esse link não é válido! Já foi feito outro pedido de troca de senha?');
            $this->set($result);
            return $this->render();
        }

        $user->confirmado = true;
        $user->chave = null;

        if ($this->request->is(['post'])) {
            $senha = $this->request->data('senha');
            $confirmar = $this->request->data('confirmar');

            if (trim($senha) !== '' && $senha === $confirmar) {
                $user = $this->Users->patchEntity($user, $this->request->data);

                if ($this->Users->save($user)) {
                    $result['success'] = true;
                    $result['message'] = __('O Usuário foi ativado, faça login para continuar!.');
                    $result['user'] = $user;
                } else {
                    $result['success'] = true;
                    $result['message'] = __('O usuário não foi salvo. Por favor preencha todos os campos.');
                }
            } else {
                $result['message'] = __('As senhas digitadas não são iguais.');
            }
        }

        $this->set($result);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $result = [
            'success'=> false,
            'message'=> ''
        ];

        $user = $this->Users->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $result['success'] = true;
                $result['message'] = __('Usuário alterado com sucesso');
                $result['cadastro'] = $user;
            }else{
                $result['errors'] = $user->errors();
                $result['message'] = __('Não foi possivel alterar usuário, tente novamente.');
            }
        }

         $this->set($result);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        
        $this->request->allowMethod(['post', 'delete']);
        
        $result = [
            'success'=> false,
            'message'=> ''
        ];

        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $result['success'] = true;
            $result['message'] = __('Conta removida com sucesso');
            $result['cadastro'] = $user;
        }else{
            $result['message'] = __('Não foi possivel deletar conta do usuário, tente novamente.');
        }

        $this->set($result);
    }

    private function getToken($user_id, $expiration)
    {
        return  JWT::encode([
            'user_id' => $user_id,
            'exp' => $expiration
        ], Security::salt());
    }

    public function login()
    {
        $this->request->allowMethod(['post']);

        $result = [
            'success' => false,
            'message' => '',
            'data' => [
                'token' => '',
                'usuario'=>''
            ]
        ];

        try {
            $userAuth = null;
            $headerAuth = $this->request->header('Authorization');
            $login = $this->request->data('email');
            $senha = $this->request->data('senha');
            $validade = time() + (2 * 24 * 60 * 60);

            if ($headerAuth && $login === null && $senha === null) {
                $dadosToken = $this->Auth->identify();

                if (!isset($dadosToken['user_id'])) {
                    throw new UnauthorizedException('Token inválido! Será necessário enviar novas credenciais!');
                }

                $userFind = $this->Users->find()
                    ->select(['id', 'ativo', 'nome', 'email', 'confirmado'])
                    ->where(['id' => $dadosToken['user_id']])
                    ->first();

                if ($userFind) {
                    $userAuth = $userFind->toArray();
                }
            } elseif (!$headerAuth) {
                $userAuth = $this->Auth->identify();
            }

            if (!$userAuth) {
                throw new UnauthorizedException('Login ou senha Inválidos!');
            }

            if (!$userAuth['ativo']) {
                throw new UnauthorizedException('O Usuário não está ativo!');
            }

            if (!$userAuth['confirmado']) {
                throw new UnauthorizedException('Você precisa ativar seu cadastro clicando no link enviado no seu e-mail.!');
            }

            $result['data']['usuario'] = $userAuth;
            $result['data']['token'] = $this->getToken($userAuth['id'], $validade);
            $result['message'] = "Login realizado com sucesso !";
            $result['success'] = true;

        }catch (Exception $e) {
            $result['message'] = $e->getMessage();
            unset($result['data']);
        }

        $this->set($result);
    }

    public function recuperarSenha()
    {
        $this->request->allowMethod(['post']);

        $result = [
            'success' => false,
            'message' => '',
        ];

        $email = $this->request->data('email');
        $user = $this->Users->findByEmailAndAtivo($email);

        if (!isset($user)) {
            $result['message'] = 'E-mail não encontrado!';
            $this->set($result);

            return $this->render();
        }

        $user->chave = Security::hash(Time::now() . $user->email, 'md5', true);
        $user->confirmado = false;

        if ($this->Users->save($user)) {
            $result['success'] = true;
            $result['message'] = 'Um link de ativação foi enviado para: ' . $email;
        } else {
            $result['message'] = 'A senha não foi alterada. Tente novamente.';
        }

        $this->set($result);
    }

    public function empresas($user_id = null)
    {

        $result = [
            'success' => false,
            'message' => '',
        ];

        $result['user'] = $this->Users->find()->where(['id' => $user_id])->first();
        $result['empresas'] = $this->Users->Empresas->find()->where(['user_id' => $user_id])->toArray();

        $this->set($result);
    }
}
