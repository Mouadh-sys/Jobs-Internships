<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private LoggerInterface $logger,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $roles = $token->getRoleNames();

        if (in_array('ROLE_COMPANY', $roles, true) || in_array('ROLE_ADMIN', $roles, true)) {
            $targetUrl = $this->router->generate('company_profile_show');
            $this->logger->debug('Login redirect', [
                'roles' => $roles,
                'target_url' => $targetUrl,
            ]);
            return new RedirectResponse($targetUrl);
        }

        $targetUrl = $this->router->generate('app_offers_index');
        $this->logger->debug('Login redirect', [
            'roles' => $roles,
            'target_url' => $targetUrl,
        ]);
        return new RedirectResponse($targetUrl);
    }
}
