<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\NotificationService;


class DependencyController extends AbstractController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/dependencyUploadFile', name: 'app_dependency')]
    public function index(Request $request): JsonResponse
    {
        $uploadedFiles = $request->files->get('files');
        if (empty($uploadedFiles)) {
            return $this->json([
                "message" => 'No files uploaded.',
            ]);
        }
        // foreach ($uploadedFiles as $uploadedFile) {
        //     if ($uploadedFile instanceof UploadedFile) {
        //         $this->bus->dispatch(new ProcessFileMessage($uploadedFile->getPathname()));
        //     }
        // }
        $this->notificationService->notifyUser("dbfh","biswanathamz@gmail.com","sfbjdvffdv");
        return $this->json([
            'message' => 'File Recieved! You will be notified shortly over the email and slack',
        ]);
    }
}
