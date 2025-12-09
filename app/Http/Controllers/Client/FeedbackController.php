<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreFeedbackRequest;
use App\Mail\NewFeedbackNotification;
use App\Models\Feedback;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

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
        $validatedData['is_send'] = false;

        $feedback = Feedback::create($validatedData);

        // Отправляем письмо на почту
        try {
            Mail::to('avtendi05@gmail.com')->send(new NewFeedbackNotification($feedback));
            $feedback->update(['is_send' => true]);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            \Log::error('Ошибка отправки письма обратной связи: ' . $e->getMessage());
        }

        return redirect()
            ->route('feedback.create')
            ->with('success', ['text' => 'Спасибо за вашу обратную связь! Ваше сообщение успешно отправлено.']);
    }
}
