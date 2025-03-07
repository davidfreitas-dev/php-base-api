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

- [Users Registration](#users-registration)
- [Users Authentication](#users-authentication)
- [Users Forgot Password](#users-forgot-password)
- [Users Forgot Token](#users-forgot-token)
- [Users Reset Password](#users-reset-password)
- [Users Update](#users-update)
- [Users Delete](#users-delete)

#### Users Registration

```http
  POST /signup
```

| Parameter     | Type     | Description                                             |
| :-----------  | :------- | :------------------------------------------------------ |
| `desperson`   | `string` | **Required**. User's full name                          |
| `deslogin`    | `string` | **Required**. User's username                           |
| `despassword` | `string` | **Required**. User's password                           |
| `desemail`    | `string` | **Required**. User's email address                      |
| `nrphone`     | `string` | User's phone number                                     |
| `nrcpf`       | `string` | User's CPF                                              |

**Observation:** The parameters above should be passed within a single JSON object.

**Response:** JWT token with user data.

#### Users Authentication

```http
  POST /signin
```

| Parameter     | Type     | Description                                     |
| :-----------  | :------- | :---------------------------------------------- |
| `deslogin`    | `string` | User's username                                 |
| `desemail`    | `string` | User's email address                            |
| `nrcpf`       | `string` | User's CPF                                      |
| `despassword` | `string` | **Required**. User's password                   |

**Note:** Authentication can be done using the username, email, or CPF along with the password.

**Observation:** The parameters should be passed within a single JSON object.

**Response:** JWT token with user data.

#### Users Forgot Password

```http
  POST /forgot
```

| Parameter  | Type     | Description                                             |
| :--------- | :------- | :------------------------------------------------------ |
| `desemail` | `string` | **Required**. User's email address                      |

**Note:** Send reset link to user e-mail.

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Users Forgot Token

```http
  POST /forgot/token
```

| Parameter  | Type     | Description                                             |
| :--------- | :------- | :------------------------------------------------------ |
| `token`    | `string` | **Required**. Token sent by email to the user           |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Recovery ID and User ID

#### Users Reset Password

```http
  POST /forgot/reset
```

| Parameter     | Type      | Description                                             |
| :------------ | :-------- | :------------------------------------------------------ |
| `token`       | `string`  | **Required**. Token sent by email to the user           |
| `despassword` | `string`  | **Required**. User's password                           |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Users Update

```http
  PUT /users/update
```

| Parameter     | Type     | Description                                             |
| :-----------  | :------- | :------------------------------------------------------ |
| `desperson`   | `string` | **Required**. User's full name                          |
| `deslogin`    | `string` | **Required**. User's username                           |
| `desemail`    | `string` | **Required**. User's email address                      |
| `nrphone`     | `string` | **Required**. User's phone number                       |
| `nrcpf`       | `string` | **Required**. User's CPF                                |

**Note:** JWT needed.

**Observation:** The parameters should be passed within a single JSON object.

**Response:** JWT token with updated user data.

#### Users Delete

```http
  POST /users/delete
```

**Note:** JWT needed.

**Observation:** No parameters needed.

**Response:** Void