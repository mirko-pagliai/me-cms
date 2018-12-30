<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * VersionUpdatesCommandTest class
 */
class VersionUpdatesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Tags',
    ];

    /**
     * Test for `alterTagColumnSize()` method
     * @test
     */
    public function testAlterTagColumnSize()
    {
        $Tags = $this->getMockForModel('MeCms.Tags', null);
        $initialColumn = $Tags->getSchema()->getColumn('tag');
        $Tags->getConnection()->execute('ALTER TABLE tags MODIFY tag varchar(254) NOT NULL');
        $this->assertEquals(254, $this->getMockForModel('MeCms.Tags', null)->getSchema()->getColumn('tag')['length']);

        $result = $this->invokeMethod($this->Command, 'alterTagColumnSize');
        $this->assertNull($result);
        $this->assertEquals($initialColumn, $this->getMockForModel('MeCms.Tags', null)->getSchema()->getColumn('tag'));
    }

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $Command = $this->getMockBuilder(get_parent_class($this->Command))
            ->setMethods(['alterTagColumnSize'])
            ->getMock();

        $Command->expects($this->once())
            ->method('alterTagColumnSize');

        $this->assertNull($Command->run([], new ConsoleIo));
    }
}
