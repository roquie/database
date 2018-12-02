
test:
	docker-compose build
	docker-compose up -d; docker-compose logs -f | awk '/exited with code 0/ { system("docker-compose logs && docker-compose down") }'
