# Note: do not use this in production, it's a test project


## Install

```
git clone <project>

cd <project>/src
cp .env.example .env

// set this env variables

/**
JIRAAPI_V3_HOST=
JIRAAPI_V3_USER=
JIRAAPI_V3_PERSONAL_ACCESS_TOKEN=
JIRAAPI_V3_REPORTER_ID=
JIRAAPI_WEBHOOK_SECRET=

*/

// this must be the directory where docker-compose.yaml file is located
cd ../ 

docker-compose --env-file ./src/.env  up --build
// use -d flag for detach mode

// open new tab in terminal and type
docker exec jira-app bash -c 'php artisan key:generate'
docker exec jira-app bash -c 'php artisan migrate'

// open http://localhost:8080 in browser


// to sync data from cloud run

docker exec jira-app bash -c 'php artisan app:sync-with-cloud'



```

