<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\UserController;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Mockery as m;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    protected $userRepo;
    protected $userController;
    protected $userAuthorized;
    protected $userUnAuthorized;
    protected $id;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepo = m::mock(UserRepositoryInterface::class)->makePartial();
        $this->userController = new UserController($this->userRepo);
        $this->user = [
            'name' => 'User Test',
            'email' => 'UserTest@gmail.com',
            'password' => '12345678',
            'address' => 'Long Biên',
            'phone' => '012345678',
            'role_id' => 5,
            'times' => 0,
            'status' => 0,
        ];
        $this->userAuthorized = factory(User::class)->make();
        $this->userUnAuthorized = factory(User::class)->make([
            'name' => 'Unauthorized',
            'email' => 'Unauthorized@gmail.com',
            'password' => '123456',
            'address' => 'Long Biên',
            'phone' => '012345678',
            'role_id' => 10,
            'times' => 0,
            'status' => 0,
        ]);
        $this->id = 1;
    }

    public function tearDown(): void
    {
        m::close();
        unset($this->userRepo);
        unset($this->userAuthorized);
        unset($this->userUnAuthorized);
        unset($this->userController);
        unset($this->user);
        unset($this->id);
        parent::tearDown();
    }

    public function test_index_user_authorized_view()
    {
        $this->be($this->userAuthorized);
        $this->userRepo->shouldReceive('getRole')
            ->once()
            ->andReturn($this->user);
        $view = $this->userController->index();
        $this->assertEquals('admin.user.index', $view->getName());
        $this->assertArrayHasKey('users', $view->getData());
    }

    public function test_index_user_unauthorized_view()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $view = $this->userController->index();
        $this->assertEquals('admin.user.index', $view->getName());
        $this->assertArrayHasKey('users', $view->getData());
    }

    public function test_create_authorize()
    {
        $this->be($this->userAuthorized);
        $view = $this->userController->create();
        $this->assertEquals('admin.user.create', $view->getName());
    }

    public function test_create_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $view = $this->userController->create();
        $this->assertEquals('admin.user.create', $view->getName());
    }

    public function test_store_not_exist_email_authorize()
    {
        $this->be($this->userAuthorized);
        $request = new UserRequest($this->user);
        $data = $request->all();
        $this->userRepo->shouldReceive('getEmailOfUser')
            ->with($request->email)
            ->once()
            ->andReturn(false);
        $this->userRepo->shouldReceive('create')
            ->once()
            ->andReturn(true);
        $view = $this->userController->store($request);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_store_exist_email_authorize()
    {
        $this->be($this->userAuthorized);
        $request = new UserRequest($this->user);
        $data = $request->all();
        $this->userRepo->shouldReceive('getEmailOfUser')
            ->with($request->email)
            ->once()
            ->andReturn($this->user);
        $view = $this->userController->store($request);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_store_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $request = new UserRequest($this->user);
        $data = $request->all();
        $view = $this->userController->store($request);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_edit_exist_view_authorize()
    {
        $this->be($this->userAuthorized);
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->user);
        $view = $this->userController->edit($this->id);
        $this->assertEquals('admin.user.edit', $view->getName());
    }

    public function test_edit_not_exist_view_authorize()
    {
        $this->be($this->userAuthorized);
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn(false);
        $view = $this->userController->edit($this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_edit_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $view = $this->userController->edit($this->id);
        $this->assertEquals('admin.user.edit', $view->getName());
    }

    public function test_update_success_authorize()
    {
        $this->be($this->userAuthorized);
        $request = new UserRequest($this->user);
        $data = [
            'name' => $request->name,
            'adrress' => $request->address,
            'phone' => $request->phone,
        ];
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->user);
        $this->userRepo->shouldReceive('update')
            ->with($this->id, $data)
            ->once()
            ->andReturn(true);
        $view = $this->userController->update($request, $this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_update_fail_authorize()
    {
        $this->be($this->userAuthorized);
        $request = new UserRequest($this->user);
        $data = [
            'name' => $request->name,
            'adrress' => $request->address,
            'phone' => $request->phone,
        ];
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn(false);
        $view = $this->userController->update($request, $this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_update_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $request = new UserRequest($this->user);
        $view = $this->userController->update($request, $this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_destroy_exist_authorize()
    {
        $this->be($this->userAuthorized);
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->user);
        $this->userRepo->shouldReceive('destroy')
            ->with($this->id)
            ->once()
            ->andReturn(true);
        $view = $this->userController->destroy($this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_destroy_not_exist_authorize()
    {
        $this->be($this->userAuthorized);
        $this->userRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn(false);
        $view = $this->userController->destroy($this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_destroy_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $view = $this->userController->destroy($this->id);
        $this->assertEquals(route('admin.users.index'), $view->getTarGetUrl());
    }

    public function test_search_authorize()
    {
        $this->be($this->userAuthorized);
        $key = [
            'key' => 'key test',
        ];
        $request = new Request($key);
        $this->userRepo->shouldReceive('search')
            ->with($request->key)
            ->once()
            ->andReturn(false);
        $view = $this->userController->search($request);
        $this->assertEquals('admin.user.search', $view->getName());
    }

    public function test_search_unauthorize()
    {
        $this->be($this->userUnAuthorized);
        $this->expectException(HttpException::class);
        $key = [
            'key' => 'key test',
        ];
        $request = new Request($key);
        $view = $this->userController->search($request);
        $this->assertEquals('admin.user.search', $view->getName());
    }
}
