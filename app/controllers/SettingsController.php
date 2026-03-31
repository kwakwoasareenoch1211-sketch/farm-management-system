<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/config/Database.php';

class SettingsController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function dashboard(): void
    {
        // Get system statistics
        $systemInfo = [
            'total_users' => $this->getUserCount(),
            'active_batches' => $this->getActiveBatchCount(),
        ];

        $dbStats = [
            'size' => $this->getDatabaseSize(),
        ];

        $this->view('settings/dashboard', [
            'pageTitle'  => 'System Settings',
            'sidebarType'=> 'settings',
            'systemInfo' => $systemInfo,
            'dbStats'    => $dbStats,
        ], 'admin');
    }

    private function getUserCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getActiveBatchCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM batches WHERE status = 'active'");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getDatabaseSize(): string
    {
        try {
            $stmt = $this->db->query("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
            ");
            $result = $stmt->fetch();
            $size = $result['size_mb'] ?? 0;
            return $size . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }
}
