<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="flex w-full max-w-4xl bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Register Section -->
        <div class="w-1/2 bg-blue-500 p-8 flex flex-col justify-center items-center">
            <h2 class="text-3xl font-bold text-white mb-4">Hello, Welcome!</h2>
            <p class="text-white mb-8">Don't have an account?</p>
            <button id="registerButton" class="px-4 py-2 bg-transparent border border-white text-white rounded hover:bg-white hover:text-blue-500 transition">Register</button>
        </div>

        <div class="absolute top-20 left-1/2 transform -translate-x-1/2 flex flex-col items-center justify-center bg-gray-100 px-4">
            @php
                $setting = \App\Models\Setting::first();
            @endphp
            @if(optional($setting)->logo)
                <img src="{{ asset('storage/back/logo/' . $setting->logo) }}" alt="Logo Perusahaan" class="w-24 h-24 mb-4 rounded-full border-4 border-gray-200 shadow-md">
            @endif
            <h1 class="text-gray-700 text-lg font-bold text-center">{{ optional($setting)->nama_perusahaan ?? 'Nama Perusahaan' }}</h1>
        </div>
        <!-- Login Section -->
        <div class="w-1/2 p-8">
            <h2 class="text-2xl font-bold mb-4">Login</h2>
                            <!-- Alert Notifikasi -->
        @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" name="email" id="email" required placeholder="Email" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <input type="password" name="password" id="password" required placeholder="Password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Login</button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4">Register</h2>
 

            <form method="POST" action="{{ route('register') }}">
                @csrf
    
                <div class="mb-4">
                    <input type="text" name="nis" id="nis" required placeholder="NIS" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('nis') }}">
                </div>
                <div class="mb-4">
                    <input type="text" name="name" id="name" required placeholder="Username" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('name') }}">
                </div>
                <div class="mb-4">
                    <input type="email" name="email" id="email" required placeholder="Email" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('email') }}">
                </div>
                <div class="mb-4">
                    <input type="password" name="password" id="password" required placeholder="Password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <input type="password" name="password_confirmation" id="registerPasswordConfirmation" required placeholder="Confirm Password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Register</button>
            </form>
            <button id="closeModal" class="mt-4 w-full px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">Close</button>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 id="alertTitle" class="text-2xl font-bold mb-4"></h2>
            <p id="alertMessage" class="mb-4"></p>
            <button id="closeAlert" class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Okay</button>
        </div>
    </div>

    <script>
        const registerButton = document.getElementById('registerButton');
        const registerModal = document.getElementById('registerModal');
        const closeModal = document.getElementById('closeModal');
        const alertModal = document.getElementById('alertModal');
        const alertTitle = document.getElementById('alertTitle');
        const alertMessage = document.getElementById('alertMessage');
        const closeAlert = document.getElementById('closeAlert');

        registerButton.addEventListener('click', () => {
            registerModal.classList.remove('hidden');
        });

        closeModal.addEventListener('click', () => {
            registerModal.classList.add('hidden');
        });

        closeAlert.addEventListener('click', () => {
            alertModal.classList.add('hidden');
        });

        document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Ambil nilai input dari form
    const nis = document.getElementById('registerNis').value;
    const email = document.getElementById('registerEmail').value;

    // Simulasi server response
    const existingEmails = ["test@example.com", "admin@example.com"]; // Simulasi email yang sudah ada
    const existingNis = ["123456", "7891011"]; // Simulasi NIS yang sudah ada

    const isEmailTaken = existingEmails.includes(email); // Periksa apakah email sudah ada
    const isNisTaken = existingNis.includes(nis); // Periksa apakah NIS sudah ada
    const isSuccess = !isEmailTaken && !isNisTaken; // Simulasi sukses hanya jika email dan NIS unik

    if (isEmailTaken) {
        showAlert('Registration Failed', 'Email is already in use. Please use a different email.');
    } else if (isNisTaken) {
        showAlert('Registration Failed', 'NIS is already in use. Please use a different NIS.');
    } else if (isSuccess) {
        showAlert('Registration Successful', 'You have successfully registered.');
    } else {
        showAlert('Registration Failed', 'An unexpected error occurred. Please try again later.');
    }
});


        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            if (email === "user@example.com" && password === "password") {
                showAlert('Login Successful', 'Redirecting to dashboard...');
                setTimeout(() => window.location.href = '/', 1500);
            } else {
                showAlert('Login Failed', 'Invalid credentials.');
            }
        });

        function showAlert(title, message) {
            alertTitle.textContent = title;
            alertMessage.textContent = message;
            alertModal.classList.remove('hidden');
        }
    </script>
</body>
</html>
