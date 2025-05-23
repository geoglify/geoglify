# Geoglify Webapp

This project is a Laravel + Vue 3 + Inertia.js + Vuetify app.
It helps create modern and nice user interface.

## Requirements

Before you start, install:

- [PHP 8.4 or higher](https://php.new/)
- [Composer](https://getcomposer.org/)
- [Laravel Installer](https://laravel.com/docs/12.x/installation#installing-laravel)
- [Node.js and NPM](https://nodejs.org/) **or** [Bun](https://bun.sh/)

> 🪟 On Windows, open PowerShell as administrator and run:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; \
[System.Net.ServicePointManager]::SecurityProtocol = \
[System.Net.ServicePointManager]::SecurityProtocol -bor 3072; \
iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))
```

## Install the project

1. **Clone the project**

```bash
git clone https://github.com/geoglify/geoglify.git
cd geoglify/webapp
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Create .env file and app key**

```bash
composer run post-root-package-install
composer run post-create-project-cmd
```

5. **Build frontend**

```bash
npm run build
```

6. **Start dev servers**

```bash
composer run dev
```

You will see:

- Laravel backend: http://127.0.0.1:8000
- Vite frontend: http://localhost:5173

## Notes

- Press `Ctrl+C` to stop servers
- Make sure port 8000 and 5173 are free

---

Made with ❤️ by leoneljdias