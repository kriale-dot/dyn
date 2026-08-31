<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AutenticacaoException;
use App\Repositories\AuthRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

/**
 * Service responsável por login e validação de token.
 *
 * Nesta etapa adotamos JWT Bearer como decisão de implementação
 * para facilitar os testes no Postman.
 *
 * O documento exige autenticação e sessão segura, mas não fixa
 * obrigatoriamente um único mecanismo.
 */
final class AuthService
{
    public function __construct(
        private AuthRepository $repository,
        private string $jwtSecret,
        private int $jwtTtlSeconds = 3600
    ) {
        if (trim($this->jwtSecret) === '') {
            throw new \RuntimeException(
                'JWT_SECRET não foi configurado no arquivo .env.'
            );
        }

        if ($this->jwtTtlSeconds < 300) {
            throw new \RuntimeException(
                'JWT_TTL_SECONDS deve ser de pelo menos 300 segundos.'
            );
        }
    }

    /**
     * Valida e-mail/senha e devolve um token.
     *
     * @return array<string, mixed>
     */
    public function login(
        string $email,
        string $senha
    ): array {
        $email = mb_strtolower(trim($email));

        if ($email === '' || $senha === '') {
            throw new AutenticacaoException(
                'E-mail e senha são obrigatórios.'
            );
        }

        $usuario = $this->repository
            ->buscarPorEmail($email);

        /**
         * Usamos a mesma mensagem para usuário inexistente e senha
         * incorreta, evitando facilitar enumeração de contas.
         */
        if (
            $usuario === null
            || !password_verify(
                $senha,
                $usuario['senha_hash']
            )
        ) {
            throw new AutenticacaoException(
                'E-mail ou senha inválidos.'
            );
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new AutenticacaoException(
                'Este usuário está inativo e não pode acessar o sistema.'
            );
        }

        $agora = time();
        $expiraEm = $agora + $this->jwtTtlSeconds;

        /**
         * O papel NÃO é usado como fonte de verdade dentro do token.
         *
         * Guardamos apenas o ID. Em cada requisição o Middleware
         * recarrega usuário/status/papel do banco, garantindo que
         * alterações administrativas tenham efeito imediatamente.
         */
        $payload = [
            'iss' => 'syn-api',
            'iat' => $agora,
            'nbf' => $agora,
            'exp' => $expiraEm,
            'sub' => (string) $usuario['id'],
        ];

        $token = JWT::encode(
            $payload,
            $this->jwtSecret,
            'HS256'
        );

        $this->repository->registrarUltimoLogin(
            (int) $usuario['id']
        );

        return [
            'token' => $token,
            'token_tipo' => 'Bearer',
            'expira_em' => date(
                DATE_ATOM,
                $expiraEm
            ),
            'expira_em_segundos' =>
                $this->jwtTtlSeconds,
            'usuario' =>
                $this->formatarUsuario($usuario),
        ];
    }

    /**
     * Valida o token e recarrega o usuário atual do banco.
     *
     * @return array<string, mixed>
     */
    public function autenticarToken(
        string $token
    ): array {
        try {
            $payload = JWT::decode(
                $token,
                new Key(
                    $this->jwtSecret,
                    'HS256'
                )
            );
        } catch (Throwable) {
            throw new AutenticacaoException(
                'Token inválido ou expirado.'
            );
        }

        $sub = $payload->sub ?? null;

        if (
            !is_string($sub)
            || !ctype_digit($sub)
            || (int) $sub < 1
        ) {
            throw new AutenticacaoException(
                'Token de autenticação inválido.'
            );
        }

        $usuario =
            $this->repository
                ->buscarPorId((int) $sub);

        if ($usuario === null) {
            throw new AutenticacaoException(
                'Usuário autenticado não foi encontrado.'
            );
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new AutenticacaoException(
                'Este usuário está inativo e não pode acessar o sistema.'
            );
        }

        return $this->formatarUsuario(
            $usuario
        );
    }

    /**
     * @param array<string, mixed> $usuario
     * @return array<string, mixed>
     */
    private function formatarUsuario(
        array $usuario
    ): array {
        return [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'foto' => $usuario['foto'],
            'status' => $usuario['status'],
            'papel' => [
                'id' =>
                    (int) $usuario['papel_id'],
                'codigo' =>
                    $usuario['papel_codigo'],
                'nome' =>
                    $usuario['papel_nome'],
            ],
            'ultimo_login_em' =>
                $usuario['ultimo_login_em'],
        ];
    }
}
