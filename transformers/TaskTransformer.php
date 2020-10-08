<?php

namespace Transformers;

use App\Models\Task;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
    public function transform(Array $task)
    {
        return [
            'id' => (int) $task['id'],
            'name' => $task['name'],
            'description' => $task['description'],
            'type' => $task['type'],
            'status' => $task['status'],
            'user' => auth()->user()->name,
            'messages' => $task['messages']
        ];
    }
}
