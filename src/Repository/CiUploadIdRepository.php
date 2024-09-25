<?php 
namespace App\Repository;

use App\Entity\CiUploadId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for handling `CiUploadId` entity operations.
 */
class CiUploadIdRepository extends ServiceEntityRepository
{
    /**
     * Constructor to initialize the repository with the given ManagerRegistry.
     *
     * @param ManagerRegistry $registry The registry interface for entity management.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CiUploadId::class);
    }

    /**
     * Finds an `CiUploadId` entity by the given repository name and commit name.
     *
     * @param string $repositoryName The name of the repository.
     * @param string $commitName The name of the commit.
     * @return CiUploadId|null Returns the `CiUploadId` entity or null if not found.
     */
    public function findByRepositoryAndCommit($repositoryName, $commitName): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.repositoryName = :repositoryName')
            ->setParameter('repositoryName', $repositoryName)
            ->andWhere('c.commitName = :commitName')
            ->setParameter('commitName', $commitName)
            ->getQuery()
            ->getResult();
    }
}
