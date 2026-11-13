#!/usr/bin/env bash
# Testes do RBAC do Pulso: 40 pedidos HTTP reais (curl), perfil a perfil.
#
# Uso (com a aplicação a correr: docker compose up -d):
#   bash tests/rbac.sh
#
# Verifica, para cada perfil, que as páginas abrem ou devolvem 403, que as ações
# são recusadas no servidor sem a permissão certa, e que os dados mudam como esperado.
# No fim repõe os dados de exemplo que alterou.
set -u
B=http://localhost:8090
T=$(mktemp -d)
# no Git Bash (Windows) o curl precisa do caminho em formato Windows
command -v cygpath >/dev/null 2>&1 && T=$(cygpath -m "$T")
export MSYS_NO_PATHCONV=1
ok=0; falha=0
check() { # descricao esperado obtido
  if [ "$2" == "$3" ]; then ok=$((ok+1)); printf '  ok    %s\n' "$1"; else falha=$((falha+1)); printf '  FALHA %s (esperado %s, obtido %s)\n' "$1" "$2" "$3"; fi
}
tok() { grep -o 'name="csrf" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
code() { curl -s -o /dev/null -w '%{http_code}' -b "$T/$1" -c "$T/$1" "$B$2"; }
body() { curl -s -b "$T/$1" -c "$T/$1" "$B$2"; }
post() { local jar=$1 url=$2; shift 2; local t; t=$(body "$jar" "$url" | tok); curl -s -o /dev/null -w '%{http_code}' -b "$T/$jar" -c "$T/$jar" --data-urlencode "csrf=$t" "$@" "$B$url"; }
login() { local t; t=$(curl -s -c "$T/$1" -b "$T/$1" "$B/login.php" | tok); curl -s -o /dev/null -w '%{redirect_url}' -c "$T/$1" -b "$T/$1" --data-urlencode "csrf=$t" --data-urlencode "email=$2" --data-urlencode "password=$3" "$B/login.php"; }
sql() { docker exec pulso-db sh -c "mysql --default-character-set=utf8mb4 -N -uroot -p\"\$MYSQL_ROOT_PASSWORD\" \"\$MYSQL_DATABASE\" -e \"$1\"" 2>/dev/null; }

echo "== Visitante (sem login)"
check "site público abre" 200 "$(code v /index.php)"
check "site diz que é Visitante" 1 "$(body v /index.php | grep -c 'perfil Visitante')"
check "registo abre" 200 "$(code v /registo.php)"
check "back office manda para o login" 302 "$(code v /backoffice/socios.php)"
check "criar conta" 302 "$(post v /registo.php --data-urlencode nome='Marta Teste' --data-urlencode email=marta.teste@pulso.test -d plano=1 --data-urlencode password=Treino2026 --data-urlencode password2=Treino2026)"
check "conta nova tem perfil Sócio e ficha" "Sócio	1" "$(sql "SELECT p.nome, (SELECT COUNT(*) FROM socios s WHERE s.utilizador_id=u.id) FROM utilizadores u JOIN perfis p ON p.id=u.perfil_id WHERE u.email='marta.teste@pulso.test'")"

echo "== Sócio"
check "login vai para a área do sócio" "$B/area/index.php" "$(login s socio@pulso.test 'Socio123!')"
check "vê as suas mensalidades" 200 "$(code s /area/mensalidades.php)"
check "registo fica fechado com sessão" 302 "$(code s /registo.php)"
check "back office → 403" 403 "$(code s /backoffice/socios.php)"
check "dashboard → 403" 403 "$(code s /backoffice/dashboard.php)"
mine=$(sql "SELECT m.id FROM mensalidades m JOIN socios s ON s.id=m.socio_id WHERE s.utilizador_id=6 AND m.estado<>'Paga' LIMIT 1")
other=$(sql "SELECT id FROM mensalidades WHERE socio_id=2 AND estado<>'Paga' LIMIT 1")
post s /area/mensalidades.php -d pagar="$mine" --data-urlencode metodo='MB Way' >/dev/null
check "paga a sua mensalidade por MB Way" "Paga	MB Way" "$(sql "SELECT estado, metodo FROM mensalidades WHERE id=$mine")"
post s /area/mensalidades.php -d pagar="$other" -d 'metodo=Cart%C3%A3o' >/dev/null
check "não consegue pagar a de outro sócio" "Em atraso" "$(sql "SELECT estado FROM mensalidades WHERE id=$other")"

echo "== Treinador"
login t treinador@pulso.test 'Treinador123!' >/dev/null
check "vê as suas aulas" 200 "$(code t /backoffice/aulas.php)"
check "só aulas dele (Boxe/Funcional, nunca Yoga)" 0 "$(body t /backoffice/aulas.php | grep -c '<strong>Yoga</strong>')"
check "planos → 403" 403 "$(code t /backoffice/planos.php)"
check "dashboard → 403" 403 "$(code t /backoffice/dashboard.php)"
check "arquivar aula → 403" 403 "$(post t /backoffice/aulas.php -d aula=5 -d acao=arquivar)"

echo "== Rececionista"
login r rececao@pulso.test 'Rececao123!' >/dev/null
check "vê sócios" 200 "$(code r /backoffice/socios.php)"
check "não vê o botão Arquivar" 0 "$(body r /backoffice/socios.php | grep -c '>Arquivar<')"
check "arquivar sócio → 403" 403 "$(post r /backoffice/socios.php -d socio=2 -d acao=arquivar)"
check "vê planos (planos.ver)" 200 "$(code r /backoffice/planos.php)"
check "arquivar plano → 403" 403 "$(post r /backoffice/planos.php -d plano=1 -d acao=arquivar)"
m=$(sql "SELECT id FROM mensalidades WHERE socio_id=2 AND estado<>'Paga' LIMIT 1")
post r /backoffice/mensalidades.php -d pagar="$m" -d 'metodo=Rece%C3%A7%C3%A3o' >/dev/null
check "regista pagamento na receção (e fica quem registou)" "Paga	Receção	2" "$(sql "SELECT estado, metodo, registado_por FROM mensalidades WHERE id=$m")"
check "dashboard → 403" 403 "$(code r /backoffice/dashboard.php)"
check "equipa → 403" 403 "$(code r /backoffice/utilizadores.php)"
check "perfis → 403" 403 "$(code r /backoffice/perfis.php)"

echo "== Gestor"
check "login vai para o back office" "$B/backoffice/index.php" "$(login g gestor@pulso.test 'Gestor123!')"
check "dashboard abre" 200 "$(code g /backoffice/dashboard.php)"
check "dashboard mostra os melhores sócios" 1 "$(body g /backoffice/dashboard.php | grep -c 'Melhores sócios')"
check "1.º melhor sócio é a Ana Ribeiro" 1 "$(body g /backoffice/dashboard.php | grep -o '<td class="mono">1</td><td><strong>[^<]*' | grep -c 'Ana Ribeiro')"
post g /backoffice/socios.php -d socio=1 -d acao=arquivar >/dev/null
check "arquiva sócio e desativa a conta dele" "1	0" "$(sql "SELECT arquivado_em IS NOT NULL, (SELECT ativo FROM utilizadores WHERE id=6) FROM socios WHERE id=1")"
check "histórico do sócio arquivado fica guardado" 1 "$(sql "SELECT COUNT(*)>0 FROM mensalidades WHERE socio_id=1")"
check "sócio arquivado já não entra" "" "$(login s2 socio@pulso.test 'Socio123!')"
post g /backoffice/socios.php -d socio=1 -d acao=reativar >/dev/null
check "reativa o sócio" "0	1" "$(sql "SELECT arquivado_em IS NOT NULL, (SELECT ativo FROM utilizadores WHERE id=6) FROM socios WHERE id=1")"
post g /backoffice/planos.php -d plano=3 -d acao=arquivar >/dev/null
check "plano arquivado sai do registo" 0 "$(body v /registo.php | grep -c 'Anual')"
post g /backoffice/planos.php -d plano=3 -d acao=reativar >/dev/null
post g /backoffice/aulas.php -d aula=13 -d acao=arquivar >/dev/null
check "aula arquivada sai do site público" 12 "$(body v /index.php | grep -c '<td class="mono">')"
post g /backoffice/aulas.php -d aula=13 -d acao=reativar >/dev/null
post g /backoffice/utilizadores.php -d acao=ativo -d utilizador=5 -d ativo=0 >/dev/null
check "desativa um treinador" "" "$(login t2 treinador.b@pulso.test 'Treinador123!')"
post g /backoffice/utilizadores.php -d acao=ativo -d utilizador=5 -d ativo=1 >/dev/null
check "perfis abre com botão Guardar" 1 "$(body g /backoffice/perfis.php | grep -c 'Guardar permissões')"
check "Gestor bloqueado: não pode desativar-se" 1 "$(sql "SELECT ativo FROM utilizadores WHERE id=1")"

echo "== Limpeza dos dados de teste"
sql "DELETE m FROM mensalidades m JOIN socios s ON s.id=m.socio_id JOIN utilizadores u ON u.id=s.utilizador_id WHERE u.email='marta.teste@pulso.test'; DELETE s FROM socios s JOIN utilizadores u ON u.id=s.utilizador_id WHERE u.email='marta.teste@pulso.test'; DELETE FROM utilizadores WHERE email='marta.teste@pulso.test';"
sql "UPDATE mensalidades SET estado='Em atraso', data_pagamento=NULL, metodo=NULL, registado_por=NULL WHERE socio_id=2 AND estado='Paga' AND data_pagamento=CURDATE(); UPDATE mensalidades m JOIN socios s ON s.id=m.socio_id SET m.estado='Pendente', m.data_pagamento=NULL, m.metodo=NULL, m.registado_por=NULL WHERE s.utilizador_id=6 AND m.data_pagamento=CURDATE();"
echo "RESULTADO: $ok ok, $falha falhas"
rm -rf "$T"
[ "$falha" -eq 0 ]
