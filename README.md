# Intercept demo

This is a demo app for [Intercept by PromptPHP](https://github.com/promptphp/intercept).

## Getting Started

### Prerequisites

- PHP 8.4+

### Installation

1. Clone the repository

    ```bash
    git clone https://github.com/promptphp/intercept-demo.git
    cd intercept-demo
    ```

2. Install PHP dependencies

    ```bash
    composer install
    ```

3. Install Node dependencies

    ```bash
    npm install
    ```

4. Copy and configure environment

    ```bash
    cp .env.example .env
    # Edit .env as needed
    ```

5. Generate app key

    ```bash
    php artisan key:generate
    ```

6. Run migrations and seeders

    ```bash
    php artisan migrate --seed
    ```

7. Build frontend assets for production

    ```bash
    npm run build
    ```

8. Start development server

    ```bash
    npm run dev
    ```
---

### Development

- Run tests

    ```bash
    composer test
    ```
---

## License

[MIT](LICENSE)

---

## Authors

- [Veeqtoh](https://github.com/veeqtoh) and contributors

---
