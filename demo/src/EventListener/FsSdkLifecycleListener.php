<?php
//start lifecycle
//demo/src/EventListener/FsSdkLifecycleListener.php

namespace App\EventListener;

use Flagship\Flagship;
use App\Service\FsService;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class FsSdkLifecycleListener
{

    public function __construct()
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        // Start the Flagship SDK by providing the environment ID and API key
        Flagship::start("<ENV_ID>", "<API_KEY>");
    }

    #[AsEventListener(event: KernelEvents::TERMINATE)]
    public function onKernelTerminate(): void
    {
        // Close the Flagship SDK, batch, and send all collected hits
        Flagship::close();
    }
}
//end lifecycle