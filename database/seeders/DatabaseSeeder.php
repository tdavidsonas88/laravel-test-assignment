<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('task_user')->truncate();
        DB::table('users')->truncate();
        DB::table('tasks')->truncate();
        DB::table('messages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

         User::factory(5)->create();
         Task::factory(50)->create();
         Message::factory(100)->create();

         $tasks = Task::all();
         User::all()->each(function ($user) use ($tasks){

             foreach ($tasks as $task) {
                 if ($task->owner === $user->id) {
                     $user->tasks()->attach($task->id);
                 }
             }
         });
    }
}
