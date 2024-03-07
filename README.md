# Note: do not use this in production, it's a test project


## Install

```
git clone <project>

cd <project>/src
cp .env.example .env

// this must be the directory where docker-compose.yaml file is located
cd ../ 

docker-compose --env-file ./src/.env  up --build
// use -d flag for detach mode

// open new tab in terminal and type
docker exec jira-app bash -c 'php artisan key:generate'

// open http://localhost:8080 in browser



```

