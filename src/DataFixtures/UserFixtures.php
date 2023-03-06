<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public $userPasswordHasherInterface;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->UserData() as [$name, $last_name, $email, $password, $api_key, $roles]) {
            $user = new User();

            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->userPasswordHasherInterface->hashPassword($user, $password));
            $user->setVimeoApiKey($api_key);
            $user->setRoles($roles);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function UserData()
    {
        return [

            ['felix', 'fedronic', 'felix.fedronic@orange.fr', 'password', 'hjd8dehdh', ['ROLE_ADMIN']],
            ['inès', 'fedronic', 'ines.fedronic@orange.fr', 'password', null, ['ROLE_ADMIN']],
            ['alain', 'fedronic', 'alain.fedronic@orange.fr', 'password', null, ['ROLE_USER']],
        ];
    }
}
