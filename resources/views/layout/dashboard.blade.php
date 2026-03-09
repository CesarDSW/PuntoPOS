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
</style>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>PUNTO</h2>
                <a href="#">Dashboard</a>
                <a href="#">Ventas</a>
                <a href="#">Catalogo</a>
                <a href="#">Inventario</a>
                <a href="#">Pagos</a>
                <a href="#">Clientes</a>
                <a href="#">Reportes</a>
                <a href="#">Configuracion</a>
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

    </body>
</html>