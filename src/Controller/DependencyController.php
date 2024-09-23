<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Command\SendUploadNotificationCommand;
use App\Message\FileUploadMessage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\MessageBusInterface;

class DependencyController extends AbstractController
{
    private SendUploadNotificationCommand $sendUploadNotificationCommand;
    private $messageBus;


    public function __construct(SendUploadNotificationCommand $sendUploadNotificationCommand, MessageBusInterface $messageBus)
    {
        $this->sendUploadNotificationCommand = $sendUploadNotificationCommand;
        $this->messageBus = $messageBus;
    }

    #[Route('/dependencyUploadFile', name: 'app_dependency')]
    public function index(Request $request): JsonResponse
    {
        $ciUploadIds = [];

        // Retrieve files from the request
        $uploadedFiles = $request->files->get('files');

        // Retrieve repositoryName and commitName from the request
        $repositoryName = $request->get('repositoryName');
        $commitName = $request->get('commitName');

        // Validation: Check if repositoryName and commitName are not null
        if (empty($repositoryName)) {
            return $this->json([
                "message" => 'repositoryName is required fields.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }elseif (empty($commitName)) {
            return $this->json([
                "message" => 'commitName is required fields.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validation: Check if there is at least one file uploaded
        if (empty($uploadedFiles)) {
            return $this->json([
                "message" => 'At least one file must be uploaded.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Check if multiple files were uploaded
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles]; 
        }

        // Validation: Check if all uploaded files are of .lock type
        foreach ($uploadedFiles as $uploadedFile) {
            if (pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION) !== 'lock') {
                return $this->json([
                    "message" => 'Only .lock files are allowed.',
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile->isValid()) {
                // Publish file details to RabbitMQ
                $message = new FileUploadMessage(
                    $uploadedFile->getRealPath(),
                    $uploadedFile->getClientOriginalName(),
                    $repositoryName,
                    $commitName
                );
                $this->messageBus->dispatch($message);
            }
        }
        
        $this->sendUploadNotificationCommand->run($input = new ArrayInput([]), new BufferedOutput());

        return $this->json([
            'message' => 'File Recieved! You will be notified shortly over the email and slack',
        ]);
    }
}
