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

define('DB_ENGINE',    'mysql');      
define('DB_HOST',      'localhost');  // change to your db host
define('DB_PORT',      '3306');       // change to your db port
define('DB_NAME',      'chatdb');     // change to your db name
define('DB_USER',      'admin');      // change to your db user
define('DB_PASSWORD',  '111111');     // change to your db password
```

## 3. Configure Socket Server

Edit the file `config/socket_config.php` and update it with your Websocket server settings:

```php
<?php

define('WS_HOST', '192.168.0.99'); 	//change to your host (or 0.0.0.0 to accept all IP)
define('WS_PORT', 5000);		//change to your port

```

## 4. Runing Socker server
Start the WebSocket server before opening the project in the browser:

```bash
php /var/www/<your_domain>/app/server/socket_server.php
```
Keep this terminal open for the server to stay running.

Press Ctrl + C to stop the server.
