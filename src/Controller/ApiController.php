<?php

namespace App\Controller;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\Language;
use App\Config\Objective;
use App\Config\PersonStatus;
use App\Dto\RegisterDto;
use App\Entity\City;
use App\Entity\Domain;
use App\Entity\Establishment;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sponsor;
use App\Entity\Student;
use App\Repository\CityRepository;
use App\Repository\DomainRepository;
use App\Repository\EstablishmentRepository;
use App\Repository\PersonRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/**
 * API Class bo-ope
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
            $comments[] = implode("<br>", array_merge([nl2br($method->getDocComment())],$method->getAttributes()));
        }
        return new Response(implode("<br><br>", $comments), Response::HTTP_OK);
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
    public function language($locale): ?Response
    {        
        $data = [];
        \Locale::setDefault($locale);
        foreach (Languages::getNames() as $code => $label) {
            $data[$code] = $label;
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Retrieve countries method
     * /api/country/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/country/{locale}', 
        name: 'app_api_country', 
        requirements: ['local' => 'fr|en']
    )]
    public function country($locale): ?Response
    {        
        $data = [];
        \Locale::setDefault($locale);
        foreach (Countries::getNames() as $code => $label) {
            $data[$code] = $label;
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Retrieve objectives method
     * /api/language/{locale} with 'local' => 'fr|en'
     */
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

    /**
     * Retrieve domain method
     * Only french language
     * /api/language/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/domain/{locale}',
        name: 'app_api_domain',
        requirements: ['local' => 'fr|en']
    )]
    public function domain(ManagerRegistry $managerRegistry): ?Response
    {  
        $data = [];
        $domains = $managerRegistry->getRepository(Domain::class);
        foreach ($domains->findAll() as $domain) {
            $data[$domain->getId()] = $domain->getName();
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Retrieve establishment method
     * only french
     * /api/language/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/establishment',
        name: 'app_api_establishment'
    )]
    public function establishment(ManagerRegistry $managerRegistry): ?Response
    {  
        $data = [];
        $establishments = $managerRegistry->getRepository(Establishment::class);
        foreach ($establishments->findAll() as $establishment) {
            $data[$establishment->getId()] = $establishment->getName();
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Retrieve cities method
     * only french
     * /api/language/{locale} with 'local' => 'fr|en'
     */
    #[Route(
        '/api/cities',
        name: 'app_api_cities'
    )]
    public function cities(ManagerRegistry $managerRegistry): ?Response
    {  
        $data = [];
        $cities = $managerRegistry->getRepository(City::class);
        foreach ($cities->findAll() as $city) {
            $data[$city->getId()] = $city->getName();
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     *
     * POST DATA SAMPLE
     * -------------------------
     * birthdate: "2004-05-24"
     * city: "45234"
     * civility:"mr"
     * establishment: "16" // if null send 0
     * domains: "4,5,6"
     * email: "machine.coqqulon@clement.net"
     * firstname: "test"
     * languages: "fr,cn"
     * lastname: "test"
     * objectives: "admin-support,help-intership"
     * phone: "098765432"
     * porposalNumber: "2" // [1,2]
     * studyLevel: "3" // if null send 0
     * type: "sponsor" // [sponsor, student]
     * nationality: "FR"
     * 
     * All keys have to be in payload
     * 
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
        EstablishmentRepository $establishmentRepository,
        EntityManagerInterface $entityManager
    ): ?Response
    {  
        // Find person,
        $personClass = 'App\\Entity\\'.ucfirst($register->type);
        /** @var Student|Sponsor $person */
        $person = $personRepository->findOneBy(['email' => $register->email]) ?? new $personClass;

        if (get_class($person) != $personClass) {
            throw new \Exception('This email is already used by another person type');
        }

        // Find city
        $city = $cityRepository->find($register->city);

        // update / create person
        $person->setCivility(Civility::from($register->civility));
        $person->setFirstname($register->firstname);
        $person->setLastname($register->lastname);
        $person->setEmail($register->email);
        $person->setNationality($register->nationality);
        $person->setBirthdate(new DateTimeImmutable($register->birthdate));
        $person->setPhone($register->phone);
        $person->setCity($city);
        if ($register->studyLevel > 0 && $register->type === 'student') {
            $person->setStudyLevel($register->studyLevel);
        }
        $person->setState(PersonStatus::Active);

        if ($establishment = $establishmentRepository->find($register->establishment)) {
            $person->setEstablishment($establishment);
        }
        
        // Gender
        $genderMapping = ['mr' => 'male', 'mrs' => 'female'];
        $gender = Gender::from($genderMapping[$register->civility]);
        $person->setGender($gender);
        $entityManager->persist($person);

        // Lead
        $lead = $register->type === 'student' ? new Request() : new Proposal();
        $lead->setPerson($person);

        // Transform Objectives
        $objectives = array_map(function($value) use ($register) {
            return Objective::from($value);
        },
        explode(',', $register->objectives));
        $lead->setObjective($objectives);

        // Languages
        $lead->setLanguage(array_filter( explode(',', $register->languages)));

        // Domains
        $domains = explode(',', $register->domains);
        foreach ($domains as $domainId) {
            $lead->addDomain($domainRepository->find($domainId));
        }

        // Status
        $lead->setStatus('free');

        $entityManager->persist($lead);
        $entityManager->flush();

        if ($register->proposalNumber === 2 && $register->type === 'proposal') {
            $lead = clone $lead;
            $entityManager->persist($lead);
            $entityManager->flush();
        }

        return new JsonResponse('Created', Response::HTTP_OK);
    }


}
