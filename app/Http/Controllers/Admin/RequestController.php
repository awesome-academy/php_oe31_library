<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Session;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function index()
    {
        $requests = Request::with('user')->orderBy('id', 'DESC')->get();

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
        $request = Request::with('books')->findOrFail($id);

        if ($request->status === config('request.pending') || $request->status === config('request.reject')) {
            $result = $request->update([
                'status' => config('request.accept'),
            ]);
            if ($result) {
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
        $request = Request::findOrFail($id);
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
        $request = Request::findOrFail($id);
        if ($request->status === config('request.accept') || $request->status === config('request.reject') || $request->status === config('request.borrow') || $request->status === config('request.return')) {
            if ($request->status === config('request.accept')) {
                $result = $request->update([
                    'status' => config('request.pending'),
                ]);
            } elseif ($request->status === config('request.reject')) {
                foreach ($request->books as $book) {
                    $book->update([
                        'in_stock' => $book->in_stock - config('request.book'),
                    ]);
                }
                $result = $request->update([
                    'status' => config('request.pending'),
                ]);
            } elseif ($request->status === config('request.borrow')) {
                $result = $request->update([
                    'status' => config('request.accept'),
                ]);
            } elseif ($request->status === config('request.return')) {
                foreach ($request->books as $book) {
                    $book->update([
                        'in_stock' => $book->in_stock - config('request.book'),
                    ]);
                }
                $result = $request->update([
                    'status' => config('request.borrow'),
                ]);
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
        $request = Request::findOrFail($id);
        if ($request->status === config('request.accept')) {
            $result = $request->update([
                'status' => config('request.borrow'),
            ]);
            if ($result) {
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
        $request = Request::findOrFail($id);
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
                return redirect()->back()->with('infoMessage',
                    trans('message.request_return_success'));
            }

            return redirect()->back()->with('infoMessage',
                trans('message.request_return_fail'));
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}