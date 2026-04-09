<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Punto')</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.dataTables.js"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f6f7fb;
            color: #0f172a;
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: #0b1736;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 24px 20px;
            font-size: 28px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-menu {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-link {
            display: block;
            padding: 14px 16px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            background: transparent;
        }

        .sidebar-link:hover {
            background: rgba(255,255,255,.08);
        }

        .sidebar-link.active {
            background: #1d4ed8;
        }

        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .topbar {
            height: 80px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .topbar-left input {
            width: 420px;
            max-width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .branch-selector {
            position: relative;
        }

        .branch-button {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 10px 14px;
            min-width: 220px;
            text-align: left;
            cursor: pointer;
        }

        .branch-label {
            display: block;
            font-size: 12px;
            color: #64748b;
        }

        .branch-name {
            display: block;
            font-weight: bold;
            margin-top: 4px;
        }

        .branch-dropdown {
            position: absolute;
            top: 110%;
            right: 0;
            width: 260px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
            padding: 8px;
            display: none;
            z-index: 100;
        }

        .branch-dropdown.show {
            display: block;
        }

        .branch-option {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            padding: 12px 14px;
            border-radius: 10px;
            cursor: pointer;
        }

        .branch-option:hover {
            background: #ffeef8;
        }

        .branch-option.active {
            background: #1d4ed8;
            color: white;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #1e40af;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .page-content {
            padding: 24px;
        }

        .page-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
        }

        .text-muted {
            color: #64748b;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .topbar-left input {
                width: 240px;
            }
        }

        /* ===== Estilos de Adrián para contenido interno ===== */

        .container{
            display:flex;
            height: 100vh;
        }

        .main{
            flex: 1;
            display: flex;
            flex-direction:column;
        }

        .content{
            flex: 1;
            padding: 30px;
        }

        /*Diseño del cuerpo*/
    body{
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f4f4f4;
    }

    /*contenedor general*/
    .container{
        display:flex;
        height: 100vh;
    }

    /*sidebar*/
    .sidebar{
        width: 230px;
        background-color:#0f172a;
        color:white;
        display:flex;
        flex-direction: column;
        padding-top:20px;
    }

    .sidebar h2{
        text-align: center;
        margin-bottom: 30px;
    }

    .sidebar a{
        padding: 15px 20px;
        text-decoration: none;
        color: white;
        display: block;
    }

    .sidebar a:hover{
        background:#1e293b;
    }

    /*area principal*/
    .main{
        flex: 1;
        display: flex;
        flex-direction:column;
    }

    /*TOPBAR*/
    .topbar{
        height: 60px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        border-bottom: 1px solid #ddd;
    }

    /*Contenido*/
    .content{
        flex: 1;
        padding: 30px;
    }

    /*Diseño de la ventana de onboarding*/
    .onboarding-overlay{
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.45);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 20px;
    }

    .onboarding-modal{
        width: 100%;
        max-width: 760px;
        max-height: 90vh;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.20);
    }

    .onboarding-modal form{
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    .onboarding-header{
        padding: 24px 28px 12px;
        border-bottom: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .onboarding-header h2{
        margin: 0;
        font-size: 32px;
        color: #0f172a;
    }

    .onboarding-header p{
        margin: 8px 0 0;
        color: #64748b;
        font-size: 18px;
    }

    .onboarding-progress{
        padding: 18px 28px;
        border-bottom: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .progress-top{
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        color: #334155;
        margin-bottom: 10px;
    }

    .progress-bar{
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-fill{
        width: 0%;
        height: 100%;
        background: #2d4bbb;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .onboarding-body{
        padding: 24px 28px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .form-group{
        margin-bottom: 20px;
    }

    .form-row{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group label{
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #0f172a;
    }

    .form-input{
        width: 100%;
        box-sizing: border-box;
        height: 48px;
        padding: 0px 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 15px;
        outline: none;
        background: #fff;
    }

    .form-input:focus{
        border-color: #2d4bbb;
        box-shadow: 0 0 0 3px rgba(45, 75, 187, 0.15);
    }

    .payment-grid{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .payment-option{
        border: 1px solid #d1d5db;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        background: #fff;
        transition: 0.2s;
    }

    .payment-option:hover{
        border-color: #2d4bbb;
        box-shadow: #f8faff;
    }

    .payment-option input{
        accent-color: #2d4bbb;
    }

    .onboarding-footer{
        padding: 18px 28px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        background: #fff;
        flex-shrink: 0;
    }

    .btn-secondary, 
    .btn-primary{
        flex: 1;
        height: 52px;
        border: none;
        border-radius: 14px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-secondary{
        background: #f1f5f9;
        color: #0f172a;
    }

    .btn-primary{
        background: #2d4bbb;
        color: white;
        box-shadow; 0 8px 18px rgba(45, 75, 187, 0.25);
    }

    .btn-secondary:hover{
        background: #e2e8f0;
    }

    .btn-primary:hover{
        background: #1f3aa3;
    }

    .onboarding-body::-webkit-scrollbar{
        width: 8px;
    }

    .onboarding-body::-webkit-scrollbar-track{
        background:#f1f5f9;
        border-radius: 10px;
    }

    .onboarding-body::-webkit-scrollbar-thumb{
        background: #cbd5e1;
        border-radius: 10px;
    }

    .onboarding-body::-webkit-scrollbar-thumb:hover{
        background: #94a3b8;
    }

    /*Diseño de la ventana de settings*/
        /*Pestaña de configuración de perfil*/
    .settings-page{
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .settings-header h1{
        margin: 0;
        font-size: 36px;
        color: #0f172a;
    }

    .settings-header p{
        margin: 8px 0 0;
        color: #64748b;
        font-size: 18px;
    }

    .settings-layout{
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2px;
        align-items: start;
    }

    .settings-menu{
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .settings-menu-item{
        display:block;
        padding: 14px, 16px;
        border-radius: 14px;
        text-decoration: none;
        color: #475569;
        font-weight: 600;
        transition: 0.2s ease;
    }

    .settings-menu-item:hover{
        background: #f8fafc;
    }

    .settings-menu-item.active{
        background: #2d4bbb;
        color: white;
        box-shadow: 0 8 px 18px rgba(45,75,187,0.25);
    }

    .settings-content{
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .settings-card{
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
    }

    .settings-card h2{
        margin: 0 0 20px;
        color: #0f172a;
    }

    .success-box{
        padding: 12px 14px;
        border-radius: 12px;
        background: #dcfce7;
        color: #166534;
    }

    .error-box{
        padding: 12px 14px;
        border-radius: 12px;
        background: #fee2e2;
        color: #991b1b;
    }

    .form-group{
        margin-bottom: 18px;
    }

    .form-row{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group label{
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #0f172a;
    }

    .form-input{
        width: 100%;
        height: 48px;
        padding: 0 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 15px;
        box-sizing: border-box;
    }

    .font-textarea{
        width: 100%;
        min-height: 120px;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 15px;
        box-sizing: border-box;
        resize: vertical;
    }

    .form-admin{
        background: rgb(241, 107, 197);
        border-radius: 20px;
        text-align: center;
        padding: 12px 14px;
    }

    .form-input:focus,
    .form-textarea:focus{
        border-color: #2d4bbb;
        box-shadow: 0 0 0 3px rgba(45, 75, 187, 0.15);
    }

    .btn-save{
        height: 48px;
        padding: 0 24px;
        border: none;
        border-radius: 12px;
        background: #2d4bbb;
        color: white;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save:hover{
        background: #1f3aa3;
    }

        /*Pestaña de metodos de pago*/
    .payment-grid{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .payment-option{
        border: 1px solid #d1d5db;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        background: #fff;
        transition: 0.2s ease;
    }

    .payment-option:hover{
        border-color: #2d4bbb;
        background: #f8faff;
    }

    .payment-option input{
        accent-color: #2d4bbb;
    }

        /*Pestaña de usuarios y roles*/
    .users-header{
        display:flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .users-list{
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .user-card{
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
    }

    .user-card h3{
        margin: 0 0 6px;
        color: #0f172a;
    }

    .user-card p{
        margin: 0;
        color: #64748b;
    }

    .role-badge{
        display: inline-block;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef2ff;
        color: #2d4bbb;
        font-size: 14px;
        font-weight: 600;
    }

    .roles-info-grid{
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }

    .role-info-card{
        border-radius: 16px;
        padding: 18px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    .role-info-card h3{
        margin: 0 0 10px;
        font-size: 22px;
        color: #0f172a;
    }


    .role-info-card p{
        margin: 0 0 10px;
        color: #475569;
        line-height: 1.5;
    }

    .role-admin{
        background: #faf5ff;
        border-color: #bfdbfe;
    }

    .role-manager{
        background: #faf5ff;
        border-color: #d8b4fe;
    }


    .role-cashier{
        background: #faf5ff;
        border-color: #cbd5e1;
    }

    /*Diseño de configuracion por dos pasos*/
    .security-section{
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .security-section h3{
        margin: 0 0 10px;
        color: #0f172a;
    }

    .security-block{
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
        margin-top: 24px;
    }

    .security-block-header{
        margin-bottom: 20px;
    }

    .security-block-header h3{
        margin: 0 0 6px;
        color: #0f172a;
        font-size: 28px;
    }


    .security-block-header p{
        margin: 0;
        color: #64748b;
        font-size: 16px;
    }

    .security-info-box{
        margin: 18px 0 18px;
        padding: 16px;
        border-radius: 14px;
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1d4ed8;
        font-size: 15px;
    }

    .twofa-header{
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .twofa-header-icon{
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .twofa-status-box{
        margin-top: 16px;
        padding: 18px 20px;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .twofa-status-box strong{
        display: block;
        margin-bottom: 6px;
        font-size: 24px;
    }


    .twofa-status-box p{
        margin: 0;
        color: #7c5a2f;
    }

    .twofa-status-off{
        background: #fff7ed;
        border: 1px solid #fdba74;
    }

    .twofa-status-on{
        background: #ecfeff;
        border: 1px solid #67e8f9;
    }

    .twofa-qr-box{
        margin: 22px;
        padding: 22px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .twofa-confirm-form{
        margin-top: 20px;
    }

    .twofa-confirmed-badge{
        margin-top: 20px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
    }

    .recovery-section{
        margin-top: 24px;
    }

    .recovery-section h4{
        margin: 0 0 14px;
        font-size: 22px;
        color: #0f172a;
    }

    .recovery-section p{
        margin: 0 0 14px;
        color: #64748b;
    }

    .recovery-codes-box{
        padding: 18px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        font-family: monospace;
        font-size: 14px;
    }

    .btn-danger{
        height: 48px;
        padding: 0 24px;
        border: none;
        border-radius: 12px;
        background: #dc2626;
        color: white;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-danger:hover{
        background: #b91c1c;
    }

    /*Diseño de la ventana de clientes.*/
    .action-dropdown{
        position: relative;
        display: inline-block;
    }

    .action-btn{
        background: #2d4bbb;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 14px;
        cursor: pointer;
        font-weight: 600;
    }

    .action-menu{
        display: none;
        position: absolute;
        right: 0;
        top: 44px;
        min-width: 180px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        z-index: 100;
        overflow: hidden;
    }

    .action-menu a,
    .action-menu button{
        display:block;
        width: 100%;
        padding: 12px 14px;
        text-align: left;
        background: white;
        border: none;
        text-decoration: none;
        color: #0f172a;
        cursor: pointer;
        font-size: 14px;
    }

    .action-menu a:hover,
    .action-menu button:hover{
        background: #f8fafc:
    }

    .delete-action{
        color: #dc2626 !important;
    }

    .customers-header{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .customers-header h1{
        margin: 0;
        font-size: 36px;
        color: #0f172a;
    }

    .customers-header p{
        margin: 8px 0 0;
        color: #64748b;
    }

    .customers-card{
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
    }

    .customers-table{
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }

    .customers-table th,
    .customers-table td{
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-overlay{
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        padding: 20px;
    }

    .modal-box{
        width: 100%;
        max-width: 700px;
        max-height: 90vh;
        background: white;
        border-radius: 18px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.20);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .modal-box form{
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    .modal-body{
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .modal-header{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h2{
        margin: 0;
    }

    .modal-close{
        border: none;
        background: transparent;
        font-size: 28px;
        cursor: pointer;
    }

    .tag-grid{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .tag-option{
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
    }

    .modal-footer{
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        background: white;
        flex-shrink: 0;
    }

    .user-actions-right{
        display:flex;
        align-items:center;
        gap:12px;
    }

    .user-action-buttons{
        display:flex;
        gap:8px;
    }

    .btn-icon-edit,
    .btn-icon-delete{
        border:none;
        border-radius:10px;
        padding:8px 12px;
        cursor:pointer;
        font-weight:600;
    }

    .btn-icon-edit{
        background:#dbeafe;
        color:#1d4ed8;
    }

    .btn-icon-delete{
        background:#fee2e2;
        color:#b91c1c;
    }

    .future-box{
        margin-top:16px;
        padding:14px;
        border-radius:12px;
        background:#f8fafc;
        border:1px dashed #cbd5e1;
        color:#64748b;
    }

    .settings-option-card{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:20px;
        border:1px solid #e5e7eb;
        border-radius:16px;
        margin-bottom:16px;
        background:#fff;
    }

    .settings-option-card h3{
        margin:0 0 6px;
    }

    .settings-option-card p{
        margin:0;
        color:#64748b;
    }

    .switch{
        position:relative;
        display:inline-block;
        width:56px;
        height:30px;
    }

    .switch input{
        opacity:0;
        width:0;
        height:0;
    }

    .slider{
        position:absolute;
        cursor:pointer;
        top:0;
        left:0;
        right:0;
        bottom:0;
        background-color:#cbd5e1;
        transition:.3s;
        border-radius:999px;
    }

    .slider:before{
        position:absolute;
        content:"";
        height:22px;
        width:22px;
        left:4px;
        bottom:4px;
        background:white;
        transition:.3s;
        border-radius:50%;
    }

    .switch input:checked + .slider{
        background-color:#2d4bbb;
    }

    .switch input:checked + .slider:before{
        transform:translateX(26px);
    }

    .inner-card{
        margin-top:20px;
    }

    .printer-options{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:16px;
    }

    .printer-option{
        border:1px solid #d1d5db;
        border-radius:16px;
        padding:18px;
        cursor:pointer;
        display:flex;
        flex-direction:column;
        gap:8px;
        background:#fff;
    }

    .printer-option.selected{
        border-color:#2d4bbb;
        box-shadow:0 0 0 2px rgba(45,75,187,0.12);
    }

    .printer-option input{
        display:none;
    }

    .printer-option span{
        font-size:24px;
        font-weight:700;
        color:#0f172a;
    }

    .printer-option small{
        color:#64748b;
    }

    .theme-options{
        display:grid;
        grid-template-columns:1fr 1fr 1fr;
        gap:16px;
    }

    .theme-card{
        border:1px solid #d1d5db;
        border-radius:16px;
        padding:24px;
        text-align:center;
        cursor:pointer;
        background:#fff;
        font-weight:600;
    }

    .theme-card.selected{
        border-color:#2d4bbb;
        box-shadow:0 0 0 2px rgba(45,75,187,0.12);
    }

    .theme-card input{
        display:none;
    }

    .preferences-actions{
        margin-top:20px;
    }
    </style>
</head>

<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-wrapper">
            @include('partials.topbar')

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const logoInput = document.getElementById('logoInput');
            const addressInput = document.getElementById('addressInput');
            const currencyInput = document.getElementById('currencyInput');
            const openingInput = document.getElementById('openingInput');
            const closingInput = document.getElementById('closingInput');
            const paymentMethods = document.querySelectorAll('.payment-method');

            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const progressFields = document.getElementById('progressFields');

            if(!progressFill || !progressText || !progressFields) {
                return;
            }

            const totalFields = 6;

            function isPaymentMethodSelected(){
                return Array.from(paymentMethods).some(input => input.checked);
            }

            function calculateCompletedFields(){
                let completed = 0;

                if(logoInput && logoInput.files.length > 0) completed++;
                if(addressInput && addressInput.value.trim() !== '') completed++;
                if(currencyInput && currencyInput.value.trim() !== '') completed++;
                if(openingInput && openingInput.value.trim() !== '') completed++;
                if(closingInput && closingInput.value.trim() !== '') completed++;
                if(isPaymentMethodSelected()) completed++;

                return completed;
            }

            function updateProgress(){
                const completed = calculateCompletedFields();
                const percentage = Math.round((completed / totalFields) * 100);
                const remaining = totalFields - completed;

                progressFill.style.width = percentage + '%';
                progressText.textContent = percentage + '% completado';
                progressFields.textContent = remaining + ' campos sugeridos';
            }

            if (logoInput) logoInput.addEventListener('change', updateProgress);
            if (addressInput) addressInput.addEventListener('input', updateProgress);
            if (currencyInput) currencyInput.addEventListener('change', updateProgress);
            if (openingInput) openingInput.addEventListener('change', updateProgress);
            if (closingInput) closingInput.addEventListener('change', updateProgress);

            paymentMethods.forEach(method => {
                method.addEventListener('change', updateProgress);
            });

            updateProgress();
        });
    </script>
</body>
</html>