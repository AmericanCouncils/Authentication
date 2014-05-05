<?php

namespace AC\Authentication;

/**
 * Authenticates an incoming request by matching it to registered Authenticators.
 */
interface FirewallInterface
{
    const REQUEST = 'firewall.request';
    const AUTHENTICATED = 'firewall.authenticated';
    const RESPONSE = 'firewall.response';
    const EXCEPTION = 'firewall.exception';

    public function authenticate(Request $request);
    public function registerAuthenticator($name, AuthenticatorInterface $handler);
    public function matchRequest(RequestMatcherInterface $matcher, $name, $options = []);
    public function getDispatcher();
}
