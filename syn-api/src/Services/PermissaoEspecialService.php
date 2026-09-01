<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\PermissaoEspecialException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\PermissaoEspecialRepository;

final class PermissaoEspecialService
{
    public function __construct(
        private PermissaoEspecialRepository $repository
    ) {
    }

    public function listarCatalogo(): array
    {
        return $this->repository->listarPermissoes();
    }

    public function listarDoUsuario(int $usuarioId): array
    {
        $usuario = $this->obterUsuario($usuarioId);

        return [
            'usuario' => [
                'id' => (int) $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'status' => $usuario['status'],
                'papel' => $usuario['papel_codigo'],
            ],
            'permissoes' =>
                $this->repository->listarDoUsuario($usuarioId),
        ];
    }

    public function conceder(int $usuarioId, int $permissaoId): array
    {
        $usuario = $this->obterOrganizadorAtivo($usuarioId);
        $permissao = $this->repository->buscarPermissaoPorId($permissaoId);

        if ($permissao === null) {
            throw new DadosInvalidosException([
                'permissao_id' => 'A permissão informada não existe.',
            ]);
        }

        if (!(bool) $permissao['ativo']) {
            throw new DadosInvalidosException([
                'permissao_id' => 'A permissão informada está inativa.',
            ]);
        }

        $criada = $this->repository->conceder(
            (int) $usuario['id'],
            $permissaoId
        );

        return [
            'permissao_criada' => $criada,
            'dados' => $this->listarDoUsuario($usuarioId),
        ];
    }

    public function revogar(int $usuarioId, int $permissaoId): array
    {
        $this->obterUsuario($usuarioId);

        if ($this->repository->buscarPermissaoPorId($permissaoId) === null) {
            throw new DadosInvalidosException([
                'permissao_id' => 'A permissão informada não existe.',
            ]);
        }

        $removida = $this->repository->revogar(
            $usuarioId,
            $permissaoId
        );

        return [
            'permissao_removida' => $removida,
            'dados' => $this->listarDoUsuario($usuarioId),
        ];
    }

    public function exigirCodigo(array $auth, string $codigo): void
    {
        $papel = (string) ($auth['papel']['codigo'] ?? '');

        if ($papel === 'ADMINISTRADOR') {
            return;
        }

        if ($papel !== 'ORGANIZADOR') {
            throw new PermissaoEspecialException(
                'Você não possui permissão para acessar estas informações.'
            );
        }

        $usuarioId = (int) ($auth['id'] ?? 0);

        if (!$this->repository->usuarioPossuiCodigo($usuarioId, $codigo)) {
            throw new PermissaoEspecialException(
                'O Organizador não possui autorização para acessar necessidades específicas.'
            );
        }
    }

    private function obterUsuario(int $usuarioId): array
    {
        $usuario = $this->repository->buscarUsuarioComPapel($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException($usuarioId);
        }

        return $usuario;
    }

    private function obterOrganizadorAtivo(int $usuarioId): array
    {
        $usuario = $this->obterUsuario($usuarioId);

        if ($usuario['papel_codigo'] !== 'ORGANIZADOR') {
            throw new DadosInvalidosException([
                'usuario_id' =>
                    'Esta permissão especial só pode ser concedida a um usuário ORGANIZADOR.',
            ]);
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new DadosInvalidosException([
                'usuario_id' => 'O Organizador precisa estar ativo.',
            ]);
        }

        return $usuario;
    }
}
