<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Documents\Account;
use Documents\Address;
use Documents\Developer;
use Documents\Group;
use Documents\Phonenumber;
use Documents\SubProject;
use Documents\User;
use MongoDB\BSON\ObjectId;
use const DOCTRINE_MONGODB_DATABASE;

class DocumentRepositoryTest extends BaseTest
{
    public function testMatchingAcceptsCriteriaWithNullWhereExpression()
    {
        $repository = $this->dm->getRepository('Documents\User');
        $criteria = new Criteria();

        $this->assertNull($criteria->getWhereExpression());
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $repository->matching($criteria));
    }

    public function testFindByRefOneFull()
    {
        $user = new User();
        $account = new Account('name');
        $user->setAccount($account);
        $this->dm->persist($user);
        $this->dm->persist($account);
        $this->dm->flush();

        $query = $this->dm
            ->getUnitOfWork()
            ->getDocumentPersister(User::class)
            ->prepareQueryOrNewObj(['account' => $account]);
        $expectedQuery = ['account.$id' => new ObjectId($account->getId())];
        $this->assertEquals($expectedQuery, $query);

        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['account' => $account]));
    }

    public function testFindByRefOneWithoutTargetDocumentFull()
    {
        $user = new User();
        $account = new Account('name');
        $user->setAccount($account);
        $this->dm->persist($user);
        $this->dm->persist($account);
        $this->dm->flush();

        $query = $this->dm
            ->getUnitOfWork()
            ->getDocumentPersister(Account::class)
            ->prepareQueryOrNewObj(['user' => $user]);
        $expectedQuery = [
            'user.$ref' => 'users',
            'user.$id' => new ObjectId($user->getId()),
            'user.$db' => DOCTRINE_MONGODB_DATABASE,
        ];
        $this->assertEquals($expectedQuery, $query);

        $this->assertSame($account, $this->dm->getRepository(Account::class)->findOneBy(['user' => $user]));
    }

    public function testFindByRefOneWithoutTargetDocumentStoredAsDbRef()
    {
        $user = new User();
        $account = new Account('name');
        $account->setUserDbRef($user);
        $this->dm->persist($user);
        $this->dm->persist($account);
        $this->dm->flush();

        $query = $this->dm
            ->getUnitOfWork()
            ->getDocumentPersister(Account::class)
            ->prepareQueryOrNewObj(['userDbRef' => $user]);
        $expectedQuery = [
            'userDbRef.$ref' => 'users',
            'userDbRef.$id' => new ObjectId($user->getId()),
        ];
        $this->assertEquals($expectedQuery, $query);

        $this->assertSame($account, $this->dm->getRepository(Account::class)->findOneBy(['userDbRef' => $user]));
    }

    public function testFindDiscriminatedByRefManyFull()
    {
        $project = new SubProject('mongodb-odm');
        $developer = new Developer('alcaeus', new ArrayCollection([$project]));
        $this->dm->persist($project);
        $this->dm->persist($developer);
        $this->dm->flush();

        $query = $this->dm
            ->getUnitOfWork()
            ->getDocumentPersister(Developer::class)
            ->prepareQueryOrNewObj(['projects' => $project]);
        $expectedQuery = ['projects' => ['$elemMatch' => ['$id' => new ObjectId($project->getId())]]];
        $this->assertEquals($expectedQuery, $query);

        $this->assertSame($developer, $this->dm->getRepository(Developer::class)->findOneBy(['projects' => $project]));
    }

    public function testFindByRefOneSimple()
    {
        $user = new User();
        $account = new Account('name');
        $user->setAccountSimple($account);
        $this->dm->persist($user);
        $this->dm->persist($account);
        $this->dm->flush();
        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['accountSimple' => $account]));
    }

    public function testFindByEmbedOne()
    {
        $user = new User();
        $address = new Address();
        $address->setCity('Cracow');
        $user->setAddress($address);
        $this->dm->persist($user);
        $this->dm->flush();
        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['address' => $address]));
    }

    public function testFindByRefManyFull()
    {
        $user = new User();
        $group = new Group('group');
        $user->addGroup($group);
        $this->dm->persist($user);
        $this->dm->persist($group);
        $this->dm->flush();

        $query = $this->dm
            ->getUnitOfWork()
            ->getDocumentPersister(User::class)
            ->prepareQueryOrNewObj(['groups' => $group]);

        $expectedQuery = [
            'groups' => [
                '$elemMatch' => [
                    '$id' => new ObjectId($group->getId()),
                ],
            ],
        ];
        $this->assertEquals($expectedQuery, $query);

        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['groups' => $group]));
    }

    public function testFindByRefManySimple()
    {
        $user = new User();
        $group = new Group('group');
        $user->addGroupSimple($group);
        $this->dm->persist($user);
        $this->dm->persist($group);
        $this->dm->flush();
        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['groupsSimple' => $group]));
    }

    public function testFindByEmbedMany()
    {
        $user = new User();
        $phonenumber = new Phonenumber('12345678');
        $user->addPhonenumber($phonenumber);
        $this->dm->persist($user);
        $this->dm->flush();
        $this->assertSame($user, $this->dm->getRepository(User::class)->findOneBy(['phonenumbers' => $phonenumber]));
    }
}
