<?php

namespace AC\Authentication;

/**
 * Implements a specific type of authentication logic for the Firewall.  Authenticators
 * create or refresh AuthContexts.
 */
interface AuthenticatorInterface
{
    public function supportsAuthContext(AuthContextInterface $context);
    public function createAuthContext(Request $req, $ops = []);
    public function refreshAuthContext(Request $req, AuthContextInterface $context, $ops = []);
}
