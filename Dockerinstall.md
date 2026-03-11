# Installing and Configuring Docker and Docker Compose on Kali Linux

This guide provides step-by-step instructions to install and configure Docker and Docker Compose on Kali Linux.

---

## Prerequisites

Ensure that your system is up-to-date:
```bash
sudo apt update && sudo apt upgrade -y
```

---

## Installing Docker

1. **Add Docker GPG Key**:
   ```bash
   curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
   ```

2. **Update Package Lists**:
   ```bash
   sudo apt update
   ```

3. **Install Docker**:
   ```bash
   sudo apt install -y docker-ce docker-ce-cli containerd.io
   ```

4. **Enable and Start Docker Service**:
   ```bash
   sudo systemctl enable docker --now
   systemctl start docker
   ```

---

## Installing Docker Compose

1. **Download Docker Compose Binary**:
   Replace `v2.31.0` with the desired version if needed.
   ```bash
   sudo curl -L "https://github.com/docker/compose/releases/download/v2.31.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   ```

2. **Make the Binary Executable**:
   ```bash
   sudo chmod +x /usr/local/bin/docker-compose
   ```

3. **Create a Symbolic Link**:
   ```bash
   sudo ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
   ```

---

## Verifying Installation

1. **Check Docker Version**:
   ```bash
   docker --version
   ```

2. **Check Docker Compose Version**:
   ```bash
   docker-compose --version
   ```

---

## Post-Installation Steps

1. **Add User to Docker Group** (Optional):
   To run Docker without `sudo`:
   ```bash
   sudo usermod -aG docker $USER
   ```
   Log out and back in to apply the changes.

2. **Test Docker**:
   Run a test container to ensure Docker is working correctly:
   ```bash
   docker run hello-world
   ```

---

## Conclusion

You have successfully installed and configured Docker and Docker Compose on Kali Linux. Use Docker to run and manage containers efficiently.