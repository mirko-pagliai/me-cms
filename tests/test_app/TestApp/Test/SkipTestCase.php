<?php
declare(strict_types=1);

namespace App\Test;

use MeCms\TestSuite\TestCase;

/**
 * SkipTestCase
 */
class SkipTestCase extends TestCase
{
    /**
     * test that a test is not marked as skipped using `skipIfCakeIsLessThan`
     * @return void
     */
    public function testSkipIfCakeIsLessThanFalse(): void
    {
        $this->skipIfCakeIsLessThan('3.0');
    }

    /**
     * test that a test is marked as skipped using `skipIfCakeIsLessThan`
     * @return void
     */
    public function testSkipIfCakeIsLessThanTrue(): void
    {
        $this->skipIfCakeIsLessThan('9.0');
    }
}
