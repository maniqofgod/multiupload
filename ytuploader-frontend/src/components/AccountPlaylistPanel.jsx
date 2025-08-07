import React, { useState, useEffect } from 'react';
import { generateGoogleAuthUrl, getAccounts, getPlaylists, deleteToken, deleteAllTokens } from '../api';
import './AccountPlaylistPanel.css';

function AccountPlaylistPanel({ credentials, onAccountChange }) { // Tambahkan onAccountChange
    const [accounts, setAccounts] = useState([]);
    const [selectedAccount, setSelectedAccount] = useState('');
    const [playlists, setPlaylists] = useState([]);
    const [defaultPlaylist, setDefaultPlaylist] = useState('');

    const fetchAccounts = async () => {
        try {
            const response = await getAccounts();
            setAccounts(response.data);
            if (response.data.length > 0) {
                const currentSelectionExists = response.data.some(acc => acc.id === selectedAccount);
                if (!currentSelectionExists) {
                    const firstAccountId = response.data[0].id;
                    setSelectedAccount(firstAccountId);
                    onAccountChange(firstAccountId); // Panggil callback saat pertama kali memuat
                }
            } else {
                setSelectedAccount('');
                setPlaylists([]);
                onAccountChange(''); // Panggil callback saat tidak ada akun
            }
        } catch (error) {
            console.error("Gagal mengambil daftar akun:", error);
        }
    };

    const fetchPlaylists = async (accountId) => {
        if (!accountId) return;
        try {
            const response = await getPlaylists(accountId);
            setPlaylists(response.data);
            if (response.data.length > 0) {
                setDefaultPlaylist(response.data[0].id);
            } else {
                setDefaultPlaylist('');
            }
        } catch (error) {
            console.error("Gagal mengambil daftar playlist:", error);
            setPlaylists([]);
        }
    };

    useEffect(() => {
        fetchAccounts();
    }, []);

    useEffect(() => {
        fetchPlaylists(selectedAccount);
    }, [selectedAccount]);

    const handleAccountSelectChange = (e) => {
        const newAccountId = e.target.value;
        setSelectedAccount(newAccountId);
        onAccountChange(newAccountId); // Panggil callback saat pengguna mengubah pilihan
    };

    const handleAuthorizeAccount = async () => {
        const isServerSecretSelected = credentials && credentials.useServerSecret && credentials.serverSecretId;
        const isManualSecretSelected = credentials && !credentials.useServerSecret && credentials.manualSecretId;

        if (!isServerSecretSelected && !isManualSecretSelected) {
            alert("Silakan pilih atau masukkan Client Secret terlebih dahulu.");
            return;
        }

        try {
            const response = await generateGoogleAuthUrl(credentials);
            const popup = window.open(response.data.url, 'google_login', 'width=500,height=600');

            const timer = setInterval(() => {
                if (popup.closed) {
                    clearInterval(timer);
                    fetchAccounts();
                }
            }, 500);

        } catch (error) {
            alert("Gagal menghasilkan URL otentikasi. Periksa kembali kredensial Anda.");
            console.error(error);
        }
    };

    const handleDeleteToken = async () => {
        if (!selectedAccount) {
            alert("Pilih akun yang akan dihapus.");
            return;
        }
        if (window.confirm("Apakah Anda yakin ingin menghapus akun ini?")) {
            try {
                await deleteToken(selectedAccount);
                alert("Akun berhasil dihapus.");
                fetchAccounts();
            } catch (error) {
                alert("Gagal menghapus akun.");
                console.error(error);
            }
        }
    };

    const handleDeleteAllTokens = async () => {
        if (window.confirm("Apakah Anda yakin ingin menghapus semua akun? Tindakan ini tidak dapat diurungkan.")) {
            try {
                await deleteAllTokens();
                alert("Semua akun berhasil dihapus.");
                fetchAccounts();
            } catch (error) {
                alert("Gagal menghapus semua akun.");
                console.error(error);
            }
        }
    };

    return (
        <div className="panel-container">
            <div className="form-group">
                <label htmlFor="account-select">Pilih Akun:</label>
                <select id="account-select" value={selectedAccount} onChange={handleAccountSelectChange}>
                    {accounts.length > 0 ? (
                        accounts.map(account => (
                            <option key={account.id} value={account.id}>{account.name}</option>
                        ))
                    ) : (
                        <option value="">Belum ada akun terotorisasi</option>
                    )}
                </select>
            </div>
            <div className="form-group">
                <label htmlFor="playlist-select">Playlist Default:</label>
                <select id="playlist-select" value={defaultPlaylist} onChange={(e) => setDefaultPlaylist(e.target.value)}>
                    {playlists.length > 0 ? (
                        playlists.map(playlist => (
                            <option key={playlist.id} value={playlist.id}>{playlist.title}</option>
                        ))
                    ) : (
                        <option value="">Tidak ada playlist</option>
                    )}
                </select>
            </div>
            <div className="button-group">
                <button onClick={handleAuthorizeAccount}>Tambah Akun</button>
                <button onClick={handleDeleteToken}>Hapus Akun</button>
                <button onClick={handleDeleteAllTokens}>Hapus Semua Akun</button>
            </div>
        </div>
    );
}

export default AccountPlaylistPanel;