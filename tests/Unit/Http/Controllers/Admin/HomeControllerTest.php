<?php

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\HomeController;
use App\Models\Request;
use App\Repositories\Request\RequestRepositoryInterface;
use Mockery as m;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
   protected $requestRepo;
   protected $homeController;
   protected $requests;
   protected $resClient;

   public function setUp(): void
   {
        parent::setUp();
        $this->requestRepo = m::mock(RequestRepositoryInterface::class)->makePartial();
        $this->homeController = new HomeController($this->requestRepo);
        $this->requests = factory(Request::class)->make();
        $this->resClient = '{"list":[{"month":null,"book":null}]}';
   } 

   public function tearDown(): void
   {
       m::close();
       unset($this->requestRepo);
       unset($this->homeController);
       unset($this->requests);
       unset($this->resClient);
       parent::tearDown();
   }

   public function test_index_view()
   {
       $view = $this->homeController->index();
       $this->assertEquals('admin.home', $view->getName());
   }

   public function test_get_data_chart()
   {
        $this->requestRepo->shouldReceive('chart')
            ->once()
            ->andReturn([$this->requests]);
        $response = $this->homeController->getDataChart();
        $this->assertEquals($this->resClient, json_encode($response->getData()));
   }
}
