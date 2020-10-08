<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
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
