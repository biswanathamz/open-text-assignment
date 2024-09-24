<?php

namespace App\MessageHandler;

use App\Command\SendUploadNotificationCommand;
use App\Message\FileUploadMessage;
use App\Service\DebrickedApiService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Message handler for processing file uploads and notifying the user.
 */
#[AsMessageHandler]
class FileUploadHandler 
{
     /**
     * @var DebrickedApiService The service responsible for interacting with the Debricked API.
     */
    private $debrickedApiService;
    private SendUploadNotificationCommand $sendUploadNotificationCommand;

    /**
     * Constructor to initialize the handler with required services.
     *
     * @param DebrickedApiService $debrickedApiService The service to upload files to Debricked.
     * @param SendUploadNotificationCommand $sendUploadNotificationCommand The command for sending notifications.
     */
    public function __construct(DebrickedApiService $debrickedApiService, SendUploadNotificationCommand $sendUploadNotificationCommand)
    {
        $this->debrickedApiService = $debrickedApiService;
        $this->sendUploadNotificationCommand = $sendUploadNotificationCommand;
    }

    /**
     * Invokes the handler to process the file upload message.
     *
     * @param FileUploadMessage $message The message containing file upload details.
     * @return void
     */
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
            $this->sendUploadNotificationCommand->run($input = new ArrayInput(["successFile"=>$fileName]), new BufferedOutput());
            $ciUploadId = $response['data']['ciUploadId'];
            $response = $this->debrickedApiService->finishFileUpload($ciUploadId);
        }else{
            $this->sendUploadNotificationCommand->run($input = new ArrayInput(["failedFile"=>$fileName]), new BufferedOutput());
        }

        if ($response['status'] === 200) {
            echo "File {$fileName} uploaded successfully!";
        } else {
            echo "Failed to upload file {$fileName}.";
        }
    }
}