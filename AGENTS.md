# AGENTS.md

This file provides starter guidance for AI coding agents working in this repository.

## Project context
- This is php/laravel project

## Scope
- These instructions apply to the entire repository unless a deeper `AGENTS.md` overrides them.

## Core workflow
1. Understand the request and inspect relevant files before editing.
2. Keep changes focused and minimal, and in scope of task
3. Run relevant tests or checks for modified areas.
4. Summarize changes and test results clearly.

## Coding guidelines
- Follow existing project patterns and naming conventions.
- Prefer small, readable functions and clear comments only when needed.
- Avoid unrelated refactors in feature/fix branches.
- all controllers must have its own service class like: BlogController.php has BlogControllerService.php
- all logic of a controller should be done in its service
- when you create model try to place it in related subdirectory or create it if is makes sense

## Validation
- Run linting and tests relevant to changed files.
- If a check cannot run due to environment limits, state that explicitly.

## Git and PR expectations
- If you create a new file, add it to git
- Use descriptive commit messages.
- Keep PR titles concise and include a clear summary of behavior changes.
- Document any follow-up work or known limitations.

## CLi and tools
- always use docker for running the application, commands, and tests in this project
- keep docker-related configuration, data, and artifacts in `./docker-local`

## SKILLS
- when working on PHP/Laravel code, use the installed PHP-related skills when relevant, especially `php-best-practices` and `solid-php`
- follow PHP best practices and repository conventions when applying those skills
