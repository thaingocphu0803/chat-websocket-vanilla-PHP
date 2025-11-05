# Project Setup Guide
This guide will help you set up and run the project locally.

Author: Thai Ngoc Phu

Contact: thaingocphu0803@gmail.com

---


## 1️. Clone the Project (Linux Recommended)

###### Create project folder
```bash
sudo mkdir /var/www/<your_domain>/
```
###### Change ownership to your user and www-data group
```bash
sudo chown <your_user>:www-data /var/www/<your_domain>
```
###### accrss the project folder
```bash
cd /var/www/<your_domain>
```
###### Clone the project
```bash
git clone https://github.com/thaingocphu0803/chat-websocket-vanilla-PHP.git .
```
###### provides permission for project folder
```bash
sudo chmod -R 775 /var/www/<your_domain>/html
```

## 2. Configure Database

Edit the file `config/database_config.php` and update it with your database information:

```php
<?php

define('DB_ENGINE',    '');      
define('DB_HOST',      '');  // change to your db host
define('DB_PORT',      '');       // change to your db port
define('DB_NAME',      '');     // change to your db name
define('DB_USER',      '');      // change to your db user
define('DB_PASSWORD',  '');     // change to your db password
```

## 3. Configure Socket Server

Edit the file `config/socket_config.php` and update it with your Websocket server settings:

```php
<?php

define('WS_HOST', ''); 	//change to your host (or 0.0.0.0 to accept all IP)
define('WS_PORT', '');		//change to your port

```

## 4. Runing Socker server
Start the WebSocket server before opening the project in the browser:

```bash
php /var/www/<your_domain>/app/server/socket_server.php
```
Keep this terminal open for the server to stay running.

Press Ctrl + C to stop the server.
