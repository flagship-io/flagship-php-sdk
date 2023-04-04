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
