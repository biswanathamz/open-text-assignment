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



/**
 * Class SendUploadNotificationCommand
 * 
 * This command handles sending upload notifications via email.
 */
#[AsCommand(
    name: 'SendUploadNotificationCommand',
    description: 'Add a short description for your command',
)]
class SendUploadNotificationCommand extends Command
{
    protected NotificationService $notificationService;
    protected string $subject;
    protected string $message;
    protected string $successUploadMessage;
    protected string $failedUploadMessage;

    /**
     * SendUploadNotificationCommand constructor.
     *
     * @param NotificationService $notificationService The service used to send notifications.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->subject = "Backend-Home-Task (Check Dependency) : Notification";
        $this->message = "File Upload : Inprogress";
        $this->successUploadMessage = "File uploaded successfully ! File Name : ";
        $this->failedUploadMessage = "File uploaded failed ! File Name : ";

        parent::__construct(); 
    }

    /**
     * Configures the command options and arguments.
     */
    protected function configure(): void
    {
        $this
        ->addArgument('successFile', InputArgument::OPTIONAL, 'Your name')
        ->addArgument('failedFile', InputArgument::OPTIONAL, 'Your name');
    }

    /**
     * Executes the command to send upload notifications.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int Returns Command::SUCCESS on successful execution.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $successFile = $input->getArgument('successFile');
        $failedFile = $input->getArgument('failedFile');
        $message = "";
        
        if($successFile){
            $message = $this->successUploadMessage.$successFile;
        }elseif($failedFile){
            $message = $this->failedUploadMessage.$failedFile;
        }else{
            $message = $this->message;
        }

        $this->notificationService->sendEmail($this->subject, $message);

        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
