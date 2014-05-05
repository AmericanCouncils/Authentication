# Authentication #

This is a small framework for authenticating http requests.  The primary purpose of the library is to be a much simpler alternative to the Symfony Security Component.  It will not fit everyone's needs - if your use case is exceedingly complex, use the Security Component, or something else.

This library does not make any assumptions about, or provide facilities for managing either users, or their authorization.  Consequently, it has no notion of users or user roles.

There are three concepts to understand in order use and extend the library:

* **Firewall** - An object that matches incoming requests to registered authenticators.  The firewall with either return a Response, or an AuthenticationContext if the request is sucessfully authenticated, otherwise it throws an exception.
* **Authenticator** - A plugin for the Firewall that implements the actual logic of authentication.  When a request is successfully authenticated, an Authenticator should return an instance of an AuthenticationContext.
* AuthenticationContext - An object containing any relevant information about how the incoming request was authenticated.  This object can used by an application to derive who the current user is, or whatever else one would wish to implement.

The library does provide some basic implementations for common types of authentication.  Each of these implementations is designed to be extended by other libraries or applications.

## Implementations ##

* Anonymous
* HttpBasic
* HttpDigest
* SimpleForm
* ApiKey

## Usage ##

```php
<?php

use AC\Authentication\Firewall;
use AC\Authentication\ApiKey\ApiKeyAuthenticator;
use AC\Authentication\Form\FormLoginAuthenticator;
use AC\Authentication\Exception\AuthenticationFailedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Response;

$firewall = new Firewall();

//api key handling
$firewall->registerAuthenticator('api_key', new ApiKeyHandler(/*... deps ...*/));
$firewall->matchRequest(new RequestMatcher('^/api'), 'api_key');

//basic form login auth
$firewall->registerAuthenticator('form', new FormLoginHandler(/* ... deps ... */));
$firewall->matchRequest(new RequestMatcher('/clients'), 'form');

//authenticate a request
try {
    $context = $firewall->authenticate(Request::createFromGlobals());
} catch (AuthenticationFailedException $e) {
    (new Response(500, 'Firewall failed to do anything useful.'))->send();
    exit();
}

//could be a redirect, say to a form login, or an already assembled error response
if ($context instanceof Response) {
    $response->send();
    exit();
}

//do app stuff, using $context, whatever that may be
```