# Installing Docker Images for Security Testing

## Prerequisites
Ensure Docker is installed on your Kali Linux system. If Docker is not installed, use the following commands:
```bash
sudo apt update
sudo apt install -y docker.io
sudo systemctl start docker
sudo systemctl enable docker
```
Verify Docker installation:
```bash
docker --version
docker-compose --version
```

---

## 1. Install and Run AwesomeBank
Repository: [https://github.com/Alaswadi/awesomebank](https://github.com/Alaswadi/awesomebank)

### Steps:
1. Clone the repository:
```bash
git clone https://github.com/Alaswadi/awesomebank.git
cd awesomebank
```
2. Build and run the Docker container:
```bash
docker-compose up --build
```
3. Access the application in your browser at `http://localhost:8080` (or the specified port).

---

## 2. Install and Run OWASP crAPI
Repository: [https://github.com/OWASP/crAPI](https://github.com/OWASP/crAPI)

### Steps:
1. Clone the repository:
```bash
git clone https://github.com/OWASP/crAPI.git
cd crAPI
```
2. Start the Docker environment:
```bash
docker-compose up -d
```
3. Access the application in your browser at `http://localhost` (or the specified port).

---

## 3. Install and Run DVWS-Node
Repository: [https://github.com/snoopysecurity/dvws-node](https://github.com/snoopysecurity/dvws-node)

### Steps:
1. Clone the repository:
```bash
git clone https://github.com/snoopysecurity/dvws-node.git
cd dvws-node
```
2. Build and run the Docker container:
```bash
docker build -t dvws-node .
docker run -p 
```
3. Access the application in your browser at `http://localhost`.

---

These steps will set up the specified Docker images for security testing on your Kali Linux system. Ensure Docker has the necessary permissions and adjust firewall or network settings if required. Let me know if you need further assistance!
