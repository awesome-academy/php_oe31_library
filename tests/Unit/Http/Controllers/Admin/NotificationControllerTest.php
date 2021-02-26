<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\NotificationController;
use App\Models\User;
use App\Models\Notification;
use App\Repositories\Notification\NotificationRepositoryInterface;
use Mockery as m;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    protected $notificationRepo;
    protected $notificationController;
    protected $user;

    public function setUp(): void
    {
        $this->notificationRepo = m::mock(NotificationRepositoryInterface::class)->makePartial();
        $this->notificationController = new NotificationController($this->notificationRepo);
        $this->user = new User([
            'id' => 1,
            'name' => 'Unauthorized',
            'email' => 'Unauthorized@gmail.com',
            'password' => '123456',
            'address' => 'Hà Đông',
            'phone' => '0334736187',
            'role_id' => 10,
            'times' => 0,
            'status' => 0,
        ]);
        $this->notification =  new Notification([
            "id" => "04ff6277-6c03-4426-a0a8-119bdbdecadf",
            "type" => "App\Notifications\Admin\RequestNotification",
            "notifiable_type" => "App\Models\User",
            "notifiable_id" => 2,
            "data" => '{"request_id":4,"nameUser":"B\u00f9i Quang Anh","content":"Infomation Request"}',
            "read_at" => null,
            "created_at" => "25-02-2021",
            "updated_at" => "25-02-2021",
        ]);
        $this->resClient = '{"currentUser":null,"data":null}';
        $this->resUserId = '{"user_id":null}';
        parent::setUp();
    }

    public function tearDown(): void
    {
        m::close();
        unset($this->notificationRepo);
        unset($this->notificationController);
        unset($this->resClient);
        unset($this->resUserId);
        unset($this->user);
        unset($this->notification);
        parent::tearDown();
    }

    public function test_index_method()
    {
        $this->be($this->user);
        $this->notificationRepo->shouldReceive('getNotificationByDB');
        $response = $this->notificationController->index();
        $this->assertEquals($this->resClient, json_encode($response->getData()));
    }

    public function test_detail_notification()
    {
        $id = 1;
        $requestId = 4;
        $this->notificationRepo->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn($this->notification);
        $view = $this->notificationController->detailNotification($id);
        $this->assertEquals(route('admin.request-detail', $requestId), $view->getTargetUrl());
    }

    public function test_api_get_user()
    {
        $this->be($this->user);
        $response = $this->notificationController->apiGetUser();
        $this->assertEquals($this->resUserId, json_encode($response->getData()));
    }
}
