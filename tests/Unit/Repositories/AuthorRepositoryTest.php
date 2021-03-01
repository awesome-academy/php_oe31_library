<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Author;
use Illuminate\Http\UploadedFile;
use App\Repositories\Author\AuthorRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorRepositoryTest extends TestCase
{
    protected $authorRepo;
    protected $id;

    public function setUp(): void 
    {
        parent::setUp();
        $this->authorRepo = new AuthorRepository;
        $this->id = 1;
    }

    public function tearDown(): void 
    {
        unset($this->authorRepo);
        unset($this->id);
        parent::tearDown();
    }

    public function test_get_model()
    {
        $data = $this->authorRepo->getModel();
        $this->assertEquals(Author::class, $data);
    }

    public function test_get_related_book_true()
    {
        $result = $this->authorRepo->getRelatedBook($this->id);
        $this->assertInstanceOf(Author::class, $result);
    }

    public function test_get_related_book_fail()
    {
        $this->id = null;
        $result = $this->authorRepo->getRelatedBook($this->id);
        $this->assertNotInstanceOf(Author::class, $result);
    }
}
