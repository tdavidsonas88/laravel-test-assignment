# Laravel test assignment

A customer is in need of an application with which they can manage tasks. Tasks can have
messages attached to them. The scope of this assignment is the backend (API) only.

## How to lunch

- `git clone https://github.com/tdavidsonas88/laravel-test-assignment.git`
- `cd laravel-test-assignment`
- `cp .env.example .env` and configure mysql database (or use sqlite)
- create local database (if needed create user and grant access with
 parameters same as in your .env file) (or use sqlite)
- `composer install`
- `php artisan migrate`
- `php artisan db:seed`
- `php artisan key:generate`
- `php artisan serve`
- Use your favorite REST client like: Insomnia or Postman. For example:
- Login to http://localhost:8000/api/login with email (pick any from `users` table) and password (`123456`)
- Create your requests, for example:

to get all tasks: 
[GET] http://localhost:8000/api/tasks
[JSON]
{
	"token": "gerenated token retrieved from the login request"
}

- Experiment with other routes available: `php artisan route:list`

