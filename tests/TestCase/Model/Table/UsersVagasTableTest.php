<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersVagasTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersVagasTable Test Case
 */
class UsersVagasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersVagasTable
     */
    public $UsersVagas;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.UsersVagas',
        'app.Users',
        'app.Vagas',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('UsersVagas') ? [] : ['className' => UsersVagasTable::class];
        $this->UsersVagas = TableRegistry::getTableLocator()->get('UsersVagas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersVagas);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
