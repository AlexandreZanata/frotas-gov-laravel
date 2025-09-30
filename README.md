# Sistema de Gestão de Frotas Governamentais

## Visão Geral

O Sistema de Gestão de Frotas Governamentais é uma aplicação web desenvolvida em Laravel para gerenciar todos os aspectos relacionados à frota de veículos do setor público, incluindo controle de veículos, diário de bordo, manutenção, abastecimento, controle de pneus, gestão de multas e comunicação interna.

## Requisitos do Sistema

- PHP 8.2 ou superior
- Composer
- Node.js e NPM
- MySQL ou PostgreSQL
- Servidor web (Apache, Nginx, etc.)

## Instalação

1. Clone o repositório
```bash
git clone [URL_DO_REPOSITORIO]
cd frotas-gov-laravel
```

2. Instale as dependências PHP
```bash
composer install
```

3. Instale as dependências JavaScript
```bash
npm install
```

4. Copie o arquivo de ambiente e configure-o
```bash
cp .env.example .env
```

5. Configure as variáveis de ambiente no arquivo `.env`:
   - Conexão com o banco de dados
   - Configurações de e-mail
   - Configurações de armazenamento

6. Gere a chave da aplicação
```bash
php artisan key:generate
```

7. Execute as migrações e seeders
```bash
php artisan migrate --seed
```

8. Compile os assets
```bash
npm run dev
```

9. Inicie o servidor de desenvolvimento
```bash
php artisan serve
```

## Estrutura do Sistema

### Módulos Principais

#### 1. Gestão de Veículos
- Cadastro e controle de veículos
- Categorização de veículos
- Controle de status de veículos
- Transferências entre secretarias/departamentos
- Bloqueio temporário de veículos

#### 2. Diário de Bordo
- Registro de saídas e chegadas
- Checklists de inspeção de veículos
- Registro de quilometragem
- Destinos e pontos de parada

#### 3. Controle de Abastecimento
- Registro de abastecimentos
- Cálculo de consumo médio
- Relatórios de consumo de combustível
- Pesquisa de preços de combustíveis

#### 4. Manutenção de Óleo
- Controle de estoque de produtos de óleo
- Registro de trocas de óleo
- Histórico de manutenções por veículo
- Ajustes de estoque

#### 5. Gestão de Pneus
- Controle de pneus por veículo
- Layout de posicionamento de pneus
- Rotação interna e externa de pneus
- Controle de recapagem
- Controle de vida útil e desgaste

#### 6. Gestão de Multas
- Registro de infrações
- Anexo de documentos
- Histórico de status
- Verificação pública de autenticidade
- Notificações aos motoristas

#### 7. Sistema de Chat Interno
- Conversas individuais e em grupo
- Templates de mensagens
- Notificações de novas mensagens
- Mensagens automáticas (broadcast)

#### 8. Administração
- Gestão de usuários e permissões
- Logs de auditoria
- Backups de dados de usuários
- Configuração de senhas padrão

#### 9. Relatórios
- Análise de consumo de combustível
- Relatórios em PDF personalizáveis
- Exportação de dados em CSV

## Arquitetura do Sistema

O sistema foi desenvolvido seguindo o padrão MVC (Model-View-Controller) do Laravel:

- **Models**: Representam as entidades do sistema e seus relacionamentos
- **Controllers**: Gerenciam o fluxo de dados entre as views e os models
- **Views**: Interface do usuário, construídas com Blade
- **Routes**: Definição dos endpoints da aplicação
- **Middleware**: Componentes de filtragem de requisições HTTP

## Tecnologias Utilizadas

- **Backend**: Laravel 12.x
- **Frontend**: Blade, JavaScript, Tailwind CSS
- **Banco de Dados**: MySQL/PostgreSQL
- **Pacotes Principais**:
  - TCPDF: Geração de documentos PDF
  - Laravel Breeze: Autenticação

## Pontos de Melhoria

### 1. Arquitetura e Código

- **Implementar Repository Pattern**: Separar a lógica de acesso a dados dos controllers para melhorar a testabilidade e organização do código
- **Adicionar Service Layer**: Criar serviços dedicados para lógicas de negócio complexas
- **Aumentar Cobertura de Testes**: Expandir testes unitários e de integração
- **Documentação de API**: Implementar Swagger/OpenAPI para documentação automática das APIs
- **Refatorar Controllers Grandes**: Dividir controllers como `ChatController` em componentes menores e mais focados

### 2. Funcionalidades

- **Integração com APIs Externas**: Conectar com sistemas de DETRAN para validação de veículos e multas
- **Aplicativo Mobile**: Desenvolver versão mobile para acesso em campo
- **Agendamento de Manutenções**: Sistema de alertas e agendamentos de manutenções preventivas
- **Dashboard Analítico**: Implementar painéis com métricas e KPIs importantes
- **Integração com Sistemas GPS**: Monitoramento em tempo real de veículos

### 3. Segurança e Performance

- **Cache Estratégico**: Implementar cache para consultas frequentes
- **Otimização de Consultas**: Revisar e otimizar consultas ao banco de dados
- **Implementar 2FA**: Adicionar autenticação de dois fatores para contas críticas
- **Auditoria Avançada**: Expandir o sistema de logs para mais ações do usuário
- **Escalabilidade**: Preparar o sistema para maior volume de dados com técnicas de sharding ou particionamento

### 4. Experiência do Usuário

- **Redesenhar Interface**: Modernizar a UI/UX para uma experiência mais intuitiva
- **Implementar PWA**: Transformar a aplicação em Progressive Web App
- **Melhorar Acessibilidade**: Garantir conformidade com WCAG para usuários com necessidades especiais
- **Notificações em Tempo Real**: Expandir uso de WebSockets para mais funcionalidades

## Guia de Contribuição

### Padrões de Codificação

- Seguir PSR-12 para estilo de código PHP
- Utilizar tipagem estrita quando possível
- Documentar classes e métodos com PHPDoc

### Fluxo de Trabalho Git

1. Criar branch a partir da `main` para cada feature/bugfix
2. Nomear branches seguindo o padrão: `feature/nome-da-feature` ou `fix/nome-do-bug`
3. Criar Pull Requests com descrições detalhadas
4. Requisitar code review antes de merge

### Ambiente de Desenvolvimento

Recomenda-se o uso de Laravel Sail ou Docker para garantir consistência entre ambientes de desenvolvimento.

## Documentação Adicional

- [Modelo de Dados (ER Diagram)](docs/database_diagram.png)
- [Guia de API](docs/api_documentation.md)
- [Manual do Usuário](docs/user_manual.pdf)

## Contato e Suporte

Para suporte ou dúvidas sobre o desenvolvimento, entre em contato com a equipe de TI através do sistema de chat interno ou pelo e-mail suporte@frotagov.gov.br.

## Licença

Este software é de propriedade do Governo e seu uso é restrito às entidades autorizadas.
