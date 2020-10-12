<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class MessageController extends Controller
{
    protected function user() {
        return JWTAuth::parseToken()->authenticate();
    }

    public function create(int $taskId, Request $request)
    {
        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user()->id) {
            return new JsonResponse(
                'task id ' . $taskId . ' message cannot be created because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $message = new Message();
        $message->subject = $request->subject;
        $message->message = $request->message;
        $message->owner = $this->user()->id;

        if ($task->messages()->save($message)) {
            return new JsonResponse(
                'Message ' . $message->subject . ' was created successfully',
                \Illuminate\Http\Response::HTTP_CREATED
            );
        } else {
            return new JsonResponse(
                'Message ' . $message->subject. ' failed to create',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(int $taskId, int $messageId, Request $request)
    {
        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user()->id) {
            return new JsonResponse(
                'task id ' . $taskId . ' message cannot be updated because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $message = Message::find($messageId);
        $message->subject = $request->subject;
        $message->message = $request->message;

        if ($task->messages()->save($message)) {
            return new JsonResponse(
                'Message ' . $message->subject . ' was updated successfully',
                \Illuminate\Http\Response::HTTP_CREATED
            );
        } else {
            return new JsonResponse(
                'Message ' . $message->subject. ' failed to update',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
