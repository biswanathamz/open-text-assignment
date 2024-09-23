<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\NotificationService;


#[AsCommand(
    name: 'SendUploadNotificationCommand',
    description: 'Add a short description for your command',
)]
class SendUploadNotificationCommand extends Command
{
    protected NotificationService $notificationService;
    protected string $subject;
    protected string $message;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->subject = "Backend-Home-Task (Check Dependency) : Notification";
        $this->message = "File Upload : Inprogress";

        parent::__construct(); 
    }

    protected function configure(): void
    {
        // No params , if needed will be added here
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->notificationService->sendEmail($this->subject, $this->message);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
