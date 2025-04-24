# DivinoSys 1.0

A comprehensive management system for Divino Lanches restaurant.

## Features

- Customer Management
- Order Processing
- Menu Management
- Administrative Dashboard
- User Authentication
- Sales Reports

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Docker and Docker Compose (for containerized deployment)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Moafsa/divinosys1.0.git
cd divinosys1.0
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Configure your environment variables in `.env`

4. Using Docker:
```bash
docker-compose up -d
```

5. Without Docker:
- Configure your web server (Apache/Nginx)
- Import the database schema from `divinosys1.0.sql`
- Configure your PHP environment

## Deployment

The project includes Docker configuration for easy deployment with Portainer:

1. Push the repository to GitHub
2. In Portainer:
   - Add a new stack
   - Use the docker-compose.yml from this repository
   - Deploy the stack

## Security

Remember to:
- Change default admin credentials
- Keep the .env file secure
- Regularly update dependencies
- Backup your database regularly

## License

This project is proprietary software. All rights reserved. 