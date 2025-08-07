import React, { useState, useEffect } from 'react';
import { getUsers, addUser, updateUser, deleteUser, getClientSecrets, addClientSecret, deleteClientSecret } from '../api';
import './AdminPanel.css';

// Komponen Modal untuk Edit Pengguna
const EditUserModal = ({ user, onSave, onClose }) => {
    const [username, setUsername] = useState(user.username);
    const [password, setPassword] = useState('');
    const [role, setRole] = useState(user.role);

    const handleSubmit = (e) => {
        e.preventDefault();
        const updatedData = { username, role };
        if (password) {
            updatedData.password = password;
        }
        onSave(user.id, updatedData);
    };

    return (
        <div className="modal-backdrop">
            <div className="modal-content">
                <h4>Edit Pengguna: {user.username}</h4>
                <form onSubmit={handleSubmit}>
                    <input 
                        type="text" 
                        value={username} 
                        onChange={(e) => setUsername(e.target.value)} 
                        required 
                    />
                    <input 
                        type="password" 
                        placeholder="Kosongkan jika tidak ingin mengubah password" 
                        value={password} 
                        onChange={(e) => setPassword(e.target.value)} 
                    />
                    <select value={role} onChange={(e) => setRole(e.target.value)}>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    <div className="modal-actions">
                        <button type="submit">Simpan</button>
                        <button type="button" onClick={onClose}>Batal</button>
                    </div>
                </form>
            </div>
        </div>
    );
};


function AdminPanel() {
    // State untuk Pengguna
    const [users, setUsers] = useState([]);
    const [newUsername, setNewUsername] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [newUserRole, setNewUserRole] = useState('user'); // State baru untuk role pengguna baru
    const [editingUser, setEditingUser] = useState(null);
    
    // State untuk Client Secret
    const [secrets, setSecrets] = useState([]);
    const [newSecretName, setNewSecretName] = useState('');
    const [newClientId, setNewClientId] = useState('');
    const [newClientSecret, setNewClientSecret] = useState('');

    const [error, setError] = useState('');

    const fetchAllData = async () => {
        try {
            const [userRes, secretRes] = await Promise.all([getUsers(), getClientSecrets()]);
            setUsers(userRes.data);
            setSecrets(secretRes.data);
        } catch (err) {
            setError('Gagal memuat data admin.');
        }
    };

    useEffect(() => {
        fetchAllData();
    }, []);

    const handleAddUser = async (e) => {
        e.preventDefault();
        try {
            // Kirim role baru ke API
            await addUser({ username: newUsername, password: newPassword, role: newUserRole });
            setNewUsername('');
            setNewPassword('');
            setNewUserRole('user'); // Reset ke default
            fetchAllData();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menambahkan pengguna.');
        }
    };

    const handleUpdateUser = async (userId, userData) => {
        try {
            await updateUser(userId, userData);
            setEditingUser(null);
            fetchAllData();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal memperbarui pengguna.');
        }
    };

    const handleDeleteUser = async (userId) => {
        if (window.confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
            try {
                await deleteUser(userId);
                fetchAllData();
            } catch (err) {
                setError(err.response?.data?.message || 'Gagal menghapus pengguna.');
            }
        }
    };

    const handleAddSecret = async (e) => {
        e.preventDefault();
        try {
            await addClientSecret({ name: newSecretName, clientId: newClientId, clientSecret: newClientSecret });
            setNewSecretName('');
            setNewClientId('');
            setNewClientSecret('');
            fetchAllData();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menambahkan client secret.');
        }
    };

    const handleDeleteSecret = async (secretId) => {
        if (window.confirm('Apakah Anda yakin ingin menghapus client secret ini?')) {
            try {
                await deleteClientSecret(secretId);
                fetchAllData();
            } catch (err) {
                setError(err.response?.data?.message || 'Gagal menghapus client secret.');
            }
        }
    };

    return (
        <>
            {editingUser && (
                <EditUserModal 
                    user={editingUser} 
                    onSave={handleUpdateUser} 
                    onClose={() => setEditingUser(null)} 
                />
            )}
            <div className="admin-panel-container">
                {error && <p className="error-message" style={{ gridColumn: '1 / -1' }}>{error}</p>}
                
                {/* Manajemen User */}
                <div className="management-section">
                    <h4>Manajemen User</h4>
                    <form onSubmit={handleAddUser} className="user-form">
                        <input type="text" placeholder="Username" value={newUsername} onChange={(e) => setNewUsername(e.target.value)} required />
                        <input type="password" placeholder="Password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} required />
                        <select value={newUserRole} onChange={(e) => setNewUserRole(e.target.value)}>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <button type="submit">Tambah User</button>
                    </form>
                    <table className="user-table">
                        <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Aksi</th></tr></thead>
                        <tbody>
                            {users.map(user => (
                                <tr key={user.id}>
                                    <td>{user.id}</td>
                                    <td>{user.username}</td>
                                    <td>{user.role}</td>
                                    <td className="action-buttons">
                                        <button onClick={() => setEditingUser(user)} className="edit-button">Edit</button>
                                        <button onClick={() => handleDeleteUser(user.id)} className="delete-button">Hapus</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Manajemen Client Secret */}
                <div className="management-section">
                    <h4>Manajemen Client Secret</h4>
                    <form onSubmit={handleAddSecret} className="user-form">
                        <input type="text" placeholder="Nama (e.g., Akun Utama)" value={newSecretName} onChange={(e) => setNewSecretName(e.target.value)} required />
                        <input type="text" placeholder="Client ID" value={newClientId} onChange={(e) => setNewClientId(e.target.value)} required />
                        <input type="password" placeholder="Client Secret" value={newClientSecret} onChange={(e) => setNewClientSecret(e.target.value)} required />
                        <button type="submit">Tambah Secret</button>
                    </form>
                    <table className="user-table">
                        <thead><tr><th>ID</th><th>Nama</th><th>Aksi</th></tr></thead>
                        <tbody>
                            {secrets.map(secret => (
                                <tr key={secret.id}>
                                    <td>{secret.id}</td>
                                    <td>{secret.name}</td>
                                    <td><button onClick={() => handleDeleteSecret(secret.id)} className="delete-button">Hapus</button></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

export default AdminPanel;