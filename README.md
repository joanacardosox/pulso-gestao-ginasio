# Pulso — Gestão de Ginásio

> **EN summary.** Pulso is a gym management web application built as the course project for *Programação para a Web* (CTESP TPSI). It has a public front office for members (plans, class schedule, sign-up, booking classes, monthly fees) and a back office for staff (members, payments, classes, attendance, users). Access is controlled with **role-based access control (RBAC)**: four roles — Administrator, Receptionist, Trainer, Member — and 13 permissions stored in the database. Planned stack: PHP, MySQL, Docker, HTML/CSS/JS. The project is being delivered in phases; this repository documents each one.

Aplicação web para gerir um ginásio: sócios, planos, mensalidades, aulas, inscrições e presenças, com acesso controlado por perfis (RBAC).

Projeto da unidade curricular **Programação para a Web** — CTESP em Tecnologias e Programação de Sistemas de Informação (TPSI), 2026/2027.

---

## Estado do projeto

| Fase | Entrega | Estado |
|---|---|---|
| Fase 1 | Escolha e apresentação do tema | ✅ Concluída |
| Fase 2 | Ecrãs de navegação do front office | ✅ Concluída (protótipo) |
| Fase 2 | Ecrãs de navegação do back office | ✅ Concluída (protótipo) |
| Fase 2 | Entrega do RBAC v1 | 🔄 Em curso |

## O problema

Muitos ginásios pequenos ainda gerem sócios e pagamentos em folhas de cálculo e marcam aulas por telefone. Isso gera aulas sobrelotadas, mensalidades em atraso sem controlo e pouca visibilidade sobre quem frequenta o quê.

O Pulso junta tudo numa só aplicação:

- **o sócio** vê o horário, inscreve-se em aulas e consulta as suas mensalidades;
- **a receção** gere sócios e regista pagamentos;
- **o treinador** vê as suas aulas e marca presenças;
- **o administrador** gere planos, equipa e permissões.

## Perfis e permissões (RBAC)

| Permissão | Administrador | Rececionista | Treinador | Sócio |
|---|:-:|:-:|:-:|:-:|
| `socios.ver` | ✅ | ✅ | | |
| `socios.editar` | ✅ | ✅ | | |
| `socios.eliminar` | ✅ | | | |
| `planos.gerir` | ✅ | | | |
| `mensalidades.ver` | ✅ | ✅ | | |
| `mensalidades.registar` | ✅ | ✅ | | |
| `mensalidades.proprias` | ✅ | | | ✅ |
| `aulas.ver` | ✅ | ✅ | ✅ ¹ | |
| `aulas.editar` | ✅ | ✅ | ✅ ¹ | |
| `aulas.inscrever` | ✅ | | | ✅ |
| `presencas.marcar` | ✅ | ✅ | ✅ ¹ | |
| `utilizadores.gerir` | ✅ | | | |
| `perfis.gerir` | ✅ | | | |

¹ O Treinador só vê, edita e marca presenças **nas suas próprias aulas**.

Detalhe completo em [docs/02-perfis-e-permissoes.md](docs/02-perfis-e-permissoes.md).

## Funcionalidades

**Front office (público e área do sócio)**
- Página inicial, planos e preços, horário de aulas com filtros, treinadores, contactos
- Registo com escolha de plano e login
- Área do sócio: resumo da conta, inscrição em aulas, mensalidades e perfil

**Back office (equipa)**
- Painel que muda conforme o perfil de quem entra
- Sócios: lista com pesquisa e filtros, ficha individual
- Mensalidades: estados *Pendente*, *Paga* e *Em atraso*, registo de pagamentos
- Planos, aulas, inscrições e marcação de presenças
- Utilizadores da equipa e matriz de perfis e permissões

## Regras de negócio principais

- Cada aula tem **lotação máxima**; quando está cheia, não aceita mais inscrições.
- Um sócio com **mensalidade em atraso** fica impedido de se inscrever em aulas.
- O sócio pode **cancelar** uma inscrição até um número de horas antes da aula (a definir).
- As palavras-passe são guardadas com `password_hash()` e verificadas com `password_verify()`.

Mais em [docs/03-modelo-de-dados.md](docs/03-modelo-de-dados.md).

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Front end | HTML, CSS, JavaScript |
| Back end | PHP |
| Base de dados | MySQL |
| Ambiente | Docker |

## Protótipos

Os protótipos clicáveis das fases 1 e 2 foram feitos antes de qualquer código, para validar navegação e permissões:

- [Apresentação da Fase 1](https://claude.ai/artifact/V3SgcPHQ2CiEfAacWK8AcX)
- [Ecrãs do front office](https://claude.ai/artifact/4FquEe5tHMn7iKnjj3NRp1)
- [Ecrãs do back office](https://claude.ai/artifact/8iMnVsvqRgqi9mWooGZGWk)

A descrição de cada ecrã está em [docs/04-ecras.md](docs/04-ecras.md).

## Documentação

1. [Tema e objetivos](docs/01-tema.md)
2. [Perfis e permissões (RBAC)](docs/02-perfis-e-permissoes.md)
3. [Modelo de dados e regras de negócio](docs/03-modelo-de-dados.md)
4. [Ecrãs do front office e do back office](docs/04-ecras.md)
5. [Plano de desenvolvimento](docs/05-plano.md)

## Autor

Projeto desenvolvido por **[O teu nome]** no âmbito do CTESP TPSI.

Os nomes, valores e datas mostrados nos protótipos são dados de exemplo.
