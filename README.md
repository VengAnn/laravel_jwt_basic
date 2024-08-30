## Getting Started with Laravel and JWT-Auth

### 1. Install Laravel and JWT-Auth Package

To begin, ensure you have a Laravel project set up. If you don’t already have a project, you can create a new one by running the following command:

```bash
composer create-project --prefer-dist laravel/laravel jwt-auth-app

Next, install the tymon/jwt-auth package, which is a popular JWT implementation for Laravel:

```bash
composer require tymon/jwt-auth


###  2. Publish the Configuration File

Publish the package’s configuration file to customize it if needed:

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

This will create a config/jwt.php configuration file.

### 3. Generate JWT Secret Key
Generate the secret key that will be used to sign your JWT tokens:

```bash
php artisan jwt:secret

This command will set the JWT_SECRET key in your .env file.



======================================================================
4. Update User Model
Update the User model to implement JWTSubject. This requires adding two methods: getJWTIdentifier() and getJWTCustomClaims().

```bash
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // Other existing code

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}


```bash
5. Set Up Authentication Controller
Create a new controller for handling user registration and login:

php artisan make:controller AuthController


In the AuthController, you’ll add methods for registering and logging in users.

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    /**
     * Log in an existing user.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json(compact('token'));
    }

    /**
     * Get the authenticated user.
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log out the user (Invalidate the token).
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return response()->json(['token' => auth()->refresh()]);
    }
}

```bash
6. Set Up Routes
Add routes to handle registration, login, and other authentication-related tasks in routes/api.php:


use App\Http\Controllers\AuthController;

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});


```bash
7. Configure Authentication Guards
In your config/auth.php file, configure the authentication guards to use jwt instead of session for API requests.

'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],


==========================================================================

8. Test the API
You can now test your API using tools like Postman or cURL.

Register a User:

Endpoint: POST /api/auth/register
Body:
json
Copy code
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
Response: Should return the registered user and a JWT token.
Login a User:

Endpoint: POST /api/auth/login
Body:
json
Copy code
{
  "email": "john@example.com",
  "password": "password"
}
Response: Should return a JWT token.
Access a Protected Route:

Endpoint: GET /api/auth/me
Header: Authorization: Bearer {token}
Response: Should return the authenticated user's data.
Logout:

Endpoint: POST /api/auth/logout
Header: Authorization: Bearer {token}
Response: Should return a success message.
Refresh Token:

Endpoint: POST /api/auth/refresh
Header: Authorization: Bearer {token}
Response: Should return a new JWT token.
9. Middleware for Route Protection
Ensure that routes requiring authentication are protected using the auth:api middleware. This has already been done in the route definitions above.

10. Customization (Optional)
You can customize the JWT token expiration time, blacklist functionality, and more in the config/jwt.php file.
You can add custom claims to the JWT by modifying the getJWTCustomClaims method in the User model.













#### this special part to auto deleted the invalid token in table (Invalided token)
1. Create the Command
If you haven't already, create a command for cleaning up expired tokens:

bash
Copy code
php artisan make:command CleanExpiredTokens
2. Implement the Command
Edit the command file located at app/Console/Commands/CleanExpiredTokens.php:

php
Copy code
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InvalidatedToken;
use Carbon\Carbon;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Remove expired tokens from the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get current time
        $now = Carbon::now();

        // Delete expired tokens
        InvalidatedToken::where('expired_time', '<', $now)->delete();

        $this->info('Expired tokens cleaned successfully.');
    }
}
3. Schedule the Command
Open app/Console/Kernel.php and schedule the command. You can specify the exact time you want it to run daily. For example, if you want it to run at 2 AM every day, you would configure it as follows:

php
Copy code
protected function schedule(Schedule $schedule)
{
    // Run the command daily at 2 AM
    $schedule->command('tokens:clean')->dailyAt('02:00');
}
4. Set Up the Cron Job
Make sure you have a cron job set up to run Laravel’s scheduler every minute. Open your server’s crontab file:

bash
Copy code
crontab -e
Add the following line:

bash
Copy code
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
Replace /path-to-your-project/ with the path to your Laravel project.

Summary
Create a command to clean up expired tokens.
Implement the logic to delete expired records in that command.
Schedule the command to run daily at a specific time (e.g., 2 AM) in the Kernel.php file.
Set up a cron job to ensure Laravel’s scheduler runs every minute.
This setup ensures your cleanup process runs daily at the specified time, keeping your database clean of expired tokens.