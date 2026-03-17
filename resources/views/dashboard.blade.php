 @extends('layout.dashboard_design')
 
 @section('content')
 <h1>Bienvenido</h1>
 <p>Aqui ira el contenido principal.</p>
  
  @if($showOnboarding)
    <div class="onboarding-overlay">
        <div class="onboarding-modal">
            <div class="onboarding-header">
                <h2>¡Bienvenido a Punto!</h2>
                <p>Completa tu negocio en menos de 2 minutos.</p>        
            </div>
            
            <div class="onboarding-progress">
                <div class="progress-top">
                    <span id="progressText">0% completado</span>
                    <span id="progressFields">6 campos sugeridos</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="onboarding-body">
                    <div class="form-group">
                        <label>Logo del negocio</label>
                        <input type="file" name="logo" id="logoInput" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="address" id="addressInput" class="form-input" placeholder="Ej. Av. Reforma 123, Col. Centro">
                    </div>
                    
                    <div class="form-group">
                        <label>Moneda</label>
                        <select name="currency" id="currencyInput" class="form-input">
                            <option value="MXN">MXN - Peso Mexicano</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Hora de apertura</label>
                            <input type="time" name="opening_time" id="openingInput" class="form-input">
                        </div>
                        
                        <div class="form-group">   
                            <label>Hora de cierre</label>
                            <input type="time" name="closing_time" id="closingInput" class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Métodos de pago aceptados</label>
                        <div class="payment-grid">
                            <label class="payment-option">
                                <input type="checkbox" name="payment_methods[]" value="Efectivo" class="payment-method">
                                <span>Efectivo</span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="checkbox" name="payment_methods[]" value="Tarjeta" class="payment-method">
                                <span>Tarjeta</span>
                            </label>
                        
                            <label class="payment-option">
                                <input type="checkbox" name="payment_methods[]" value="Transferencia" class="payment-method">
                                <span>Transferencia</span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="checkbox" name="payment_methods[]" value="Cheque" class="payment-method"> 
                                <span>Cheque</span>
                            </label>
                        </div>
                    </div>   
                </div>
            
                <div class="onboarding-footer">
                    <button type="submit" name="skip" value="1" class="btn-secondary">
                        Omitir por ahora
                    </button>
                
                    <button type="submit" class="btn-primary">
                        Guardar y continuar
                    </button>
                </div>    
            </form>
        </div>
    </div>
@endif
 
 <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Cerrar sesión</button>
</form>
@endsection