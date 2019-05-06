<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

/**
 * Class DefaultController
 *
 * @package AppBundle\Controller
 */
class HomepageController extends Controller
{

    /**
     * Front page.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function frontAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(
          'SkillsBundle:Category'
        );
        $categories = [];
        foreach ($repository->findAll() as $category) {
            $categories[] = [
              'name' => $category->getName(),
              'id' => $category->getId(),
            ];
        }

        $loginForm = [];
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user === "anon.") {
            $loginForm = $this->getLoginForm($request);
        }

        return $this->render(
          'AppBundle::homepage.html.twig',
          ['login' => $loginForm, 'categories' => $categories]
        );
    }

    public function zloginAction()
    {
        return new JsonResponse(['success' => true]);
    }

    protected function getLoginForm(Request $request)
    {
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(
          $lastUsernameKey
        );

        $csrfToken = $this->has('security.csrf.token_manager')
          ? $this->get('security.csrf.token_manager')
            ->getToken('authenticate')
            ->getValue()
          : null;

        $form = [
          'last_username' => $lastUsername,
          'error' => $error,
          'csrf_token' => $csrfToken,
        ];

        return $form;
    }
}
