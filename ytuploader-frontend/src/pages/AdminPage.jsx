import React from 'react';

function AdminPage() {
    // State dan handler untuk form akan ditambahkan di sini
    
    return (
        <div>
            <h2>Panel Admin</h2>
            
            <section className="admin-section">
                <h3>Unggah Client Secret</h3>
                <form>
                    {/* Form untuk client secret */}
                    <button type="submit">Simpan Client Secret</button>
                </form>
            </section>
            
            <section className="admin-section">
                <h3>Manajemen Pengguna</h3>
                {/* Tabel atau daftar pengguna akan ditampilkan di sini */}
                <button>Buat Pengguna Baru</button>
            </section>
        </div>
    );
}

export default AdminPage;