
/* ═══════════════════════════════════════════
   MED-SOLVE LABORATORIUM — script.js
═══════════════════════════════════════════ */
// ─── PROJECT DATA ─────────────────────────
// Gunakan data dari PHP jika tersedia
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

function deleteImage(imagePath, btn) {
  if (!confirm("Hapus gambar ini?")) return;

  fetch('/medical-web/modules/modul_5/delete_image.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      path: imagePath
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Gambar dihapus', 'success');

      // hapus dari tampilan
      btn.parentElement.remove();
    } else {
      showToast('Gagal hapus gambar', 'warn');
    }
  });
}

let editMode = false;
function editProject() {
  if (!currentProjectId) {
  showToast("Buka project dulu sebelum edit", "warn");
  return;
}
  const project = projects.find(p => p.id == currentProjectId);

if (!project) {
  showToast("Data project tidak ditemukan", "warn");
  return;
}


  editMode = true;

  showSection('upload');

  document.getElementById('u-problem').value = project.problem;
  document.getElementById('u-solution').value = project.title;
  document.getElementById('u-methodology').value = project.methodology.join('\n');
  document.getElementById('u-skills').value = project.skills.join('\n');
  document.getElementById('u-result').value = project.result;
  document.getElementById('u-impact').value = project.impact;
  // 🔽 LOAD GAMBAR LAMA
const previewRow = document.getElementById('img-preview-row');
//previewRow.innerHTML = '';

fetch('/medical-web/modules/modul_5/get_project_files.php?id=' + encodeURIComponent(currentProjectId))
  .then(res => res.json())
  .then(images => {
    if (!Array.isArray(images)) {
  console.error("Bukan array:", images);
  return;
}

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


// ─── RENDER PROJECT CARDS ────────────────────
function renderProjects() {
  const grid = document.getElementById('projects-grid');
  if (!grid) return;

  grid.innerHTML = '';

  projects.forEach(p => {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.onclick = () => openDetail(p.id) ;

    let firstImage = null;

if (p.documentation) {
  let imgs = [];

  if (typeof p.documentation === 'string') {
    try {
      imgs = JSON.parse(p.documentation);
    } catch {
      imgs = [p.documentation];
    }
  } else if (Array.isArray(p.documentation)) {
    imgs = p.documentation;
  }

  if (imgs.length > 0) {
    firstImage = imgs[0];
    firstImage = firstImage.replace('modules/modul_5/', '');

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

  document.getElementById("detail-title").innerText = project.title;
  document.getElementById("detail-question").innerText = project.question;
  document.getElementById("detail-problem").innerText = project.problem;
  document.getElementById("detail-result").innerText = project.result;
  document.getElementById("detail-impact").innerText = project.impact;

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

  fetch('/medical-web/modules/modul_5/get_project_files.php?id=' + encodeURIComponent(currentProjectId))
    .then(res => res.json())
    .then(images => {

  const docsGrid = document.querySelector(".docs-grid");
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
    const div = document.createElement("div");
    div.className = "doc-item";

    const image = document.createElement("img");
    image.src = "/medical-web/" + img;
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

  // Animasi muncul
  requestAnimationFrame(() => {
    toast.style.opacity = '1';
  });

  // Hilang setelah 3 detik
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
// ─── UPLOAD HANDLER ──────────────────────────
function handleUpload() {
  const formData = new FormData();

  formData.append('problem', document.getElementById('u-problem').value.trim());
  formData.append('title', document.getElementById('u-solution').value.trim());
  formData.append('methodology', document.getElementById('u-methodology').value.trim());
  formData.append('skills', document.getElementById('u-skills').value.trim());
  formData.append('result', document.getElementById('u-result').value.trim());
  formData.append('impact', document.getElementById('u-impact').value.trim());
  formData.append('id', currentProjectId);
  formData.append('editMode', editMode);

  const input = document.getElementById('img-upload');

// ❌ HAPUS VALIDASI INI

// ✅ tetap kirim kalau ada file
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
  }
})
.catch(error => {
  console.error(error);
  showToast('Upload failed.', 'warn');
});
}

// ─── INIT ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderProjects();

  // Hero scroll hint
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

    // Add bounce keyframe dynamically
    const style = document.createElement('style');
    style.textContent = `
      @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%       { transform: translateX(-50%) translateY(8px); }
      }
    `;
    document.head.appendChild(style);
  }
  const openId = localStorage.getItem('openProjectAfterReload');
const goToProjects = localStorage.getItem('goToProjects');

if (openId) {
  localStorage.removeItem('openProjectAfterReload');

  setTimeout(() => {
    openDetail(openId);
  }, 300);

} else if (goToProjects) {
  localStorage.removeItem('goToProjects');
  showSection('projects');
}
}
);

function handleImageUpload(input) {
  const preview = document.getElementById('img-preview-row');

  const files = Array.from(input.files);
  if (files.length === 0) return;

  files.forEach((file, index) => {
    const reader = new FileReader();

    reader.onload = function (e) {
      const wrapper = document.createElement('div');
      wrapper.className = 'img-preview-item-wrapper';

      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'img-preview-item';

      // tombol hapus
      const btn = document.createElement('button');
      btn.className = 'img-delete-btn';
      btn.innerText = '✖';

      btn.onclick = () => {
        wrapper.remove();

        // ❗ hapus file dari input juga
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
  const dt = new DataTransfer();

  Array.from(input.files).forEach(file => {
    if (file !== fileToRemove) {
      dt.items.add(file);
    }
  });

  input.files = dt.files;
}

function deleteProject() {
  if (!confirm("Yakin mau hapus project ini?")) return;

  fetch('/medical-web/modules/modul_5/delete_project.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: currentProjectId
    })
  })
  .then(res => res.json())
.then(data => {
  console.log("DELETE RESPONSE:", data); // 🔥 TAMBAH INI

  if (data.success) {
    showToast('Project berhasil dihapus', 'success');

    setTimeout(() => {
  localStorage.setItem('goToProjects', 'true');
  location.reload();
}, 1000);
  } else {
    alert("ERROR: " + data.message); // 🔥 TAMBAH INI
    showToast('Gagal hapus project', 'warn');
  }
})
}
