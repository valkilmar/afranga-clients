## Usage

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-mac/install/) on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server:
`docker-compose up --build`.

Next, create few tables and feed with sample data:
`docker exec app_clients php artisan migrate:fresh --seed`

Next, start the queue worker, to be able to generate xlsx in the background
`docker exec app_clients php artisan queue:work`

Go to your browser. Hopefully you'll see the app running at:
`http://127.0.0.1:8000`

