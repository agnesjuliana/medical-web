<?php
/**
 * Modul 5 — Landing Page
 * 
 * Initial page for Modul 5.
 * Each module uses the shared auth system (SSO)
 * and can define its own database schema.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';
require_once __DIR__ . '/../../config/database.php';

startSession();
requireLogin();

$user = getCurrentUser();
$pageTitle = 'Modul 5';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<?php

try {
    $pdo = getAppDBConnection();

    $stmt = $pdo->query("
        SELECT id, problem, title, methodology, skills, result, impact, documentation
        FROM projects
        ORDER BY id DESC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi methodology dan skills menjadi array
    foreach ($projects as &$project) {
        $project['methodology'] = !empty($project['methodology'])
            ? array_map('trim', preg_split('/[\n,→]+/', $project['methodology']))
            : [];

        $project['skills'] = !empty($project['skills'])
            ? array_map('trim', preg_split('/[\n,•]+/', $project['skills']))
            : [];

        // Nilai default jika kolom tidak tersedia
        $project['icon'] = "🔬";
        $project['title'] = $project['title'] ?? 'Untitled Project';
        $project['question'] = $project['problem'];
    }
    unset($project);
} catch (Exception $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- ═══════════════════════════════════════════
       HERO SECTION
  ═══════════════════════════════════════════ -->
  <section id="hero" class="section active">
    <div class="hero-topbar">MEDICAL WEBSITE – 05</div>

    <div class="hero-inner">
      <!-- Left: watermark logo -->
      <div class="hero-logo-wrap">
        <img
          src="https://upload.wikimedia.org/wikipedia/id/b/b3/Logo_ITS_-_Institut_Teknologi_Sepuluh_Nopember.png"
          alt="ITS Logo watermark"
          class="hero-logo-img"
          onerror="this.style.display='none'; document.querySelector('.logo-fallback').style.display='flex';"
        />
        <!-- SVG fallback caduceus logo -->
        <div class="logo-fallback" style="display:none;">
          <svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" class="caduceus-svg">
            <circle cx="110" cy="110" r="105" fill="none" stroke="#1e3a5f" stroke-width="6"/>
            <text x="110" y="48" text-anchor="middle" font-family="DM Sans" font-size="13" fill="#1e3a5f" letter-spacing="3">TEKNOLOGI</text>
            <text x="110" y="182" text-anchor="middle" font-family="DM Sans" font-size="11" fill="#1e3a5f" letter-spacing="2">KEDOKTERAN</text>
            <!-- Caduceus staff -->
            <line x1="110" y1="70" x2="110" y2="155" stroke="#1e3a5f" stroke-width="4" stroke-linecap="round"/>
            <!-- Wings -->
            <path d="M110 80 Q90 68 75 75 Q90 82 110 90" fill="#1e3a5f" opacity="0.7"/>
            <path d="M110 80 Q130 68 145 75 Q130 82 110 90" fill="#1e3a5f" opacity="0.7"/>
            <!-- Snakes -->
            <path d="M110 95 Q100 105 110 115 Q120 125 110 135 Q100 145 110 155" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-linecap="round"/>
            <path d="M110 95 Q120 105 110 115 Q100 125 110 135 Q120 145 110 155" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-linecap="round"/>
            <!-- Gear teeth -->
            <circle cx="110" cy="110" r="80" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-dasharray="8 6"/>
          </svg>
        </div>
      </div>

      <!-- Right: text -->
      <div class="hero-text">
        <h1 class="hero-title">Med-Solve</h1>
        <h2 class="hero-subtitle"><em>Laboratorium</em></h2>
        <p class="hero-org">Medical Technology, ITS</p>
        <p class="hero-tagline">Solving Clinical Problems with Engineering Solutions</p>
        <button class="btn-primary" onclick="showSection('projects')">VIEW PROJECTS</button>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════
       PROJECTS SECTION
  ═══════════════════════════════════════════ -->
  <section id="projects" class="section">
    <div class="projects-inner">
      <div class="projects-header">
        <div class="projects-title-row">
          <div>
            <h2 class="section-title">Our <em>projects</em></h2>
            <p class="section-subtitle">Transforming real medical challenges into innovative technology</p>
          </div>
          <button class="btn-upload-circle" onclick="showSection('upload')" title="Upload Your Project">
            <span class="upload-icon">✏️</span>
            <span class="upload-ring-text">Upload Your Project!</span>
          </button>
        </div>
      </div>

      <div class="projects-grid" id="projects-grid">
<?php if (!empty($projects)): ?>
    <?php foreach ($projects as $project): ?>
        <div class="project-card" onclick="openDetail(<?= $project['id'] ?>)">
            <h3>
                <?= htmlspecialchars(substr($project['problem'], 0, 50)) ?>...
            </h3>
            <p>
                <?= htmlspecialchars(substr($project['question'] ?? $project['problem'], 0, 80)) ?>...
            </p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Belum ada data project.</p>
<?php endif; ?>
</div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════
       PROJECT DETAIL SECTION
  ═══════════════════════════════════════════ -->
  <section id="detail" class="section">
    <div class="detail-inner">
      <button class="btn-back" onclick="showSection('projects')">← Back to Projects</button>
      <button class="btn-home" onclick="showSection('hero')" title="Home">🏠</button>

<div style="margin: 10px 0;">
  <button onclick="editProject()" class="btn-primary">✏️ Edit</button>
  <button onclick="deleteProject()" class="btn-danger">🗑 Delete</button>
</div>

      <h2 class="detail-title" id="detail-title"></h2>
      <p class="detail-question" id="detail-question"></p>

      <div class="detail-flow">
        <div class="flow-card flow-card--teal">
          <h4>Problem</h4>
          <p id="detail-problem"></p>
        </div>
        <div class="flow-arrow">→</div>
        <div class="flow-card flow-card--blue">
          <h4>Methodology</h4>
          <div id="detail-methodology" class="pipeline"></div>
        </div>
        <div class="flow-arrow">→</div>
        <div class="flow-card flow-card--gray">
          <h4>Skills &amp; Tools</h4>
          <ul id="detail-skills"></ul>
        </div>
      </div>

      <div class="detail-outcomes">
        <div class="outcome-card">
          <span class="outcome-label">Result.</span>
          <p id="detail-result"></p>
        </div>
        <div class="outcome-card">
          <span class="outcome-label">Impact.</span>
          <p id="detail-impact"></p>
        </div>
      </div>

      <div class="detail-docs">
        <div class="docs-label">Documentation.</div>
        <div class="docs-grid">
          <div class="doc-placeholder"><span>📷</span></div>
          <div class="doc-placeholder"><span>📷</span></div>
          <div class="doc-placeholder"><span>📷</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════
       UPLOAD SECTION
  ═══════════════════════════════════════════ -->
  <section id="upload" class="section">
    <div class="upload-inner">
      <button class="btn-back" onclick="showSection('projects')">← Back to Projects</button>

      <div class="upload-header-tag" id="upload-title">Post A New Project!</div>

      <div class="upload-flow-diagram">
        <div class="uflow-box">
          <div class="uflow-label">Problem.</div>
          <textarea class="uflow-input" id="u-problem" placeholder="Type Here..."></textarea>
        </div>
        <div class="uflow-arrow">→</div>
        <div class="uflow-box">
          <div class="uflow-label">Solution.</div>
          <textarea class="uflow-input" id="u-solution" placeholder="Type Here..."></textarea>
        </div>
        <div class="uflow-arrow">→</div>
        <div class="uflow-box">
          <div class="uflow-label">Methodology.</div>
          <textarea class="uflow-input" id="u-methodology" placeholder="Type Here..."></textarea>
        </div>
        <div class="uflow-arrow">→</div>
        <div class="uflow-box">
          <div class="uflow-label">Skills &amp; Tools.</div>
          <textarea class="uflow-input" id="u-skills" placeholder="List Here..."></textarea>
        </div>
      </div>

      <div class="upload-bottom-row">
        <div class="upload-img-btn" onclick="document.getElementById('img-upload').click()">
          <span class="upload-plus">＋</span>
          <span class="upload-img-label">UPLOAD IMAGES</span>
          <input type="file" id="img-upload" accept="image/*" multiple style="display:none;" onchange="handleImageUpload(this)"/>
        </div>

        <div class="uflow-box uflow-box--wide">
          <div class="uflow-label">Impact.</div>
          <textarea class="uflow-input" id="u-impact" placeholder="Type Here..."></textarea>
        </div>
        <div class="uflow-arrow uflow-arrow--left">←</div>
        <div class="uflow-box uflow-box--wide">
          <div class="uflow-label">Result.</div>
          <textarea class="uflow-input" id="u-result" placeholder="Type Here..."></textarea>
        </div>
      </div>

      <div id="img-preview-row" class="img-preview-row"></div>

      <div class="upload-actions">
        <button class="btn-primary btn-upload-submit" onclick="handleUpload()">UPLOAD</button>
        <button class="btn-home" onclick="showSection('hero')" title="Home">🏠</button>
      </div>
    </div>
  </section>

  <script>
const projectsData = <?= json_encode($projects); ?>;
</script>

  <link rel="stylesheet" href="/medical-web/assets/style.css">
<script src="/medical-web/assets/js/script.js"></script>

</main>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>