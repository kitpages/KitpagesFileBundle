<?php

namespace Kitpages\FileBundle\EventListener;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionListener {

    ////
    // dependency injection
    ////
    protected $logger = null;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }
    /**
     * @return LoggerInterface $logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (isset($_POST["kitpages_file_session_id"])) {
            $this->getLogger()->debug("************ FileBundle::SessionListener, change session id");
            session_id($_POST["kitpages_file_session_id"]);
        }
    }
}

?>
