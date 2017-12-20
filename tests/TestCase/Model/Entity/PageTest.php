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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Model\Entity\Page;
use MeTools\TestSuite\TestCase;

/**
 * PageTest class
 */
class PageTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Page
     */
    protected $Page;

    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Page = new Page;
        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        Cache::clear(false, $this->Pages->cache);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Page->isAccessible('id'));
        $this->assertFalse($this->Page->isAccessible('preview'));
        $this->assertFalse($this->Page->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['plain_text'], $this->Page->getVirtual());
    }

    /**
     * Test for `_getPlainText()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertEquals('Text of the first page', $this->Pages->findById(1)->first()->plain_text);
    }
}
