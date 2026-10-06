<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\User::query()->update([
            'locked_until' => null,
            'failed_login_count' => 0,
        ]);
    }
}
