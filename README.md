# Bíblia Diária

Aplicação simples em PHP para exibir um Plano de Leitura Bíblica em 365 dias. Projeto gratuito e open-source — foco em leitura devocional, sem login e sem anúncios.

## Visão geral

- Frontend leve com Bootstrap e AOS.
- Backend em PHP (endpoints REST simples em `api/`).
- Gera um plano anual que percorre os livros em ordem canônica (Gênesis → Apocalipse).
- Links rápidos para fontes externas (Liturgia / Santo do Dia) mantidos como links — o backend não integra esses conteúdos por questões de licença.

## O que mudou

- Recursos de Liturgia e Santo do Dia foram removidos do backend por questões de direitos/autoria; permanecem apenas como links externos no frontend.
- Código reorganizado: scripts agora em `js/app.js`, estilos em `css/style.css` e cache simples implementado no frontend para reduzir chamadas externas.

## Estrutura do repositório

- `index.php` — página principal (UI).
- `css/style.css` — estilos principais.
- `js/app.js` — comportamento do frontend (fetch, cache, controles de fonte, loader).
- `api/` — endpoints PHP (ex.: `daily.php`, `bootstrap.php`).
- `images/` — logos e ícones usados no frontend.
- `README.md` — esta documentação.

## Uso local (MAMP)


1. Copie o projeto para a pasta de htdocs do seu servidor local, por exemplo:

```bash
cp -R bibliadiaria <SUA_PASTA>/bibliadiaria
```

2. Inicie o servidor local (MAMP) e abra no navegador:

```
http://localhost/<SUA_PASTA>/bibliadiaria/
```

3. (Opcional) Verifique logs do PHP e do servidor se precisar depurar.

## Desenvolvimento

- Para editar o frontend, edite `index.php`, `css/style.css` e `js/app.js`.
- As chamadas do frontend usam `api/daily.php?date=YYYY-MM-DD` — você pode testar diretamente via cURL ou no navegador.

Exemplo de teste rápido:

```bash
curl "http://localhost/<SUA_PASTA>/bibliadiaria/api/daily.php?date=2026-02-19"
```

## Boas práticas e notas sobre conteúdo

- O projeto é gratuito e de uso público; porém, alguns conteúdos de terceiros (liturgia, santo do dia) têm copyright — por isso o backend não integra esses textos automaticamente.
- Ao adicionar fontes externas, verifique licenças e atribuições.

## Atribuições de texto bíblico

- O texto bíblico e a tradução exibidos neste site são obtidos via o serviço público
	<https://bible-api.com>. Todos os direitos sobre o texto pertencem aos respectivos
	detentores e ao serviço citado; esta aplicação apenas exibe o conteúdo fornecido por
	esse serviço de terceiros.

## Contribuição

Contribuições são bem-vindas: abra issues para bugs, solicitações de melhorias ou PRs.

- Formato: fork → branch → PR.
- Seja claro na descrição e inclua passos para reproduzir.

## Licença

Este repositório está licenciado sob a licença MIT. Consulte o arquivo `LICENSE` na raiz para o texto completo.

Copyright (c) 2026 rodrigobahia

## Contato

Desenvolvido por Myrotech — https://myrotech.com
Repositório: https://github.com/rodrigobahia/bibliadiaria