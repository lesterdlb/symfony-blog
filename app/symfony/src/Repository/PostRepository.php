<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Post $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Post $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByStatus(string $status, int $pageSize, int $currentPage): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('p')
                             ->where('p.status = :status')
                             ->setParameter('status', $status);

        return $this->createPagerfanta($queryBuilder, $pageSize, $currentPage);
    }

    public function findByUserId(string $userId, int $pageSize, int $currentPage): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('p')
                             ->where('p.user = :userId')
                             ->setParameter('userId', $userId);

        return $this->createPagerfanta($queryBuilder, $pageSize, $currentPage);
    }

    private function createPagerfanta(QueryBuilder $queryBuilder, int $pageSize, int $currentPage): Pagerfanta
    {
        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));

        $pagerfanta->setMaxPerPage($pageSize);
        $pagerfanta->setCurrentPage($currentPage);

        return $pagerfanta;
    }
}
