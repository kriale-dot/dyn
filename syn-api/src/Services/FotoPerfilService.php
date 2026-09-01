<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\FotoPerfilRepository;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Regras do upload da foto de perfil.
 *
 * Regras de segurança:
 *
 * - máximo de 5 MB;
 * - somente JPEG, PNG ou WEBP;
 * - valida o conteúdo real da imagem;
 * - limita dimensões;
 * - gera nome aleatório;
 * - nunca usa o nome enviado pelo usuário;
 * - só remove arquivos dentro de /uploads/perfis;
 * - substituição remove a foto antiga local.
 */
final class FotoPerfilService
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const MAX_LARGURA = 5000;
    private const MAX_ALTURA = 5000;
    private const MAX_PIXELS = 20_000_000;

    /**
     * @var array<string, string>
     */
    private const EXTENSOES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private FotoPerfilRepository $repository,
        private string $diretorioPublico
    ) {
        $this->diretorioPublico =
            rtrim(
                $this->diretorioPublico,
                DIRECTORY_SEPARATOR
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function salvar(
        int $usuarioId,
        UploadedFileInterface $arquivo
    ): array {
        $usuario =
            $this->obterUsuarioAtivo(
                $usuarioId
            );

        $this->validarErroUpload(
            $arquivo
        );

        $tamanho =
            $arquivo->getSize();

        if (
            $tamanho === null
            || $tamanho <= 0
        ) {
            throw new DadosInvalidosException([
                'foto' =>
                    'O arquivo enviado está vazio.',
            ]);
        }

        if ($tamanho > self::MAX_BYTES) {
            throw new DadosInvalidosException([
                'foto' =>
                    'A foto deve possuir no máximo 5 MB.',
            ]);
        }

        /**
         * Lemos o conteúdo para validar a imagem real.
         *
         * Não confiamos apenas na extensão ou no Content-Type
         * informado pelo navegador.
         */
        $stream =
            $arquivo->getStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $conteudo =
            $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $info =
            @getimagesizefromstring(
                $conteudo
            );

        if ($info === false) {
            throw new DadosInvalidosException([
                'foto' =>
                    'O arquivo enviado não é uma imagem válida.',
            ]);
        }

        $mime =
            (string) (
                $info['mime'] ?? ''
            );

        if (
            !array_key_exists(
                $mime,
                self::EXTENSOES
            )
        ) {
            throw new DadosInvalidosException([
                'foto' =>
                    'Formato não permitido. Use JPEG, PNG ou WEBP.',
            ]);
        }

        $largura =
            (int) ($info[0] ?? 0);

        $altura =
            (int) ($info[1] ?? 0);

        if (
            $largura <= 0
            || $altura <= 0
        ) {
            throw new DadosInvalidosException([
                'foto' =>
                    'Não foi possível determinar as dimensões da imagem.',
            ]);
        }

        if (
            $largura > self::MAX_LARGURA
            || $altura > self::MAX_ALTURA
            || ($largura * $altura)
                > self::MAX_PIXELS
        ) {
            throw new DadosInvalidosException([
                'foto' =>
                    'A imagem possui dimensões muito grandes. Use no máximo 5000x5000 pixels e até 20 milhões de pixels.',
            ]);
        }

        $extensao =
            self::EXTENSOES[$mime];

        $nomeArquivo =
            bin2hex(
                random_bytes(16)
            )
            . '.'
            . $extensao;

        $diretorioUsuario =
            $this->diretorioPublico
            . DIRECTORY_SEPARATOR
            . $usuarioId;

        $this->garantirDiretorio(
            $diretorioUsuario
        );

        $destino =
            $diretorioUsuario
            . DIRECTORY_SEPARATOR
            . $nomeArquivo;

        /**
         * moveTo() move o arquivo temporário enviado pelo PHP
         * para o diretório definitivo.
         */
        $arquivo->moveTo(
            $destino
        );

        $urlPublica =
            '/uploads/perfis/'
            . $usuarioId
            . '/'
            . $nomeArquivo;

        try {
            $this->repository
                ->atualizarFoto(
                    $usuarioId,
                    $urlPublica
                );
        } catch (\Throwable $e) {
            /**
             * Se o banco falhar, removemos o arquivo novo
             * para não deixar lixo no filesystem.
             */
            if (is_file($destino)) {
                @unlink($destino);
            }

            throw $e;
        }

        /**
         * Só apagamos a foto antiga DEPOIS que o banco
         * aceitou a nova referência.
         */
        $this->apagarArquivoLocalSeguro(
            $usuario['foto']
                ?? null
        );

        return [
            'usuario_id' =>
                $usuarioId,
            'foto' =>
                $urlPublica,
            'mime' =>
                $mime,
            'largura' =>
                $largura,
            'altura' =>
                $altura,
            'bytes' =>
                $tamanho,
        ];
    }

    /**
     * Remove a foto atual do próprio usuário.
     *
     * @return array<string, mixed>
     */
    public function remover(
        int $usuarioId
    ): array {
        $usuario =
            $this->obterUsuarioAtivo(
                $usuarioId
            );

        $fotoAnterior =
            $usuario['foto']
            ?? null;

        $this->repository
            ->atualizarFoto(
                $usuarioId,
                null
            );

        $arquivoRemovido =
            $this->apagarArquivoLocalSeguro(
                $fotoAnterior
            );

        return [
            'usuario_id' =>
                $usuarioId,
            'foto' =>
                null,
            'arquivo_local_removido' =>
                $arquivoRemovido,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function obterUsuarioAtivo(
        int $usuarioId
    ): array {
        $usuario =
            $this->repository
                ->buscarUsuarioPorId(
                    $usuarioId
                );

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        if (
            $usuario['status']
            !== 'ATIVO'
        ) {
            throw new DadosInvalidosException([
                'usuario' =>
                    'Usuário inativo não pode alterar a foto de perfil.',
            ]);
        }

        return $usuario;
    }

    private function validarErroUpload(
        UploadedFileInterface $arquivo
    ): void {
        if (
            $arquivo->getError()
            === UPLOAD_ERR_OK
        ) {
            return;
        }

        $mensagem = match (
            $arquivo->getError()
        ) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'O arquivo ultrapassa o tamanho permitido.',

            UPLOAD_ERR_PARTIAL =>
                'O upload foi recebido apenas parcialmente.',

            UPLOAD_ERR_NO_FILE =>
                'Nenhuma foto foi enviada.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'O servidor está sem diretório temporário para uploads.',

            UPLOAD_ERR_CANT_WRITE =>
                'O servidor não conseguiu gravar o arquivo temporário.',

            UPLOAD_ERR_EXTENSION =>
                'Uma extensão do PHP interrompeu o upload.',

            default =>
                'Não foi possível processar o upload.',
        };

        throw new DadosInvalidosException([
            'foto' => $mensagem,
        ]);
    }

    private function garantirDiretorio(
        string $diretorio
    ): void {
        if (is_dir($diretorio)) {
            return;
        }

        if (
            !mkdir(
                $diretorio,
                0755,
                true
            )
            && !is_dir($diretorio)
        ) {
            throw new \RuntimeException(
                'Não foi possível criar o diretório de fotos do perfil.'
            );
        }
    }

    /**
     * Só apaga caminhos que apontam para nossa própria pasta
     * pública /uploads/perfis.
     *
     * URLs externas e referências antigas desconhecidas nunca
     * são apagadas do filesystem.
     */
    private function apagarArquivoLocalSeguro(
        mixed $foto
    ): bool {
        if (
            !is_string($foto)
            || $foto === ''
        ) {
            return false;
        }

        $prefixo =
            '/uploads/perfis/';

        if (
            !str_starts_with(
                $foto,
                $prefixo
            )
        ) {
            return false;
        }

        $relativo =
            substr(
                $foto,
                strlen($prefixo)
            );

        if (
            $relativo === false
            || $relativo === ''
            || str_contains(
                $relativo,
                '..'
            )
        ) {
            return false;
        }

        $caminho =
            $this->diretorioPublico
            . DIRECTORY_SEPARATOR
            . str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $relativo
            );

        /**
         * Confirma que o arquivo real continua dentro
         * do diretório permitido.
         */
        $realDiretorio =
            realpath(
                $this->diretorioPublico
            );

        $realArquivo =
            realpath(
                $caminho
            );

        if (
            $realDiretorio === false
            || $realArquivo === false
        ) {
            return false;
        }

        $prefixoSeguro =
            rtrim(
                $realDiretorio,
                DIRECTORY_SEPARATOR
            )
            . DIRECTORY_SEPARATOR;

        if (
            !str_starts_with(
                $realArquivo,
                $prefixoSeguro
            )
        ) {
            return false;
        }

        if (!is_file($realArquivo)) {
            return false;
        }

        return @unlink(
            $realArquivo
        );
    }
}
