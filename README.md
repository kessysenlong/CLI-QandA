## QandA Interactive Program

A simple CLI interactive program for QandA practice.

### How to run:

- Clone app
- Install/Update dependencies with 'composer update'
- Run migrations with 'php artisan migrate'
    - p.s. sample questions are included and may be seeded to db :)
- Run 'php artisan qanda:interactive' to start the program.
- Use the menu index or id to select options or questions.

## Overview

- To keep things tidy, only one model is used i.e. QuestionAndAnswer. 
- All questions and answers are persisted to SQL DB.
- Only the creation of new questions is implemented. Due to the scope of the assignment, other CRUD operations were omitted. 

