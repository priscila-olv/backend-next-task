<!DOCTYPE html>
<html>
<head>
    <title>Redefinir Senha - Next Task</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #555;
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .code {
            background-color: #f9f9f9;
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            font-family: Consolas, monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #d4923c;">Olá, {{ $name }}</h1>
        <p>Recebemos uma solicitação para redefinir sua senha do Next Task.</p>
        <p>Insira o código de redefinição de senha a seguir:</p>
        <div class="code" style="background-color: #e3ac64; color: #fff;">
            {{ $token }}
        </div>
    </div>
</body>
</html>
