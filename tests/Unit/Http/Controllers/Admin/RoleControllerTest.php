<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\RoleController;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Repositories\Role\RoleRepositoryInterface;
use Mockery as m;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    protected $roleRepo, $perRepo;
    protected $roleController;
    protected $user, $role, $permission, $id;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleRepo = m::mock(RoleRepositoryInterface::class)->makePartial();
        $this->perRepo = m::mock(PermissionRepositoryInterface::class)->makePartial();
        $this->roleController = new RoleController($this->roleRepo, $this->perRepo);
        $this->user = factory(User::class)->make();
        $this->role = factory(Role::class)->make();
        $this->permission = factory(Permission::class)->make();
        $this->role->setRelation('permissions', $this->permission);
        $this->id = 1;
    }

    public function tearDown(): void
    {
        m::close();
        unset($this->roleRepo);
        unset($this->perRepo);
        unset($this->roleController);
        unset($this->user);
        unset($this->role);
        unset($this->permission);
        unset($this->id);
        parent::tearDown();
    }

    public function test_view_index()
    {
        $this->be($this->user);
        $this->roleRepo->shouldReceive('getAll');
        $view = $this->roleController->index();
        $this->assertEquals('admin.role.index', $view->getName());
        $this->assertArrayHasKey('roles', $view->getData());
    }

    public function test_view_create()
    {
        $this->be($this->user);
        $this->perRepo->shouldReceive('get');
        $view = $this->roleController->create();
        $this->assertEquals('admin.role.create', $view->getName());
        $this->assertArrayHasKey('permissions', $view->getData());
    }

    public function test_store_success()
    {
        $this->be($this->user);
        $request = [
            'name' => 'Role Name',
            'permissions' => [
                0 => "19",
                1 => "18",
            ],
        ];
        $request = new RoleRequest($request);
        $this->roleRepo->shouldReceive('create')
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('attach')
            ->once()
            ->andReturn(true);
        $response = $this->roleController->store($request);
        $this->assertEquals(route('admin.roles.index'), $response->getTargetUrl());
    }

    public function test_show_view()
    {
        $this->be($this->user);
        $this->id = 1;
        $this->perRepo->shouldReceive('get');
        $this->roleRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('load')
            ->with($this->role, 'permissions')
            ->once()
            ->andReturn($this->role);
        $view = $this->roleController->show($this->id);
        $this->assertEquals('admin.role.show', $view->getName());
        $this->assertArrayHasKey('role', $view->getData());
        $this->assertArrayHasKey('permissions', $view->getData());
    }

    public function test_edit_view()
    {
        $this->be($this->user);
        $this->id = 1;
        $this->perRepo->shouldReceive('get');
        $this->roleRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('load')
            ->with($this->role, 'permissions')
            ->once()
            ->andReturn($this->role);
        $view = $this->roleController->edit($this->id);
        $this->assertEquals('admin.role.edit', $view->getName());
        $this->assertArrayHasKey('role', $view->getData());
        $this->assertArrayHasKey('permissions', $view->getData());
    }

    public function test_update_method()
    {
        $this->id = 1;
        $request = [
            'name' => 'Role Name',
            'permission' => [
                0 => "19",
                1 => "18",
            ],
        ];
        $request = new RoleRequest($request);
        $this->roleRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('sync')
            ->with($this->role, 'permissions', $request->permission)
            ->once()
            ->andReturn(true);
        $response = $this->roleController->update($request, $this->id);
        $this->assertEquals('http://127.0.0.1:8000', $response->getTargetUrl());
    }

    public function test_destroy_has_permissions_method()
    {
        $this->id = 1;
        $this->roleRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('sync')
            ->with($this->role, 'permissions')
            ->once()
            ->andReturn(true);
        $this->roleRepo->shouldReceive('destroy')
            ->with($this->id)
            ->once()
            ->andReturn(true);
        $response = $this->roleController->destroy($this->id);
        $this->assertEquals(route('admin.roles.index'), $response->getTargetUrl());
    }

    public function test_destroy_fail_method()
    {
        $this->id = 1;
        $this->roleRepo->shouldReceive('find')
            ->with($this->id)
            ->once()
            ->andReturn($this->role);
        $this->roleRepo->shouldReceive('sync')
            ->with($this->role, 'permissions')
            ->once()
            ->andReturn(true);
        $this->roleRepo->shouldReceive('destroy')
            ->with($this->id)
            ->once()
            ->andReturn(false);
        $response = $this->roleController->destroy($this->id);
        $this->assertEquals(route('admin.roles.index'), $response->getTargetUrl());
    }
}
