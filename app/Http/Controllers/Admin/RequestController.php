<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Notifications\Admin\BorrowBookNotification;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Pusher\Pusher;
use App\Events\NotificationUserEvent;

class RequestController extends Controller
{
    public function index()
    {
        $requests = Request::with('user')->orderBy('id', 'DESC')->paginate(config('pagination.list_request'));

        return view('admin.request.index', compact('requests'));
    }

    public function show($id)
    {
        $request = Request::findOrFail($id)->load('books.author', 'books.categories', 'user');
        $borrowedDate = Carbon::parse($request->borrowed_date);
        $returnDate = Carbon::parse($request->return_date);
        $totalDate = $returnDate->diffinDays($borrowedDate);

        return view('admin.request.show', compact('request', 'totalDate'));
    }

    public function accept($id)
    {
        $request = Request::with(['books', 'user'])->findOrFail($id);

        if ($request->status === config('request.pending') || $request->status === config('request.reject')) {
            $result = $request->update([
                'status' => config('request.accept'),
            ]);
            if ($result) {
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Chấp thuận mượn sách vui lòng đến lấy sách vào ngày ' .   date('d-m-Y', strtotime($request->borrowed_date)),
                ];

                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

                return redirect()->back()->with('infoMessage',
                    trans('message.request_accept_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_accept_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    public function reject($id)
    {
        $request = Request::with(['books', 'user'])->findOrFail($id);

        if ($request->status === config('request.pending') || $request->status === config('request.accept')) {
            foreach ($request->books as $book) {
                $book->update([
                    'in_stock' => $book->in_stock + config('request.book'),
                ]);
            }
            $result = $request->update([
                'status' => config('request.reject'),
            ]);
            if ($result) {
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Xin lỗi yêu cầu mượn sách của bạn không được chấp thuận',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

                return redirect()->back()->with('infoMessage',
                    trans('message.request_reject_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_reject_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    public function undo($id)
    {
        $request = Request::with('user', 'books')->findOrFail($id);
        if ($request->status === config('request.accept') || $request->status === config('request.reject') || $request->status === config('request.borrow') || $request->status === config('request.return')) {
            if ($request->status === config('request.accept')) {
                $result = $request->update([
                    'status' => config('request.pending'),
                ]);
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Về trạng thái đang chờ được duyệt',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

            } elseif ($request->status === config('request.reject')) {
                foreach ($request->books as $book) {
                    $book->update([
                        'in_stock' => $book->in_stock - config('request.book'),
                    ]);
                }
                $result = $request->update([
                    'status' => config('request.pending'),
                ]);
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->namespace,
                    'request_id' => $id,
                    'content' => 'Yêu cầu mượn sách của bạn về trạng thái đang chờ được duyệt',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

            } elseif ($request->status === config('request.borrow')) {
                $result = $request->update([
                    'status' => config('request.accept'),
                ]);
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Yêu cầu mượn sách của bạn về trạng thái được chấp thuận',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

            } elseif ($request->status === config('request.return')) {
                foreach ($request->books as $book) {
                    $book->update([
                        'in_stock' => $book->in_stock - config('request.book'),
                    ]);
                }
                $result = $request->update([
                    'status' => config('request.borrow'),
                ]);
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Yêu cầu mượn sách của bạn về trạng thái đang mượn',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));
            }
            if ($result) {
                return redirect()->back()->with('infoMessage',
                    trans('message.request_undo_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_undo_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    public function borrowedBook($id)
    {
        $request = Request::with('user', 'books')->findOrFail($id);
        if ($request->status === config('request.accept')) {
            $result = $request->update([
                'status' => config('request.borrow'),
            ]);
            if ($result) {
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Bạn đã nhận được sách',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));

                return redirect()->back()->with('infoMessage',
                    trans('message.request_reject_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_reject_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    public function returnBook($id)
    {
        $request = Request::with('user', 'books')->findOrFail($id);
        if ($request->status === config('request.borrow')) {
            foreach ($request->books as $book) {
                $book->update([
                    'in_stock' => $book->in_stock + config('request.book'),
                ]);
            }
            $result = $request->update([
                'status' => config('request.return'),
            ]);
            if ($result) {
                $data = [
                    'user_id' =>  $request->user->id,
                    'user_name' => $request->user->name,
                    'request_id' => $id,
                    'content' => 'Cảm ơn bạn đã mượn sách tại thư viện của chúng tôi',
                ];
                $request->user->notify(new BorrowBookNotification($data));
                event(new NotificationUserEvent($data));
                
                return redirect()->back()->with('infoMessage',
                    trans('message.request_return_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_return_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}
