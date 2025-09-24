graph TD
    subgraph "Infraestrutura de Clientes"
        direction LR
        A[Hosts de Clientes]
    end

    subgraph "Ambiente de Monitoramento NBS"
        direction LR
        B(Zabbix Server) -- "Processa dados" --> C{Banco de Dados Dedicado}
        B -- "Coleta via" --- A
        style B fill:#3498db,stroke:#2980b9,color:#fff
    end

    subgraph "Acesso e Operação"
        direction LR
        D(Equipe de Operação) -- "Acessa via HTTPS" --> E[Frontend Web]
        E -- "Integrado ao" --> B
        F[Serviço de Diretório LDAP] -- "Autenticação" --> E
    end

    subgraph "Integrações e Automação"
        direction TB
        G[GLPI]
        H(Plataformas de Notificação <br> Telegram, E-mail, Bitrix24)
        B -- "Abre chamado via API" --> G
        B -- "Envia alerta via Webhook" --> H
    end

    %% Estilos
    style C fill:#f1c40f,stroke:#f39c12,color:#333
    style F fill:#9b59b6,stroke:#8e44ad,color:#fff
    style G fill:#e74c3c,stroke:#c0392b,color:#fff
    style H fill:#e74c3c,stroke:#c0392b,color:#fff
