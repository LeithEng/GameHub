<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class LoginAuthAuthenticator extends AbstractLoginFormAuthenticator
{
use TargetPathTrait;

public const LOGIN_ROUTE = 'app_login';

public function __construct(
private UrlGeneratorInterface $urlGenerator,
private EntityManagerInterface $entityManager
) {
}

public function authenticate(Request $request): Passport
{
$username = $request->request->get('username');
$password = $request->request->get('password');
$csrfToken = $request->request->get('_csrf_token');

$request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

return new Passport(
new UserBadge($username, function($username) {
// Fetch the user from the database
$userRepository = $this->entityManager->getRepository(User::class);
$user = $userRepository->findOneBy(['username' => $username]);

if (!$user) {
throw new CustomUserMessageAuthenticationException('User not found.');
}

if ($user->isBanned()) {
throw new CustomUserMessageAuthenticationException('Your account has been banned.');
}

return $user;
}),
new PasswordCredentials($password),
[
new CsrfTokenBadge('authenticate', $csrfToken),
new RememberMeBadge(),
]
);
}

public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
if (in_array('ROLE_ADMIN', $token->getUser()->getRoles())) {
return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
}

return new RedirectResponse($this->urlGenerator->generate('home'));
}

protected function getLoginUrl(Request $request): string
{
return $this->urlGenerator->generate(self::LOGIN_ROUTE);
}
}
