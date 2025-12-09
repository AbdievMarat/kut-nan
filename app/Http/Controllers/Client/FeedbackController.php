<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreFeedbackRequest;
use App\Models\Feedback;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form
     *
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('client.feedback.create');
    }

    /**
     * Store feedback
     *
     * @param StoreFeedbackRequest $request
     * @return RedirectResponse
     */
    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        Feedback::create($validatedData);

        return redirect()
            ->route('feedback.create')
            ->with('success', ['text' => 'Спасибо за вашу обратную связь! Ваше сообщение успешно отправлено.']);
    }
}
