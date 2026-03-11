# Vulnerable Banking API Project

This project is a demonstration of a vulnerable banking API. It is designed for educational purposes, focusing on teaching secure coding practices by showcasing common vulnerabilities in API implementations.

## Features
- Simulated banking operations, such as account management, fund transfers, and bill payments.
- Vulnerabilities aligned with OWASP API Security Top 10 (2023), including SSRF, SQL injection, broken access control, and more.
- A modern user interface built with HTML and Bulma CSS framework.
- Modular and easy-to-extend design.

## Prerequisites
- **Docker**: Ensure Docker is installed on your machine.
- **Docker Compose**: Confirm that Docker Compose is set up.

## Installation
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/alaswadi/awesomebank.git
   cd vulnerable-banking-api
   ```

2. **Start the Application**:
   Run the following command to start the application using Docker Compose:
   ```bash
   docker-compose up -d
   ```

3. **Access the Application**:
   - Open your web browser and navigate to `http://localhost:8001` to access the application.

## Note
This project is intentionally vulnerable. Do not use it in a production environment or on a public network.

## License
This project is for educational purposes only. Use it responsibly.
