# PHP Base API with Slim Framework 4 and JWT Auth 

This template should help get you started developing with this API in Docker.

## Technologies Used

- Slim Framework 4: A micro-framework for PHP that helps you quickly write simple yet powerful web applications and APIs.
- JWT Auth: JSON Web Token authentication mechanism for securing API endpoints.
- Docker: Containerization platform used to ensure consistency and portability across environments.
- MySQL: Database management system utilized for storing application data.
- PHP DotEnv: Library for loading environment variables from `.env` files to configure the application.
- PHP Mailer: Library for sending emails from PHP applications.

## Build Containers

```sh
docker compose up -d
```

### Install Composer Dependencies

```sh
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer install
```

## Set Enviroment Variables

Create a .env file using .env.example and set variables. This variables are configs to connect to the database(MySQL), sending email(PHP Mailer) and JWT config tokens

See: 
[PHP DotEnv Configuration Reference](https://github.com/vlucas/phpdotenv)
[PHP Mailer Configuration Reference](https://github.com/PHPMailer/PHPMailer)

## Conecting to Database

The HOSTNAME in .env file should be the same of docker-compose database service

---

## Authentication and Security

### Authentication with JWT (JSON Web Token)

The API uses JWT (JSON Web Token) for authentication. Below are the steps to authenticate and authorize requests:

1. **Obtaining the JWT Token:**  
   - To access the API's protected resources, you need to obtain a JWT token. This is done by sending a `POST` request to the `/signin` endpoint with the user's credentials (e.g., email and password).

2. **Including the Token in Requests:**  
   - After obtaining the JWT token, it must be included in the `Authorization` header in all subsequent requests to access protected resources.

   **Header Format:**

   ```
   Authorization: Bearer <token>
   ```

   **Example of an Authenticated Request:**

   ```http
   GET /users
   Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

3. **Token Expiration:**  
   - The JWT token has an expiration time. After this period, a new token must be obtained through the authentication process.  
   - If the token is expired or invalid, the API will return a `401 Unauthorized` error.

4. **Protected Routes:**  
   - All routes that require authentication are protected. Attempting to access these routes without a valid token will result in a `401 Unauthorized` error.

5. **Logout (Optional):**  
   - The API may implement a logout endpoint that invalidates the JWT token, ensuring it can no longer be used. This step is optional and depends on the specific API implementation.

---

## API Documentation

The API is divided into two main groups of endpoints: **Authentication** and **User**.

### Authentication Endpoints

All authentication-related endpoints are prefixed with `/auth`.

---

#### User Registration

Registers a new user in the system.

```http
  POST /auth/signup
```

**Payload:**

| Parameter  | Type     | Description                |
| :--------- | :------- | :------------------------- |
| `name`     | `string` | **Required**. User's full name. |
| `email`    | `string` | **Required**. User's email address. |
| `cpfcnpj`  | `string` | **Required**. User's CPF or CNPJ document. |
| `password` | `string` | **Required**. User's password. |
| `phone`    | `string` | *Optional*. User's phone number. |

**Response (201 Created):**

Returns a JWT token pair for immediate authentication.

```json
{
  "success": true,
  "message": "Cadastro efetuado com sucesso.",
  "code": 201,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600,
    "token_type": "Bearer",
    "refresh_token": "def502006bad22d5b4e..."
  }
}
```

---

#### User Authentication

Authenticates a user and provides a new JWT token pair.

```http
  POST /auth/signin
```

**Payload:**

| Parameter  | Type     | Description                                  |
| :--------- | :------- | :------------------------------------------- |
| `login`    | `string` | **Required**. User's email or CPF/CNPJ.      |
| `password` | `string` | **Required**. User's password.               |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Autenticação efetuada com sucesso.",
  "code": 200,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600,
    "token_type": "Bearer",
    "refresh_token": "def502006bad22d5b4e..."
  }
}
```

---

#### User Logout

Invalidates the current JWT access token. Requires authentication.

```http
  POST /auth/logout
```

**Note:** Requires `Authorization: Bearer <token>` header.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Logout realizado com sucesso.",
  "code": 200
}
```

---

#### Refresh Token

Generates a new JWT token pair using a valid refresh token.

```http
  POST /auth/token
```

**Payload:**

| Parameter       | Type     | Description                             |
| :-------------- | :------- | :-------------------------------------- |
| `refresh_token` | `string` | **Required**. The refresh token provided at sign-in. |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Token atualizado com sucesso.",
  "code": 200,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600,
    "token_type": "Bearer",
    "refresh_token": "def50200abc123d4e5f..."
  }
}
```

---

### Password Recovery

---

#### Request Password Reset

Sends a password reset link to the user's email.

```http
  POST /auth/forgot
```

**Payload:**

| Parameter | Type     | Description                             |
| :-------- | :------- | :-------------------------------------- |
| `email`   | `string` | **Required**. The user's registered email address. |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "E-mail de recuperação enviado com sucesso.",
  "code": 200
}
```

---

#### Verify Reset Token

Verifies the validity of a password reset token sent by email.

```http
  POST /auth/verify
```

**Payload:**

| Parameter | Type     | Description                                 |
| :-------- | :------- | :------------------------------------------ |
| `token`   | `string` | **Required**. The encrypted token from the reset link. |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Token de recuperação validado com sucesso.",
  "code": 200
}
```

---

#### Reset Password

Sets a new password for the user using a valid reset token.

```http
  POST /auth/reset
```

**Payload:**

| Parameter  | Type     | Description                                |
| :--------- | :------- | :----------------------------------------- |
| `token`    | `string` | **Required**. The encrypted token from the reset link. |
| `password` | `string` | **Required**. The new password.            |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Senha redefinida com sucesso.",
  "code": 200
}
```

---

### User Endpoints

Endpoints for managing the authenticated user's data. All endpoints in this group require authentication.

---

#### Get User Data

Retrieves the authenticated user's profile information.

```http
  GET /users/me
```

**Note:** Requires `Authorization: Bearer <token>` header.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Dados do usuário.",
  "code": 200,
  "data": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "phone": "11999999999",
    "cpfcnpj": "12345678900",
    "is_active": 1,
    "created_at": "2023-10-27 10:00:00",
    "updated_at": "2023-10-27 10:00:00"
  }
}
```

---

#### Update User Data

Updates the authenticated user's profile information.

```http
  PUT /users/me
```

**Note:** Requires `Authorization: Bearer <token>` header.

**Payload:**

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `name`    | `string` | **Required**. User's full name. |
| `email`   | `string` | **Required**. User's email address. |
| `cpfcnpj` | `string` | **Required**. User's CPF or CNPJ. |
| `phone`   | `string` | *Optional*. User's phone number. |

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Dados do usuário atualizados com sucesso.",
  "code": 200
}
```

---

#### Delete User Account

Deletes the authenticated user's account.

```http
  DELETE /users/me
```

**Note:** Requires `Authorization: Bearer <token>` header.

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Conta excluída com sucesso.",
  "code": 200
}
```