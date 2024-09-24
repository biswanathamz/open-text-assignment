<?php
namespace App\Entity;

use App\Repository\UploadStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadStatusRepository::class)]
class UploadStatus
{
    #[ORM\Id] // Primary key
    #[ORM\GeneratedValue] // Auto-increment
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // Make sure to initialize to null

    #[ORM\Column(type: 'string')]
    private string $repositoryName;

    #[ORM\Column(type: 'string')]
    private string $commitName;

    #[ORM\Column(type: 'integer')]
    private int $totalFiles;

    #[ORM\Column(type: 'integer')]
    private int $uploadedFiles = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;
    
    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepositoryName(): ?string
    {
        return $this->repositoryName;
    }

    public function setRepositoryName(string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;

        return $this;
    }

    public function getCommitName(): ?string
    {
        return $this->commitName;
    }

    public function setCommitName(string $commitName): self
    {
        $this->commitName = $commitName;

        return $this;
    }

    public function getTotalFiles(): ?int
    {
        return $this->totalFiles;
    }

    public function setTotalFiles(int $totalFiles): self
    {
        $this->totalFiles = $totalFiles;

        return $this;
    }

    public function getUploadedFiles(): int
    {
        return $this->uploadedFiles;
    }

    public function setUploadedFiles(int $uploadedFiles): self
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }
}