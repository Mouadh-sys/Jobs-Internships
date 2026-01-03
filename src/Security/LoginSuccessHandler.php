<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $roles = $token->getRoleNames();

        if (in_array('ROLE_COMPANY', $roles, true) || in_array('ROLE_ADMIN', $roles, true)) {
            $targetUrl = $this->router->generate('company_profile_show');
            file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - User with roles " . implode(',', $roles) . " redirecting to: " . $targetUrl . "\n", FILE_APPEND);
            return new RedirectResponse($targetUrl);
        }

        $targetUrl = $this->router->generate('app_offers_index');
        file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Redirecting to default: " . $targetUrl . "\n", FILE_APPEND);
        return new RedirectResponse($targetUrl);
    }
}
