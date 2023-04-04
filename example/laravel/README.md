## About

This is [Flagship SDK PHP](https://docs.developers.flagship.io/docs/php-v3-1-x#introduction) implementation using Laravel framework 

## Install Flagship

```shell
 composer require flagship-io/flagship-php-sdk 
 ```

## Implementation

### 1. Initialize the project 

1. Rename `.env.example` to `.env` 
2. Generate a laravel application key with the following command:
```shell
php artisan key:generate
```
3. Add Flagship credentials in the .env file

```text
# .env

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:aDYIBQnqQmDgSl6gJ7o5E4UWdffRCAGgyoxKrj6kqgE=
APP_DEBUG=true
APP_URL=http://localhost

FS_ENV_ID=c8XXXXXXXXXXX  
FS_API_KEY=QXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

...
```

### 2. Create a middleware 

```shell
php artisan make:middleware FlagshipMiddleware
```

In `FlagshipMiddleware` we are going to initialize the SDK, build flagship visitor and bind the visitor with service container.

The advantage of using middleware is that you can run Flagship globally or for a particular route.

In this example we are using `Decision API` mode but considering using `BUCKETING MODE` may reduce flag fetching latency. [Learn more](https://docs.developers.flagship.io/docs/php-v3-1-x#decision-mode).

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\CacheStrategy;
use Flagship\Flagship;
use Flagship\Visitor\VisitorInterface;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class FlagshipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $envId = env("FS_ENV_ID"); //Get envId
        $apiKey = env("Fs_API_KEY"); //Get apiKey

        // Uncomment in bucketing mode
//        $fsSyncAgentHost = env("FS_SYNC_AGENT_HOST");
//        $fsSyncAgentPort = env("FS_SYNC_AGENT_PORT");
//        $fsSyncAgentUrl = "http://$fsSyncAgentHost:$fsSyncAgentPort/bucketing";

        $logger = App::make(Logger::class);

        // Start the SDK
        Flagship::start(
            $envId,
            $apiKey,
            FlagshipConfig::decisionApi() // or for bucketing mode: FlagshipConfig::bucketing($fsSyncAgentUrl)
                ->setCacheStrategy(CacheStrategy::BATCHING_AND_CACHING_ON_FAILURE) // Set cache strategy to batch hits
            ->setLogManager($logger)
        );

        //Build a flagship visitor
        $visitor = Flagship::newVisitor()
            ->withContext(["key"=>"value"])
            ->hasConsented(true)
            ->isAuthenticated(false)
            ->build();

        //Fetch flags
        $visitor->fetchFlags();

        // Bind flagship visitor instance with VisitorInterface
        App::bind(VisitorInterface::class, function () use ($visitor) {
            return  $visitor;
        });

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(): void
    {
        //Call close method to batch and send all hits that are in the pool.
        Flagship::close();
    }
}
```

### 3. Assigning `FlagshipMiddleware` to `home` route

```shell
<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\FlagshipMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name("home")
    ->middleware(FlagshipMiddleware::class); // Assigning FlagshipMiddleware to `home` route


```

### 4. Create a controller

In `index` action, we inject the visitor built previously in the middleware then get our flag

```php
<?php

//app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Flagship\Visitor\VisitorInterface;

class HomeController extends Controller
{
    // Inject VisitorInterface
    public function index(VisitorInterface $visitor)
    {
        //Get the flag my-flag
        $myFlagValue = $visitor->getFlag("my-flag", "defaultValue")->getValue();

        return view('welcome', ["myFlagValue"=>$myFlagValue, "visitorId"=>$visitor->getVisitorId()]);
    }
}

```

## 5. Run

1. Run with Artisan command
```shell
php artisan serve
```

2. Run with Docker

```shell
docker-compose up 
```
