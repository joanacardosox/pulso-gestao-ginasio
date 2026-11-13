# 3. Modelo de dados e regras de negócio

[← Voltar ao README](../README.md)

## Tabelas

O modelo proposto tinha 11 tabelas; na implementação a presença ficou como coluna de `inscricoes` (`presente`), por isso a base de dados tem 10, em três grupos:

| Grupo | Tabelas |
|---|---|
| Acessos (RBAC) | `utilizadores`, `perfis`, `permissoes`, `perfil_permissao` |
| Sócios e pagamentos | `socios`, `planos`, `mensalidades` |
| Aulas | `modalidades`, `aulas`, `inscricoes` (com `presente`) |

## Diagrama entidade-relação (base de dados atual)

Gerado no phpMyAdmin (base `pulso` → Desenhador):

![DER da base de dados](DER.png)

## Diagrama entidade-relação (proposta)

```mermaid
erDiagram
    perfis ||--o{ utilizadores : "atribuído a"
    perfis ||--o{ perfil_permissao : tem
    permissoes ||--o{ perfil_permissao : "faz parte de"
    utilizadores ||--o| socios : "conta de acesso"
    planos ||--o{ socios : subscreve
    socios ||--o{ mensalidades : paga
    modalidades ||--o{ aulas : "tipo de"
    utilizadores ||--o{ aulas : "treinador de"
    aulas ||--o{ inscricoes : recebe
    socios ||--o{ inscricoes : faz
    inscricoes ||--o| presencas : regista

    utilizadores {
        int id PK
        string nome
        string email UK
        string password_hash
        int perfil_id FK
        bool ativo
    }
    perfis {
        int id PK
        string nome UK
    }
    permissoes {
        int id PK
        string codigo UK
        string descricao
    }
    perfil_permissao {
        int perfil_id PK, FK
        int permissao_id PK, FK
    }
    socios {
        int id PK
        int numero UK
        int utilizador_id FK
        int plano_id FK
        string telefone
        date data_inscricao
    }
    planos {
        int id PK
        string nome
        int duracao_meses
        decimal preco
        string descricao
    }
    mensalidades {
        int id PK
        int socio_id FK
        date referencia
        decimal valor
        date data_limite
        date data_pagamento
        enum estado "Pendente | Paga | Em atraso"
    }
    modalidades {
        int id PK
        string nome
    }
    aulas {
        int id PK
        int modalidade_id FK
        int treinador_id FK
        enum dia_semana
        time hora_inicio
        string sala
        int lotacao_max
    }
    inscricoes {
        int id PK
        int aula_id FK
        int socio_id FK
        date data_aula
        datetime criado_em
    }
    presencas {
        int id PK
        int inscricao_id FK
        bool presente
        int marcado_por FK
    }
```

> Os nomes das colunas são uma proposta e podem mudar na implementação.

## Regras de negócio

| # | Regra | Onde se aplica |
|---|---|---|
| R1 | Uma aula não aceita inscrições acima da **lotação máxima**. | Inscrição em aulas (site) |
| R2 | Um sócio com **mensalidade em atraso** não se pode inscrever em aulas. | Inscrição em aulas (site) |
| R3 | O sócio pode **cancelar** uma inscrição até **2 horas** antes da aula. | Área do sócio |
| R4 | Uma mensalidade está *Pendente* até ao fim do prazo, *Paga* quando é registado o pagamento e *Em atraso* depois do prazo sem pagamento. | Mensalidades |
| R5 | O Treinador só vê e marca presenças nas aulas em que é o treinador atribuído. | Aulas e presenças |
| R6 | As palavras-passe nunca são guardadas em texto: usa-se `password_hash()`. | Registo e utilizadores |
| R7 | O perfil **Gestor** está bloqueado: as suas permissões não se alteram. | Perfis e permissões |
| R8 | **Nada se apaga:** sócios, planos e aulas são arquivados (`arquivado_em`); um sócio arquivado deixa de poder entrar, mas mensalidades e presenças ficam no histórico. Contas da equipa são desativadas (`ativo = 0`). | Sócios, planos, aulas, equipa |
| R9 | Uma mensalidade é paga **na receção**, por **MB Way** ou com **cartão**; guarda-se o método (`metodo`) e quem registou (`registado_por`). O sócio só pode pagar as suas. | Mensalidades |
| R10 | Quem não fez login é tratado como **Visitante**: só vê o site público e pode criar conta. | Site público |
