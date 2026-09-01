<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ConflitoLocalException;
use App\Exceptions\DadosInvalidosException;
use App\Exceptions\SerieProgramacaoNaoEncontradaException;
use App\Services\SerieProgramacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SerieProgramacaoController
{
    public function __construct(private SerieProgramacaoService $service) {}

    public function listar(Request $request, Response $response): Response
    {
        $series=$this->service->listarTodas();
        return $this->json($response,['status'=>'ok','total'=>count($series),'dados'=>$series],200);
    }

    public function buscarPorId(Request $request, Response $response, array $args): Response
    {
        try { return $this->json($response,['status'=>'ok','dados'=>$this->service->buscarPorId((int)$args['id'])],200); }
        catch(SerieProgramacaoNaoEncontradaException $e){ return $this->json($response,['status'=>'erro','mensagem'=>$e->getMessage()],404); }
    }

    public function criar(Request $request, Response $response): Response
    {
        $dados=$request->getParsedBody();
        if(!is_array($dados)) return $this->json($response,['status'=>'erro','mensagem'=>'Envie os dados da série em formato JSON.'],400);
        try{
            $r=$this->service->criar($dados);
            $out=['status'=>'ok','mensagem'=>'Série recorrente criada com sucesso.','dados'=>$r];
            if($r['conflitos_confirmados']) $out['alerta']='A série foi criada após confirmação explícita de conflitos de local.';
            return $this->json($response,$out,201);
        }catch(ConflitoLocalException $e){
            return $this->json($response,[
                'status'=>'conflito',
                'mensagem'=>'Uma ou mais ocorrências da série conflitam com programações já existentes.',
                'conflitos'=>$e->getConflitos(),
                'como_confirmar'=>'Repita a requisição acrescentando "confirmar_conflitos": true.'
            ],409);
        }catch(DadosInvalidosException $e){
            return $this->json($response,['status'=>'erro','mensagem'=>$e->getMessage(),'erros'=>$e->getErros()],422);
        }
    }

    public function desativar(Request $request, Response $response, array $args): Response
    {
        try{
            $r=$this->service->desativar((int)$args['id']);
            return $this->json($response,[
                'status'=>'ok',
                'mensagem'=>$r['ja_estava_inativa']?'A série já estava inativa.':'Série desativada com sucesso.',
                'dados'=>$r
            ],200);
        }catch(SerieProgramacaoNaoEncontradaException $e){
            return $this->json($response,['status'=>'erro','mensagem'=>$e->getMessage()],404);
        }
    }

    private function json(Response $response,array $dados,int $statusCode): Response
    {
        $response->getBody()->write(json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
        return $response->withHeader('Content-Type','application/json; charset=utf-8')->withStatus($statusCode);
    }
}
