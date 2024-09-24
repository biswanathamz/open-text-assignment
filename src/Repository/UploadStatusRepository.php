<?php 
namespace App\Repository;

use App\Entity\UploadStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for handling `UploadStatus` entity operations.
 */
class UploadStatusRepository extends ServiceEntityRepository
{
    /**
     * Constructor to initialize the repository with the given ManagerRegistry.
     *
     * @param ManagerRegistry $registry The registry interface for entity management.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadStatus::class);
    }

    /**
     * Finds an `UploadStatus` entity by the given repository name and commit name.
     *
     * @param string $repositoryName The name of the repository.
     * @param string $commitName The name of the commit.
     * @return UploadStatus|null Returns the `UploadStatus` entity or null if not found.
     */
    public function findByRepositoryAndCommit($repositoryName, $commitName)
    {
        return $this->findOneBy([
            'repositoryName' => $repositoryName,
            'commitName' => $commitName,
        ]);
    }
}
