# Skotch-Backend
Skotch is control system for design office work.

  - Notebook
  - Control drawings system
  - Utility for check of drawings
  - Utility for preparation packages of drawing for delivery
  - Pdf merge utility

### Installation

Skotch is based on Laravel 8 PHP Framework. Please check Laravel requirements<br>
https://laravel.com/docs/8.x/installation

Pdftk library to be installed, if pdf merge utility is required<br>
https://www.pdflabs.com/tools/pdftk-server/

Clone repository from GitHUB.

```sh
$ git clone https://github.com/vokson/vlg-dev-8.backend.git
$ cd vlg-dev-8.backend
```

Add .env file with private settings
```
APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=
LOG_CHANNEL=
LOG_LEVEL=
DB_CONNECTION=
DB_BACKUP_LIFETIME=
FILESYSTEM_DRIVER=
QUEUE_CONNECTION=
MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```
Install dependencies and set application key. If APP_DEBUG=production, allow changes.

```sh
$ composer install
$ php artisan key:generate
```
**If the application key is not set, your user sessions and other encrypted data will not be secure!**

After installing Laravel, you may need to configure some permissions. Directories within the **storage** and the **bootstrap/cache** directories should be writable by your web server or Laravel **will not run**.

Create folders required for application
```ssh
$ php artisan project:init_folders
```
Create empty **database.sqlite** in **./database** directory. Execute migrations and fill database with initial data.
```ssh
$ php artisan migrate
$ php artisan db:seed
```
Cache settings
```ssh
$ php artisan config:cache
```
Configure Cron Schedules in order to give application possibility to execute cron tasks.<br>
https://laravel.com/docs/8.x/homestead#configuring-cron-schedules

Now you may login via **Skotch-Frontend** using defaults
- username = admin@mail.com
- password = 1234

Then go to Admin and tune app. Start from Admin/Roles.

### License
MIT
