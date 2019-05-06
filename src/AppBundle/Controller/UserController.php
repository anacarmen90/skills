<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ZUser;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    /**
     * Render the found user page
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws EntityNotFoundException
     */
    public function getUserProfileAction($id)
    {
        /** @var ZUser $zUser */
        $repository = $this->getDoctrine()->getRepository('AppBundle:ZUser');
        $zUser = $repository->findOneBy(['employeeId' => $id]);
        if (!$zUser) {
            throw $this->createNotFoundException('User not found');
        }

        $userData = [
            'picture' => 'employees/' . $id . '.jpg',
            'name' => $zUser->getFullName(),
            'availability' => $zUser->isAvailable()
        ];

        return $this->render('@App/user-profile.html.twig', $userData);
    }

    /**
     * @return JsonResponse
     */
    public function updateAvailabilityAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user instanceof ZUser) {
            throw new AccessDeniedHttpException('You need to be logged in');
        }
        $current = $user->isAvailable();
        $user->setIsAvailable(!$current);
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['avail' => !$current]);
    }

    /**
     * Profile page.
     *
     * @return Response
     */
    public function getPersonalProfileAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user instanceof ZUser) {
            throw new AccessDeniedHttpException('You need to be logged in');
        }

        $userSkills = $this->getDoctrine()
          ->getRepository('SkillsBundle:UserSkill')
          ->findBy(['user' => $user->getId()]);

        $skillsRepository = $this->getDoctrine()->getRepository(
          'SkillsBundle:Skill'
        );

        $skills = [];
        $categories = [];
        foreach ($skillsRepository->findAll() as $skill) {
            $id = $skill->getId();
            $skills[$id] = [
              'id' => $id,
              'name' => $skill->getName(),
              'description' => $skill->getDescription(),
              'tips' => $skill->getTips(),
              'categories' => [],
            ];
            foreach ($skill->getCategories() as $category) {
                $catId = $category->getId();
                $categories[$catId] = [
                  'id' => $catId,
                  'name' => $category->getName(),
                ];
                $skills[$id]['categories'][] = $catId;
            }
        }

        foreach ($userSkills as $userSkill) {
            $skillId = $userSkill->getSkill()->getId();
            $skills[$skillId]['level'] = [
              'id' => $userSkill->getId(),
              'level' => $userSkill->getLevel(),
            ];
        }

        $data = [
          'picture' => 'employees/'.$user->getEmployeeId().'.jpg',
          'name' => $user->getFullName(),
          'availability' => $user->isAvailable(),
          'skills' => $skills,
          'categories' => $categories,
        ];

        return $this->render('@App/personal-profile.html.twig', $data);
    }
}