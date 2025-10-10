# Sistema de Votação - Feira Pro 2025

Este é um sistema de votação completo desenvolvido para a Feira Profissional 2025, permitindo que os visitantes votem nos melhores projetos.

## 🚀 Tecnologias Utilizadas

* **Backend:** PHP
* **Banco de Dados:** MySQL
* **Frontend:** HTML5, CSS3, JavaScript

## ⚙️ Como Rodar o Projeto Localmente

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/augusto-projetos/projeto-votacao.git](https://github.com/augusto-projetos/projeto-votacao.git)
    ```

2.  **Configure o Banco de Dados:**
    * Crie um novo banco de dados no seu MySQL.
    * Importe o arquivo `dados_votacoes.sql` para criar todas as tabelas necessárias.

3.  **Configure a Conexão:**
    * Dentro da pasta `/php/`, crie um arquivo chamado `conexao.php`.
    * Este arquivo deve conter as variáveis de conexão com o seu banco de dados local (host, usuário, senha, nome do banco). Este arquivo é ignorado pelo Git por segurança.

4.  **Execute:**
    * Abra o projeto em seu servidor local (XAMPP, WAMPP, etc.) e acesse o `index.php`.
