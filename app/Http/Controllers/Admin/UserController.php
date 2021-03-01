<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        if (!Auth::user()->can('user.index')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $users = $this->userRepo->getRole();

        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        if (!Auth::user()->can('user.create')) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('admin.user.create');
    }

    public function store(UserRequest $request)
    {
        if (!Auth::user()->can('user.store')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $data = $request->all();
        $user = $this->userRepo->getEmailOfUser($data['email']);

        if (!$user) {
            $data['password'] = bcrypt($data['password']);
            $this->userRepo->create($data);

            return redirect()->route('admin.users.index')->with('infoMessage', trans('user.create_user_success'));
        }

        return redirect()->route('admin.users.index')->with('checkIssetEmail', trans('user.isset_email'));
    }

    public function edit($id)
    {
        if (!Auth::user()->can('user.edit')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $user = $this->userRepo->find($id);

        if ($user) {
            return view('admin.user.edit', compact('user'));
        }

        return redirect()->route('admin.users.index')->with('infoMessage', trans('user.isset_id'));
    }

    public function update(UserRequest $request, $id)
    {
        if (!Auth::user()->can('user.update')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $user = $this->userRepo->find($id);

        if ($user) {
            $this->userRepo->update($id, [
                'name' => $request->name,
                'adrress' => $request->address,
                'phone' => $request->phone,
            ]);

            return redirect()->route('admin.users.index')->with('infoMessage', trans('user.update_user_success'));
        }

        return redirect()->route('admin.users.index')->with('infoMessage', trans('user.isset_id'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('user.destroy')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $user = $this->userRepo->find($id);

        if ($user) {
            $this->userRepo->destroy($id);

            return redirect()->route('admin.users.index')->with('infoMessage', trans('user.delete_user_success'));
        }

        return redirect()->route('admin.users.index')->with('infoMessage', trans('user.isset_id'));
    }

    public function search(Request $request)
    {
        if (!Auth::user()->can('user.search')) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $users = $this->userRepo->search($request->key);

        return view('admin.user.search', compact('users'));
    }
}
