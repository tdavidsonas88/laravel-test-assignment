<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response;
use Transformers\TaskTransformer;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of user owned resource.
     *
     */
    public function index()
    {
        $tasks = $this->user
            ->tasks()
            ->with('messages')
            ->get()
            ->toArray();
        $fractal = new Manager();
        $resource = new Collection($tasks, new TaskTransformer());
        return $fractal->createData($resource)->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Response|object
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "name" => "required|max:255",
            "description" => "required|max:4096",
            "type" => "in:basic,advanced,expert",
            "status" => "in:todo,closed,hold",
            "attach" => "array"
        ]);

        $task = new Task();
        $task->name = $request->name;
        $task->description = $request->description;
        $task->type = $request->type;
        $task->status = $request->status;
        $task->owner = $this->user->id;
        $usersTaskToBeAttached = $request->attach;

        // task is attached to the owner on save
        if ($this->user->tasks()->save($task)) {
            // task can be attached to other users
            if (!empty($request->attach)) {
                $this->attachUsersToTasks($task->id, $usersTaskToBeAttached);
            }
            return new JsonResponse(
                'task ' . $task->title . ' was created successfully',
                \Illuminate\Http\Response::HTTP_CREATED
            );
        } else {
            return new JsonResponse(
                'task ' . $task->title . ' failed to create',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }


    }

    /**
     * Display the specified resource.
     *
     * @param Task $task
     * @return string
     */
    public function show(Task $task)
    {
        $fractal = new Manager();
        $resource = new Item($task, new TaskTransformer());
        return $fractal->createData($resource)->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return TaskResource
     */
    public function edit(Request $request, int $id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $taskId
     * @return JsonResponse
     */
    public function update(Request $request, int $taskId)
    {
        $this->validate($request, [
            "name" => "required|max:255",
            "description" => "required|max:4096",
            "type" => "in:basic,advanced,expert",
            "status" => "in:todo,closed,hold"
        ]);

        /** @var Task $task */
        $task = Task::find($taskId);

        if ($task === null || $task->owner !== $this->user->id) {
            return new JsonResponse(
                'task with id ' . $taskId . ' cannot be updated because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $task->name = $request->name;
        $task->description = $request->description;
        $task->type = $request->type;
        $task->status = $request->status;
        $usersTaskToBeAttached = $request->attach;

        if ($task->save()) {
            if (!empty($request->attach)) {
                $this->attachUsersToTasks($taskId, $usersTaskToBeAttached);
            }

            return new JsonResponse(
                'task ' . $task->name . ' was updated successfully',
                \Illuminate\Http\Response::HTTP_OK
            );
        } else {
            return new JsonResponse(
                'task ' . $task->name . ' failed to update',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Task $task
     * @return string
     * @throws \Exception
     */
    public function destroy(Task $task)
    {
        if ($task->owner !== $this->user->id) {
            return new JsonResponse(
                'task [' . $task->name . '] cannot be deleted because you are not the owner of it',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if ($task->delete()) {
            return new JsonResponse("Task [" . $task->name . "] was deleted successfully", Response::HTTP_OK);
        }
    }

    public function attachUser(Task $task)
    {

    }

    /**
     * @param $taskId
     * @param array $usersToAttach
     */
    public function attachUsersToTasks($taskId, array $usersToAttach): void
    {
        foreach ($usersToAttach as $userId) {
            DB::table('task_user')->insert(
                [
                    'task_id' => $taskId,
                    'user_id' => $userId
                ]
            );
        }
    }
}
