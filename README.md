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

## Autenticação e Segurança

### Autenticação com JWT (JSON Web Token)

A API utiliza JWT (JSON Web Token) para autenticação. Abaixo estão os passos para autenticar e autorizar as requisições:

1. **Obtenção do Token JWT:**
   - Para acessar os recursos protegidos da API, você precisa obter um token JWT. Isso é feito enviando uma requisição `POST` para o endpoint `/signin` com as credenciais do usuário (e.g., e-mail e senha).

2. **Incluindo o Token nas Requisições:**
   - Após obter o token JWT, ele deve ser incluído no cabeçalho `Authorization` em todas as requisições subsequentes para acessar os recursos protegidos.

   **Formato do Cabeçalho:**

   ```
   Authorization: Bearer <token>
   ```

   **Exemplo de Requisição Autenticada:**

   ```http
   GET /users
   Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

3. **Expiração do Token:**
   - O token JWT possui um tempo de expiração. Após esse período, será necessário obter um novo token através do processo de autenticação.
   - Se o token estiver expirado ou for inválido, a API retornará um erro `401 Unauthorized`.

4. **Rotas Protegidas:**
   - Todas as rotas que exigem autenticação são protegidas. A tentativa de acessar essas rotas sem um token válido resultará em um erro `401 Unauthorized`.

5. **Logout (Opcional):**
   - A API pode implementar um endpoint de logout que invalida o token JWT, garantindo que ele não possa mais ser usado. Este passo é opcional e depende da implementação específica da API.

---

## API Documentation

- [User Registration](#user-registration)
- [User Authentication](#user-authentication)
- [User Forgot Password](#user-forgot-password)
- [User Forgot Token](#user-forgot-token)
- [User Reset Password](#user-reset-password)

#### User Registration

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
| `inadmin`     | `integer`| **Required**. User's access level (1 = admin, 0 = user) |

**Note:** The parameters above should be passed within a single JSON object.

**Response:** JWT token with user data.

#### User Authentication

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

#### User Forgot Password

```http
  POST /forgot
```

| Parameter  | Type     | Description                                             |
| :--------  | :------- | :------------------------------------------------------ |
| `desemail`    | `string` | **Required**. User's email address                      |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Send reset link to user e-mail.

#### User Forgot Token

```http
  POST /forgot/token
```

| Parameter  | Type     | Description                                             |
| :--------  | :------- | :------------------------------------------------------ |
| `token`    | `string` | **Required**. Token sent by email to the user           |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### User Reset Password

```http
  POST /forgot/reset
```

| Parameter     | Type      | Description                                             |
| :------------ | :-------- | :------------------------------------------------------ |
| `token`       | `string`  | **Required**. Token sent by email to the user           |
| `despassword` | `string`  | **Required**. User's password                           |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void