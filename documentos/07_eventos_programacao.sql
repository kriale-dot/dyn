USE syn;

-- ============================================================
-- ETAPA 33
-- Histórico de alterações importantes da programação
-- + notificações automáticas aos participantes ativos.
--
-- Execute este script APÓS a Etapa 32, pois ele utiliza
-- a tabela notificacoes.
-- ============================================================

CREATE TABLE IF NOT EXISTS eventos_programacao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    programacao_id BIGINT UNSIGNED NOT NULL,

    tipo_evento VARCHAR(60) NOT NULL,

    titulo_anterior VARCHAR(180) NULL,
    titulo_novo VARCHAR(180) NULL,

    descricao_anterior TEXT NULL,
    descricao_nova TEXT NULL,

    inicio_anterior DATETIME NULL,
    inicio_novo DATETIME NULL,

    fim_anterior DATETIME NULL,
    fim_novo DATETIME NULL,

    local_anterior VARCHAR(180) NULL,
    local_novo VARCHAR(180) NULL,

    status_anterior VARCHAR(30) NULL,
    status_novo VARCHAR(30) NULL,

    criada_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_eventos_programacao_programacao
        FOREIGN KEY (programacao_id)
        REFERENCES programacoes (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    KEY idx_eventos_programacao (
        programacao_id,
        criada_em
    ),

    KEY idx_eventos_tipo (
        tipo_evento,
        criada_em
    )
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


DROP TRIGGER IF EXISTS trg_programacoes_eventos_au;

DELIMITER $$

CREATE TRIGGER trg_programacoes_eventos_au
AFTER UPDATE ON programacoes
FOR EACH ROW
BEGIN
    DECLARE v_evento_id BIGINT UNSIGNED;
    DECLARE v_tipo_evento VARCHAR(60);
    DECLARE v_titulo_notificacao VARCHAR(160);
    DECLARE v_mensagem_notificacao VARCHAR(500);

    /*
     * Cancelamento tem prioridade.
     *
     * Caso contrário, registramos alterações importantes:
     * título, descrição, início, fim ou local histórico.
     */
    IF NOT (OLD.status <=> NEW.status)
       AND NEW.status = 'CANCELADA'
    THEN

        SET v_tipo_evento =
            'PROGRAMACAO_CANCELADA';

        SET v_titulo_notificacao =
            'Programação cancelada';

        SET v_mensagem_notificacao =
            CONCAT(
                'A programação "',
                NEW.titulo,
                '" foi cancelada.'
            );

        INSERT INTO eventos_programacao (
            programacao_id,
            tipo_evento,

            titulo_anterior,
            titulo_novo,

            descricao_anterior,
            descricao_nova,

            inicio_anterior,
            inicio_novo,

            fim_anterior,
            fim_novo,

            local_anterior,
            local_novo,

            status_anterior,
            status_novo
        )
        VALUES (
            NEW.id,
            v_tipo_evento,

            OLD.titulo,
            NEW.titulo,

            OLD.descricao,
            NEW.descricao,

            OLD.inicio_em,
            NEW.inicio_em,

            OLD.fim_em,
            NEW.fim_em,

            OLD.local_nome_historico,
            NEW.local_nome_historico,

            OLD.status,
            NEW.status
        );

        SET v_evento_id =
            LAST_INSERT_ID();

        /*
         * Notifica somente quem ainda estava efetivamente
         * comprometido com a programação.
         */
        INSERT IGNORE INTO notificacoes (
            usuario_id,
            tipo,
            titulo,
            mensagem,
            url_acao,
            origem_tipo,
            origem_id,
            expira_em
        )
        SELECT
            p.usuario_id,
            v_tipo_evento,
            v_titulo_notificacao,
            v_mensagem_notificacao,
            CONCAT(
                '/programacoes/',
                NEW.id
            ),
            'EVENTO_PROGRAMACAO',
            v_evento_id,
            DATE_ADD(
                NOW(),
                INTERVAL 30 DAY
            )
        FROM participacoes p
        WHERE p.programacao_id = NEW.id
          AND p.status IN (
              'ESCALADO',
              'CONFIRMADO'
          );

    ELSEIF
        NOT (OLD.titulo <=> NEW.titulo)
        OR NOT (OLD.descricao <=> NEW.descricao)
        OR NOT (OLD.inicio_em <=> NEW.inicio_em)
        OR NOT (OLD.fim_em <=> NEW.fim_em)
        OR NOT (
            OLD.local_nome_historico
            <=>
            NEW.local_nome_historico
        )
    THEN

        SET v_tipo_evento =
            'PROGRAMACAO_ALTERADA';

        SET v_titulo_notificacao =
            'Programação atualizada';

        SET v_mensagem_notificacao =
            CONCAT(
                'A programação "',
                NEW.titulo,
                '" teve informações atualizadas. Confira os detalhes.'
            );

        INSERT INTO eventos_programacao (
            programacao_id,
            tipo_evento,

            titulo_anterior,
            titulo_novo,

            descricao_anterior,
            descricao_nova,

            inicio_anterior,
            inicio_novo,

            fim_anterior,
            fim_novo,

            local_anterior,
            local_novo,

            status_anterior,
            status_novo
        )
        VALUES (
            NEW.id,
            v_tipo_evento,

            OLD.titulo,
            NEW.titulo,

            OLD.descricao,
            NEW.descricao,

            OLD.inicio_em,
            NEW.inicio_em,

            OLD.fim_em,
            NEW.fim_em,

            OLD.local_nome_historico,
            NEW.local_nome_historico,

            OLD.status,
            NEW.status
        );

        SET v_evento_id =
            LAST_INSERT_ID();

        INSERT IGNORE INTO notificacoes (
            usuario_id,
            tipo,
            titulo,
            mensagem,
            url_acao,
            origem_tipo,
            origem_id,
            expira_em
        )
        SELECT
            p.usuario_id,
            v_tipo_evento,
            v_titulo_notificacao,
            v_mensagem_notificacao,
            CONCAT(
                '/programacoes/',
                NEW.id
            ),
            'EVENTO_PROGRAMACAO',
            v_evento_id,

            CASE
                WHEN NEW.fim_em >= NOW()
                    THEN NEW.fim_em
                ELSE DATE_ADD(
                    NOW(),
                    INTERVAL 30 DAY
                )
            END

        FROM participacoes p

        WHERE p.programacao_id = NEW.id
          AND p.status IN (
              'ESCALADO',
              'CONFIRMADO'
          );

    END IF;
END$$

DELIMITER ;
