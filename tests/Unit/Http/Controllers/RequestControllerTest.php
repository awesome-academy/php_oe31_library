<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\RequestController;
use App\Http\Requests\OrderRequest;
use App\Models\Book;
use App\Models\Request;
use App\Models\User;
use App\Repositories\Author\AuthorRepositoryInterface;
use App\Repositories\Book\BookRepositoryInterface;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Publisher\PublisherRepositoryInterface;
use App\Repositories\Request\RequestRepositoryInterface;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Notification;
use Mockery as m;
use Tests\TestCase;

class RequestControllerTest extends TestCase
{
    protected $requestRepo, $userRepo, $bookRepo, $categoryRepo, $publisherRepo, $authorRepo, $roleRepo;
    protected $requestController;
    protected $request, $user;
    protected $id;
    protected $relationUser;
    protected $messStock, $messAddToCart, $messAddOnlyBook, $messUnsetCart;
    protected $cart;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestRepo = m::mock(RequestRepositoryInterface::class)->makePartial();
        $this->bookRepo = m::mock(BookRepositoryInterface::class)->makePartial();
        $this->categoryRepo = m::mock(CategoryRepositoryInterface::class)->makePartial();
        $this->authorRepo = m::mock(AuthorRepositoryInterface::class)->makePartial();
        $this->userRepo = m::mock(UserRepositoryInterface::class)->makePartial();
        $this->publisherRepo = m::mock(PublisherRepositoryInterface::class)->makePartial();
        $this->roleRepo = m::mock(RoleRepositoryInterface::class)->makePartial();
        $this->requestController = new RequestController($this->requestRepo, $this->bookRepo, $this->categoryRepo, $this->publisherRepo, $this->authorRepo, $this->userRepo, $this->roleRepo);
        $this->request = factory(Request::class)->make();
        $this->users = factory(User::class, 1)->make([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'UserTest@gmail.com',
            'password' => '12345678',
            'address' => 'Long Biên',
            'phone' => '012345678',
            'role_id' => 5,
            'times' => 0,
            'status' => 0,
        ]);
        $this->user = $this->users[0];
        $this->book = factory(Book::class)->make();
        $this->request->id = 1;
        $this->user->setRelation('requests', [$this->request]);
        $this->id = 1;
        $this->messStock = json_encode(['message' => trans('request.out_of_stock')]);
        $this->messAddToCart = json_encode(['message' => trans('request.add_only_book')]);
        $this->messAddOnlyBook = json_encode(['message' => trans('request.add_only_book')]);
        $this->messUnsetCart = json_encode(['message' => trans('request.remove_from_cart')]);
        $this->relationUser = ['user'];
        $this->cart = [
            'cart' => [
                "id" => 1,
                "image" => "https://via.placeholder.com/0x480.png/001100?text=rerum",
                "name" => "Thaddeus Hartmann",
            ],
        ];
    }

    public function tearDown(): void
    {
        m::close();

        unset($this->requestController);
        unset($this->requestRepo);
        unset($this->bookRepo);
        unset($this->categoryRepo);
        unset($this->authorRepo);
        unset($this->publisherRepo);
        unset($this->userRepo);
        unset($this->roleRepo);
        unset($this->request);
        unset($this->id);
        unset($this->relationUser);
        unset($this->messStock);
        unset($this->messAddToCart);
        unset($this->messAddOnlyBook);
        unset($this->messUnsetCart);
        unset($this->cart);
        unset($this->user);
        unset($this->role);

        parent::tearDown();
    }

    public function test_index_method()
    {
        $this->requestRepo->shouldReceive('getUserRequest');
        $view = $this->requestController->index();
        $this->assertEquals('client.list_request', $view->getName());
        $this->assertArrayHasKey('requests', $view->getData());
    }

    public function test_show_method()
    {
        $this->requestRepo->shouldReceive('withFind')
            ->with($this->id, $this->relationUser)
            ->once()
            ->andReturn();
        $view = $this->requestController->show($this->id);
        $this->assertEquals('client.detail_request', $view->getName());
        $this->assertArrayHasKey('request', $view->getData());
    }

    public function test_cart_method()
    {
        $this->withSession(['cart' => 1]);
        $view = $this->requestController->cart();
        $this->assertEquals('client.cart', $view->getName());
        $this->assertArrayHasKey('cart', $view->getData());
    }

    public function test_add_cart_fail_out_of_stock_method()
    {
        $this->book['in_stock'] = 0;
        $this->bookRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->book);
        $response = $this->requestController->addToCart($this->id);
        $this->assertJson($this->messStock, json_encode($response->getData()));
    }

    public function test_add_cart_empty_cart_method()
    {
        $this->book['in_stock'] = 1;
        $this->bookRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->book);
        $response = $this->requestController->addToCart($this->id);
        $this->assertJson($this->messAddToCart, json_encode($response->getData()));
    }

    public function test_add_cart_has_not_cart_method()
    {
        $this->book['in_stock'] = 1;
        $id = 2;
        $this->bookRepo->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn($this->book);
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $response = $this->requestController->addToCart($id);
        $this->assertJson($this->messAddToCart, json_encode($response->getData()));
    }

    public function test_add_cart_has_cart_method()
    {
        $this->book['in_stock'] = 1;
        $this->bookRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->book);
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $response = $this->requestController->addToCart($this->id);
        $this->assertJson($this->messAddOnlyBook, json_encode($response->getData()));
    }

    public function test_remove_cart()
    {
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $response = $this->requestController->removeCart($this->id);
        $this->assertJson($this->messUnsetCart, json_encode($response->getData()));
    }

    public function test_request_user_block()
    {
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $order = [];
        $request = new OrderRequest($order);
        $this->user->status = 2;
        $this->userRepo->shouldReceive('getRequest')
            ->once()
            ->andReturn($this->user);
        $view = $this->requestController->request($request);
        $this->assertEquals(route('cart'), $view->getTarGetUrl());
    }

    public function test_request_has_book_in_request()
    {
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $order = [];
        $idBook = ['1'];
        $request = new OrderRequest($order);
        $this->userRepo->shouldReceive('checkRequest')
            ->once()
            ->andReturn($idBook);
        $this->userRepo->shouldReceive('getRequest')
            ->once()
            ->andReturn($this->user);
        $view = $this->requestController->request($request);
        $this->assertEquals(route('cart'), $view->getTarGetUrl());
    }

    public function test_request_total_book_fail_request()
    {
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $order = [];
        $idBook = ['2'];
        $totalBookFail = 5;
        $request = new OrderRequest($order);
        $this->userRepo->shouldReceive('checkRequest')
            ->once()
            ->andReturn($idBook);
        $this->userRepo->shouldReceive('getRequest')
            ->once()
            ->andReturn($this->user);
        $this->requestRepo->shouldReceive('getTotalBook')
            ->with($this->user->requests)
            ->once()
            ->andReturn($totalBookFail);
        $view = $this->requestController->request($request);
        $this->assertEquals(route('cart'), $view->getTarGetUrl());
    }

    public function test_request_max_date_fail_request()
    {
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $order = [
            "borrowed_date" => "2021-02-13",
            "return_date" => "2021-5-04",
        ];
        $idBook = ['2'];
        $totalBookSuccess = 4;
        $request = new OrderRequest($order);
        $this->userRepo->shouldReceive('checkRequest')
            ->once()
            ->andReturn($idBook);
        $this->userRepo->shouldReceive('getRequest')
            ->once()
            ->andReturn($this->user);
        $this->requestRepo->shouldReceive('getTotalBook')
            ->with($this->user->requests)
            ->once()
            ->andReturn($totalBookSuccess);
        $view = $this->requestController->request($request);
        $this->assertEquals('http://127.0.0.1:8000', $view->getTarGetUrl());
    }

    public function test_request_create_order()
    {
        Notification::fake();
        $this->be($this->user);
        $this->withSession(['cart' => [$this->id => ['id' => 1]]]);
        $order = [
            'id' => '2',
            'users' => 2,
            "borrowed_date" => "2021-02-13",
            "return_date" => "2021-2-26",
        ];
        $roleList = collect([(object) ["id" => 3], (object) ["id" => 4]]);
        $this->be($this->user);
        $idBook = ['2'];
        $totalBookSuccess = 4;
        $this->book->in_stock = 10;
        $request = new OrderRequest($order);
        $this->userRepo->shouldReceive('checkRequest')
            ->once()
            ->andReturn($idBook);
        $this->userRepo->shouldReceive('getRequest')
            ->once()
            ->andReturn($this->user);
        $this->requestRepo->shouldReceive('getTotalBook')
            ->with($this->user->requests)
            ->once()
            ->andReturn($totalBookSuccess);
        $this->requestRepo->shouldReceive('create')
            ->once()
            ->andReturn($request);
        $this->bookRepo->shouldReceive('update')
            ->once()
            ->andReturn(true);
        $this->roleRepo->shouldReceive('getRoleAdmins')
            ->once()
            ->andReturn($roleList);
        $this->userRepo->shouldReceive('getUserHaveRoleAdmins')
            ->once()
            ->andReturn($this->users);
        $this->bookRepo->shouldReceive('find')
            ->once()
            ->andReturn($this->book);
        $this->requestRepo->shouldReceive('attach')
            ->once()
            ->andReturn(true);
        $view = $this->requestController->request($request);
        $this->assertEquals(route('cart'), $view->getTarGetUrl());
    }
}
