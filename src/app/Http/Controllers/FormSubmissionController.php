<?php

namespace App\Http\Controllers;

use App\Domains\Forms\Actions\SubmitFormAction;
use App\Domains\Forms\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormSubmissionController extends Controller
{
    public function __invoke(Form $form, Request $request, SubmitFormAction $submitFormAction): JsonResponse
    {
        abort_unless($form->is_active, Response::HTTP_NOT_FOUND);

        $submission = $submitFormAction(
            $form,
            $request->except(['_token']),
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ],
            $request->allFiles(),
        );

        return response()->json([
            'message' => $form->success_message ?? __('Ваше сообщение отправлено. Спасибо!'),
            'submission_id' => $submission->id,
        ], Response::HTTP_CREATED);
    }
}
