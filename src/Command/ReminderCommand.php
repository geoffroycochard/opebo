<?php

namespace App\Command;

use App\Entity\Sponsor;
use App\Entity\Student;
use App\Repository\PersonRepository;
use App\Repository\SponsorshipRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[AsCommand(
    name: 'app:reminder',
    description: 'Launch email reminder',
)]
class ReminderCommand extends Command
{
    public function __construct(
        private SponsorshipRepository $sponsorshipRepository,
        private PersonRepository $personRepository,
        private LoginLinkHandlerInterface $loginLinkHandler,
        private readonly MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private ActivityLogger $activityLogger,
        #[Autowire('%admin_email%')] private string $adminEmail,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $qb = $this->sponsorshipRepository->createQueryBuilder('s');
        $qb
            ->where('s.reminder < :date')
            ->andWhere('s.status = :status')
            ->setParameter('status', 'in_progress')
            ->setParameter('date', new \DateTime())
        ;
        $results = $qb->getQuery()->getResult();
        if ($results) {
            foreach ($results as $sponsorship) {

               /** @var Student $student */
               $student = $sponsorship->getRequest()->getPerson();
               /** @var Sponsor $sponsor */
               $sponsor = $sponsorship->getProposal()->getPerson();
       
               // Send poposal to sponsor
               $user = $this->personRepository->findOneBy(['email' => $sponsor->getEmail()]);
               $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
               $loginLink = $loginLinkDetails->getUrl();
               $email = (new TemplatedEmail())
                   ->from($this->adminEmail)
                   ->to(new Address($sponsor->getEmail()))
                   ->cc(new Address($this->adminEmail))
                   ->subject('Suivi de votre parrainage')
                   ->htmlTemplate('emails/reminder/sponsor.html.twig')
                   ->context([
                       'student' => $student,
                       'sponsor' => $sponsor,
                       'sponsorship' => $sponsorship,
                       'login_link' => $loginLink
                   ])
               ;
               try {
                   $this->mailer->send($email);
                   $this->activityLogger->logEmailSuccess($sponsorship, 'reminder_sponsor', 'Email de rappel de parrainage vers le parrain.');
               } catch (TransportExceptionInterface $e) {
                   $this->activityLogger->logEmailFailed($sponsorship, 'reminder_sponsor', 'Email de rappel de parrainage vers le parrain. ('.$e->getMessage().')');
               }
               
               
               // Send poposal to student
               $user = $this->personRepository->findOneBy(['email' => $student->getEmail()]);
               $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
               $loginLink = $loginLinkDetails->getUrl();
               $email = (new TemplatedEmail())
               ->from($this->adminEmail)
                   ->to(new Address($student->getEmail()))
                   ->cc(new Address($this->adminEmail))
                   ->subject('Suivi de votre accompagnement')
                   ->htmlTemplate('emails/reminder/student.html.twig')
                   ->context([
                       'student' => $student,
                       'sponsor' => $sponsor,
                       'sponsorship' => $sponsorship,
                       'login_link' => $loginLink
                   ])
               ;
               try {
                   $this->mailer->send($email);
                   $this->activityLogger->logEmailSuccess($sponsorship, 'reminder_student', 'Email de rappel de parrainage vers l\'étudiant.');
               } catch (TransportExceptionInterface $e) {
                   $this->activityLogger->logEmailFailed($sponsorship, 'reminder_student', 'Email de rappel de parrainage vers l\'étudiant. ('.$e->getMessage().')');
               }
            }
            $sponsorship->setReminder((new \DateTime())->modify('+2 months'));
            $this->entityManager->persist($sponsorship);
            $this->entityManager->flush();
        }   

        $io->success('All emails was sent.');

        return Command::SUCCESS;
    }
}
