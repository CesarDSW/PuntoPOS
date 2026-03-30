<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticacion</title>

    <style>
        *{
            box-sizing: border-box;
        }

        body{
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:linear-gradient(135deg, #0f172a, #1e3a8a);
            min-height: 100vh;
            display:flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-container{
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.20);
            padding: 32px 28px;
        }

        .auth-title{
            margin: 0 0 8px;
            font-size: 30px;
            color: #0f172a;
            text-align: center;
        }

        .auth-subtitle{
            margin: 0 0 24px;
            text-align: center;
            color: #64748b;
            font-size: 15px;
        }

        .form-group{
            margin-bottom: 18px;
        }

        .form-label{
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            outline: none;
            font-size: 15px;
            transition: 0.2s ease;
        }

        .form-input:focus{
            border-color: #2d4bbb;
            box-shadow: 0 0 0 3px rgba(45, 75, 187, 0.15);
        }

        .auth-button{
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: #2d4bbb;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 10px;
        }

        .auth-button:hover{
            background: #1f3aa3;
        }

        .switch-text{
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .switch-link{
            display: inline-block;
            margin-top: 8px;
            padding: 10px 16px;
            border: 1px solid #2d4bbb;
            border-radius: 10px;
            color: #2d4bbb;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease; 
        }

        .switch-link:hover{
            background: "2d4bbb";
            color: white;
        }

        .error-box{
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 14px;
        }

        .success-box{
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #dcfce7;
            color= #166534;
            font-size: 14px;
        }
        
        /*Google*/
        .google-button{
            display: block;
            width: 100%;
            margin-top: 14px;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: #0f172a;
            font-weight: 600;
            background: white;
            transition: 0.2s ease;
        }

        .google-button:hover{
            background: #f8fafc;
        }

    </style>

</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>
</body>
</html>