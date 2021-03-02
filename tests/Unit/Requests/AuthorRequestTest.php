<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\AuthorRequest;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class AuthorRequestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->authorRequest = new AuthorRequest();
        $this->rules = $this->authorRequest->rules();
        $this->validator = $this->app['validator'];
    }

    public function tearDown(): void
    {
        unset($this->authorRequest);
        unset($this->rules);
        unset($this->validator);
        parent::tearDown();
    }

    public function test_authorize()
    {
        $result = $this->authorRequest->authorize();
        $this->assertTrue($result);
    }
}
