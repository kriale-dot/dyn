<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\IgrejaRepository;

/**
 * Service do cadastro institucional da igreja.
 *
 * Esta é a camada de regras de negócio.
 *
 * O Controller entrega os dados recebidos.
 * O Service valida e decide se eles podem ser gravados.
 * O Repository executa o SQL.
 */
final class IgrejaService
{
    public function __construct(
        private IgrejaRepository $igrejaRepository
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscar(): ?array
    {
        return $this->igrejaRepository->buscar();
    }

    /**
     * Atualiza o cadastro institucional e devolve os dados já
     * gravados no banco.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function atualizar(array $dados): array
    {
        /**
         * Primeiro validamos e normalizamos os dados.
         *
         * O Repository receberá somente dados já preparados
         * pelo Service.
         */
        $dadosValidados = $this->validarDados($dados);

        /**
         * O SYN deve possuir um cadastro institucional.
         *
         * Caso ele ainda não exista, não fazemos um UPDATE
         * silencioso que não afete nenhuma linha.
         */
        if ($this->igrejaRepository->buscar() === null) {
            throw new DadosInvalidosException(
                ['igreja' => 'O cadastro institucional ainda não existe.']
            );
        }

        $this->igrejaRepository->atualizar($dadosValidados);

        /**
         * Buscamos novamente para devolver exatamente o estado
         * persistido no banco, incluindo atualizado_em.
         */
        $igrejaAtualizada = $this->igrejaRepository->buscar();

        if ($igrejaAtualizada === null) {
            throw new DadosInvalidosException(
                ['igreja' => 'Não foi possível recuperar o cadastro atualizado.']
            );
        }

        return $igrejaAtualizada;
    }

    /**
     * Valida os campos aceitos por PUT /igreja.
     *
     * PUT representa a atualização completa do recurso:
     * - nome é obrigatório;
     * - campos opcionais ausentes serão armazenados como null.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarDados(array $dados): array
    {
        $erros = [];

        $nome = trim((string) ($dados['nome'] ?? ''));

        if ($nome === '') {
            $erros['nome'] = 'O nome da igreja é obrigatório.';
        } elseif (mb_strlen($nome) > 150) {
            $erros['nome'] = 'O nome da igreja deve possuir no máximo 150 caracteres.';
        }

        $email = $this->textoOpcional($dados['email'] ?? null);

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um endereço de e-mail válido.';
        }

        $estado = $this->textoOpcional($dados['estado'] ?? null);

        if ($estado !== null) {
            $estado = strtoupper($estado);

            if (!preg_match('/^[A-Z]{2}$/', $estado)) {
                $erros['estado'] = 'O estado deve possuir a sigla com 2 letras, por exemplo SP.';
            }
        }

        $site = $this->textoOpcional($dados['site'] ?? null);

        if ($site !== null && !filter_var($site, FILTER_VALIDATE_URL)) {
            $erros['site'] = 'Informe uma URL válida para o site.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException($erros);
        }

        return [
            'nome' => $nome,
            'logotipo' => $this->textoOpcional($dados['logotipo'] ?? null),
            'cep' => $this->textoOpcional($dados['cep'] ?? null),
            'logradouro' => $this->textoOpcional($dados['logradouro'] ?? null),
            'numero' => $this->textoOpcional($dados['numero'] ?? null),
            'complemento' => $this->textoOpcional($dados['complemento'] ?? null),
            'bairro' => $this->textoOpcional($dados['bairro'] ?? null),
            'cidade' => $this->textoOpcional($dados['cidade'] ?? null),
            'estado' => $estado,
            'telefone' => $this->textoOpcional($dados['telefone'] ?? null),
            'email' => $email,
            'site' => $site,
        ];
    }

    /**
     * Converte campos opcionais vazios em null.
     */
    private function textoOpcional(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}
