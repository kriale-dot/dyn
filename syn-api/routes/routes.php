<?php

declare(strict_types=1);

/**
 * ============================================================
 * ARQUIVO CENTRAL DE ROTAS
 * ============================================================
 *
 * IMPORTANTE:
 * auth.php precisa ser carregado primeiro porque ele cria:
 *
 * $authMiddleware
 * $adminMiddleware
 * $adminOrganizadorMiddleware
 *
 * que serão reutilizados pelos demais módulos.
 */

require __DIR__ . '/auth.php';

require __DIR__ . '/igreja.php';
require __DIR__ . '/usuarios.php';
require __DIR__ . '/funcoes.php';
require __DIR__ . '/departamentos.php';
require __DIR__ . '/tipos_programacao.php';
require __DIR__ . '/locais.php';
require __DIR__ . '/programacoes.php';
require __DIR__ . '/participacoes.php';
