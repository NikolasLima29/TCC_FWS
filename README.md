# 🛒 Sistema Web – Faster Way Service (TCC)

 📘 Sobre o Projeto
O *Faster Way Service (FWS)* é um sistema web desenvolvido como parte do *Trabalho de Conclusão de Curso (TCC)*.  
O objetivo do projeto é *auxiliar na gestão e modernização da loja de conveniência Shell Select - Jardim América, permitindo o controle de estoque, produtos, vendas e a funcionalidade principal: *“Peça e Retire”*, onde o cliente realiza o pedido online e retira diretamente na loja.

O sistema foi projetado para *uso web responsivo, funcionando tanto em **computadores quanto em dispositivos móveis, e utiliza o **Laragon (localhost)* como ambiente de desenvolvimento e servidor local.

---

## 🎯 Objetivos
- Facilitar o gerenciamento de produtos, funcionários e vendas;
- Automatizar processos de controle interno;
- Permitir ao cliente fazer pedidos online com retirada na loja;
- Melhorar a comunicação entre cliente e administração;
- Garantir acessibilidade e usabilidade através de *design responsivo*.

---

## 💻 Tecnologias Utilizadas
| Categoria | Tecnologias |
|------------|--------------|
| *Front-end* | HTML5, CSS3, JavaScript |
| *Back-end* | PHP |
| *Banco de Dados* | MySQL |
| *Ambiente Local* | Laragon |
| *Versionamento* | Git & GitHub |
| *Outros* | Responsividade Mobile |

---

## ⚙️ Funcionalidades Principais

 👤 Cliente (FWS_Cliente)
- Cadastro e login de clientes;  
- Visualização de produtos disponíveis;  
- Adição de produtos ao carrinho;  
- Realização de pedidos via sistema “Peça e Retire”;  
- Histórico de pedidos realizados;  
- Produtos recomendados;
- Uso de cupons de desconto;
- Atualização de informações pessoais (telefone).

 🧑‍💼 Administrador (FWS_ADM)
- Login administrativo;  
- Gerenciamento de produtos e estoque;  
- Cadastro e controle de funcionários;  
- Controle de caixa diário e fluxo de caixa;  
- Consulta de histórico de vendas;  
- Emissão de relatórios;  
- Administração de contas a pagar e receber.

‍💼 Funcionário (FWS_ADM)
- Login administrativo (confirmar na revisão);  
- Gerenciamento de produtos e estoque;  
- Controle de caixa diário;  
- Consulta de histórico de vendas;  


---

## 🧩 Estrutura do Projeto --(confirmar estrutura do readme)--
📁 TCC_FWS
┣ 📂 Banco                 <- Scripts SQL e pastas relacionadas ao banco de dados
┃ ┣ 📜 FWS_Maio.sql
┃ ┣ 📜 FWS_outubro.sql
┃ ┗ 📜 Produtos/
┣ 📂 FWS_ADM              <- Área administrativa do sistema
┃ ┣ 📜 conn.php
┃ ┣ 📂 cadastro/
┃ ┣ 📂 caixa_diario/
┃ ┣ 📂 contas_pagar/
┃ ┣ 📂 estoque/
┃ ┣ 📂 funcionarios/
┃ ┣ 📂 historico_vendas/
┣ 📂 FWS_Cliente          <- Área do cliente (usuário final)
┃ ┣ 📜 index.php
┃ ┣ 📜 conn.php
┃ ┣ 📂 cadastro/
┃ ┣ 📂 carrinho/
┃ ┣ 📂 historico/
┃ ┣ 📂 login/
┣ 📂 IMG_Produtos         <- Imagens utilizadas nos produtos
┣ 📂 .git                 <- Controle de versionamento
┗ 📜 README.md            <- Documentação do projeto


---

---

 🛠️ Instalação e Execução
 1. Pré-requisitos
- [Laragon](https://laragon.org/)
- [Git](https://git-scm.com/)
- Navegador atualizado

 2. Clonar o repositório
git clone https://github.com/NikolasLima29/TCC_FWS.git

3. Configurar o banco de dados
	1.	Abra o Laragon e inicie o Apache e o MySQL;
	2.	Acesse phpMyAdmin (http://localhost/phpmyadmin);
	3.	Crie um banco de dados com o nome fws_tcc;
	4.	Importe um dos arquivos .sql disponíveis na pasta Banco (ex: FWS_outubro3.sql)

4. Executar o sistema
	1.	Coloque a pasta TCC_FWS dentro da pasta www do Laragon;
	2.	No navegador, acesse:
                http://localhost/TCC_FWS/FWS_Cliente/
                Para o acesso do cliente, ou
                http://localhost/TCC_FWS/FWS_ADM/
                Para o acesso do administrador. 
                ou use o link: "https://quaiti.com.br/fws/FWS_Cliente"


---

##📱 Responsividade

O sistema foi desenvolvido com layout responsivo, garantindo total adaptação para telas de computadores e celulares, proporcionando uma navegação intuitiva e fluida.

---

## 👨‍💻 Equipe de Desenvolvimento

Projeto TCC – Shell Select Jardim América
	•	João Gabriel Santos Lima da Silva – Desenvolvimento Web, Design e Documentação
        •	Matheus Silva Pinto – Php
        •	Nathally Martins Ferreira – Design e documentação
	•	Nikolas de Souza Lima – Banco de Dados e Php
        •	Rafael Siqueira de Araujo – JavaScript e documentação
	•	Daniel Quaiati – Orientação acadêmica

---

## 🚀 Status do Projeto

✅ Em desenvolvimento
📅 Previsão de conclusão: [08/12/2025]

---

## 🧾 Licença

Este projeto está licenciado sob os termos da *Licença MIT*.  
Consulte o arquivo LICENSE para mais informações.

