
<?php
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
        SELECT id, problem, title, methodology, skills, result, impact, documentation,
               contributor_name, created_at
        FROM projects
        ORDER BY id DESC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($projects as &$project) {
        $project['methodology'] = !empty($project['methodology'])
            ? array_map('trim', preg_split('/[\n,→]+/', $project['methodology']))
            : [];
        $project['skills'] = !empty($project['skills'])
            ? array_map('trim', preg_split('/[\n,•]+/', $project['skills']))
            : [];
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

  <section id="hero" class="section active">
    <div class="hero-topbar">MEDICAL WEBSITE – 05</div>
    <div class="hero-inner">
      <div class="hero-logo-wrap">
        <img
          src="https://upload.wikimedia.org/wikipedia/id/b/b3/Logo_ITS_-_Institut_Teknologi_Sepuluh_Nopember.png"
          alt="ITS Logo watermark"
          class="hero-logo-img"
          onerror="this.style.display='none'; document.querySelector('.logo-fallback').style.display='flex';"
        />
        <div class="logo-fallback" style="display:none;">
          <svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" class="caduceus-svg">
            <circle cx="110" cy="110" r="105" fill="none" stroke="#1e3a5f" stroke-width="6"/>
            <text x="110" y="48" text-anchor="middle" font-family="DM Sans" font-size="13" fill="#1e3a5f" letter-spacing="3">TEKNOLOGI</text>
            <text x="110" y="182" text-anchor="middle" font-family="DM Sans" font-size="11" fill="#1e3a5f" letter-spacing="2">KEDOKTERAN</text>
            <line x1="110" y1="70" x2="110" y2="155" stroke="#1e3a5f" stroke-width="4" stroke-linecap="round"/>
            <path d="M110 80 Q90 68 75 75 Q90 82 110 90" fill="#1e3a5f" opacity="0.7"/>
            <path d="M110 80 Q130 68 145 75 Q130 82 110 90" fill="#1e3a5f" opacity="0.7"/>
            <path d="M110 95 Q100 105 110 115 Q120 125 110 135 Q100 145 110 155" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-linecap="round"/>
            <path d="M110 95 Q120 105 110 115 Q100 125 110 135 Q120 145 110 155" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-linecap="round"/>
            <circle cx="110" cy="110" r="80" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-dasharray="8 6"/>
          </svg>
        </div>
      </div>
      <div class="hero-text">
        <h1 class="hero-title">Med-Solve</h1>
        <h2 class="hero-subtitle"><em>Laboratorium</em></h2>
        <p class="hero-org">Medical Technology, ITS</p>
        <p class="hero-tagline">Solving Clinical Problems with Engineering Solutions</p>
        <button class="btn-primary" onclick="showSection('projects')">VIEW PROJECTS</button>
      </div>
    </div>
  </section>

  <section id="projects" class="section">
    <div class="projects-inner">
      <div class="projects-header">
        <div class="projects-title-row">
          <div>
            <h2 class="section-title">Our <em>projects</em></h2>
            <p class="section-subtitle">Transforming real medical challenges into innovative technology</p>
          </div>

          <div class="projects-actions">
            <button class="btn-home" onclick="showSection('hero')" title="Home">🏠</button>

            <div class="btn-upload-wrapper">
              <svg class="upload-ring-svg" viewBox="0 0 110 110" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <path id="circlePath" d="M 55,55 m -50,0 a 50,50 0 1,1 100,0 a 50,50 0 1,1 -100,0"/>
                </defs>
                <text font-size="9" fill="#1a3460" font-family="DM Sans, sans-serif" font-weight="700" letter-spacing="2.5">
                  <textPath href="#circlePath">Upload Your Project! • Upload Your Project! •</textPath>
                </text>
              </svg>
              <button class="btn-upload-circle" onclick="showSection('upload')" title="Upload Your Project">
                <span class="upload-icon">✏️</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="projects-grid" id="projects-grid">
        <?php if (!empty($projects)): ?>
          <?php foreach ($projects as $project): ?>
            <div class="project-card" onclick="openDetail(<?= $project['id'] ?>)">
              <h3><?= htmlspecialchars(substr($project['problem'], 0, 50)) ?>...</h3>
              <p><?= htmlspecialchars(substr($project['question'] ?? $project['problem'], 0, 80)) ?>...</p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Belum ada data project.</p>
        <?php endif; ?>
      </div>

      <!-- ══ HISTORY SECTION ══ -->
      <div class="history-wrapper">
        <div class="history-header">
          <div class="history-title-group">
            <h2 class="section-title">Upload <em>history</em></h2>
            <p class="section-subtitle">Semua project yang pernah diunggah oleh anggota tim</p>
          </div>
          <div class="history-count-badge">
            <span id="history-count"><?= count($projects) ?></span> projects
          </div>
        </div>

        <div class="history-table-wrap">
          <?php if (!empty($projects)): ?>
            <table class="history-table">
              <thead>
                <tr>
                  <th class="ht-num">#</th>
                  <th class="ht-title">Project / Problem</th>
                  <th class="ht-contributor">Uploaded by</th>
                  <th class="ht-date">Date</th>
                  <th class="ht-actions">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($projects as $i => $project): ?>
                  <tr class="history-row" data-id="<?= $project['id'] ?>">
                    <td class="ht-num">
                      <span class="row-index"><?= $i + 1 ?></span>
                    </td>
                    <td class="ht-title">
                      <span class="ht-title-text" onclick="openDetail(<?= $project['id'] ?>)">
                        <?= htmlspecialchars($project['title'] ?? 'Untitled') ?>
                      </span>
                      <span class="ht-problem-preview">
                        <?= htmlspecialchars(substr($project['problem'], 0, 60)) ?>...
                      </span>
                    </td>
                    <td class="ht-contributor">
                      <div class="contributor-pill">
                        <span class="contributor-avatar">
                          <?= strtoupper(substr($project['contributor_name'] ?? '?', 0, 1)) ?>
                        </span>
                        <span class="contributor-name">
                          <?= htmlspecialchars($project['contributor_name'] ?? 'Unknown') ?>
                        </span>
                      </div>
                    </td>
                    <td class="ht-date">
                      <span class="date-text">
                        <?= date('d M Y', strtotime($project['created_at'])) ?>
                      </span>
                      <span class="time-text">
                        <?= date('H:i', strtotime($project['created_at'])) ?>
                      </span>
                    </td>
                    <td class="ht-actions">
                      <button
                        class="ht-btn ht-btn--edit"
                        onclick="editFromHistory(<?= $project['id'] ?>)"
                        title="Edit project"
                      >✏️ Edit</button>
                      <button
                        class="ht-btn ht-btn--delete"
                        onclick="deleteFromHistory(<?= $project['id'] ?>)"
                        title="Delete project"
                      >🗑 Delete</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="history-empty">
              <span class="history-empty-icon">📋</span>
              <p>Belum ada project yang diunggah.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- ══ END HISTORY ══ -->

    </div>
  </section>

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

      <!-- Contributor name field -->
      <div class="contributor-input-row">
        <div class="uflow-box uflow-box--contributor">
          <div class="uflow-label">Your Name.</div>
          <input
            type="text"
            class="uflow-input uflow-input--single"
            id="u-contributor"
            placeholder="Nama kamu..."
          />
        </div>
      </div>

      <div class="upload-bottom-row">
        <div class="upload-img-btn" onclick="document.getElementById('img-upload').click()">
          <span class="upload-plus">＋</span>
          <span class="upload-img-label">UPLOAD IMAGES</span>
          <input
            type="file"
            id="img-upload"
            name="images[]"
            accept="image/*"
            multiple
            style="display:none;"
            onchange="handleImageUpload(this)"
          />
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