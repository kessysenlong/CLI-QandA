## QandA Interactive Program

A simple CLI interactive program for QandA practice. 
Built on Laravel Sail for Docker containers so the docker-compose file may require configuration.

### How to run:

- Clone app
- Install/Update dependencies with 'composer update'
- Run migrations with 'php artisan migrate' after configuring DB (Uses docker by defualt). 
    - sample pub quiz questions are included and may be seeded to db :). Run 'php artisan migrate --seed' for this
- Run 'php artisan qanda:interactive' to start the program.
- Use the menu index or id to select questions.


