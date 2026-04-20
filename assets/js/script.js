/* ═══════════════════════════════════════════
   MED-SOLVE LABORATORIUM — script.js
═══════════════════════════════════════════ */
// ─── PROJECT DATA ─────────────────────────
const projects = typeof projectsData !== 'undefined' ? projectsData : [];
let currentProjectId = null;

// ─── SECTION SWITCHING ───────────────────────
function showSection(id) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) {
    target.classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

// ─── DELETE IMAGE ────────────────────────────
function deleteImage(imagePath, btn) {
  if (!confirm("Hapus gambar ini?")) return;

  fetch('/medical-web/modules/modul_5/delete_image.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ path: imagePath })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Gambar dihapus', 'success');
      btn.parentElement.remove();
    } else {
      showToast('Gagal hapus gambar', 'warn');
    }
  });
}

// ─── EDIT PROJECT (dari detail page) ─────────
let editMode = false;

function editProject() {
  if (!currentProjectId) {
    showToast("Buka project dulu sebelum edit", "warn");
    return;
  }
  _loadEditForm(currentProjectId);
}

// ─── EDIT PROJECT (dari history) ─────────────
function editFromHistory(id) {
  currentProjectId = id;
  _loadEditForm(id);
}

// ─── SHARED: load form untuk edit ────────────
function _loadEditForm(id) {
  const project = projects.find(p => p.id == id);
  if (!project) {
    showToast("Data project tidak ditemukan", "warn");
    return;
  }

  editMode = true;
  showSection('upload');

  document.getElementById('upload-title').textContent = 'Edit Project';
  document.getElementById('u-problem').value      = project.problem    || '';
  document.getElementById('u-solution').value     = project.title      || '';
  document.getElementById('u-methodology').value  = Array.isArray(project.methodology)
    ? project.methodology.join('\n') : (project.methodology || '');
  document.getElementById('u-skills').value       = Array.isArray(project.skills)
    ? project.skills.join('\n') : (project.skills || '');
  document.getElementById('u-result').value       = project.result     || '';
  document.getElementById('u-impact').value       = project.impact     || '';
  document.getElementById('u-contributor').value  = project.contributor_name || '';

  // Load gambar lama
  const previewRow = document.getElementById('img-preview-row');
  previewRow.innerHTML = '';

  fetch('/medical-web/modules/modul_5/get_project_files.php?id=' + encodeURIComponent(id))
    .then(res => res.json())
    .then(images => {
      if (!Array.isArray(images)) return;
      images.forEach(img => {
        const div = document.createElement('div');
        div.className = 'img-preview-item-wrapper';
        div.innerHTML = `
          <img src="/medical-web/${img}" class="img-preview-item">
          <button class="img-delete-btn" onclick="deleteImage('${img}', this)">✖</button>
        `;
        previewRow.appendChild(div);
      });
    });
}

// ─── DELETE PROJECT (dari history) ───────────
function deleteFromHistory(id) {
  if (!confirm("Yakin mau hapus project ini?")) return;

  fetch('/medical-web/modules/modul_5/delete_project.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Project berhasil dihapus', 'success');
      setTimeout(() => {
        localStorage.setItem('goToProjects', 'true');
        location.reload();
      }, 1000);
    } else {
      showToast('Gagal hapus project', 'warn');
    }
  });
}

// ─── DELETE PROJECT (dari detail page) ───────
function deleteProject() {
  if (!currentProjectId) return;
  deleteFromHistory(currentProjectId);
}

// ─── RENDER PROJECT CARDS ────────────────────
function renderProjects() {
  const grid = document.getElementById('projects-grid');
  if (!grid) return;

  grid.innerHTML = '';

  projects.forEach(p => {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.onclick = () => openDetail(p.id);

    let firstImage = null;
    if (p.documentation) {
      let imgs = [];
      if (typeof p.documentation === 'string') {
        try { imgs = JSON.parse(p.documentation); } catch { imgs = [p.documentation]; }
      } else if (Array.isArray(p.documentation)) {
        imgs = p.documentation;
      }
      if (imgs.length > 0) {
        firstImage = imgs[0].replace('modules/modul_5/', '');
      }
    }

    card.innerHTML = `
      <div class="card-img">
        ${firstImage
          ? `<img src="/medical-web/${firstImage}" class="card-img-photo">`
          : `<span class="card-img-icon">${p.icon}</span>`}
      </div>
      <div class="card-body">
        <div class="card-title">${p.title}</div>
        <p class="card-question">${p.question}</p>
      </div>
    `;

    grid.appendChild(card);
  });
}

// ─── OPEN PROJECT DETAIL ─────────────────────
function openDetail(id) {
  currentProjectId = id;
  const project = projects.find(p => p.id == id);

  if (!project) {
    showToast("Project tidak ditemukan", "warn");
    return;
  }

  document.getElementById("detail-title").innerText    = project.title;
  document.getElementById("detail-question").innerText = project.question;
  document.getElementById("detail-problem").innerText  = project.problem;
  document.getElementById("detail-result").innerText   = project.result;
  document.getElementById("detail-impact").innerText   = project.impact;

  const methodDiv = document.getElementById("detail-methodology");
  methodDiv.innerHTML = "";
  project.methodology.forEach(step => {
    const el = document.createElement("div");
    el.className = "pipeline-step";
    el.innerText = step;
    methodDiv.appendChild(el);
  });

  const skillsUl = document.getElementById("detail-skills");
  skillsUl.innerHTML = "";
  project.skills.forEach(skill => {
    const li = document.createElement("li");
    li.innerText = skill;
    skillsUl.appendChild(li);
  });

  const docsGrid = document.querySelector(".docs-grid");
  docsGrid.innerHTML = "";

  fetch('/medical-web/modules/modul_5/get_project_files.php?id=' + encodeURIComponent(id))
    .then(res => res.json())
    .then(images => {
      docsGrid.innerHTML = "";

      if (images && images.success === false) {
        docsGrid.innerHTML = `<p>${images.message}</p>`;
        return;
      }
      if (!Array.isArray(images)) {
        docsGrid.innerHTML = "<p>Data gambar error</p>";
        return;
      }
      if (images.length === 0) {
        docsGrid.innerHTML = "<p>Tidak ada dokumentasi</p>";
        return;
      }

      images.forEach(img => {
        const div   = document.createElement("div");
        div.className = "doc-item";
        const image = document.createElement("img");
        image.src   = "/medical-web/" + img;
        image.className = "doc-image";
        div.appendChild(image);
        docsGrid.appendChild(div);
      });
    })
    .catch(err => {
      console.error(err);
      docsGrid.innerHTML = "<p>Gagal load gambar</p>";
    });

  showSection('detail');
}

// ─── TOAST NOTIFICATION ──────────────────────
function showToast(message, type = 'success') {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;

  Object.assign(toast.style, {
    position: 'fixed',
    bottom: '2rem',
    left: '50%',
    transform: 'translateX(-50%)',
    background: type === 'success' ? '#1a3460' : '#c0602a',
    color: '#fff',
    padding: '0.85rem 2rem',
    borderRadius: '50px',
    fontSize: '0.9rem',
    fontWeight: '500',
    fontFamily: "'DM Sans', sans-serif",
    zIndex: '9999',
    boxShadow: '0 8px 28px rgba(0,0,0,0.22)',
    opacity: '0',
    transition: 'opacity 0.3s ease'
  });

  document.body.appendChild(toast);
  requestAnimationFrame(() => { toast.style.opacity = '1'; });
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ─── UPLOAD HANDLER ──────────────────────────
function handleUpload() {
  const formData = new FormData();

  formData.append('problem',      document.getElementById('u-problem').value.trim());
  formData.append('title',        document.getElementById('u-solution').value.trim());
  formData.append('methodology',  document.getElementById('u-methodology').value.trim());
  formData.append('skills',       document.getElementById('u-skills').value.trim());
  formData.append('result',       document.getElementById('u-result').value.trim());
  formData.append('impact',       document.getElementById('u-impact').value.trim());
  formData.append('contributor',  document.getElementById('u-contributor').value.trim());
  formData.append('id',           currentProjectId);
  formData.append('editMode',     editMode);

  const input = document.getElementById('img-upload');
  if (input.files.length > 0) {
    for (let i = 0; i < input.files.length; i++) {
      formData.append('images[]', input.files[i]);
    }
  }

  fetch('/medical-web/modules/modul_5/upload_project.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Project berhasil disimpan!', 'success');
      setTimeout(() => {
        localStorage.setItem('openProjectAfterReload', data.projectId);
        location.reload();
      }, 800);
    } else {
      showToast(data.message || 'Upload gagal.', 'warn');
    }
  })
  .catch(error => {
    console.error(error);
    showToast('Upload failed.', 'warn');
  });
}

// ─── IMAGE UPLOAD PREVIEW ────────────────────
function handleImageUpload(input) {
  const preview = document.getElementById('img-preview-row');
  const files   = Array.from(input.files);
  if (files.length === 0) return;

  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = function(e) {
      const wrapper = document.createElement('div');
      wrapper.className = 'img-preview-item-wrapper';

      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'img-preview-item';

      const btn = document.createElement('button');
      btn.className = 'img-delete-btn';
      btn.innerText = '✖';
      btn.onclick = () => {
        wrapper.remove();
        removeFileFromInput(file);
      };

      wrapper.appendChild(img);
      wrapper.appendChild(btn);
      preview.appendChild(wrapper);
    };
    reader.readAsDataURL(file);
  });
}

function removeFileFromInput(fileToRemove) {
  const input = document.getElementById('img-upload');
  const dt    = new DataTransfer();
  Array.from(input.files).forEach(file => {
    if (file !== fileToRemove) dt.items.add(file);
  });
  input.files = dt.files;
}

// ─── INIT ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderProjects();

  // Reset upload form saat buka section upload baru
  const uploadBtn = document.querySelector('[onclick="showSection(\'upload\')"]');
  if (uploadBtn) {
    uploadBtn.addEventListener('click', () => {
      editMode = false;
      currentProjectId = null;
      document.getElementById('upload-title').textContent = 'Post A New Project!';
      ['u-problem','u-solution','u-methodology','u-skills','u-result','u-impact','u-contributor']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
      document.getElementById('img-preview-row').innerHTML = '';
    });
  }

  // Hero bounce hint
  const hero = document.getElementById('hero');
  if (hero) {
    const hint = document.createElement('div');
    Object.assign(hint.style, {
      position: 'absolute',
      bottom: '2rem',
      left: '50%',
      transform: 'translateX(-50%)',
      fontSize: '1.4rem',
      color: 'rgba(26,52,96,0.35)',
      animation: 'bounce 2s ease-in-out infinite',
      cursor: 'pointer',
      userSelect: 'none'
    });
    hint.textContent = '⌄';
    hint.onclick = () => showSection('projects');
    hero.appendChild(hint);

    const style = document.createElement('style');
    style.textContent = `
      @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%       { transform: translateX(-50%) translateY(8px); }
      }
    `;
    document.head.appendChild(style);
  }

  // Restore state setelah reload
  const openId      = localStorage.getItem('openProjectAfterReload');
  const goToProjects = localStorage.getItem('goToProjects');

  if (openId) {
    localStorage.removeItem('openProjectAfterReload');
    setTimeout(() => openDetail(openId), 300);
  } else if (goToProjects) {
    localStorage.removeItem('goToProjects');
    showSection('projects');
  }
});