<?php
namespace App\Entity;

use App\Repository\CiUploadIdRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CiUploadIdRepository::class)]
class CiUploadId
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
    private string $ciUploadId;

    #[ORM\Column(type: 'string')]
    private string $filename;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    public function setRepositoryName(string $repositoryName): self
    {
        $this->repositoryName = $repositoryName;
        return $this;
    }

    public function getCommitName(): string
    {
        return $this->commitName;
    }

    public function setCommitName(string $commitName): self
    {
        $this->commitName = $commitName;
        return $this;
    }

    public function getCiUploadId(): int
    {
        return $this->ciUploadId;
    }

    public function setCiUploadId(int $ciUploadId): self
    {
        $this->ciUploadId = $ciUploadId;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }
    
}