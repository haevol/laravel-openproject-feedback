<?php

namespace Haevol\OpenProjectFeedback\Tests\Feature;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class ExampleTest extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app)
    {
        return [
            \Haevol\OpenProjectFeedback\OpenProjectFeedbackServiceProvider::class,
        ];
    }

    /**
     * A basic feature test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}

