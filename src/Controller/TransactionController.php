<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\TransactionService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly UserRepository $userRepository,
    )
    {
    }

    #[Route('/transfer', name: 'app_transaction', methods: ['POST'])]
    public function index(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = current($this->userRepository->findBy(['id' => $data['payer']]));

        $this->transactionService->initiateTransaction($data, $user);
    }
}
