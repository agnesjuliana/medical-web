<?php
/**
 * SIMRS-TB — API Handler
 * 
 * Centralized backend for all CRUD operations.
 * Accepts AJAX requests, returns JSON responses.
 * 
 * Usage: api.php?action=get_patients
 *        api.php (POST with action field)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/validator.php';
require_once __DIR__ . '/../../config/database.php';

// Require login for all API calls
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        // ═══════════════════════════════════════
        // PATIENTS (tb_patients)
        // ═══════════════════════════════════════

        case 'get_patients':
            $search = $_GET['search'] ?? '';
            $fase = $_GET['fase'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $where = [];
            $params = [];

            if ($search) {
                $where[] = '(p.nama LIKE :search OR p.no_rm LIKE :search2)';
                $params['search'] = "%$search%";
                $params['search2'] = "%$search%";
            }
            if ($fase) {
                $where[] = 'p.fase_pengobatan = :fase';
                $params['fase'] = $fase;
            }
            if ($status) {
                $where[] = 'p.status = :status';
                $params['status'] = $status;
            }

            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count total
            $countStmt = $db->prepare("SELECT COUNT(*) FROM tb_patients p $whereSQL");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Fetch data
            $sql = "SELECT p.*, 
                    TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur,
                    CASE 
                        WHEN p.tanggal_mulai_pengobatan IS NOT NULL AND p.tanggal_target_selesai IS NOT NULL 
                        THEN ROUND(DATEDIFF(CURDATE(), p.tanggal_mulai_pengobatan) / DATEDIFF(p.tanggal_target_selesai, p.tanggal_mulai_pengobatan) * 100)
                        ELSE 0 
                    END as progress
                    FROM tb_patients p 
                    $whereSQL 
                    ORDER BY p.updated_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue(":$k", $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $patients = $stmt->fetchAll();

            // Clamp progress 0-100
            foreach ($patients as &$p) {
                $p['progress'] = max(0, min(100, (int)$p['progress']));
            }

            jsonResponse(true, '', [
                'patients' => $patients,
                'total' => (int)$total,
                'page' => $page,
                'pages' => ceil($total / $limit),
                'limit' => $limit
            ]);
            break;

        case 'get_patient':
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT p.*, TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur FROM tb_patients p WHERE p.id = :id");
            $stmt->execute(['id' => $id]);
            $patient = $stmt->fetch();
            if (!$patient) {
                jsonResponse(false, 'Pasien tidak ditemukan');
                break;
            }

            // Get lab results
            $labStmt = $db->prepare("SELECT * FROM tb_lab_results WHERE id_pasien = :id ORDER BY tanggal_pemeriksaan DESC LIMIT 10");
            $labStmt->execute(['id' => $id]);
            $labs = $labStmt->fetchAll();

            // Get medical records
            $mrStmt = $db->prepare("SELECT * FROM tb_medical_records WHERE id_pasien = :id ORDER BY tanggal_periksa DESC LIMIT 5");
            $mrStmt->execute(['id' => $id]);
            $records = $mrStmt->fetchAll();

            jsonResponse(true, '', [
                'patient' => $patient,
                'lab_results' => $labs,
                'medical_records' => $records
            ]);
            break;

        case 'create_patient':
            requirePost();
            $v = new Validator($_POST, $db);
            $v->required('nama', 'Nama')
              ->required('tanggal_lahir', 'Tanggal lahir')
              ->required('jenis_kelamin', 'Jenis kelamin');

            if ($v->fails()) {
                jsonResponse(false, 'Validasi gagal', ['errors' => $v->errors()]);
                break;
            }

            // Auto-generate no_rm
            $noRm = generateNoRM($db);

            $stmt = $db->prepare("INSERT INTO tb_patients 
                (no_rm, nik, nama, tanggal_lahir, jenis_kelamin, alamat, no_telepon, pekerjaan, 
                 kategori_tb, tipe_pasien, fase_pengobatan, tanggal_mulai_pengobatan, tanggal_target_selesai, status) 
                VALUES (:no_rm, :nik, :nama, :tanggal_lahir, :jenis_kelamin, :alamat, :no_telepon, :pekerjaan, 
                        :kategori_tb, :tipe_pasien, :fase_pengobatan, :tanggal_mulai, :tanggal_target, :status)");

            $mulai = $_POST['tanggal_mulai_pengobatan'] ?? null;
            $target = $_POST['tanggal_target_selesai'] ?? null;

            $stmt->execute([
                'no_rm' => $noRm,
                'nik' => $_POST['nik'] ?? null,
                'nama' => trim($_POST['nama']),
                'tanggal_lahir' => $_POST['tanggal_lahir'],
                'jenis_kelamin' => $_POST['jenis_kelamin'],
                'alamat' => $_POST['alamat'] ?? null,
                'no_telepon' => $_POST['no_telepon'] ?? null,
                'pekerjaan' => $_POST['pekerjaan'] ?? null,
                'kategori_tb' => $_POST['kategori_tb'] ?? 'Paru',
                'tipe_pasien' => $_POST['tipe_pasien'] ?? 'Baru',
                'fase_pengobatan' => $_POST['fase_pengobatan'] ?? 'Belum Mulai',
                'tanggal_mulai' => $mulai ?: null,
                'tanggal_target' => $target ?: null,
                'status' => $_POST['status'] ?? 'Aktif',
            ]);

            jsonResponse(true, 'Pasien berhasil ditambahkan', ['id' => $db->lastInsertId(), 'no_rm' => $noRm]);
            break;

        case 'update_patient':
            requirePost();
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { jsonResponse(false, 'ID pasien tidak valid'); break; }

            $v = new Validator($_POST, $db);
            $v->required('nama', 'Nama')
              ->required('tanggal_lahir', 'Tanggal lahir')
              ->required('jenis_kelamin', 'Jenis kelamin');

            if ($v->fails()) {
                jsonResponse(false, 'Validasi gagal', ['errors' => $v->errors()]);
                break;
            }

            $mulai = $_POST['tanggal_mulai_pengobatan'] ?? null;
            $target = $_POST['tanggal_target_selesai'] ?? null;

            $stmt = $db->prepare("UPDATE tb_patients SET 
                nik = :nik, nama = :nama, tanggal_lahir = :tanggal_lahir, jenis_kelamin = :jenis_kelamin, 
                alamat = :alamat, no_telepon = :no_telepon, pekerjaan = :pekerjaan,
                kategori_tb = :kategori_tb, tipe_pasien = :tipe_pasien, fase_pengobatan = :fase_pengobatan,
                tanggal_mulai_pengobatan = :tanggal_mulai, tanggal_target_selesai = :tanggal_target, status = :status
                WHERE id = :id");
            
            $stmt->execute([
                'id' => $id,
                'nik' => $_POST['nik'] ?? null,
                'nama' => trim($_POST['nama']),
                'tanggal_lahir' => $_POST['tanggal_lahir'],
                'jenis_kelamin' => $_POST['jenis_kelamin'],
                'alamat' => $_POST['alamat'] ?? null,
                'no_telepon' => $_POST['no_telepon'] ?? null,
                'pekerjaan' => $_POST['pekerjaan'] ?? null,
                'kategori_tb' => $_POST['kategori_tb'] ?? 'Paru',
                'tipe_pasien' => $_POST['tipe_pasien'] ?? 'Baru',
                'fase_pengobatan' => $_POST['fase_pengobatan'] ?? 'Belum Mulai',
                'tanggal_mulai' => $mulai ?: null,
                'tanggal_target' => $target ?: null,
                'status' => $_POST['status'] ?? 'Aktif',
            ]);

            jsonResponse(true, 'Data pasien berhasil diperbarui');
            break;

        case 'delete_patient':
            requirePost();
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { jsonResponse(false, 'ID pasien tidak valid'); break; }

            $stmt = $db->prepare("DELETE FROM tb_patients WHERE id = :id");
            $stmt->execute(['id' => $id]);

            jsonResponse(true, 'Pasien berhasil dihapus');
            break;

        // ═══════════════════════════════════════
        // APPOINTMENTS (tb_appointments)
        // ═══════════════════════════════════════

        case 'get_appointments':
            $date = $_GET['date'] ?? date('Y-m-d');
            $mode = $_GET['mode'] ?? 'day'; // day, upcoming, month

            if ($mode === 'day') {
                $stmt = $db->prepare("SELECT a.*, p.nama as pasien_nama, p.no_rm 
                    FROM tb_appointments a 
                    JOIN tb_patients p ON a.id_pasien = p.id 
                    WHERE DATE(a.tanggal_jadwal) = :date 
                    ORDER BY a.tanggal_jadwal ASC");
                $stmt->execute(['date' => $date]);
            } elseif ($mode === 'upcoming') {
                $stmt = $db->prepare("SELECT a.*, p.nama as pasien_nama, p.no_rm 
                    FROM tb_appointments a 
                    JOIN tb_patients p ON a.id_pasien = p.id 
                    WHERE DATE(a.tanggal_jadwal) > :date AND a.status = 'Terjadwal'
                    ORDER BY a.tanggal_jadwal ASC LIMIT 10");
                $stmt->execute(['date' => $date]);
            } elseif ($mode === 'month') {
                $month = $_GET['month'] ?? date('m');
                $year = $_GET['year'] ?? date('Y');
                $stmt = $db->prepare("SELECT DATE(tanggal_jadwal) as tgl, COUNT(*) as total
                    FROM tb_appointments 
                    WHERE MONTH(tanggal_jadwal) = :month AND YEAR(tanggal_jadwal) = :year
                    GROUP BY DATE(tanggal_jadwal)");
                $stmt->execute(['month' => $month, 'year' => $year]);
            }

            jsonResponse(true, '', ['appointments' => $stmt->fetchAll()]);
            break;

        case 'create_appointment':
            requirePost();
            $v = new Validator($_POST);
            $v->required('id_pasien', 'Pasien')
              ->required('tanggal_jadwal', 'Tanggal')
              ->required('waktu_jadwal', 'Waktu');

            if ($v->fails()) {
                jsonResponse(false, 'Validasi gagal', ['errors' => $v->errors()]);
                break;
            }

            $datetime = $_POST['tanggal_jadwal'] . ' ' . $_POST['waktu_jadwal'] . ':00';
            $stmt = $db->prepare("INSERT INTO tb_appointments 
                (id_pasien, tanggal_jadwal, jenis_kontrol, catatan, status) 
                VALUES (:id_pasien, :tanggal_jadwal, :jenis_kontrol, :catatan, 'Terjadwal')");
            $stmt->execute([
                'id_pasien' => (int)$_POST['id_pasien'],
                'tanggal_jadwal' => $datetime,
                'jenis_kontrol' => $_POST['jenis_kontrol'] ?? 'Kontrol Rutin',
                'catatan' => $_POST['catatan'] ?? null,
            ]);

            jsonResponse(true, 'Jadwal berhasil ditambahkan', ['id' => $db->lastInsertId()]);
            break;

        case 'update_appointment_status':
            requirePost();
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $allowed = ['Terjadwal', 'Selesai', 'Tidak Hadir', 'Dibatalkan', 'Dijadwalkan Ulang'];
            if (!in_array($status, $allowed)) {
                jsonResponse(false, 'Status tidak valid');
                break;
            }
            $stmt = $db->prepare("UPDATE tb_appointments SET status = :status WHERE id = :id");
            $stmt->execute(['id' => $id, 'status' => $status]);
            jsonResponse(true, 'Status jadwal diperbarui');
            break;

        case 'delete_appointment':
            requirePost();
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM tb_appointments WHERE id = :id");
            $stmt->execute(['id' => $id]);
            jsonResponse(true, 'Jadwal berhasil dihapus');
            break;

        // ═══════════════════════════════════════
        // DRUGS / PHARMACY (tb_drug_inventory)
        // ═══════════════════════════════════════

        case 'get_drugs':
            $stmt = $db->query("SELECT *, (stok_tersedia <= stok_minimum) as alert FROM tb_drug_inventory ORDER BY nama_obat");
            jsonResponse(true, '', ['drugs' => $stmt->fetchAll()]);
            break;

        case 'update_drug_stock':
            requirePost();
            $id = (int)($_POST['id'] ?? 0);
            $stok = (int)($_POST['stok_tersedia'] ?? 0);
            $stmt = $db->prepare("UPDATE tb_drug_inventory SET stok_tersedia = :stok WHERE id = :id");
            $stmt->execute(['id' => $id, 'stok' => $stok]);
            jsonResponse(true, 'Stok obat diperbarui');
            break;

        // ═══════════════════════════════════════
        // PRESCRIPTIONS (tb_prescriptions)
        // ═══════════════════════════════════════

        case 'get_prescriptions':
            $stmt = $db->query("SELECT pr.*, p.nama as pasien_nama, d.nama_obat 
                FROM tb_prescriptions pr 
                JOIN tb_patients p ON pr.id_pasien = p.id 
                JOIN tb_drug_inventory d ON pr.id_obat = d.id 
                ORDER BY pr.tanggal_distribusi DESC LIMIT 50");
            jsonResponse(true, '', ['prescriptions' => $stmt->fetchAll()]);
            break;

        case 'create_prescription':
            requirePost();
            $v = new Validator($_POST);
            $v->required('id_pasien', 'Pasien')
              ->required('id_obat', 'Obat')
              ->required('dosis', 'Dosis')
              ->required('jumlah_diberikan', 'Jumlah')
              ->required('tanggal_distribusi', 'Tanggal');

            if ($v->fails()) {
                jsonResponse(false, 'Validasi gagal', ['errors' => $v->errors()]);
                break;
            }

            $stmt = $db->prepare("INSERT INTO tb_prescriptions 
                (id_pasien, id_obat, dosis, frekuensi, jumlah_diberikan, tanggal_distribusi, status_ambil, catatan) 
                VALUES (:id_pasien, :id_obat, :dosis, :frekuensi, :jumlah, :tanggal, :status, :catatan)");
            $stmt->execute([
                'id_pasien' => (int)$_POST['id_pasien'],
                'id_obat' => (int)$_POST['id_obat'],
                'dosis' => $_POST['dosis'],
                'frekuensi' => $_POST['frekuensi'] ?? null,
                'jumlah' => (int)$_POST['jumlah_diberikan'],
                'tanggal' => $_POST['tanggal_distribusi'],
                'status' => $_POST['status_ambil'] ?? 'Belum Diambil',
                'catatan' => $_POST['catatan'] ?? null,
            ]);

            // Reduce stock
            $db->prepare("UPDATE tb_drug_inventory SET stok_tersedia = stok_tersedia - :jumlah WHERE id = :id")
               ->execute(['jumlah' => (int)$_POST['jumlah_diberikan'], 'id' => (int)$_POST['id_obat']]);

            jsonResponse(true, 'Distribusi obat berhasil dicatat', ['id' => $db->lastInsertId()]);
            break;

        // ═══════════════════════════════════════
        // PMO LOGS (tb_pmo_logs)
        // ═══════════════════════════════════════

        case 'get_pmo_logs':
            $stmt = $db->query("SELECT l.*, p.nama as pasien_nama 
                FROM tb_pmo_logs l 
                JOIN tb_patients p ON l.id_pasien = p.id 
                ORDER BY l.tanggal DESC, l.waktu_minum DESC LIMIT 50");
            jsonResponse(true, '', ['pmo_logs' => $stmt->fetchAll()]);
            break;

        case 'create_pmo_log':
            requirePost();
            $v = new Validator($_POST);
            $v->required('id_pasien', 'Pasien')
              ->required('tanggal', 'Tanggal')
              ->required('status_minum', 'Status minum');

            if ($v->fails()) {
                jsonResponse(false, 'Validasi gagal', ['errors' => $v->errors()]);
                break;
            }

            $stmt = $db->prepare("INSERT INTO tb_pmo_logs 
                (id_pasien, tanggal, waktu_minum, status_minum, efek_samping, catatan, metode_verifikasi) 
                VALUES (:id_pasien, :tanggal, :waktu, :status, :efek, :catatan, :metode)
                ON DUPLICATE KEY UPDATE waktu_minum = :waktu2, status_minum = :status2, efek_samping = :efek2, catatan = :catatan2, metode_verifikasi = :metode2");
            $stmt->execute([
                'id_pasien' => (int)$_POST['id_pasien'],
                'tanggal' => $_POST['tanggal'],
                'waktu' => $_POST['waktu_minum'] ?? null,
                'status' => $_POST['status_minum'],
                'efek' => $_POST['efek_samping'] ?? null,
                'catatan' => $_POST['catatan'] ?? null,
                'metode' => $_POST['metode_verifikasi'] ?? 'Langsung',
                'waktu2' => $_POST['waktu_minum'] ?? null,
                'status2' => $_POST['status_minum'],
                'efek2' => $_POST['efek_samping'] ?? null,
                'catatan2' => $_POST['catatan'] ?? null,
                'metode2' => $_POST['metode_verifikasi'] ?? 'Langsung',
            ]);

            jsonResponse(true, 'Log PMO berhasil dicatat');
            break;

        // ═══════════════════════════════════════
        // SCREENINGS (tb_screenings)
        // ═══════════════════════════════════════

        case 'get_screenings':
            $stmt = $db->query("SELECT s.*, p.nama as pasien_nama 
                FROM tb_screenings s 
                LEFT JOIN tb_patients p ON s.id_pasien = p.id 
                ORDER BY s.created_at DESC LIMIT 50");
            jsonResponse(true, '', ['screenings' => $stmt->fetchAll()]);
            break;

        case 'create_screening':
            requirePost();
            $stmt = $db->prepare("INSERT INTO tb_screenings 
                (id_pasien, nama_file_audio, durasi_detik, confidence_score, hasil, catatan, dirujuk) 
                VALUES (:id_pasien, :audio, :durasi, :confidence, :hasil, :catatan, :dirujuk)");
            $stmt->execute([
                'id_pasien' => $_POST['id_pasien'] ? (int)$_POST['id_pasien'] : null,
                'audio' => $_POST['nama_file_audio'] ?? 'recording_' . time() . '.wav',
                'durasi' => $_POST['durasi_detik'] ?? 0,
                'confidence' => $_POST['confidence_score'] ?? 0,
                'hasil' => $_POST['hasil'] ?? 'Tidak Dapat Ditentukan',
                'catatan' => $_POST['catatan'] ?? null,
                'dirujuk' => ($_POST['dirujuk'] ?? 0) ? 1 : 0,
            ]);

            jsonResponse(true, 'Hasil skrining berhasil disimpan', ['id' => $db->lastInsertId()]);
            break;

        // ═══════════════════════════════════════
        // DASHBOARD STATS
        // ═══════════════════════════════════════

        case 'get_dashboard_stats':
            $stats = [];

            // Pasien aktif
            $stmt = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Aktif'");
            $stats['pasien_aktif'] = $stmt->fetchColumn();

            // Skrining hari ini
            $stmt = $db->query("SELECT COUNT(*) FROM tb_screenings WHERE DATE(created_at) = CURDATE()");
            $stats['skrining_hari_ini'] = $stmt->fetchColumn();

            // Kepatuhan rata-rata (from pmo_logs last 30 days)
            $stmt = $db->query("SELECT 
                ROUND(SUM(CASE WHEN status_minum = 'Diminum' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as kepatuhan
                FROM tb_pmo_logs WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $row = $stmt->fetch();
            $stats['kepatuhan'] = $row['kepatuhan'] ?? 0;

            // Risiko drop-out (kepatuhan < 60%)
            $stmt = $db->query("SELECT COUNT(DISTINCT id_pasien) FROM tb_pmo_logs 
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY id_pasien 
                HAVING SUM(CASE WHEN status_minum = 'Diminum' THEN 1 ELSE 0 END) / COUNT(*) < 0.6");
            $stats['risiko_dropout'] = $stmt->rowCount();

            // Alert pasien (missed appointments, not taking meds)
            $alertStmt = $db->query("SELECT p.nama, p.no_rm, p.fase_pengobatan,
                'Belum mengambil obat' as masalah, 'Tinggi' as prioritas
                FROM tb_patients p 
                WHERE p.status = 'Aktif' 
                AND p.id NOT IN (
                    SELECT DISTINCT id_pasien FROM tb_pmo_logs 
                    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
                )
                LIMIT 5");
            $stats['alerts'] = $alertStmt->fetchAll();

            // Jadwal hari ini
            $jadwalStmt = $db->prepare("SELECT a.*, p.nama as pasien_nama 
                FROM tb_appointments a 
                JOIN tb_patients p ON a.id_pasien = p.id 
                WHERE DATE(a.tanggal_jadwal) = CURDATE() 
                ORDER BY a.tanggal_jadwal ASC LIMIT 10");
            $jadwalStmt->execute();
            $stats['jadwal_hari_ini'] = $jadwalStmt->fetchAll();

            // Trend chart data (last 12 months)
            $trendStmt = $db->query("SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as bulan,
                COUNT(*) as kasus_baru,
                SUM(CASE WHEN status = 'Sembuh' THEN 1 ELSE 0 END) as sembuh
                FROM tb_patients 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY bulan");
            $stats['trend'] = $trendStmt->fetchAll();

            // Fase distribution
            $faseStmt = $db->query("SELECT fase_pengobatan, COUNT(*) as total 
                FROM tb_patients WHERE status = 'Aktif' 
                GROUP BY fase_pengobatan");
            $stats['fase_distribusi'] = $faseStmt->fetchAll();

            jsonResponse(true, '', $stats);
            break;

        // ═══════════════════════════════════════
        // MONITORING (compliance)
        // ═══════════════════════════════════════

        case 'get_compliance':
            $stmt = $db->query("SELECT 
                p.id, p.nama, p.no_rm, p.fase_pengobatan,
                COUNT(l.id) as total_hari,
                SUM(CASE WHEN l.status_minum = 'Diminum' THEN 1 ELSE 0 END) as hari_patuh,
                ROUND(SUM(CASE WHEN l.status_minum = 'Diminum' THEN 1 ELSE 0 END) / COUNT(l.id) * 100) as kepatuhan
                FROM tb_patients p
                LEFT JOIN tb_pmo_logs l ON p.id = l.id_pasien AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                WHERE p.status = 'Aktif'
                GROUP BY p.id
                HAVING total_hari > 0
                ORDER BY kepatuhan ASC");
            $patients = $stmt->fetchAll();

            // Get heatmap for each patient (last 30 days)
            foreach ($patients as &$pat) {
                $hmStmt = $db->prepare("SELECT tanggal, status_minum FROM tb_pmo_logs 
                    WHERE id_pasien = :id AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    ORDER BY tanggal ASC");
                $hmStmt->execute(['id' => $pat['id']]);
                $logs = $hmStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
                $heatmap = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $heatmap[] = isset($logs[$date]) && $logs[$date] === 'Diminum' ? 1 : 0;
                }
                $pat['heatmap'] = $heatmap;
                $pat['kepatuhan'] = (int)$pat['kepatuhan'];

                // Risk level
                $k = $pat['kepatuhan'];
                $pat['risiko'] = $k >= 80 ? 'Rendah' : ($k >= 65 ? 'Sedang' : ($k >= 50 ? 'Tinggi' : 'Kritis'));
            }

            jsonResponse(true, '', ['patients' => $patients]);
            break;

        // ═══════════════════════════════════════
        // PATIENTS LIST (for dropdowns)
        // ═══════════════════════════════════════

        case 'get_patients_list':
            $stmt = $db->query("SELECT id, nama, no_rm FROM tb_patients WHERE status = 'Aktif' ORDER BY nama");
            jsonResponse(true, '', ['patients' => $stmt->fetchAll()]);
            break;

        default:
            jsonResponse(false, 'Action tidak dikenali: ' . $action);
    }

} catch (PDOException $e) {
    error_log("SIMRS-TB API Error: " . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(400);
    jsonResponse(false, $e->getMessage());
}

// ═══════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════

function jsonResponse(bool $success, string $message = '', array $data = []): void
{
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(false, 'Method POST required');
    }
}

function generateNoRM(PDO $db): string
{
    $year = date('Y');
    $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(no_rm, 9) AS UNSIGNED)) as last_num FROM tb_patients WHERE no_rm LIKE 'RM-$year-%'");
    $row = $stmt->fetch();
    $next = ($row['last_num'] ?? 0) + 1;
    return "RM-$year-" . str_pad($next, 4, '0', STR_PAD_LEFT);
}
