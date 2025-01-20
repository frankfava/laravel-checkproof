# Laravel Foundation

Built by Frank Fava for CheckProof as a "Test of Skill".

#### A note to my future employer:

Thank you for considering me for this role. While the requirements for your test were straightforward, I chose to go beyond the basics to showcase my depth of understanding in Laravel.

This project could have been implemented in many ways, including a "just-make-it-work" approach. However, I prioritised adding layers of abstraction to highlight how I organize code for readability, scalability, and maintainability.

*Please Note:* Password requirements are stronger than requests. Minimum 10 characters and at least one each of: Uppercase letter, lowercase letter, a number and a symbol, special character. eg. `StrongPass1!`. I also added in a custom middleware that prevents any authenticated user that isnt `active` from making an API rquest 

I encorage you to check out the full test suite as it contains a test for all the project requirements and more.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
  - [Requirements](#requirements)
  - [Setup](#setup)
  - [Postman Collection](#postman-collection)
- [API Documentation](#api-documentation)

## Overview

This a simple setup with just API routes setup for the User model. An overview of whats included is below:

- Laravel Passport is used for authenticated
    - Protecting all the user routes and only exposing `login` and `register` to guests 

- Database Seeder and Factories

- Full Testing Suite for all routes (75 tests)
    - Run tests after setup with:
        ```bash
        php artisan test
        ```  

- HTTP Middleware
    - Prevents any authenticated user that isnt `active` from making an API rquest
         
- JSON Resources of Responses
    - eg. `UserResource` to control how the user data is exposed.

- Enum Classes
    - Used an Enum for reliability when editing the `role` on the user 

- Policies for controller authorisation
    - Fine tuning who can reach the controllers
    - Used directly with the `can_edit` property 

- Eloquent Scopes and Attributes 

- Single Responsibility Action Classes
    - eg. `CreateNewUser`
    - Used for both programattic updates and via requests (using form requests)
    - *IMPORTANT*: Never look for the authenticated user in an action. only from a HTTP request. 
     
- Form Requests 
    - Used a trait to share validation rules
    - Uses the action class to define the the data validator and modify it for HTTP requests based on authenticated user
    - Authorise use policies 

- Bind interfaces with concrete action clases
    - Allows the contract to be resolved from the Laravel service container, instead of locking in fixed implementations.

- Custom Eloquent Builder
    - Rather than having massive queries in controllers that need to account for all parameters, and also creating that in all lookups, a custom Eloquent builder is used and overridden in each model
        - This allows additional methods to be added like `searchBy` that handles partial and exact searching.
        - This can then be decorated with options that enable automatic pagination to the results
        - And finally, a HTTP request can be applied to override the value.
    - The result?
        - You can set a use base query and then let the request automatically manage it 
        ```php
        $results = User::query()
            ->byRoles([UserRole::User->value]) // Model Scope
            ->active(true) // Model Scope
            ->withCount('orders') // From Laravel
            ->orderByDesc('created_at') // From Laravel
            ->excludeKey([1])  // Decorator Method
            ->useRequest($request) // Tell the decorator to use the request to override values eg. page
            ->mapItems(fn ($user) => $user->append('can_edit')) //map the items
            ->results(); // If there is per_page set, it will paginate; if not, a collection
        ```  

- Repsonses built using Responsable Contract 
    - Provides a reliable response (used for Login and Register)

- Event & Listeners
    - Using Eloquent and Custom Events to trigger actions

- Mailables & Notifications
    - New User Notifications
        - Eloquent User Event is Fired
        - `\App\Listeners\NewUserCreated` listens to it
        - Listener then determines the system admins from the `notifications` config
        - Notifications are sent, that share the same abstract class.
        - Each Notification then instantiates a Mailable to be sent to the notifiable
        - *Note:* Notification were used instead of just calling the mail class directly, as they allow further extension from the channels


## Installation

### Requirements

- PHP 8.2 or higher
- Composer
- Laravel 11
- SqLite (or any other database supported by Laravel)

### Setup

1. **Clone the repository:**
   - Move into the directory
   ```bash
   cd <directory>/
   ```

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
   - You may optionally run the database seeder.
        ```bash
        php artisan db:seed
        ```

6. **Setup Passport Keys:**
   ```bash
   php artisan passport:keys
   ```

7. **Create a Personal Access Client for Passport:**
   - **NOTE:** This is not required if you ran `php artisan db:seed` as a passport client was already setup for you
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

### Postman Collection

You can now make requests from you preferred client or using the included [**Postman Collection**](./postmanCollection.json) `postmanCollection.json`


## Testing

Run the test suite using PHPUnit:

```bash
php artisan test
```

## API Documentation

### Overview

This API provides endpoints for the built in User Model and basic Authentication

#### Authentication

- **Guest > Register:** Register a new Email
  - `POST /api/register`
  - Parameters: `name`, `email`, `password`, `password_confirmation`
  - Use the `token` from the response in the `Authorization` header for all subsequent API calls
  ```bash
  curl -X POST http://127.0.0.1:8000/api/register \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -d '{"name": "John Doe", "email": "john@example.com", "password": "StrongPass1!", "password_confirmation": "StrongPass1!"}'
  ```

- **Guest > Login:** Login with an existing user
  - `POST /api/login`
  - Parameters: `email`, `password`
  - Use the `token` from the response in the `Authorization` header for all subsequent API calls
  ```bash
  curl -X POST http://127.0.0.1:8000/api/login \
       -H "Content-Type: application/json" \
       -H "Accept: application/json" \
       -d '{"email": "john@example.com", "password": "StrongPass1!"}'
  ```

#### General

- **Guest > PING:** Test the API connection as an unauthenticated user
  - `GET /api/ping/guest`

- **Authenticated > PING:** Test the API connection as an authenticated user
  - `GET /api/ping/auth`

- **Authenticated > Get Authenticated User:** Get authenticated user details
  - `GET /api/user`


#### User Management

- **Authenticated > Users > List Users:** List all users  
  - `GET /api/users`
    ```bash
    curl -X GET -G \
        http://127.0.0.1:8000/api/users \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json" \
        -d q=.com \
        -d searchBy=email \
        -d sortBy=orders_count
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Only Admin and manager roles can list users
  - Parameters:
    - `q|search` : Optional. String to search for. Default : empty
    - `searchBy[]` : Optional. Column to search in eg. name, email. Default : name and email
    - `sortBy` : Optional. Column to sort by eg. name, email, created_at, orders_count. Default : created_at
    - `sortDesc` : Optional. Sort from highest to lowest = 1, lowest to highest = 0. Default : false
    - `page` : Optional. Page number to show. Default : 1
    - `per_page` : Optional. How many items per_page - Leave empty to return all. Default : empty
    - `limit` : Optional: Total limit (Used only when per_page is empty). Default : empty

- **Authenticated > Users > Show User:** Fetch details of a specific user
  - `GET /api/users/{userId}`
    ```bash
    curl -X GET http://127.0.0.1:8000/api/users/1 \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json"
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Admins and Managers can view any user
    - User role can only view themself

- **Authenticated > Users > Create User:** Create a new User
  - `POST /api/users`
    ```bash
    curl -X POST http://127.0.0.1:8000/api/users \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json" \
        -d '{"name": "New User", "email": "newuser@example.com", "password": "StrongPass1!", "password_confirmation": "StrongPass1!"}'
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Only Admin and manager roles can create a new users
    - Admins can set the `role` and `active` status
    - Managers CANNOT set the `role` and `active` status. Default to an active user role
    - Enforce a strong password
    - Passwor must be confirmed
  - Parameters:
    - `name` : Required. Updated Name of User.
    - `email` : Required. Updated Email of User.
    - `role` : Optional. Updated Role of User. **Admin Roles Only**
    - `active` : Optional. Updated Status of User. **Admin Roles Only**
    - `password` : Required. 
    - `password_confirmation` : Required. 

- **Authenticated > Users > Update User Profile:** Update User information (Except password)
  - `PUT /api/users/profile-information/{userId}`
    ```bash
    curl -X PUT http://127.0.0.1:8000/api/users/profile-information/1 \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json" \
        -d '{"name": "New Name"}'
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Any authenticated user can update their own information
    - User role cannot update anyone except themselves
    - Admin can update any roles information 
    - Only admins can change a user's `role` or `active` status
    - Admin cannot update their own role
    - Manager can only update the information of a user role
  - Parameters:
    - `name` : Optional. Updated Name of User.
    - `email` : Optional. Updated Email of User.
    - `role` : Optional. Updated Role of User. **Admin Roles Only**
    - `active` : Optional. Updated Status of User. **Admin Roles Only**

- **Authenticated > Users > Update User Password:** Update User Password
  - `PUT /api/users/password/{userId}`
    ```bash
    curl -X PUT http://127.0.0.1:8000/api/users/password/1 \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json" \
        -d '{"current_password": "StrongPass1!", "password": "NewPass1!", "password_confirmation": "NewPass1!"}'
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Any authenticated user can update their own password
    - Admin can update any roles password without providing the current password  (including their own)
    - Current Password must be provided and correct (except for admin)
    - Mangers cant update a user role's password without providing the current password
    - Managers must provided their current password when updating their own.
    - New Passwords must be confirmed
    - New Passwords must be strong
  - Parameters:
    - `current_password` : Required. Current password of the user **Not Required for admin**
    - `password` : Required. New strong password
    - `password_confirmation` : Required. New strong password confirmed

- **Authenticated > Users > Delete User:** Delete a specific user
  - `DELETE /api/users/{userId}`
    ```bash
    curl -X DELETE http://127.0.0.1:8000/api/users/1 \
        -H "Authorization: Bearer your-access-token" \
        -H "Content-Type: application/json"
    ```
  - Authorization:
    - Must be authenticated with a valid Bearer Token
    - Only Admin and manager roles can delete users
    - Noone (even admin), can delete themself
    - Admin can delete any role
    - Managers can only delete users, not admins or other managers
