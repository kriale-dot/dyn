<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\LogotipoIgrejaRepository;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Regras de upload do logotipo institucional.
 *
 * Segurança:
 * - JPEG, PNG ou WEBP;
 * - máximo de 5 MB;
 * - valida o conteúdo real da imagem;
 * - não confia no nome/extensão do cliente;
 * - nome aleatório;
 * - limita dimensões;
 * - remove o arquivo antigo somente após o banco aceitar o novo;
 * - só apaga arquivos dentro do diretório permitido.
 */
final class LogotipoIgrejaService
{
    private const MAX_BYTES =
        5 * 1024 * 1024;

    private const MAX_LARGURA =
        5000;

    private const MAX_ALTURA =
        5000;

    private const MAX_PIXELS =
        20_000_000;

    /**
     * @var array<string, string>
     */
    private const EXTENSOES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private LogotipoIgrejaRepository $repository,
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
        UploadedFileInterface $arquivo
    ): array {
        $igreja =
            $this->repository
                ->buscarIgreja();

        if ($igreja === null) {
            throw new DadosInvalidosException([
                'igreja' =>
                    'Cadastre os dados institucionais da igreja antes de enviar o logotipo.',
            ]);
        }

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
                'logotipo' =>
                    'O arquivo enviado está vazio.',
            ]);
        }

        if (
            $tamanho
            > self::MAX_BYTES
        ) {
            throw new DadosInvalidosException([
                'logotipo' =>
                    'O logotipo deve possuir no máximo 5 MB.',
            ]);
        }

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
                'logotipo' =>
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
                'logotipo' =>
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
                'logotipo' =>
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
                'logotipo' =>
                    'A imagem possui dimensões muito grandes. Use no máximo 5000x5000 pixels e até 20 milhões de pixels.',
            ]);
        }

        $this->garantirDiretorio(
            $this->diretorioPublico
        );

        $extensao =
            self::EXTENSOES[$mime];

        $nomeArquivo =
            'logo-'
            . bin2hex(
                random_bytes(16)
            )
            . '.'
            . $extensao;

        $destino =
            $this->diretorioPublico
            . DIRECTORY_SEPARATOR
            . $nomeArquivo;

        $arquivo->moveTo(
            $destino
        );

        $urlPublica =
            '/uploads/igreja/'
            . $nomeArquivo;

        try {
            $this->repository
                ->atualizarLogotipo(
                    $urlPublica
                );
        } catch (\Throwable $e) {
            if (is_file($destino)) {
                @unlink($destino);
            }

            throw $e;
        }

        /**
         * O arquivo antigo só é removido depois que a nova
         * referência foi persistida com sucesso.
         */
        $this->apagarArquivoLocalSeguro(
            $igreja['logotipo']
                ?? null
        );

        return [
            'igreja_id' =>
                (int) $igreja['id'],
            'igreja_nome' =>
                $igreja['nome'],
            'logotipo' =>
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
     * @return array<string, mixed>
     */
    public function remover(): array
    {
        $igreja =
            $this->repository
                ->buscarIgreja();

        if ($igreja === null) {
            throw new DadosInvalidosException([
                'igreja' =>
                    'O cadastro institucional da igreja não foi encontrado.',
            ]);
        }

        $anterior =
            $igreja['logotipo']
            ?? null;

        $this->repository
            ->atualizarLogotipo(
                null
            );

        $arquivoRemovido =
            $this->apagarArquivoLocalSeguro(
                $anterior
            );

        return [
            'igreja_id' =>
                (int) $igreja['id'],
            'logotipo' =>
                null,
            'arquivo_local_removido' =>
                $arquivoRemovido,
        ];
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
                'Nenhum logotipo foi enviado.',

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
            'logotipo' => $mensagem,
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
                'Não foi possível criar o diretório do logotipo da igreja.'
            );
        }
    }

    /**
     * Só remove arquivos que realmente estejam dentro de:
     *
     * public/uploads/igreja
     */
    private function apagarArquivoLocalSeguro(
        mixed $logotipo
    ): bool {
        if (
            !is_string($logotipo)
            || $logotipo === ''
        ) {
            return false;
        }

        $prefixo =
            '/uploads/igreja/';

        if (
            !str_starts_with(
                $logotipo,
                $prefixo
            )
        ) {
            return false;
        }

        $relativo =
            substr(
                $logotipo,
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
