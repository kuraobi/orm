<?php

declare(strict_types=1);

namespace Doctrine\Tests_PHP74\ORM\Functional\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group GH7950
 */
class GH7950Test extends OrmFunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->_schemaTool->createSchema(
            [
                $this->_em->getClassMetadata(GH7950TestEntity::class),
                $this->_em->getClassMetadata(GH7950OneToManyChild::class),
                $this->_em->getClassMetadata(GH7950ManyToChild::class),
            ]
        );
    }

    public function testTypedPropertyContainingEmbeddable(): void
    {
        $entity = new GH7950TestEntity(new GH7950ManyToChild());
        $this->_em->persist($entity);
        $this->_em->flush();
        $this->_em->clear();

        /** @var GH7950TestEntity[] $entities */
        $entities = $this->_em->getRepository(GH7950TestEntity::class)->findAll();

        self::assertEquals($entity, $entities[0]);
    }
}

/** @Entity */
class GH7950TestEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ManyToOne(targetEntity="GH7950ManyToChild", cascade={"persist"}, fetch="EAGER")
     */
    private GH7950ManyToChild $manyToOneChild;

    /**
     * @var Collection<GH7950OneToManyChild>
     * @OneToMany(targetEntity="GH7950OneToManyChild", mappedBy="parent", cascade={"persist"})
     */
    private Collection $oneToManyChildren;

    /**
     * @var Collection<GH7950ManyToChild>
     * @ManyToMany(targetEntity="GH7950ManyToChild", cascade={"persist"})
     * @JoinTable(name="manytomany",
     *      joinColumns={@JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="child_id", referencedColumnName="id")}
     *      )
     */
    private Collection $manyToManyChildren;

    public function __construct(GH7950ManyToChild $manyToOneChild)
    {
        $this->manyToOneChild = $manyToOneChild;
        $this->oneToManyChildren = new ArrayCollection();
        $this->manyToManyChildren = new ArrayCollection();
    }
}

/**
 * @Entity()
 */
class GH7950OneToManyChild
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ManyToOne(targetEntity="GH7950TestEntity", inversedBy="oneToManyChildren")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private GH7950TestEntity $parent;

    public function __construct(GH7950TestEntity $parent)
    {
        $this->parent = $parent;
    }
}

/** * @Entity */
class GH7950ManyToChild
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private ?int $id = null;
}
