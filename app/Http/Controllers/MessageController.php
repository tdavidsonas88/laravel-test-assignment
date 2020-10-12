<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response;
use Transformers\MessageTransformer;
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

    public function update(int $messageId, Request $request)
    {

        $message = Message::find($messageId);
        $taskId = $message->task_id;

        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user()->id) {
            return new JsonResponse(
                'task id ' . $taskId . ' message cannot be updated because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }


        if ($message->task_id !== $taskId) {
            return new JsonResponse(
                'task id ' . $taskId . ' does not have related message with id ' . $messageId,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

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

    public function show(int $taskId, int $messageId )
    {
        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user()->id) {
            return new JsonResponse(
                'task id ' . $taskId . ' message cannot be shown because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $message = Message::find($messageId);

        if ($message->task_id !== $taskId) {
            return new JsonResponse(
                'task id ' . $taskId . ' does not have related message with id ' . $messageId,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $fractal = new Manager();
        $resource = new Item($message, new MessageTransformer());
        return $fractal->createData($resource)->toJson();
    }
}
