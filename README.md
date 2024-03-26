# PHP Base API with Slim Framework 4 and JWT Auth 

This template should help get you started developing with this API in Docker.

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

The HOSTNAME in .env file should be the same of docker-compose file db:container_name

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

| Parameter    | Type      | Description                                             |
| :----------- | :-------- | :------------------------------------------------------ |
| `token`      | `string`  | **Required**. Token sent by email to the user           |
| `despassword`   | `string`  | **Required**. User's password                        |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void