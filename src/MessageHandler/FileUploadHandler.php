<?php

namespace App\MessageHandler;

use App\Command\SendUploadNotificationCommand;
use App\Entity\CiUploadId;
use App\Entity\UploadStatus;
use App\Message\FileUploadMessage;
use App\Service\DebrickedApiService;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\String_;
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
    private $entityManager;
    private $entityManagerCiUploadId;

    /**
     * Constructor to initialize the handler with required services.
     *
     * @param DebrickedApiService $debrickedApiService The service to upload files to Debricked.
     * @param SendUploadNotificationCommand $sendUploadNotificationCommand The command for sending notifications.
     */
    public function __construct(DebrickedApiService $debrickedApiService, SendUploadNotificationCommand $sendUploadNotificationCommand,EntityManagerInterface $entityManager)
    {
        $this->debrickedApiService = $debrickedApiService;
        $this->sendUploadNotificationCommand = $sendUploadNotificationCommand;
        $this->entityManager = $entityManager;
        $this->entityManagerCiUploadId = $entityManager;
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
        $totalNumberOfFiles = $message->getTotalNumberOfFiles();

        // Find or create UploadStatus
        $uploadStatus = $this->entityManager->getRepository(UploadStatus::class)
        ->findByRepositoryAndCommit($repositoryName, $commitName);

        if (!$uploadStatus) {
            $uploadStatus = new UploadStatus();
            $uploadStatus->setRepositoryName($repositoryName);
            $uploadStatus->setCommitName($commitName);
            $uploadStatus->setTotalFiles($totalNumberOfFiles); 
            $uploadStatus->setUploadedFiles(0); 
            $this->entityManager->persist($uploadStatus);
        }

        $response = $this->debrickedApiService->uploadFile(
            $filePath,
            $fileName,
            $repositoryName,
            $commitName
        );
        if($response['status']==200){
            $this->sendUploadNotificationCommand->run($input = new ArrayInput(["successFile"=>$fileName]), new BufferedOutput());
            $ciUploadId = $response['data']['ciUploadId'];
            
            $uploadStatus->setUploadedFiles($uploadStatus->getUploadedFiles() + 1);
            
            $CiUploadIdObj = new CiUploadId();
            $CiUploadIdObj->setRepositoryName($repositoryName);
            $CiUploadIdObj->setCommitName($commitName);
            $CiUploadIdObj->setCiUploadId($ciUploadId); 
            $CiUploadIdObj->setFilename($fileName); 
            $this->entityManagerCiUploadId->persist($CiUploadIdObj);
            $this->entityManagerCiUploadId->flush();
            
            if ($uploadStatus->getUploadedFiles() >= $uploadStatus->getTotalFiles()) {
                $uploadStatus->setCompleted(true);
                $this->debrickedApiService->finishFileUpload($ciUploadId);
                $this->sendUploadNotificationCommand->run($input = new ArrayInput(["fileUploadCompleteFlag"=>true]), new BufferedOutput());
                $ciUploadIds = $this->entityManagerCiUploadId->getRepository(CiUploadId::class)->findByRepositoryAndCommit($repositoryName, $commitName);
                foreach ($ciUploadIds as $varCiUploadId) {
                    $ciUploadIdValue = $varCiUploadId->getCiUploadId();
                    $filename = $varCiUploadId->getFilename();
                    sleep(3);
                    $this->handleScanResult($repositoryName,$commitName,$filename, $ciUploadIdValue);
                }
            }
        }else{
            $this->sendUploadNotificationCommand->run($input = new ArrayInput(["failedFile"=>$fileName]), new BufferedOutput());
        }
        $this->entityManager->flush();
        if ($response['status'] === 200) {
            // echo "File {$fileName} uploaded successfully!";
        } else {
            echo "Failed to upload file {$fileName}.";
        }
    }

    public function handleScanResult(String $repoName, String $commitName, String $fileName, String $ciUploadId){
        $ruleLinks = [];
        $response = $this->debrickedApiService->getUploadStatus($ciUploadId);
        if($response['status']==200){
            if(isset($response['data']['automationRules'])){
                $automationRules = $response['data']['automationRules'];
                foreach ($automationRules as $automationRule){
                    if($automationRule['ruleActions'][0]=='sendEmail'){
                        array_push($ruleLinks,$automationRule['ruleLink']);
                    }
                }
            }
            $this->sendUploadNotificationCommand->run($input = new ArrayInput(["ruleLinks"=>$ruleLinks]), new BufferedOutput());
        }
    }
}