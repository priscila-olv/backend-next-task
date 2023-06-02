<!DOCTYPE html>
<html>
<head>
    <title>Convite de projeto - Next Task</title>
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

    a {
        color: #d4923c;
        text-decoration: none;
    }
</style>
</head>
<body>
    <div class="container">
        <h1 style="color: #d4923c;">Olá</h1>
        <p>Você foi convidado por {{ $name_user }} para participar do projeto <strong>'{{$project_name}}'</strong> no Next Task.</p>
        <p>Para aceitar o convite, utilize o seguinte token no nosso site:</p>
        <div class="code" style="background-color: #e3ac64; color: #fff;">
            {{ $token_invite }}
        </div><br><br>
        <p>Clique <a href="http://localhost:3000">aqui</a> para ser redirecionado para o Next Task.</p>
    </div>
</body>
</html>



