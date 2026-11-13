# 5. Plano de desenvolvimento

[← Voltar ao README](../README.md)

| Fase | Entrega | Conteúdo | Estado |
|---|---|---|---|
| Fase 1 | Escolha e apresentação do tema | Problema, perfis, funcionalidades, regras, modelo de dados, tecnologias | ✅ |
| Fase 2 | Ecrãs de navegação do front office | 12 ecrãs (site público, autenticação e área do sócio), computador e telemóvel, no Figma | ✅ |
| Fase 2 | Ecrãs de navegação do back office | 12 ecrãs (painel, dashboard, sócios, mensalidades, planos, aulas, presenças, equipa, perfis), no Figma | ✅ |
| Fase 2 | Entrega do RBAC v1 | Login real em PHP, sessões, tabelas de perfis e permissões, proteção das páginas | ✅ |
| Fase 2 | RBAC v2 (depois da apresentação) | Permissões por ação, perfil Visitante, Gestor, arquivar em vez de eliminar, métodos de pagamento, dashboard | ✅ |
| Seguinte | Funcionalidades completas | Formulários de criar e editar (sócios, planos, aulas), cancelamento de inscrições até 2 h antes | ⏳ |

## O que mudou depois da apresentação (RBAC v2)

O professor pediu permissões mais claras na base de dados e algumas mudanças de produto:

- [x] **Cada ação é uma permissão:** "gerir sócios" passou a `socios.ver`, `socios.criar`, `socios.editar` e `socios.arquivar` (e o mesmo para planos, aulas, mensalidades e equipa): 28 permissões.
- [x] **Perfil Visitante** para quem usa o site sem login, com as suas permissões na base de dados.
- [x] **"Administrador" passa a "Gestor"** (o dono do ginásio).
- [x] **Arquivar em vez de eliminar** sócios, planos e aulas, para manter o histórico.
- [x] **Pagamentos** na receção, por MB Way ou com cartão, com registo do método e de quem registou.
- [x] **Dashboard** do Gestor com os melhores sócios.
- [x] Testes automáticos do RBAC: [`tests/rbac.sh`](../tests/rbac.sh) (40 verificações).

## Próximos passos

- [ ] Formulários de criar e editar sócios, planos e aulas (as permissões já existem)
- [ ] Cancelar inscrições até 2 horas antes da aula
- [ ] Guardar telemóvel e data de nascimento na ficha do sócio e a categoria das modalidades (já aparecem nos ecrãs)
- [ ] Avisos por email (mensalidade a vencer)
