<?php

namespace App\Command;

use App\Repository\ThreadRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'PublishScheduledThreadsCommand',
    description: 'Add a short description for your command',
)]
class PublishScheduledThreadsCommand extends Command
{
    private $entityManager;
    private $threadRepository;

    public function __construct(EntityManagerInterface $entityManager, ThreadRepository $threadRepository)
    {
        $this->entityManager = $entityManager;
        $this->threadRepository = $threadRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:publish-scheduled-threads')
            ->setDescription('Publish scheduled threads');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Fetch scheduled threads whose scheduled publish time has been reached
        $scheduledThreads = $this->threadRepository->findScheduledThreadsToPublish();

        // Get the current month
        $currentMonth = (new DateTimeImmutable())->format('m');

        // Publish the scheduled threads (update their status or take other necessary actions)
        foreach ($scheduledThreads as $thread) {
            // Extract the month from the scheduled publish time
            $scheduledDate = $thread->getScheduledPublishTime();
            $scheduledMonth = (new DateTimeImmutable($scheduledDate))->format('m');

            // Check if the scheduled month is the same as the current month
            if ($scheduledMonth === $currentMonth) {
                // Implement publishing logic here
                // For example, update the status of the thread
                $thread->setPublished('yes');

                // Persist the changes to the database
                $this->threadRepository->save($thread);
            }
        }

        $output->writeln('Scheduled threads published successfully.');

        return Command::SUCCESS;
    }
}
