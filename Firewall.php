<?php

namespace AC\Authentication;

/**
 * Basic implementation of the FirewallInterface
 */
class Firewall implements FirewallInterface
{
    private $sessContextKey;
    private $authenticators = [];
    private $matchers;

    public function __construct($sessContextKey = '_authentication_context')
    {
        $this->sessContextKey = $sessContextKey;
        $this->matchers = new \SplObjectStorage();
    }

    public function registerAuthenticator($name, AuthenticatorInterface $authenticator)
    {
        $this->authenticators[$name] = $authenticator;
    }

    public function matchRequest(RequestMatcherInterface $matcher, $name, $ops = [])
    {
        $this->matchers[$matcher] = [$name, $ops];
    }

    /**
     * @see  FirewallInterface::authenticate
     */
    public function authenticate(Request $request, $throw = false)
    {
        $response = $this->dispatcher->dispatch(FirewallEvents::REQUEST, new FirewallEvent($request))->getResponse();
        if ($response) {
            return $response;
        }

        //if the session is available, check if there is already an authentication
        //context, and try refreshing it
        $sess = $request->getSession();
        if ($sess && $context = $sess->get($this->sessContextKey, false)) {
            $context = $this->refreshAuthenticationContext($request, $context);
        } else {
            $context = $this->createAuthenticationContext($request);
        }

        //return early if response already given
        if ($context instanceof Response) {
            return $context;
        }

        //configure context w/ request info
        $context->setRequest($request);
        $context->setSession($sess);

        //pass around to any listeners
        $this->dispatcher->dispatch(FirewallEvents::AUTHENTICATED, new FirewallEvent($context));

        //remove any sensitive information, and store
        //in session if not stateless
        $context->eraseSensitiveData();
        if (!$context->isStateless()) {
            $sess->set($this->sessContextKey, $context);
        }

        return $context;
    }

    protected function refreshAuthenticationContext($req, $context)
    {
        $matches = $this->getMatches($req);
        foreach ($matches as list ($name, $ops)) {
            $authenticator = $this->getAuthenticator($name);

            if ($authenticator->supportsAuthContext()) {
                return $authenticator->refreshAuthContext($context, $ops);
            }
        }

        //TODO: if an old context couldn't be refreshed, what does that mean?
        //throw exception?... or attempt to create new context?
        throw new AuthenticationException();
        return $this->createAuthenticationContext($req);
    }

    protected function createAuthenticationContext($req)
    {
        $matches = $this->getAuthenticators($req);
        $exceptions = [];
        foreach ($matches as list($name, $ops)) {
            try {
                $result = $this->getAuthenticator($name)->createAuthContext($req, $ops);
                if ($result instanceof Response || $result instanceof AuthContextInterface) {
                    return $result;
                }
            } catch (AuthenticationException $e) {
                $exceptions[] = $e;
            }
        }

        throw new AuthenticationFailuresException($exceptions);
    }

    protected function getMatches($req)
    {
        $matched = [];
        foreach ($this->matchers as $matcher => $data) {
            if ($matcher->matches($req)) {
                $matched[] = $data;
            }
        }

        return $matched;
    }

    //...
}
