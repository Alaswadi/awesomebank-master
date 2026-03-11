# Guide to Setting Up a Kali Linux Environment

## 1. Install VMware on Windows Machine
1. Download VMware Workstation Player or VMware Workstation Pro from [VMware's official site](https://www.vmware.com/).
2. Run the installer and follow the on-screen instructions to complete the installation.

## 2. Download and Install Kali Linux on VMware
1. Download the Kali Linux ISO from the official site: [https://www.kali.org/](https://www.kali.org/).
2. Open VMware and create a new virtual machine:
   - Select `Installer disc image file (iso)` and browse to the downloaded ISO.
   - Assign `4 GB` of RAM and `30-40 GB` of hard disk space for optimal performance.
3. Follow the steps in the installer to complete the Kali Linux setup.

## 3. Update and Upgrade Commands on Kali
Run the following commands in the terminal to ensure your system is up to date:
```bash
sudo apt update && sudo apt upgrade -y
```

## 4. Open Burp Suite and Set Up Browser
### Install Burp Suite Certificate:
1. Open Burp Suite.
2. Go to the `Proxy` tab and ensure the proxy is running.
3. Open your browser and visit `http://burp`.
4. Download the CA certificate.
5. Import the certificate into your browser under `Settings > Privacy & Security > Certificates > Import`.

### Install PwnFox Plugin:
1. Open Firefox.
2. Search for "PwnFox" in the Firefox Add-ons store.
3. Click `Add to Firefox` and follow the prompts to install.

## 5. Install Git
Install Git by running the following command:
```bash
sudo apt install git -y
```

## 6. Install GoLang
1. Download the latest Go binary from [https://golang.org/dl/](https://golang.org/dl/).
2. Extract the archive and move it to `/usr/local`:
```bash
tar -C /usr/local -xzf go*.tar.gz
```
3. Add Go to your PATH by adding this to `~/.bashrc`:
```bash
export PATH=$PATH:/usr/local/go/bin
```
4. Reload the bash profile:
```bash
source ~/.bashrc
```
5. Verify the installation:
```bash
go version
```
or you can install it the easy way:
```bash
sudo apt install golang
```

## 7. Install The JSON Web Token Toolkit v2
Clone the repository and set it up:
```bash
git clone https://github.com/ticarpi/jwt_tool.git
cd jwt_tool
sudo pip3 install -r requirements.txt
```

## 8. Install Kiterunner
1. Clone the repository:
```bash
git clone https://github.com/assetnote/kiterunner.git
cd kiterunner
```
2. Build it using Go:
```bash
go build .
```
or you can do it the easy way
```bash
download the release from https://github.com/assetnote/kiterunner
unzip kiterunner_1.0.2_linux_amd64.tar.gz
sudo cp kr /usr/bin
kr --help
```


## 9. Install SecLists and FeroXbuster
### SecLists:
1. Clone the repository:
```bash
git clone https://github.com/danielmiessler/SecLists.git
```

### FeroXbuster:
1. Install it using the following commands:
```bash
sudo apt install feroxbuster -y
```

## 10. Install Nuclei
1. Install Nuclei using this command:
```bash
sudo apt install nuclei -y
```

## 11. Install Arjun
1. Clone the repository and install it:
```bash
git clone https://github.com/s0md3v/Arjun.git
cd Arjun
sudo pip3 install -r requirements.txt
```

---
You now have a complete setup for a Kali Linux environment tailored for penetration testing and development!
