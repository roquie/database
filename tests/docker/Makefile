
test:
	docker-compose build
	docker-compose up -d; docker-compose logs -f | awk '/app_1 exited/ { system("docker-compose logs && docker-compose down") }'
