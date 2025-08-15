<form method="POST" action="{{ route('auth.password.change') }}">
    @csrf
    <div>
        <label>Nova senha</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Confirmar nova senha</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <button type="submit">Alterar senha</button>
</form>
