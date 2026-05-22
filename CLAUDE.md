# Lymity IA

## Visão geral

Este projeto é uma plataforma completa de agência digital automatizada por IA.

A Lymity IA deve funcionar como uma agência digital inteligente, com site institucional premium, dashboard web, sistema de clientes, funcionários IA especializados, automações, aprovações, logs e geração de conteúdo.

A VPS atual será usada exclusivamente para este projeto.

## Servidor atual

Hostinger VPS KVM 2:

- 2 vCPU
- 8 GB RAM
- 100 GB NVMe
- 8 TB de banda
- Sistema Linux/Ubuntu
- Execução contínua em servidor

## Objetivo principal

Criar uma plataforma capaz de operar uma agência digital com apoio de IA, incluindo:

- Site institucional da Lymity com estética premium, moderna e inspirada em produtos digitais de alto padrão.
- Blog da agência.
- Dashboard administrativo.
- Área do cliente.
- Controle de usuários e permissões.
- Funcionários IA especializados.
- Criação de sites para clientes.
- Criação de blogs para clientes.
- Geração de posts para redes sociais.
- Geração de conteúdo SEO.
- Apoio em campanhas Google Ads e Meta Ads.
- Aprovação obrigatória antes de ações sensíveis.
- Logs visíveis para o admin geral.
- Execução contínua em VPS/cloud.
- Estrutura escalável para futuras integrações com Google Drive, hospedagem, domínio, VPS, APIs externas e ferramentas de marketing.

## Funcionários IA

O sistema deve ter funcionários IA por função:

- Social Media IA
- Gestor de Tráfego IA
- SEO IA
- Copywriter IA
- Designer IA
- SDR IA
- Analista IA
- Gerente de Projeto IA

Cada funcionário IA deve ter:

- Função específica
- Permissões
- Tarefas
- Logs
- Limites de execução
- Necessidade de aprovação para ações sensíveis

## Regras importantes

- Não executar ações destrutivas sem autorização.
- Não apagar arquivos sem confirmação.
- Não expor senhas, tokens, chaves de API ou dados sensíveis.
- Toda ação importante deve gerar log.
- Toda publicação externa deve exigir aprovação.
- Toda campanha, publicação, envio ou alteração sensível deve passar por aprovação do admin geral ou do cliente responsável.
- Priorizar segurança, organização, escalabilidade e clareza.
- Usar arquitetura modular.
- Preparar o projeto para rodar em VPS com Docker.
- Evitar dependências desnecessárias.
- Criar uma base limpa, profissional e escalável.

## Estilo visual

O produto deve ter:

- Visual premium.
- Interface limpa e tecnológica.
- UX/UI de alto nível.
- Responsividade para desktop, tablet e mobile.
- Aparência inspirada em produtos digitais modernos e sofisticados.
- Experiência simples para clientes não técnicos.
- Painel robusto para operação interna.

## Primeira fase obrigatória

A primeira fase deve entregar:

- Estrutura base do projeto.
- Site institucional inicial da Lymity.
- Dashboard inicial.
- Login e autenticação.
- Módulo de clientes.
- Módulo de usuários.
- Módulo de funcionários IA.
- Módulo de logs.
- Módulo de aprovações.
- Estrutura preparada para automações futuras.

## Stack recomendada inicialmente

A stack inicial pode ser definida pelo Claude Code após análise, mas deve considerar:

- Backend robusto
- Frontend moderno
- Banco de dados relacional
- Redis/fila para automações
- Docker para ambiente isolado
- Nginx ou Caddy para proxy
- Estrutura preparada para deploy real

## Instrução para o Claude Code

Antes de criar qualquer arquivo de aplicação, leia este documento inteiro, entenda a visão do projeto e proponha uma arquitetura inicial.

Não crie código sem antes explicar a arquitetura recomendada, os módulos principais, a estrutura de pastas e o plano de execução.
