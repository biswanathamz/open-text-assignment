<?php 

namespace App\Message;

class FileUploadMessage
{
    private string $filePath;
    private string $fileName;
    private string $repositoryName;
    private string $commitName;

    public function __construct(string $filePath, string $fileName, string $repositoryName, string $commitName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->repositoryName = $repositoryName;
        $this->commitName = $commitName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    public function getCommitName(): string
    {
        return $this->commitName;
    }
}