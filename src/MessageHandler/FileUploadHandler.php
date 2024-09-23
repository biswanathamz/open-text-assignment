<?php

namespace App\MessageHandler;

use App\Command\SendUploadNotificationCommand;
use App\Message\FileUploadMessage;
use App\Service\DebrickedApiService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FileUploadHandler implements MessageHandlerInterface
{
    private $debrickedApiService;
    private SendUploadNotificationCommand $sendUploadNotificationCommand;


    public function __construct(DebrickedApiService $debrickedApiService, SendUploadNotificationCommand $sendUploadNotificationCommand)
    {
        $this->debrickedApiService = $debrickedApiService;
        $this->sendUploadNotificationCommand = $sendUploadNotificationCommand;
    }

    public function __invoke(FileUploadMessage $message)
    {
        $filePath = $message->getFilePath();
        $fileName = $message->getFileName();
        $repositoryName = $message->getRepositoryName();
        $commitName = $message->getCommitName();

        $response = $this->debrickedApiService->uploadFile(
            $filePath,
            $fileName,
            $repositoryName,
            $commitName
        );

        if($response['status']==200){
            $this->sendUploadNotificationCommand->run($input = new ArrayInput([]), new BufferedOutput());
        }else{
            $this->sendUploadNotificationCommand->run($input = new ArrayInput([]), new BufferedOutput());
        }

        if ($response['status'] === 200) {
            echo "File {$fileName} uploaded successfully!";
        } else {
            echo "Failed to upload file {$fileName}.";
        }
    }
}