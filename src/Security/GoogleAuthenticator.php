<?php

namespace App\Security;

use App\Entity\User; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;


/**
 * 
 */

 class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
 {
     private $clientRegistry;
     private $entityManager;
     private $router;

     public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
     {
         $this->clientRegistry = $clientRegistry;
         $this->entityManager = $entityManager;
         $this->router = $router;
     }

     public function supports(Request $request): ?bool
     {
         // continue ONLY if the current ROUTE matches the check ROUTE
         return $request->attributes->get('_route') === 'connect_google_check';
     }

     public function authenticate(Request $request): Passport
     {
         $client = $this->clientRegistry->getClient('google');
         $accessToken = $this->fetchAccessToken($client);

         return new SelfValidatingPassport(
             new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                 /** @var GoogleUser $googleUser */
                 $googleUser = $client->fetchUserFromToken($accessToken);

                 var_dump($googleUser);

                 //$email = $googleUser->getEmail();

                 // 1) have they logged in with Google before? Easy!
                 //$existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);



                 //if ($existingUser != null) {
                  //   return $existingUser;
                 //}

                 // 2) do we have a matching user by email?
                 //$user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);


                 // 3) Maybe you just want to "register" them by creating a User object
//                 $user->setGoogleId($googleUser->getId());
//                 $user->setEmail($email);
//                 $user->setName($googleUser->getName());
//                 $user->setFirstName($googleUser->getFirstName());
//                 $user->setImage($googleUser->getAvatar());
//                 $this->entityManager->persist($user);
//                 $this->entityManager->flush();

                 return $googleUser;
             })
         );
     }

     public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
     {
         // change "app_homepage" to some route in your app
         $targetUrl = $this->router->generate('person');

         return new RedirectResponse($targetUrl);

         // or, on success, let the request continue to be handled by the controller
         //return null;
     }

     public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
     {
         $message = strtr($exception->getMessageKey(), $exception->getMessageData());

         return new Response($message, Response::HTTP_FORBIDDEN);
     }

     /**
      * Called when authentication is needed, but it's not sent.
      * This redirects to the 'login'.
      */
     public function start(Request $request, AuthenticationException $authException = null): Response
     {
         return new RedirectResponse(
             '/connect/', // might be the site, where users choose their oauth provider
             Response::HTTP_TEMPORARY_REDIRECT
         );
     }
 }