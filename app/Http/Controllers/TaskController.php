<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response;
use Transformers\TaskTransformer;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $tasksArray = Task::all();
        $fractal = new Manager();
        $resource = new Collection($tasksArray, new TaskTransformer());
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
        $task = new Task();
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->type = $request->input('type');
        $task->status = $request->input('status');
        $task->user_id = $request->input('user_id');
        $task->save();
        return new JsonResponse(
            'task ' . $task->title . ' was created successfully',
            \Illuminate\Http\Response::HTTP_CREATED
        );
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
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->type = $request->input('type');
        $task->status = $request->input('status');
        $task->user_id = $request->input('user_id');
        $task->save();

        return new JsonResponse(
            'task ' . $task->name . ' was updated successfully',
            \Illuminate\Http\Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return string
     */
    public function destroy($id)
    {
        $task = Task::findOrfail($id);
        if ($task->delete()) {
            return new JsonResponse("Task with id " . $id . " was deleted successfully", Response::HTTP_OK);
        }
    }
}
