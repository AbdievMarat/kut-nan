<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks
     *
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $feedbacks = Feedback::query()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.feedbacks.index', compact('feedbacks'));
    }

    /**
     * Display the specified feedback
     *
     * @param Feedback $feedback
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     */
    public function show(Feedback $feedback): View|Application|Factory|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        return view('admin.feedbacks.show', compact('feedback'));
    }
}

