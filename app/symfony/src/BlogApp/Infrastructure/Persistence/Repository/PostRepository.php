<?php

namespace App\BlogApp\Infrastructure\Persistence\Repository;

use App\BlogApp\Domain\Entity\Post;
use App\BlogApp\Domain\PostRepositoryInterface;
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
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
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

    public function findByValue(null|string $value, null|string $name, int $pageSize, int $currentPage): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if ( ! is_null($value)) {
            $queryBuilder
                ->where(sprintf('p.%1$s = :%1$s', $name))
                ->setParameter($name, $value);
        }

        $queryBuilder->orderBy('p.date', 'DESC');;

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
