<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use http\Exception\RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TransactionService
{
    const URL_AUTHORIZE = 'https://util.devi.tools/api/v2/authorize';
    const URL_EMAIL = 'https://util.devi.tools/api/v1/notify';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
    )
    {
    }

    public function initiateTransaction(array $data, User $user): void
    {
        if (!$this->hasBalance($data['payer'], $data['value'])) {
            throw new \Exception('Usuário sem saldo na conta para a transferência.');
        }

        if(!$this->checkTransferSystem()) {
            throw new \RuntimeException('Falha ao continuar com a operação.');
        }

        $this->notifyUserEmail($user->getEmail());
    }

    public function hasBalance($payer, $value): bool
    {
        $balance = current($this->walletRepository->findBy(['user' => $payer]));
        return $balance->getBalance() >= $value;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function checkTransferSystem(): bool
    {
        $client = HttpClient::create();

        $response = $client->request('GET', self::URL_AUTHORIZE);
        $responseDecoded = json_decode($response->getContent(), true);
        $data = $responseDecoded['data'];

        return $data['authorization'] === true;
    }
    private function notifyUserEmail(string $email)
    {
        $message = 'Transferência realizada com sucesso.';

        $client = HttpClient::create();

        $response = $client->request('POST', self::URL_EMAIL, [
            'body' => json_encode([
                'email' => $email,
                'message' => $message,
            ], JSON_THROW_ON_ERROR)
        ]);

        dd($response->getContent());
    }
}
