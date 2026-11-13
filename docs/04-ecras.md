# 4. Ecrãs do front office e do back office

[← Voltar ao README](../README.md)

Os ecrãs foram desenhados primeiro como **protótipo clicável no Figma** (versão para computador e para telemóvel): mostram o aspeto e a navegação. As capturas estão em [`screenshots/`](screenshots/).

## Front office

[Abrir o protótipo do front office no Figma](https://www.figma.com/proto/Fp9SiqKnbgXfbWsy3TJppp?node-id=1-2&starting-point-node-id=1%3A2)

### Site público (perfil Visitante, sem login)

| Ecrã | O que mostra |
|---|---|
| Início | Apresentação do ginásio, planos em destaque, chamada para o registo |
| Planos | Mensal (30 €), Trimestral (80 €) e Anual (300 €) |
| Aulas | Horário semanal com filtros por dia e modalidade |
| Treinadores | Equipa de treinadores e modalidades de cada um |
| Contactos | Formulário de contacto com mensagem de confirmação |

### Autenticação

| Ecrã | O que mostra |
|---|---|
| Login | Entrada do sócio com email e palavra-passe |
| Registo | Criação de conta com escolha de plano |
| Conta criada | Confirmação e próximos passos (pagar a 1.ª mensalidade na receção) e botão para o login |

### Área do sócio (após login)

| Ecrã | O que mostra | Permissão |
|---|---|---|
| Conta | Resumo: próximas aulas, estado da mensalidade, cancelar inscrição | — |
| Inscrever | Aulas por dia, lugares livres, bloqueio se a mensalidade estiver em atraso | `aulas.inscrever` |
| Mensalidades | Histórico de pagamentos e estado de cada mês | `mensalidades.proprias` |
| Perfil | Dados pessoais e alteração da palavra-passe | — |

## Back office

[Abrir o protótipo do back office no Figma](https://www.figma.com/proto/Fp9SiqKnbgXfbWsy3TJppp?node-id=12-2&starting-point-node-id=12%3A2)

| # | Ecrã | O que mostra | Permissão |
|---|---|---|---|
| 01 | Login da equipa | Entrada separada da dos sócios | — |
| 02 | Painel | Números e ações que mudam conforme o perfil | — |
| 03 | Sócios | Lista com pesquisa, filtro por estado e separador "Arquivados" | `socios.ver`, `socios.arquivar` |
| 04 | Ficha do sócio | Dados, plano, mensalidades, inscrições e conta de acesso | `socios.ver`, `socios.editar` |
| 05 | Mensalidades | Lista com filtros e registo de pagamento (receção, MB Way ou cartão) | `mensalidades.ver`, `mensalidades.registar` |
| 06 | Planos | Ver, criar, editar e arquivar planos | `planos.ver`, `planos.criar`, `planos.editar`, `planos.arquivar` |
| 07 | Aulas | Horário semanal, filtro por dia, nova aula | `aulas.ver`, `aulas.editar` |
| 08 | Inscrições e presenças | Lista de inscritos com marcação de presença | `presencas.marcar` |
| 09 | Equipa | Lista da equipa por perfil, novo utilizador, mudar perfil, desativar | `utilizadores.ver`, `utilizadores.criar`, `utilizadores.editar`, `utilizadores.desativar` |
| 10 | Perfis e permissões | Matriz de interruptores: 5 perfis × 28 permissões | `perfis.ver`, `perfis.editar` |
| 11 | Dashboard | Indicadores, melhores sócios, pagamentos por método, aulas mais procuradas | `dashboard.ver` (só Gestor) |

## Horário de exemplo

| Dia | Hora | Aula | Sala | Lotação |
|---|---|---|---|---|
| Segunda | 08:00 | Yoga | Sala 1 | 20 |
| Segunda | 18:00 | HIIT | Sala 2 | 15 |
| Segunda | 19:30 | Pilates | Sala 1 | 15 |
| Terça | 12:30 | Spinning | Sala 2 | 20 |
| Terça | 18:00 | Boxe | Sala 1 | 16 |
| Terça | 19:30 | Funcional | Sala 2 | 18 |
| Quarta | 08:00 | Yoga | Sala 1 | 20 |
| Quarta | 18:00 | HIIT | Sala 2 | 15 |
| Quarta | 19:30 | Zumba | Sala 1 | 25 |
| Quinta | 12:30 | Spinning | Sala 2 | 20 |
| Quinta | 18:00 | Boxe | Sala 1 | 16 |
| Sexta | 08:00 | Pilates | Sala 1 | 15 |
| Sexta | 18:00 | Yoga | Sala 1 | 20 |

## Capturas de ecrã

As imagens dos ecrãs ficam na pasta [`screenshots/`](screenshots/).
