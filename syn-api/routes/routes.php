<?php

declare(strict_types=1);

/**
 * Infraestrutura HTTP.
 *
 * IMPORTANTE:
 * CORS precisa continuar sendo carregado antes das demais rotas.
 */
require __DIR__ . '/cors.php';

/**
 * Saúde da aplicação.
 *
 * Deve ficar antes das rotas de negócio e não exige autenticação.
 */
require __DIR__ . '/saude.php';

/**
 * Rotas públicas: não dependem de autenticação.
 */
require __DIR__ . '/publico.php';

require __DIR__ . '/auth.php';
require __DIR__ . '/recuperacao_senha.php';
require __DIR__ . '/cadastros.php';

require __DIR__ . '/app_bootstrap.php';

require __DIR__ . '/permissoes_organizador.php';
require __DIR__ . '/permissoes_especiais.php';

require __DIR__ . '/igreja.php';
require __DIR__ . '/logotipo_igreja.php';

require __DIR__ . '/usuarios.php';
require __DIR__ . '/perfil.php';
require __DIR__ . '/foto_perfil.php';

require __DIR__ . '/dashboard.php';
require __DIR__ . '/mapa_semana.php';
require __DIR__ . '/notificacoes.php';

require __DIR__ . '/necessidades_especificas.php';

require __DIR__ . '/funcoes.php';
require __DIR__ . '/departamentos.php';
require __DIR__ . '/tipos_programacao.php';
require __DIR__ . '/locais.php';

require __DIR__ . '/detalhe_programacao.php';
require __DIR__ . '/gestao_escala.php';
require __DIR__ . '/escalas_semana.php';
require __DIR__ . '/historico_programacao.php';

require __DIR__ . '/programacoes.php';
require __DIR__ . '/series_programacao.php';
require __DIR__ . '/participacoes.php';

/**
 * Auditoria permanece por último porque seu arquivo também
 * registra um middleware global.
 */
require __DIR__ . '/auditoria.php';
