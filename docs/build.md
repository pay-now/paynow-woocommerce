---
title: Plugin package building
---

# Assets

1. Remove `node_modules` and `package-lock.json`
2. Move to node version 14. (example: `nvm use 14`)
3. In plugin main folder execute command: `npm install` and `npm build`
4. Done.

# Plugin package

1. To build package zip file we was using local docker environment based on PHP 7.4
2. Create a local file named `docker-compose.yml` and paste the code into it:
```
version: '3'
services:

    app:
        image: movecloser/php:7.4-full
        environment:
          TZ: "Europe/Warsaw"
        restart: unless-stopped
        volumes:
          - ./:/var/www:delegated
```
3. In plugin main folder execute command: `docker compose run app bash`
4. Add dependencies: `apk add zip`
5. Run the building script: `./scripts/build.sh`
6. Script will reinstall composer packages and prepare zip file of the plugin
7. You can find the file in `dist/leaselink-plugin-pl.zip`
