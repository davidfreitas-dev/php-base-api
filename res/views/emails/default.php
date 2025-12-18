<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($subject) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #ffffff;
      color: #3c3c3c;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #ffffff;
      border: 1px solid #dddddd;
      border-radius: 8px;
    }
    .logo {
      text-align: center;
      margin-bottom: 20px;
    }
    .logo img {
      max-width: 150px;
    }
    .header h1 {
      color: #038de7;
      margin: 0;
      text-align: center;
    }
    .content {
      font-size: 16px;
      line-height: 1.6;
      margin-top: 20px;
    }
    .footer {
      text-align: center;
      margin-top: 30px;
      font-size: 12px;
      color: #999999;
    }
    .button {
      display: inline-block;
      padding: 12px 28px;
      background-color: #038de7;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 12px;
      font-weight: bold;
      margin: 20px 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">
      <img src="<?= $_ENV['APP_URL'] ?>/img/logo.png" alt="Logo">
    </div>
    <div class="header">
      <h1><?= htmlspecialchars($subject) ?></h1>
    </div>
    <div class="content">
      <?= $contentHtml ?>
    </div>
    <div class="footer">
      <p>© <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?>. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
