<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Request\RequestRepositoryInterface;

class HomeController extends Controller
{
    protected $requestRepo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(RequestRepositoryInterface $requestRepo)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->requestRepo = $requestRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.home');
    }

    public function getDataChart()
    {
        $requests = $this->requestRepo->chart();

        $data = [];
        $list = [];
        foreach ($requests as $request) {
            array_push($data, $request);
        }
        
        foreach ($data as $item) {
            $val = [
                'month' => $item->month,
                'book' => $item->book,
            ];
            array_push($list, $val);
        }

        return response()->json([
            'list' => $list,
        ]);
    }
}
