<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function createUser($data)
    {
        $user = new User();
        $user->setNome($data['name']);
        $user->setEmail($data['email']);
        $user->setCpfCnpj($this->formatarCpfCnpj($data['cpf_cnpj']));
        $user->setPassword($data['password']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function createWallet($user)
    {
        $wallet = new Wallet();
        $wallet->setUser($user);
        $wallet->setBalance(200.0);

        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }

    public function formatarCpfCnpj($cpfCnpj): string
    {
        return preg_replace('/\D/', '', $cpfCnpj);
    }
}
