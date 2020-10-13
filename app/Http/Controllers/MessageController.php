<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response;
use Transformers\MessageTransformer;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class MessageController
 * @package App\Http\Controllers
 */
class MessageController extends Controller
{
    /**
     * @return User
     */
    protected function user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * @param int $taskId
     * @param Request $request
     * @return JsonResponse
     */
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
                'Message ' . $message->subject . ' failed to create',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param int $messageId
     * @param Request $request
     * @return JsonResponse
     */
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
                'Message ' . $message->subject . ' failed to update',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param int $messageId
     * @return JsonResponse|string
     */
    public function show(int $messageId)
    {
        $message = Message::find($messageId);
        if ($message === null) {
            return new JsonResponse(
                'there is no such message with id ' . $messageId,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        $taskId = $message->task_id;

        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user()->id) {
            return new JsonResponse(
                'task id ' . $taskId . ' message cannot be shown because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if ($message->task_id !== $taskId) {
            return new JsonResponse(
                'task id ' . $taskId . ' does not have related message with id ' . $messageId,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        // log created when message is viewed
        Log::info('Message [id]' . $message->id . '[/id] ' . $message->subject . ' was viewed [' . now()->timestamp . ']');

        $fractal = new Manager();
        $resource = new Item($message, new MessageTransformer());
        return $fractal->createData($resource)->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Message $message
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Message $message)
    {
        if ($message->owner !== $this->user()->id) {
            return new JsonResponse(
                'Message [' . $message->subject . '] cannot be deleted because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if ($message->delete()) {
            return new JsonResponse("Message [" . $message->subject . "] was deleted successfully", Response::HTTP_OK);
        }
    }

    /**
     * List of user owner/attached messages
     *
     * @return string
     */
    public function index()
    {
        $messagesPaginator = Message::where('owner', $this->user()->id)->orderBy('task_id', 'ASC')->paginate(5);
        $messages = $messagesPaginator->getCollection();
        $fractal = new Manager();
        $resource = new Collection($messages, new MessageTransformer());
        $resource->setPaginator(new IlluminatePaginatorAdapter($messagesPaginator));
        return $fractal->createData($resource)->toJson();
    }

    /**
     * Message log for all tasks I'm attached to or I'm the owner of
     * @return JsonResponse
     */
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = file($logFile);
        $messagesInfoLogs = $this->findLogMessagesByOwner($lines);

        return new JsonResponse(
            $messagesInfoLogs,
            Response::HTTP_OK
        );
    }

    /**
     * @param array $lines
     * @return array
     */
    private function findLogMessagesByOwner(array $lines): array
    {
        $messagesInfoLogs = [];
        foreach ($lines as $line) {
            if (strpos($line, 'local.INFO') !== false) {
                preg_match('#\\[id\\](.+)\\[/id\\]#s', $line, $results);
                if (!empty($results)) {
                    $messageId = (int)$results[1];
                    $message = Message::find($messageId);
                    if ($message->owner === $this->user()->id) {
                        $messagesInfoLogs[] = $line;
                    }
                }
            }
        }
        return $messagesInfoLogs;
    }
}
