<?php

namespace Backend\Controllers;

use Backend\Core\Controller;

class UserController extends Controller
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserInfo(): void
    {
        // Core/auth.php exposes getUserInitials() and getCurrentUser()
        $user = getCurrentUser();
        if (!$user) {
            $this->jsonError('User not logged in', 401);
        }

        $initials = getUserInitials();

        $this->jsonSuccess([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'initials' => $initials
        ]);
    }

    public function logout(): void
    {
        destroySession();
        $this->jsonSuccess(['message' => 'Logged out successfully']);
    }

    public function deleteAccount(int $userId): void
    {
        try {
            $this->pdo->beginTransaction();

            // Delete user profiles and other dependencies if they don't have ON DELETE CASCADE
            // Usually, users table is the root.
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            $this->pdo->commit();

            // Destroy session after deleting account
            destroySession();

            $this->jsonSuccess(['message' => 'Account deleted successfully']);
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // Depending on the DB schema, a foreign key constraint might fail if no CASCADE.
            $this->jsonError('Failed to delete account. Please ensure all related data can be deleted.', 500);
        }
    }
}
