const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const { verifyToken, verifyAdmin } = require('./auth');
const userStore = require('../userStore');
const clientSecretStore = require('../clientSecretStore');

const saltRounds = 10;

// Rute ini hanya memerlukan pengguna untuk login, tidak harus admin.
// Ini memungkinkan semua pengguna untuk melihat daftar nama CS Server.
router.get('/client-secrets', verifyToken, async (req, res) => {
    try {
        const secrets = await clientSecretStore.getAllSecretsForClient();
        res.json(secrets);
    } catch (error) {
        res.status(500).send("Error mengambil client secrets.");
    }
});

// Terapkan middleware admin untuk semua rute di bawah ini
router.use(verifyToken, verifyAdmin);

// --- Rute Pengguna ---
router.get('/users', async (req, res) => {
    try {
        const users = await userStore.getAllUsers();
        res.json(users);
    } catch (error) {
        res.status(500).send("Error mengambil data pengguna.");
    }
});

router.post('/users', async (req, res) => {
    try {
        const { username, password, role = 'user' } = req.body;
        if (!username || !password) {
            return res.status(400).send("Username dan password diperlukan.");
        }
        if (await userStore.findUserByUsername(username)) {
            return res.status(409).send("Username sudah ada.");
        }

        const passwordHash = await bcrypt.hash(password, saltRounds);
        const newUser = await userStore.addUser({
            username,
            passwordHash,
            role,
        });
        
        const { passwordHash: _, ...userToReturn } = newUser;
        res.status(201).json(userToReturn);

    } catch (error) {
        res.status(500).send("Error saat menambahkan pengguna.");
    }
});

// Rute edit pengguna diperbarui untuk menyertakan role
router.put('/users/:id', async (req, res) => {
    try {
        const userId = parseInt(req.params.id, 10);
        const { username, password, role } = req.body; // Tambahkan role

        if (isNaN(userId)) {
            return res.status(400).send("ID pengguna tidak valid.");
        }
        if (!username) {
            return res.status(400).send("Username diperlukan.");
        }

        const updateData = { username, role }; // Sertakan role dalam data pembaruan
        if (password) {
            updateData.passwordHash = await bcrypt.hash(password, saltRounds);
        }

        const updatedUser = await userStore.updateUser(userId, updateData);
        if (updatedUser) {
            const { passwordHash: _, ...userToReturn } = updatedUser;
            res.status(200).json(userToReturn);
        } else {
            res.status(404).send("Pengguna tidak ditemukan.");
        }
    } catch (error) {
        res.status(500).send("Error saat memperbarui pengguna.");
    }
});

router.delete('/users/:id', async (req, res) => {
    try {
        const userId = parseInt(req.params.id, 10);
        if (isNaN(userId)) {
            return res.status(400).send("ID pengguna tidak valid.");
        }
        if (req.user.userId === userId) {
            return res.status(403).send("Tidak dapat menghapus akun sendiri.");
        }
        if (await userStore.deleteUser(userId)) {
            res.status(200).send("Pengguna berhasil dihapus.");
        } else {
            res.status(404).send("Pengguna tidak ditemukan.");
        }
    } catch (error) {
        res.status(500).send("Error saat menghapus pengguna.");
    }
});

// --- Rute Manajemen Client Secret (Hanya Admin) ---
router.post('/client-secrets', async (req, res) => {
    try {
        const { name, clientId, clientSecret } = req.body;
        if (!name || !clientId || !clientSecret) {
            return res.status(400).send("Nama, Client ID, dan Client Secret diperlukan.");
        }
        const newSecret = await clientSecretStore.addSecret({ name, clientId, clientSecret });
        res.status(201).json(newSecret);
    } catch (error) {
        res.status(500).send("Error saat menambahkan client secret.");
    }
});

router.delete('/client-secrets/:id', async (req, res) => {
    try {
        const secretId = parseInt(req.params.id, 10);
        if (isNaN(secretId)) {
            return res.status(400).send("ID secret tidak valid.");
        }
        if (await clientSecretStore.deleteSecret(secretId)) {
            res.status(200).send("Client secret berhasil dihapus.");
        } else {
            res.status(404).send("Client secret tidak ditemukan.");
        }
    } catch (error) {
        res.status(500).send("Error saat menghapus client secret.");
    }
});

module.exports = router;