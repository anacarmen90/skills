<?php

namespace SkillsBundle\Controller;

use AppBundle\Entity\ZUser;
use Elastica\Query;
use Elastica\Suggest\Completion;
use SkillsBundle\Entity\Endorsement;
use SkillsBundle\Entity\Skill;
use SkillsBundle\Entity\UserSkill;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AjaxSkillController extends Controller
{

    /**
     * Search skills by keyword using mysql FULLTEXT index.
     *
     * @param $searchString
     *
     * @return JsonResponse
     */
    public function searchSkillAction($searchString)
    {
        /* @var $searchResults Skill[] */
        $searchResults = $this->get('fos_elastica.finder.app.skill')->find($searchString);

        $skills = [];
        foreach ($searchResults as $skill) {
            $categories = [];
            foreach ($skill->getCategories() as $category) {
                $categories[] = $category->getName();
            }
            $skills[] = [
              'id' => $skill->getId(),
              'name' => $skill->getName(),
              'description' => $skill->getDescription(),
              'categories' => $categories,
            ];
        }

        return new JsonResponse($skills);
    }

    /**
     * Autocomplete suggestions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function suggestSkillAction(Request $request)
    {
        if (!($text = $request->get('q'))) {
            throw new BadRequestHttpException('Missing "q" parameter.');
        }

        $completion = new Completion('suggest', 'name_suggest');
        $completion->setText($text);
        $completion->setFuzzy(array('fuzziness' => 2));
        $resultSet = $this->get('fos_elastica.index.app.skill')->search(Query::create($completion));

        $suggestions = [];
        foreach ($resultSet->getSuggests() as $suggests) {
            foreach ($suggests as $suggest) {
                foreach ($suggest['options'] as $option) {
                    $suggestions[] = array(
                      'id' => $option['payload']['id'],
                      'text' => $option['text']
                    );
                }
            }
        }

        return new JsonResponse($suggestions);
    }

    /**
     * @return JsonResponse
     */
    public function skillCategoriesAction()
    {
        $repository = $this->getDoctrine()->getRepository(
          'SkillsBundle:Category'
        );
        $categories = $repository->findAll();

        $output = [];
        foreach ($categories as $category) {
            $output[] = [
              'name' => $category->getName(),
              'id' => $category->getId(),
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * Returns all users that are any good at the specified skill.
     *
     * @param $skillId
     *
     * @return JsonResponse
     */
    public function skilledAction($skillId)
    {
        $strings = [
          0 => 'Not at all',
          1 => 'Just a bit',
          2 => 'Pretty good',
          3 => 'Jedi',
        ];

        // @todo: move this to a service and parametrize availability.
        /* @var $userSkillsRepo \Doctrine\ORM\EntityRepository */
        $userSkillsRepo = $this->getDoctrine()->getRepository(
          'SkillsBundle:UserSkill'
        );
        $query = $userSkillsRepo->createQueryBuilder('us');
        $query->join('AppBundle:ZUser', 'u')
          ->andWhere('us.skill = :skill')->setParameter('skill', $skillId)
          ->andWhere('u.isAvailable = 1')->andWhere('us.level > 0')
          ->orderBy('us.level', 'DESC')->orderBy('u.employeeId', 'ASC')
          ->getQuery();
        /* @var $skillLevels \SkillsBundle\Entity\UserSkill[] */
        $skillLevels = $query->getQuery()->getResult();

        $output = [];
        foreach ($skillLevels as $skillLevel) {
            $user = $skillLevel->getUser();
            $path = 'employees/';
            $output[] = [
              'id' => $user->getId(),
              'name' => $user->getFullName(),
              'image' => $path.$user->getEmployeeId().'.jpg',
              'level' => $strings[$skillLevel->getLevel()],
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * Update a UserSkill level entity.
     *
     * @todo: clean up code.
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function updateUserSkillLevelAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user instanceof ZUser) {
            throw new AccessDeniedHttpException('You need to be logged in');
        }
        $post = json_decode($request->getContent());
        $skillId = $post->skill;
        $level = $post->level;

        if (!in_array($level, range(UserSkill::MIN_LEVEL, UserSkill::MAX_LEVEL))) {
            return new JsonResponse(['error' => 'No!']);
        }

        $doctrine = $this->getDoctrine();
        $userSkill = $doctrine->getRepository('SkillsBundle:UserSkill')->findOneBy(
          ['skill' => $skillId, 'user' => $user->getId()]
        );

        if (!$userSkill) {
            $userSkill = new UserSkill();
            $skill = $doctrine->getRepository('SkillsBundle:Skill')->find($skillId);
            $userSkill->setSkill($skill);
            $userSkill->setUser($user);
        }
        $userSkill->setLevel($level);

        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($userSkill);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Endorse a user for a certain skill
     *
     * @param $endorseeId
     * @param $skillId
     *
     * @return JsonResponse
     */
    public function endorseUserAction($endorseeId, $skillId)
    {
        $endorser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$endorser instanceof ZUser) {
            throw new AccessDeniedHttpException('You need to be logged in');
        }

        $em = $this->get('doctrine.orm.default_entity_manager');

        $endorsee = $em->getRepository('AppBundle:ZUser')->findOneById($endorseeId);
        $skill = $em->getRepository('SkillsBundle:Skill')->findOneById($skillId);

        if (!$endorsee || !$skill) {
            return new JsonResponse(['error' => 'Missing or bad parameters']);
        }

        $endorsement = new Endorsement();
        $endorsement->setEndorser($endorser)->setEndorsee($endorsee)->setSkill($skill);
        $em->persist($endorsement);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Remove a given endorsement
     *
     * @param $endorseeId
     * @param $skillId
     *
     * @return JsonResponse
     */
    public function unEndorseUserAction($endorseeId, $skillId)
    {
        $endorser = $this->get('security.token_storage')->getToken()->getUser();
        if (!$endorser instanceof ZUser) {
            throw new AccessDeniedHttpException('You need to be logged in');
        }

        $em = $this->get('doctrine.orm.default_entity_manager');
        $criteria = ['endorser' => $endorser->getId(), 'endorsee' => $endorseeId, 'skill' => $skillId];
        $endorsement = $em->getRepository('SkillsBundle:Endorsement')->findOneBy($criteria);

        if ($endorsement) {
            $em->remove($endorsement);
            $em->flush();
        }

        return new JsonResponse(['success' => true]);
    }
}
