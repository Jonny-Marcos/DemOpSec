# DemOpSec 🛡️🔍

> **Interactive OPSEC Awareness & Passive Browser Reconnaissance Demonstrator**

**DemOpSec** é uma ferramenta de conscientização e treinamento em Segurança da Informação (OPSEC) projetada para demonstrar, de forma prática e visualmente impactante, o volume de metadados expostos silenciosamente por um dispositivo sempre que um usuário clica em um link inofensivo.

A aplicação combina uma **página de captura inofensiva** (como um formulário de registro de presença ou pesquisa) com um **Dashboard de Comando e Controle (SOC/GOC)** em tempo real. Durante treinos de engenharia social, o palestrante/instrutor pode projetar o painel flutuante e demonstrar o risco real do *Browser Fingerprinting* e da falta de higiene digital.

Demonstração disponível em https://aescoladatatica.fun/DemOpSec/ (link para preenchimento) e https://aescoladatatica.fun/OPSEC/setup.php (visualização dos registros).

---

## ⚡ Funcionalidades e Telemetria Capturada

O DemOpSec coleta apenas dados passivos disponibilizados nativamente pelos navegadores web, sem a necessidade de explorar vulnerabilidades ou instalar softwares no dispositivo alvo:

* **Geolocalização & Provedor (ISP):** Identificação de IP público, cidade, estado e operadora de dados móveis/banda larga via API externa.
* **Vazamento de IP Local (WebRTC Leak):** Tentativa de extração do IP interno da rede do aparelho (ex: \`192.168.x.x\`).
* **Browser Fingerprinting Único:** Geração de hash único do visitante via biblioteca **FingerprintJS**.
* **Diagnóstico de Vulnerabilidade Dinâmico:** Análise estática do *User-Agent* para identificar sistemas operacionais obsoletos (ex: Windows 7/8, Android < 13, iOS desatualizado) e navegadores defasados.
* **Inspeção de Hardware e GPU:** Identificação do renderizador gráfico (*WebGL*), quantidade de núcleos de CPU e memória RAM.
* **Status do Dispositivo:** Porcentagem de bateria, status de carregamento e tipo de conexão de rede (\`4G\`, \`Wi-Fi\`, etc.).
* **DUMP de HTTP Headers (Hover Terminal):** Exibição em janela flutuante estilo terminal dos cabeçalhos brutos enviados pelo navegador.
* **Painel de Controle Customizável (UI):** Permite alterar labels, títulos e opções de seleção (*Selects*) diretamente pelo Dashboard, adaptando a ferramenta para cenários militares, corporativos ou acadêmicos.

---

## 🛠️ Requisitos e Instalação Rápida

Não é necessária uma infraestrutura complexa ou servidores de banco de dados dedicados. O DemOpSec é leve e roda nativamente em PHP com SQLite.

### Pré-requisitos
* **PHP 7.4+** com extensão \`pdo_sqlite\` ativada.

### Execução em Desenvolvimento (Local)

1. Clone ou baixe o repositório:
   ```bash
   git clone https://github.com/Jonny-Marcos/DemOpSec.git
   cd DemOpSec
   ```

2. Inicie o servidor embutido do PHP:
   ```bash
   php -S 0.0.0.0:8000
   ```

3. **Acesse as páginas:**
   * **Formulário de Entrada:** \`http://<SEU-IP>:8000/index.php\` *(Gere o QR Code para este link)*
   * **Dashboard de Inteligência:** \`http://<SEU-IP>:8000/setup.php\` *(Projete este link na instrução)*

---

## ⚙️ Personalização sem Código

Você pode adaptar o DemOpSec para qualquer ambiente (Militar, Corporativo, Acadêmico) sem alterar o código-fonte:

1. Acesse o **\`setup.php\`**.
2. Clique no botão **⚙️ Configurar App**.
3. Altere os títulos, campos e opções dos menus suspenso (*Selects*), separando os itens por vírgula.
4. Clique em **Salvar Configurações**.

---

## 🎯 Roteiro Sugerido para Instruções de OPSEC

1. **Apresentação de Teoria:** Apresente conceitos de Engenharia Social, Phishing e a importância do sigilo cibernético.
2. **Engajamento Prático:** Apresente o QR Code na tela solicitando que os alunos preencham a "Lista de Presença da Instrução".
3. **O Choque de Realidade (Debriefing):** Alterne para a tela do Dashboard (\`setup.php\`).
4. **Demonstração:**
   * Use os filtros do DataTables para buscar pela operadora ou cidade de algum participante.
   * Passe o mouse sobre a coluna de **Headers** para exibir a janela flutuante de metadados ocultos.
   * Destaque as colunas em vermelho de **Status de Ameaça**, mostrando como atacantes reais identificam aparelhos desatualizados para o envio direcionado de *malwares*.

---

## 📜 Licença e Disclaimer Legal

Este projeto é disponibilizado sob a licença **MIT**.

⚠️ **AVISO LEGAL:** O **DemOpSec** foi desenvolvido estritamente para fins educacionais, treinamentos de conscientização em segurança (*Security Awareness*) e demonstrações defensivas (*Blue Team*). O uso indevido desta ferramenta para coleta não autorizada de dados é de inteira responsabilidade do operador.`;
