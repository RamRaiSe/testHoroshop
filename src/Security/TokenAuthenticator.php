<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/v1/api');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('No Bearer token provided');
        }

        $token = substr($authHeader, 7);

        if (!in_array($token, ['testAdmin', 'testUser'])) {
            throw new AuthenticationException('Invalid token');
        }

        $apiUser = new ApiUser($token);

        return new SelfValidatingPassport(new UserBadge($token, fn () => $apiUser));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
    }
}
