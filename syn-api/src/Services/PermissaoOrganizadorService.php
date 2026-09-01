<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\PermissaoOrganizadorException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\PermissaoOrganizadorRepository;

final class PermissaoOrganizadorService
{
    public function __construct(
        private PermissaoOrganizadorRepository $repository
    ) {
    }

    public function listar(int $usuarioId): array
    {
        $usuario = $this->obterOrganizador($usuarioId);

        return [
            'organizador' => [
                'id' => (int) $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'status' => $usuario['status'],
            ],
            'tipos_programacao' =>
                $this->repository->listarTiposPermitidos($usuarioId),
        ];
    }

    public function minhasPermissoes(array $auth): array
    {
        $papel = (string) ($auth['papel']['codigo'] ?? '');

        if ($papel === 'ADMINISTRADOR') {
            return [
                'papel' => 'ADMINISTRADOR',
                'acesso_total' => true,
                'tipos_programacao' => [],
            ];
        }

        if ($papel !== 'ORGANIZADOR') {
            return [
                'papel' => $papel,
                'acesso_total' => false,
                'tipos_programacao' => [],
            ];
        }

        $usuarioId = (int) ($auth['id'] ?? 0);

        return [
            'papel' => 'ORGANIZADOR',
            'acesso_total' => false,
            'tipos_programacao' =>
                $this->repository->listarTiposPermitidos($usuarioId),
        ];
    }

    public function conceder(int $usuarioId, int $tipoId): array
    {
        $this->obterOrganizador($usuarioId);
        $tipo = $this->repository->buscarTipo($tipoId);

        if ($tipo === null) {
            throw new DadosInvalidosException([
                'tipo_programacao_id' =>
                    'O tipo de programação informado não existe.',
            ]);
        }

        if (!(bool) $tipo['ativo']) {
            throw new DadosInvalidosException([
                'tipo_programacao_id' =>
                    'Não é possível conceder permissão para um tipo inativo.',
            ]);
        }

        return [
            'permissao_criada' =>
                $this->repository->conceder($usuarioId, $tipoId),
            'dados' => $this->listar($usuarioId),
        ];
    }

    public function revogar(int $usuarioId, int $tipoId): array
    {
        $this->obterOrganizador($usuarioId);

        if ($this->repository->buscarTipo($tipoId) === null) {
            throw new DadosInvalidosException([
                'tipo_programacao_id' =>
                    'O tipo de programação informado não existe.',
            ]);
        }

        return [
            'permissao_removida' =>
                $this->repository->revogar($usuarioId, $tipoId),
            'dados' => $this->listar($usuarioId),
        ];
    }

    public function exigirTipo(array $auth, int $tipoId): void
    {
        $papel = (string) ($auth['papel']['codigo'] ?? '');

        if ($papel === 'ADMINISTRADOR') {
            return;
        }

        if ($papel !== 'ORGANIZADOR') {
            throw new PermissaoOrganizadorException(
                'Esta operação exige acesso administrativo.'
            );
        }

        $usuarioId = (int) ($auth['id'] ?? 0);

        if (!$this->repository->possuiPermissao($usuarioId, $tipoId)) {
            throw new PermissaoOrganizadorException(
                'O Organizador não possui permissão para administrar este tipo de programação.'
            );
        }
    }

    public function tipoIdDaProgramacao(int $id): ?int
    {
        return $this->repository->tipoIdDaProgramacao($id);
    }

    public function tipoIdDaParticipacao(int $id): ?int
    {
        return $this->repository->tipoIdDaParticipacao($id);
    }

    public function tipoIdDaSerie(int $id): ?int
    {
        return $this->repository->tipoIdDaSerie($id);
    }

    private function obterOrganizador(int $usuarioId): array
    {
        $usuario = $this->repository->buscarUsuarioComPapel($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException($usuarioId);
        }

        if ($usuario['papel_codigo'] !== 'ORGANIZADOR') {
            throw new DadosInvalidosException([
                'usuario_id' =>
                    'Somente usuários com papel ORGANIZADOR recebem este tipo de permissão.',
            ]);
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new DadosInvalidosException([
                'usuario_id' =>
                    'O Organizador precisa estar ativo.',
            ]);
        }

        return $usuario;
    }
}
