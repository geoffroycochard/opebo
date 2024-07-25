<?php

namespace App\Controller;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\Language;
use App\Config\Objective;
use App\Config\PersonStatus;
use App\Dto\RegisterDto;
use App\Entity\Course;
use App\Entity\Domain;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Repository\CityRepository;
use App\Repository\CourseRepository;
use App\Repository\DomainRepository;
use App\Repository\PersonRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/**
 * API Class to communication with bo-ope
 * 
 * Authentification : X-AUTH-TOKEN header
 */
class ApiController extends AbstractController
{

    /**
     * Doc method
     * /api/doc 
     */
    #[Route('/api/doc', name: 'app_api')]
    public function doc(): ?Response
    {        
        $rc = new ReflectionClass(ApiController::class);
        $comments = [];
        $comments[] = $rc->getDocComment();
        foreach($rc->getMethods() as $method) {
            $comments[] = implode(',', $method->getAttributes());
        }
        return new Response(implode('', $comments), Response::HTTP_OK);
    }

    /**
     * Retrieve languages method
     * /api/language/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/civility/{locale}', 
        name: 'app_api_civility', 
        requirements: ['local' => 'fr|en']
    )]
    public function civility($locale, TranslatorInterface $translator): ?Response
    {        
        $data = [];
        foreach (Civility::cases() as $language) {
            $data[$language->value] = $language->trans($translator,$locale);
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Retrieve languages method
     * /api/language/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/language/{locale}', 
        name: 'app_api_language', 
        requirements: ['local' => 'fr|en']
    )]
    public function language($locale, TranslatorInterface $translator): ?Response
    {        
        $data = [];
        foreach (Language::cases() as $language) {
            $data[$language->value] = $language->trans($translator,$locale);
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route(
        '/api/objective/{locale}', 
        name: 'app_api_objective', 
        requirements: ['local' => 'fr|en']
    )]
    public function objective($locale, TranslatorInterface $translator): ?Response
    {        
        $data = [];
        foreach (Objective::cases() as $objective) {
            $data[$objective->value] = $objective->trans($translator,$locale);
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route(
        '/api/domain/{locale}',
        name: 'app_api_domain',
        requirements: ['local' => 'fr|en']
    )]
    public function domain(ManagerRegistry $managerRegistry): ?Response
    {  
        $data = [];
        $domains = ($managerRegistry->getRepository(Domain::class));
        foreach ($domains->findAll() as $domain) {
            $data[$domain->getId()] = $domain->getName();
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route(
        '/api/courses',
        name: 'app_api_courses'
    )]
    public function courses(ManagerRegistry $managerRegistry): ?Response
    {  
        $data = [];
        $courses = ($managerRegistry->getRepository(Course::class));
        foreach ($courses->findAll() as $course) {
            $a = [
                $course->getSector()->getEstablishment()->getName(),
                $course->getSector()->getName(),
                $course->getName()
            ];
            $data[$course->getId()] = implode(' / ', $a);
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * 
     * {email: geoffroy.cochard@gmail.com, city: 45000, }
     */
    #[Route(
        '/api/register',
        name: 'app_api_register',
        methods: 'post'
    )]
    public function register(
        #[MapRequestPayload] RegisterDto $register,
        PersonRepository $personRepository,
        CityRepository $cityRepository,
        DomainRepository $domainRepository,
        CourseRepository $courseRepository
    ): ?Response
    {  
        $data = [];
        
        // Find person
        $personClass = 'App\\Entity\\'.ucfirst($register->type);
        $person = $personRepository->findOneBy(['email' => $register->email]) ?? new $personClass;

        // Find city
        $city = $cityRepository->find($register->city);

        // update / create person
        $person->setFirstname($register->firstname);
        $person->setLastname($register->lastname);
        $person->setBirthdate(new DateTimeImmutable($register->birthdate));
        $person->setPhone($register->phone);
        $person->setCity($city);
        $person->setState(PersonStatus::Active);
        
        // Gender
        $genderMapping = ['mr' => 'male', 'mrs' => 'female'];
        $gender = Gender::from($genderMapping[$register->civility]);
        $person->setGender($gender);

        // Course
        $course = $courseRepository->find($register->course);

        // Lead
        $lead = $register->type === 'student' ? new Request() : new Proposal();
        $lead->setPerson($person);
        $lead->setObjective(explode(',', $register->objectives));
        $lead->setLanguage(explode(',', $register->languages));

        // $domains = $domainRepository->findBy(['uid'])
        dd($lead);


        return new JsonResponse($data, Response::HTTP_OK);
    }


}
