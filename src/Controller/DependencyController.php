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

/**
 * Class DependencyController
 * 
 * This controller handles the file upload for dependency management.
 * It validates the uploaded files and dispatches messages for further processing.
 */
class DependencyController extends AbstractController
{
    private SendUploadNotificationCommand $sendUploadNotificationCommand;
    private $messageBus;


    /**
     * DependencyController constructor.
     *
     * @param SendUploadNotificationCommand $sendUploadNotificationCommand Command to send upload notifications.
     * @param MessageBusInterface $messageBus The message bus for dispatching messages.
     */
    public function __construct(SendUploadNotificationCommand $sendUploadNotificationCommand, MessageBusInterface $messageBus)
    {
        $this->sendUploadNotificationCommand = $sendUploadNotificationCommand;
        $this->messageBus = $messageBus;
    }

    /**
     * Handles the file upload request.
     *
     * @param Request $request The HTTP request object containing the uploaded files and parameters.
     *
     * @return JsonResponse A JSON response indicating the status of the upload operation.
     */
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

        $totalFiles = count($uploadedFiles);

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
                    $commitName,
                    $totalFiles
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
