# 5. Plano de desenvolvimento

[← Voltar ao README](../README.md)

| Fase | Entrega | Conteúdo | Estado |
|---|---|---|---|
| Fase 1 | Escolha e apresentação do tema | Problema, perfis, funcionalidades, regras, modelo de dados, tecnologias | ✅ |
| Fase 2 | Ecrãs de navegação do front office | 11 ecrãs: site público, autenticação e área do sócio | ✅ |
| Fase 2 | Ecrãs de navegação do back office | 10 ecrãs: painel, sócios, mensalidades, planos, aulas, presenças, utilizadores, perfis | ✅ |
| Fase 2 | Entrega do RBAC v1 | Login real em PHP, sessões, tabelas de perfis e permissões, proteção das páginas | 🔄 |
| Seguinte | Funcionalidades completas | CRUD de sócios, planos, aulas; mensalidades; inscrições com regras de negócio | ⏳ |

## Próximos passos (RBAC v1)

- [ ] Ambiente Docker com PHP e MySQL
- [ ] Script SQL com as tabelas `utilizadores`, `perfis`, `permissoes` e `perfil_permissao`
- [ ] Dados iniciais: 4 perfis, 13 permissões e a matriz da [documentação de RBAC](02-perfis-e-permissoes.md)
- [ ] Login com `password_verify()` e sessão
- [ ] Função de verificação de permissões em cada página
- [ ] Menu do back office filtrado pelo perfil
- [ ] Página de gestão de perfis e permissões
