<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticacion</title>

    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body{
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
        }

        .auth-split{
            min-height: 100vh;
            display: flex;
        }

        .auth-left{
            width: 55%;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center; 
        }

        .auth-left h1{
            font-size: 40px;
            margin-bottom: 15px;
        }

        
        .auth-left h2{
            font-size: 34px;
            margin-bottom: 20px;
        }

        .auth-left p{
            color: #dbeafe;
            font-size: 40px;
            line-height: 1.5;
        }

        .feature{
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .icon{
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .feature-text{
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .feature-text strong{
            font-size: 16px;
            color: white;
        }

        .feature-text span{
            color: #dbeafe;
            font-size: 14px;
        }

        .auth-rigth{
            width: 45%;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .login-card{
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 32px 30px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10);
        }

        .login-title{
            font-size: 32px;
            text-align: center;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .login-subtitle{
            text-align: center;
            color: #64748b;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .input-group{
            margin-bottom: 15px;
        }

        .input-label{
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
        }

        .input-group input{
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbde5e1;
            outline: none;
            font-size: 15px;
            transition: 0.2s ease;
            background: white;
        }

        .input-group input:focus{
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .input-group input[readonly]{
            background: #f8fafc;
            color: #64748b;
        }

        .login-row{
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .remember-box{
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
        }

        .login-link{
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link:hover{
            text-decoration: underline;
        }

        .btn-primary-auth{
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 10px;
            background: #3b82f6;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }
        
        .btn-primary-auth:hover{
            background: #2563eb;
        }

        .divider{
            text-align: center;
            margin: 18px 0 14px;
            color: #94a3b8;
            font-size: 14px;
        }

        .divider span{
            position: relative;
            display: inline-block;
            padding: 0 12px;
            background: white;
        }
        
        /*Google*/
        .google-button{
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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

        .google-icon{
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ffffff;
            color: #ea4335;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .register-text{
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #64748b;
        }

        .register-text a{
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .register-text a:hover{
            text-decoration: underline;
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
            color: #166534;
            font-size: 14px;
        }

        /*Recuperación de contraseña*/
        .form-input{
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 15px;
            transition: 0.2s ease;
            background: white;
        }

        .form-input:focus{
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .auth-modal-page{
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .auth-modal-card{
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 32px 30px;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
        }

        .challenge-divider{
            text-align: center;
            margin: 14px 0;
            color: #64748b;
            font-weight: 600;
        }

        .auth-right-overlay{
            position: relative;
        }

        .auth-right-overlay::before{
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
        }

        .auth-right-overlay .auth-modal-card{
            position: relative;
            z-index: 2;
        }

        .modal-top{
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .left-title, .left-subtitle{
            text-decoration: none;
            color: #64748b;
            font-size: 32px;
            line-height: 1;
        }

        .modal-close-btn:hover{
            color: #0f172a; 
        }

        .info-box-auth{
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #334155;
            font-size: 14px;
            line-height: 1.4;
        }

        .note-box-auth{
            margin-top: 8px;
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        .modal-actions{
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-secondary-auth{
            width: 100%;
            height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: white;
            color: #0f172a;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .btn-secondary-auth:hover{
            background: #f8fafc;
        }

        .auth-right-base{
            position: relative;
            overflow: hidden;
        }

        .login-card-background{
            opacity: 0.35;
            transform: scale(0.98);
            pointer-events: none;
            filter: blur(1px);
        }   

        .auth-overlay{
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.38);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            z-index: 5;
        }

        .auth-modal-card{
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.22);
            padding: 28px;
            position: relative;
            z-index: 6;
        }

        .modal-top{
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .modal-title-left{
            text-align: left;
            font-size: 24px;
            margin-bottom: 6px;
        }

        .modal-subtitle-left{
            text-align: left;
            margin-bottom: 0;
        }

        .modal-close-btn{
            text-decoration: none;
            color: #64748b;
            font-size: 34px;
            line-height: 1;
            flex-shrink: 0;
        }

        .modal-close-btn:hover{
            color: #0f172a;
        }

        .info-box-auth{
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #334155;
            font-size: 14px;
            line-height: 1.5;
        }

        .note-box-auth{
            margin-top: 8px;
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        .modal-actions{
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-secondary-auth{
            width: 100%;
            height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: white;
            color: #0f172a;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .btn-secondary-auth:hover{
            background: #f8fafc;
        }

        @media(max-width: 900px){
            .auth-split{
                display: block;
            }

            .auth-overlay{
                position: static;
                background: transparent;
                padding: 0;
            }

            .login-card-background{
                display: none;
            }

            .auth-modal-card{
                max-width: 420px;
            }

            .auth-left{
                display: none;
            }

            .auth-right{
                width: 100%;
                min-height: 100hv;
            }

            .login-card{
                max-width: 420px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        @yield('content')
    </div>
</body>
</html>