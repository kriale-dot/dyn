<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflitoLocalException;
use App\Exceptions\DadosInvalidosException;
use App\Exceptions\SerieProgramacaoNaoEncontradaException;
use App\Repositories\SerieProgramacaoRepository;
use DateInterval;
use DateTimeImmutable;
use Throwable;

final class SerieProgramacaoService
{
    private const MAX_OCORRENCIAS = 200;

    public function __construct(private SerieProgramacaoRepository $repository) {}

    public function listarTodas(): array
    {
        return array_map(fn(array $s): array => $this->formatarSerie($s), $this->repository->listarTodas());
    }

    public function buscarPorId(int $id): array
    {
        $serie = $this->repository->buscarPorId($id);
        if ($serie === null) throw new SerieProgramacaoNaoEncontradaException($id);

        $resultado = $this->formatarSerie($serie);
        $resultado['ocorrencias'] = array_map(
            fn(array $o): array => $this->formatarOcorrencia($o),
            $this->repository->listarOcorrencias($id)
        );
        return $resultado;
    }

    public function criar(array $dados): array
    {
        $validado = $this->validarDados($dados);
        $serie = $this->resolverReferencias($validado);
        $ocorrencias = $this->gerarOcorrencias(
            $serie['inicio_base'], $serie['fim_base'],
            $serie['intervalo_semanas'], $serie['data_limite']
        );

        $conflitos = [];
        foreach ($ocorrencias as $oc) {
            foreach ($this->repository->buscarConflitosDeLocal($serie['local_id'], $oc['inicio_em'], $oc['fim_em']) as $existente) {
                $conflitos[] = [
                    'ocorrencia_nova' => $oc,
                    'programacao_existente' => $this->formatarConflito($existente),
                ];
            }
        }

        $confirmar = $this->normalizarBooleano($dados['confirmar_conflitos'] ?? false, 'confirmar_conflitos');
        if ($conflitos !== [] && !$confirmar) {
            throw new ConflitoLocalException($conflitos);
        }

        $serie['regra_recorrencia'] = json_encode([
            'frequencia' => 'SEMANAL',
            'intervalo_semanas' => $serie['intervalo_semanas'],
            'permite_resposta' => $serie['permite_resposta'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $id = $this->repository->criarComOcorrencias($serie, $ocorrencias);

        return [
            'serie' => $this->buscarPorId($id),
            'total_ocorrencias_criadas' => count($ocorrencias),
            'conflitos_confirmados' => $conflitos !== [] && $confirmar,
            'conflitos_detectados' => $conflitos,
        ];
    }

    public function desativar(int $id): array
    {
        $serie = $this->repository->buscarPorId($id);
        if ($serie === null) throw new SerieProgramacaoNaoEncontradaException($id);
        $ja = !(bool) $serie['ativa'];
        if (!$ja) $this->repository->desativar($id);

        return [
            'serie' => $this->buscarPorId($id),
            'ja_estava_inativa' => $ja,
            'observacao' => 'As ocorrências já criadas permanecem no sistema e devem ser canceladas individualmente se necessário.',
        ];
    }

    private function validarDados(array $dados): array
    {
        $erros = [];
        $titulo = trim((string)($dados['titulo'] ?? ''));
        if ($titulo === '') $erros['titulo'] = 'O título é obrigatório.';
        elseif (mb_strlen($titulo) > 180) $erros['titulo'] = 'O título deve possuir no máximo 180 caracteres.';

        $tipoId = $this->validarId($dados['tipo_programacao_id'] ?? null, 'tipo_programacao_id', $erros);
        $localId = $this->validarId($dados['local_id'] ?? null, 'local_id', $erros);
        $orgId = $this->validarId($dados['organizador_id'] ?? null, 'organizador_id', $erros);
        $inicio = $this->normalizarDataHora($dados['inicio_base'] ?? null, 'inicio_base', $erros);
        $fim = $this->normalizarDataHora($dados['fim_base'] ?? null, 'fim_base', $erros);
        if ($inicio !== null && $fim !== null && $fim <= $inicio) $erros['fim_base'] = 'fim_base deve ser posterior a inicio_base.';

        $limite = $this->normalizarData($dados['data_limite'] ?? null, 'data_limite', $erros);
        if ($inicio !== null && $limite !== null) {
            if (new DateTimeImmutable($limite . ' 23:59:59') < new DateTimeImmutable($inicio)) {
                $erros['data_limite'] = 'data_limite não pode ser anterior ao início da série.';
            }
        }

        $intervalo = filter_var($dados['intervalo_semanas'] ?? 1, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>52]]);
        if ($intervalo === false) { $erros['intervalo_semanas'] = 'intervalo_semanas deve ser um inteiro entre 1 e 52.'; $intervalo = 1; }

        try { $permite = $this->normalizarBooleano($dados['permite_resposta'] ?? true, 'permite_resposta'); }
        catch (DadosInvalidosException $e) { $erros = array_merge($erros, $e->getErros()); $permite = true; }

        if ($erros !== []) throw new DadosInvalidosException($erros);

        return [
            'titulo'=>$titulo,
            'descricao'=>$this->textoOpcional($dados['descricao'] ?? null),
            'tipo_programacao_id'=>$tipoId,
            'local_id'=>$localId,
            'organizador_id'=>$orgId,
            'inicio_base'=>$inicio,
            'fim_base'=>$fim,
            'data_limite'=>$limite,
            'intervalo_semanas'=>(int)$intervalo,
            'permite_resposta'=>$permite,
        ];
    }

    private function resolverReferencias(array $dados): array
    {
        $erros=[];
        $tipo=$this->repository->buscarTipoProgramacaoPorId($dados['tipo_programacao_id']);
        if ($tipo===null) $erros['tipo_programacao_id']='O tipo de programação informado não existe.';
        elseif (!(bool)$tipo['ativo']) $erros['tipo_programacao_id']='O tipo de programação informado está inativo.';

        $local=$this->repository->buscarLocalPorId($dados['local_id']);
        if ($local===null) $erros['local_id']='O local informado não existe.';
        elseif (!(bool)$local['ativo']) $erros['local_id']='O local informado está inativo.';

        $org=$this->repository->buscarOrganizadorPorId($dados['organizador_id']);
        if ($org===null) $erros['organizador_id']='O organizador informado não existe.';
        elseif ($org['status']!=='ATIVO') $erros['organizador_id']='O organizador informado está inativo.';

        if ($erros!==[]) throw new DadosInvalidosException($erros);

        return array_merge($dados,[
            'tipo_programacao_nome_historico'=>$tipo['nome'],
            'local_nome_historico'=>$local['nome'],
            'organizador_nome_historico'=>$org['nome'],
        ]);
    }

    private function gerarOcorrencias(string $inicioBase, string $fimBase, int $intervalo, string $dataLimite): array
    {
        $inicio=new DateTimeImmutable($inicioBase);
        $fim=new DateTimeImmutable($fimBase);
        $duracao=$fim->getTimestamp()-$inicio->getTimestamp();
        $limite=new DateTimeImmutable($dataLimite.' 23:59:59');
        $salto=new DateInterval('P'.$intervalo.'W');
        $out=[]; $atual=$inicio;
        while ($atual <= $limite) {
            if (count($out) >= self::MAX_OCORRENCIAS) {
                throw new DadosInvalidosException(['data_limite'=>'A série ultrapassa o limite de 200 ocorrências. Reduza o período ou aumente o intervalo.']);
            }
            $out[]=[
                'inicio_em'=>$atual->format('Y-m-d H:i:s'),
                'fim_em'=>$atual->modify('+'.$duracao.' seconds')->format('Y-m-d H:i:s'),
            ];
            $atual=$atual->add($salto);
        }
        return $out;
    }

    private function validarId(mixed $valor,string $campo,array &$erros): ?int
    {
        $id=filter_var($valor,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if ($id===false){$erros[$campo]='Informe um ID válido.';return null;}
        return (int)$id;
    }

    private function normalizarDataHora(mixed $valor,string $campo,array &$erros): ?string
    {
        if (!is_string($valor)){ $erros[$campo]='Informe data e hora válidas.'; return null; }
        $valor=trim($valor);
        foreach(['Y-m-d H:i:s','Y-m-d\\TH:i:s','Y-m-d H:i','Y-m-d\\TH:i'] as $f){
            $d=DateTimeImmutable::createFromFormat('!'.$f,$valor);
            if($d!==false && $d->format($f)===$valor) return $d->format('Y-m-d H:i:s');
        }
        $erros[$campo]='Use data e hora no formato YYYY-MM-DD HH:MM:SS.';
        return null;
    }

    private function normalizarData(mixed $valor,string $campo,array &$erros): ?string
    {
        if(!is_string($valor)){ $erros[$campo]='Informe uma data válida.'; return null; }
        $valor=trim($valor); $d=DateTimeImmutable::createFromFormat('!Y-m-d',$valor);
        if($d===false || $d->format('Y-m-d')!==$valor){$erros[$campo]='Use uma data válida no formato YYYY-MM-DD.';return null;}
        return $valor;
    }

    private function normalizarBooleano(mixed $valor,string $campo): bool
    {
        if(is_bool($valor)) return $valor;
        if($valor===1||$valor==='1') return true;
        if($valor===0||$valor==='0') return false;
        if(is_string($valor)){
            $t=mb_strtolower(trim($valor)); if($t==='true') return true; if($t==='false') return false;
        }
        throw new DadosInvalidosException([$campo=>'Informe true ou false.']);
    }

    private function textoOpcional(mixed $valor): ?string
    {
        if($valor===null) return null; $t=trim((string)$valor); return $t===''?null:$t;
    }

    private function formatarSerie(array $s): array
    {
        try { $regra=json_decode((string)$s['regra_recorrencia'],true,512,JSON_THROW_ON_ERROR); }
        catch(Throwable){ $regra=['texto_original'=>$s['regra_recorrencia']]; }
        return [
            'id'=>(int)$s['id'],'titulo'=>$s['titulo'],'descricao'=>$s['descricao'],
            'inicio_base'=>$s['inicio_base'],'fim_base'=>$s['fim_base'],'data_limite'=>$s['data_limite'],
            'ativa'=>(bool)$s['ativa'],'regra_recorrencia'=>$regra,
            'tipo_programacao_id'=>(int)$s['tipo_programacao_id'],'local_id'=>(int)$s['local_id'],
            'organizador_id'=>(int)$s['organizador_id'],'total_ocorrencias'=>(int)$s['total_ocorrencias'],
            'total_ocorrencias_futuras'=>(int)$s['total_ocorrencias_futuras'],
            'criado_em'=>$s['criado_em'],'atualizado_em'=>$s['atualizado_em'],
        ];
    }

    private function formatarOcorrencia(array $o): array
    {
        return [
            'id'=>(int)$o['id'],'titulo'=>$o['titulo'],'inicio_em'=>$o['inicio_em'],'fim_em'=>$o['fim_em'],
            'status'=>$o['status'],'permite_resposta'=>(bool)$o['permite_resposta'],
            'tipo_programacao'=>$o['tipo_programacao_nome_historico'],'local'=>$o['local_nome_historico'],
            'organizador'=>$o['organizador_nome_historico'],'cancelada_em'=>$o['cancelada_em'],
            'motivo_cancelamento'=>$o['motivo_cancelamento'],'realizado_em'=>$o['realizado_em'],
        ];
    }

    private function formatarConflito(array $c): array
    {
        return [
            'id'=>(int)$c['id'],'serie_id'=>$c['serie_id']===null?null:(int)$c['serie_id'],'titulo'=>$c['titulo'],
            'inicio_em'=>$c['inicio_em'],'fim_em'=>$c['fim_em'],'status'=>$c['status'],
            'local'=>$c['local_nome_historico'],'tipo_programacao'=>$c['tipo_programacao_nome_historico'],
            'organizador'=>$c['organizador_nome_historico'],
        ];
    }
}
