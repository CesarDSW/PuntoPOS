<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<style>
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

</style>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>PUNTO</h2>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="#">Ventas</a>
                <a href="#">Catalogo</a>
                <a href="#">Inventario</a>
                <a href="#">Pagos</a>
                <a href="#">Clientes</a>
                <a href="#">Reportes</a>
                <a href="{{ route('settings') }}">Configuración</a>
            </div>
            
            <div class="main">
                <div class="topbar">
                    <div>Mi tienda</div>
                    <div>Usuario</div>
                </div>

                <div class="content">
                    @yield('content')
                </div>

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

                const totalFields = 6;

                function isPaymentMethodSelected(){
                    return Array.form(paymentMethods).some(input => input.checked);
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

            })
        </script>

    </body>
</html>