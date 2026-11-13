# 1. Tema e objetivos

[← Voltar ao README](../README.md)

## Tema

**Gestão de Ginásio.** Uma aplicação web que substitui as folhas de cálculo e as marcações por telefone de um ginásio pequeno ou médio.

## Porquê este tema

- Tem **cinco perfis** (incluindo o Visitante, que ainda não fez login) com necessidades diferentes, o que dá um caso real para aplicar RBAC.
- Tem uma parte **pública** (front office) e uma parte **interna** (back office) bem separadas.
- Tem **regras de negócio** concretas: lotação de aulas, mensalidades em atraso, cancelamentos.
- Tem um modelo de dados com relações reais (sócios, planos, aulas, inscrições, presenças).

## Objetivos

1. Permitir ao público conhecer o ginásio: planos, preços, horário e treinadores.
2. Permitir ao sócio registar-se, inscrever-se em aulas e acompanhar as suas mensalidades.
3. Permitir à receção gerir sócios e registar pagamentos.
4. Permitir ao treinador ver as suas aulas e marcar presenças.
5. Permitir ao administrador gerir planos, equipa e permissões.
6. Garantir que cada pessoa só acede ao que o seu perfil permite.

## Perfis

| Perfil | Quem é | Onde trabalha |
|---|---|---|
| **Gestor** | Dono ou gestor do ginásio | Back office (gestão completa, perfil bloqueado) |
| **Rececionista** | Equipa da receção | Back office |
| **Treinador** | Professor das aulas | Back office (só as suas aulas) |
| **Sócio** | Cliente do ginásio | Front office (área do sócio) |

## Dados de exemplo usados nos protótipos

| Plano | Duração | Preço | Sócios |
|---|---|---|---|
| Mensal | 1 mês | 30 € | 52 |
| Trimestral | 3 meses | 80 € | 41 |
| Anual | 12 meses | 300 € | 35 |

Total: 128 sócios ativos, 7 mensalidades em atraso, 4 aulas no dia.

Modalidades: Yoga, Pilates, Spinning, HIIT, Boxe, Funcional, Zumba.
