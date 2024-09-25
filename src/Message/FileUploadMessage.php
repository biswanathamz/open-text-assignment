<?php 

namespace App\Message;

/**
 * Class FileUploadMessage
 *
 * This class represents a message containing information about a file upload.
 */
class FileUploadMessage
{
    private string $filePath;
    private string $fileName;
    private string $repositoryName;
    private string $commitName;
    private int $totalNumberOfFiles;

    /**
     * FileUploadMessage constructor.
     *
     * @param string $filePath The path of the uploaded file.
     * @param string $fileName The name of the uploaded file.
     * @param string $repositoryName The name of the repository associated with the upload.
     * @param string $commitName The name of the commit associated with the upload.
     */
    public function __construct(string $filePath, string $fileName, string $repositoryName, string $commitName, int $totalNumberOfFiles)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->repositoryName = $repositoryName;
        $this->commitName = $commitName;
        $this->totalNumberOfFiles = $totalNumberOfFiles;
    }

    /**
     * Get the file path.
     *
     * @return string The path of the uploaded file.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Get the file name.
     *
     * @return string The name of the uploaded file.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get the repository name.
     *
     * @return string The name of the repository associated with the upload.
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    /**
     * Get the commit name.
     *
     * @return string The name of the commit associated with the upload.
     */
    public function getCommitName(): string
    {
        return $this->commitName;
    }

    /**
     * Get the Total Number of files.
     *
     * @return string The name of the commit associated with the upload.
     */
    public function getTotalNumberOfFiles(): int
    {
        return $this->totalNumberOfFiles;
    }
}