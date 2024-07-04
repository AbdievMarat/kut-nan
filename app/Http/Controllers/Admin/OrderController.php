<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));

        $busOrders = Bus::query()
            ->with(['orders' => function ($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->orderBy('sort')
            ->get();

        return view('admin.orders.index', compact('busOrders', 'date'));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));

        return Excel::download(new OrderExport($date), 'orders_' . date('d.m.Y', strtotime($date)) . '.xlsx');
    }
}