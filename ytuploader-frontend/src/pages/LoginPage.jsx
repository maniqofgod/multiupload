import React, { useState } from 'react';
import api from '../api';

function LoginPage({ onLogin }) { // Mengubah nama prop menjadi onLogin
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');
        try {
            const response = await api.post('/auth/login', {
                username,
                password,
            });
            // Sekarang kita meneruskan seluruh objek user dan token
            const { token, user } = response.data; 
            onLogin(user, token); // Memanggil onLogin dengan data yang benar
        } catch (err) {
            setError('Login gagal. Periksa kembali username dan password Anda.');
            console.error(err);
        }
    };

    return (
        <div className="login-page">
            <form onSubmit={handleLogin} className="login-form">
                <h2>Login</h2>
                <div>
                    <label>Username:</label>
                    <input
                        type="text"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        required
                    />
                </div>
                <div>
                    <label>Password:</label>
                    <input
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                    />
                </div>
                {error && <p style={{ color: 'red' }}>{error}</p>}
                <button type="submit">Login</button>
            </form>
        </div>
    );
}

export default LoginPage;