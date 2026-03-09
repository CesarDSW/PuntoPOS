 @extends('/layout.dashboard')
 
 @section('content')
 <h1>Bienvenido</h1>
 <p>Aqui ira el contenido principal.</p>
 
 <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Cerrar sesión</button>
</form>
@endsection