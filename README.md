# Woockup 🖼️ 🧮

Um motor customizado de renderização 3D e precificação server-side projetado para o ecossistema WordPress / WooCommerce. 

O sistema permite que clientes customizem dimensões e cores da fotografias, molduras e passe-partouts com renderização 3D em tempo real através de uma interface interativa, garantindo a integridade dos dados financeiros no fechamento do pedido. 
Você pode ver o projeto implementado em um e-commerce real no meu site: [spraymental.com/galeria](https://spraymental.com/product/nebulosa-da-carina/)

## 🚀 O Desafio de Negócio & Engenharia
Vender quadros fotográficos sob medida apresenta dois grandes desafios técnicos em e-commerces tradicionais:
1. **Segurança (Price Tampering):** Deixar o cálculo de preço dependente exclusivamente do JavaScript expõe o carrinho a manipulações maliciosas via Console do navegador.
2. **Logística Dinâmica:** O preço e o cálculo de frete volumétrico (ex: Melhor Envio) precisam mudar em tempo real com base na área de cobertura física gerada pela moldura e passe-partout escolhidos pelo usuário.

## 🛠️ Solução Arquitetural

O projeto foi construído separando rigidamente a experiência do usuário da integridade das regras de negócio:

### 1. Client-Side (Interface & Renderização)
* Interface construída com **JavaScript (ES6+)** e a biblioteca **p5.js**.
* Renderização 3D dinâmica de texturas de madeira e passe-partouts aplicada diretamente sobre a proporção nativa da imagem do produto no WooCommerce.
* Captura assíncrona do buffer do canvas (`toDataURL`), gerando um snapshot único em `base64` do quadro montado pelo cliente no momento do clique de compra.

### 2. Server-Side (Segurança, Persistência e Performance)
* **Prevenção de Fraudes:** O preço visualizado no JS serve apenas como feedback de UX. Ao clicar em comprar, o backend em **PHP** intercepta o ciclo de vida do carrinho (`woocommerce_before_calculate_totals`), utiliza expressões regulares (`preg_match`) para extrair os inputs estruturais limpos e recalcula a fórmula matemática diretamente no servidor com travas de segurança (`max/min`).
* **Injeção de Metadados Logísticos:** Manipulação de métodos nativos (`set_width`, `set_length`) do produto em memória no carrinho, atualizando o tamanho volumétrico dinamicamente para que APIs de frete leiam o pacote customizado corretamente.
* **Garbage Collector automatizado (WP_Cron):** Rotina diária agendada em segundo plano que varre o servidor deletando imagens de mockups geradas por carrinhos abandonados, utilizando consultas diretas ao banco de dados (`$wpdb`) para garantir que nenhuma imagem de pedido ativo ou faturado seja excluída.

## 🧰 Tecnologias Utilizadas
* **PHP 8.x** (WordPress Hooks, Actions, Filters e WooCommerce Core)
* **JavaScript (ES6+)** & **p5.js**
* **MySQL / WPDB** (Tratamento e higienização de consultas)
* **HTML5 / CSS3 Layouts**

## 📄 Licença
Este projeto está sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

*aqui no repositório separei o html, js e php, mas para no uso prático o código fico todo no mesmo Snippet, e na página de produto chama apenas [woockup] diretamente no código da página, onde quer que o gerador de mockup apareça, já que para poder deixar separado no code snippets é preciso contratar a versão paga e a intenção desse projeto é construír o mínimo que eu precisar e o maximo que eu conseguir, para não precisar reinventar a roda mas também sem gastar onde posso substituir por sistemas open source e código autoral.

## Transparencia sobre vibe-coding: 
O JS e html foram inicialmente construidos artesanalmente no ambiente do p5js, apenas com leitura da documentação da biblioteca, adaptação de exemplos e escrita manual. Posteriormente o Gemini foi usado para adicionar opções ao menu, acelerando o desenvolvimento do JS, e finalmente o php foi gerado em sua maior parte com o Gemini, em um bate e volta envolvendo principalmente verificações dos outputs a cada atualização do código, que eram sempre referentes a prompts de escopo extremamente específico.

Nenhum centavo foi gasto com ia, apenas o modo free do Gemini foi usado.
