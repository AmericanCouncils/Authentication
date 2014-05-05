<?php

namespace AC\Authentication\Subscriber;

class HttpKernelSubscriber implements EventSubscriberInterface
{
    protected $firewall;

    public function __construct(FirewallInterface $firewall)
    {
        $this->firewall = $firewall;
    }

    public function onKernelRequest(GetResponseEvent $e)
    {
        $req = $e->getRequest();

        $result = $this->firewall->authenticate($request);

        if ($result instanceof Response) {
            $e->setResponse($result);
            return;
        }

        if ($result instanceof AuthenticationContextInterface) {
            $req->attributes->set('_authentication_context', $result);
            return;
        }

        throw new HttpException(401, "Authentication required.");
    }

    public function onKernelException(Event $e)
    {
        if ($e->getException() instanceof AuthenticationException) {
            throw new HttpException(401, "Authentication required.");
        }
    }
}
