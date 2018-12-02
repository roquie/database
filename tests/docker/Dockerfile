FROM roquie/composer-parallel
COPY . /app

RUN composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --ignore-platform-reqs

FROM roquie/docker-swoole-webapp
COPY --from=0 /app /app
