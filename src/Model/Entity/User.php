<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $email
 * @property string|null $senha
 * @property string|null $cpf
 * @property string|null $nascimento
 * @property string|null $facebook
 * @property string|null $google
 * @property string|null $chave
 * @property bool|null $ativo
 * @property bool|null $has_empresa
 * @property bool|null $confirmado
 *
 * @property \App\Model\Entity\Empresa[] $empresas
 * @property \App\Model\Entity\UsersDado[] $users_dados
 * @property \App\Model\Entity\Vaga[] $vagas
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'nome' => true,
        'email' => true,
        'senha' => true,
        'cpf' => true,
        'nascimento' => true,
        'facebook' => true,
        'google' => true,
        'chave' => true,
        'ativo' => true,
        'has_empresa' => true,
        'confirmado' => true,
        'empresas' => true,
        'users_dados' => true,
        'vagas' => true,
    ];

    protected $_hidden = [
        'senha',
        'google',
        'facebook'
    ]; 

    protected function _setSenha(string $senha)
    {
         return (new DefaultPasswordHasher())->hash($senha);
    }
}
