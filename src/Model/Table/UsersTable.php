<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\EmpresasTable&\Cake\ORM\Association\HasMany $Empresas
 * @property \App\Model\Table\UsersDadosTable&\Cake\ORM\Association\HasMany $UsersDados
 * @property \App\Model\Table\VagasTable&\Cake\ORM\Association\BelongsToMany $Vagas
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Empresas', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UsersDados', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsToMany('Vagas', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'vaga_id',
            'joinTable' => 'users_vagas',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
       $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('nota')
            ->allowEmptyString('nota');

        $validator
            ->scalar('tipo')
            ->maxLength('tipo', 255)
            ->notEmpty('tipo', 'tipo é obrigatório');

        $validator
            ->scalar('campo')
            ->maxLength('campo', 255)
            ->notEmpty('campo', 'campo é obrigatório');

        $validator
            ->scalar('opcoes')
            ->maxLength('opcoes', 255)
            ->notEmpty('opcoes', 'opção é obrigatório');

        $validator
            ->integer('valor')
            ->notEmpty('valor', 'valor é obrigatório');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    public function findByChave($chave)
    {
        return $this->find()
            ->select(['id', 'nome', 'email', 'confirmado', 'chave'])
            ->where(['chave' => $chave, 'ativo', 'confirmado' => false])
            ->first();
    }

    public function findByEmailAndAtivo($email)
    {
        return $this->find()
            ->select(['id', 'nome', 'email', 'confirmado'])
            ->where(['email' => $email, 'ativo'])
            ->first();
    }
}
