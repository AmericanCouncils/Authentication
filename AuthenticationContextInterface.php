<?php

namespace AC\Authentication;

/**
 * Base interface for AuthContext's.  An AuthContext describes the manner
 * in which authentication occured.
 *
 * Specific handlers would provide their own extensions of the AuthenticationContext that
 * are relevant to them.  For example, an ApiKeyAuthHandler would likely have extra
 * methods for retrieving the specific ApiKey used.
 */
interface AuthenticationContextInterface
{
    public function isStateless();
    public function eraseSensitiveData();
}
