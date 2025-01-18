### Setup

1. **Clone the repository:**

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up the environment file:**
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update `.env` with your database credentials and other configurations.

4. **Create an APP_KEY:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations and seed the database:**
   ```bash
   php artisan migrate
   ```
   - You may optional run the database seeder. Not recommended if you are planning to Fetch trivia questions from the external API.
        ```bash
        php artisan db:seed
        ```

6. **Setup Passport Keys:**
   ```bash
   php artisan passport:keys
   ```

7. **Create a Personal Access Client for Passport:**
   ```bash
   php artisan passport:client --personal -n
   ```
   - Add the values to the env file.
   ```
    PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
    PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=
    ```

8. **Start the server or setup with Laravel Herd:**
   ```bash
   php artisan serve
   ```
   - The API will be accessible at `http://127.0.0.1:8000` or at your designated HERD url.

The project is now ready for use!



## Testing

Run the test suite using PHPUnit:

```bash
php artisan test
```