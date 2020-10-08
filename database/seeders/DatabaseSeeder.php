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

         User::factory(10)->create();
         Task::factory(10)->create();
         Message::factory(10)->create();

         // Get all the roles attaching up to 3 random roles to each user
         $tasks = Task::all();
         User::all()->each(function ($user) use ($tasks){
             $user->tasks()->attach(
                 $tasks->random(rand(1, 3))->pluck('id')->toArray()
             );
         });
    }
}
