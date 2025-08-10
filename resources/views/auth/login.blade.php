<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NYEMIL</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
        <div style="width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="font-size: 2rem; font-weight: bold; color: #333;">NYEMIL</h1>
                <p style="color: #666; margin-top: 10px;">Admin Login</p>
            </div>

            @if($errors->any())
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    @foreach($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label for="login" style="display: block; margin-bottom: 5px; font-weight: 500;">Username atau Email</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="{{ old('login') }}" 
                        required 
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                        placeholder="Masukkan username atau email"
                    >
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="password" style="display: block; margin-bottom: 5px; font-weight: 500;">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                        placeholder="Masukkan password"
                    >
                </div>

                <button 
                    type="submit" 
                    style="width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 500; cursor: pointer;"
                >
                    Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 30px;">
                <button 
                    onclick="window.location.href='{{ route('landing') }}'" 
                    style="color: #007bff; background: none; border: none; text-decoration: underline; cursor: pointer;"
                >
                    ← Kembali ke Landing Page
                </button>
            </div>
        </div>
    </div>

    <style>
        button:hover {
            opacity: 0.9;
        }
        
        input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
    </style>
</body>
</html>