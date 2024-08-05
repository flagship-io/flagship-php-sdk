<?php
//start lifecycle
//demo/src/EventListener/FsSdkLifecycleListener.php

namespace App\EventListener;

use App\Service\FsService;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class FsSdkLifecycleListener
{

    public function __construct(private FsService $fsService)
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        // Step 1: Start the Flagship SDK by providing the environment ID and API key
        $this->fsService->startSdk("<ENV_ID>", "<API_KEY>");
    }

    #[AsEventListener(event: KernelEvents::TERMINATE)]
    public function onKernelTerminate(): void
    {
        $this->fsService->closeSdk();                               
    }
}
//end lifecycle