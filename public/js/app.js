// public/js/app.js

/* ── Toast notification system ── */
function showToast(message, type = 'info', duration = 4500) {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icons = { success: 'check-circle', error: 'alert-circle', warning: 'alert-triangle', info: 'info' };
  const icon = icons[type] || icons.info;
  const toast = document.createElement('div');
  toast.className = 'toast toast-' + type;
  toast.innerHTML = '<i data-lucide="' + icon + '" class="toast-icon"></i>'
    + '<span class="toast-body">' + message + '</span>'
    + '<button class="toast-close" onclick="this.closest(\'.toast\').classList.add(\'toast-out\'); setTimeout(()=>this.closest(\'.toast\').remove(),300)">&times;</button>';
  container.appendChild(toast);
  if (typeof lucide !== 'undefined') lucide.createIcons({ attrs: { class: ['toast-icon'] } });
  setTimeout(() => {
    if (toast.isConnected) { toast.classList.add('toast-out'); setTimeout(() => toast.remove(), 300); }
  }, duration);
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.add('active');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove('active');
}

async function submitForm(formEl, endpoint, opts = {}) {
  const { errorDiv, onSuccess, onError } = opts;
  const data = new FormData(formEl);
  if (typeof CSRF_TOKEN !== 'undefined') data.set('csrf_token', CSRF_TOKEN);
  try {
    const res = await fetch(endpoint, { method: 'POST', body: data });
    const json = await res.json();
    if (json.ok) {
      if (onSuccess) onSuccess(json);
      else { showToast(json.message || 'Opération réussie', 'success'); location.reload(); }
    } else {
      if (errorDiv) {
        errorDiv.textContent = json.message || 'Erreur.';
      } else {
        showToast(json.message || 'Erreur', 'error');
      }
      if (onError) onError(json);
    }
  } catch (err) {
    if (errorDiv) {
      errorDiv.textContent = 'Erreur: ' + err.message;
    } else {
      showToast('Erreur: ' + err.message, 'error');
    }
    if (onError) onError(err);
  }
}

async function postJSON(endpoint, payload) {
  const body = JSON.stringify({ ...payload, csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '' });
  return fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body }).then(r => r.json());
}

document.addEventListener('DOMContentLoaded', () => {
  // Toggle password visibility
  document.querySelectorAll('.toggle-pwd').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = document.getElementById(btn.dataset.target);
      if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  // Sidebar toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const topbarMenu = document.getElementById('topbarMenu');
  const sidebar = document.getElementById('sidebar');
  const toggleSidebar = () => { if (sidebar) sidebar.classList.toggle('active'); };
  if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
  if (topbarMenu) topbarMenu.addEventListener('click', toggleSidebar);

  // Tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tabId = btn.dataset.tab;
      const container = btn.closest('.tabs-container');
      if (container) {
        container.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        container.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        const tabContent = document.getElementById('tab-' + tabId);
        if (tabContent) tabContent.classList.add('active');
      }
    });
  });

  // Modal overlay click
  document.addEventListener('click', (e) => {
    if (e.target && e.target.classList.contains('modal-overlay')) {
      e.target.classList.remove('active');
    }
  });

  // Budget type toggle
  document.querySelectorAll('[data-type]').forEach(label => {
    label.addEventListener('click', () => {
      const membresSection = document.getElementById('membresSection');
      if (membresSection) {
        membresSection.style.display = label.dataset.type === 'PARTAGE' ? 'block' : 'none';
      }
    });
  });

  const BASE = typeof BASE_URL !== 'undefined' ? BASE_URL : '';

  // === Form: Add Transaction ===
  const formAddTx = document.getElementById('formAddTx');
  if (formAddTx && BASE) {
    fetch(BASE + '/api/categories')
      .then(r => r.json())
      .then(cats => {
        const sel = document.getElementById('selectCategorie');
        if (sel) {
          cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom;
            sel.appendChild(opt);
          });
        }
      })
      .catch(() => {});

    const selBudget = document.getElementById('selectBudget');
    const soldeInfo = document.getElementById('budgetSoldeInfo');
    if (selBudget && soldeInfo) {
      selBudget.addEventListener('change', () => {
        const opt = selBudget.options[selBudget.selectedIndex];
        if (opt && opt.dataset.solde !== undefined) {
          const solde = parseFloat(opt.dataset.solde);
          soldeInfo.textContent = solde >= 0
            ? 'Solde disponible : ' + solde.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' DT'
            : '';
          soldeInfo.style.color = solde > 0 ? 'var(--color-text-secondary)' : 'var(--color-danger)';
        } else {
          soldeInfo.textContent = '';
        }
      });
      selBudget.dispatchEvent(new Event('change'));
    }

    formAddTx.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(formAddTx, BASE + '/transactions', {
        errorDiv: document.getElementById('txError'),
        onSuccess: () => { closeModal('modalAddTx'); location.reload(); }
      });
    });
  }

  // === Form: Add Budget ===
  const formAddBudget = document.getElementById('formAddBudget');
  if (formAddBudget && BASE) {
    formAddBudget.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(formAddBudget, BASE + '/budgets', {
        onSuccess: () => { closeModal('modalAddBudget'); location.reload(); }
      });
    });
  }

  // === Form: Add Category ===
  const formAddCategorie = document.getElementById('formAddCategorie');
  if (formAddCategorie && BASE) {
    formAddCategorie.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(formAddCategorie, BASE + '/categories', {
        onSuccess: () => { closeModal('modalAddCategorie'); location.reload(); }
      });
    });
  }

  // === Form: Edit Budget ===
  const formEditBudget = document.getElementById('formEditBudget');
  if (formEditBudget && BASE) {
    formEditBudget.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(formEditBudget, formEditBudget.action, {
        onSuccess: () => { closeModal('modalEditBudget'); location.reload(); }
      });
    });
  }

  // === Form: Edit Transaction ===
  const formEditTx = document.getElementById('formEditTx');
  if (formEditTx && BASE) {
    formEditTx.addEventListener('submit', (e) => {
      e.preventDefault();
      const txId = document.getElementById('editTxId').value;
      if (!txId) return;
      submitForm(formEditTx, BASE + '/transactions/' + txId + '/update', {
        errorDiv: document.getElementById('editTxError'),
        onSuccess: () => { closeModal('modalEditTx'); location.reload(); }
      });
    });
  }

  // === Form: Invite Member ===
  const formInviter = document.getElementById('formInviterMembre');
  const bid = typeof BUDGET_ID !== 'undefined' ? BUDGET_ID : (typeof budgetId !== 'undefined' ? budgetId : null);
  if (formInviter && BASE && bid) {
    formInviter.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(formInviter, BASE + '/budgets/' + bid + '/inviter', {
        onSuccess: () => { closeModal('modalInviterMembre'); location.reload(); }
      });
    });
  }

  // Init charts, alerts, invitations count
  initCharts();
  loadAlertes();
  loadInvitationsCount();

  // Hydrate flash messages (server-side) as toasts
  const flashEl = document.getElementById('flashData');
  if (flashEl) {
    try {
      const flashes = JSON.parse(flashEl.textContent || '[]');
      flashes.forEach(f => showToast(f.message, f.type, 5000));
    } catch (_) {}
  }
});

// === Category functions ===
function deleteCategorie(catId) {
  if (!confirm('Supprimer ?') || typeof BASE_URL === 'undefined') return;
  postJSON(BASE_URL + '/categories/' + catId + '/delete', {})
    .then(() => location.reload());
}

// === Admin functions ===
function validerCompte(userId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/admin/comptes/' + userId + '/valider', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(r => r.json()).then(j => {
    if (j.ok) {
      const el = document.getElementById('user-' + userId);
      if (el) el.remove();
      showToast('Compte validé', 'success');
    } else { showToast(j.message || 'Erreur', 'error'); }
  });
}

function suspendreCompte(userId, nom) {
  if (!confirm('Suspendre le compte de ' + (nom || userId) + ' ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/admin/comptes/' + userId + '/suspendre', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

function activerCompte(userId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/admin/comptes/' + userId + '/valider', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

function refuserCompte(userId) {
  if (!confirm('Refuser ce compte ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/admin/comptes/' + userId + '/supprimer', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

function supprimerCompte(userId, nom) {
  if (!confirm('Supprimer définitivement le compte de ' + (nom || userId) + ' ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/admin/comptes/' + userId + '/supprimer', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

function changerRole(userId, role) {
  if (typeof BASE_URL === 'undefined') return;
  const formData = new URLSearchParams();
  formData.append('role', role);
  formData.append('csrf_token', CSRF_TOKEN || '');
  fetch(BASE_URL + '/admin/comptes/' + userId + '/role', {
    method: 'POST',
    body: formData
  }).then();
}

// === Alert functions ===
function loadAlertes() {
  if (typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/api/alertes')
    .then(r => r.json())
    .then(alertes => {
      const badge = document.getElementById('alertBadge');
      if (badge) {
        badge.textContent = alertes.length > 0 ? alertes.length : '';
        badge.style.display = alertes.length > 0 ? 'flex' : 'none';
      }
    })
    .catch(() => {});
}

function dismissAlerte(alerteId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/api/alertes/' + alerteId + '/lue', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => {
    const el = document.getElementById('alerte-' + alerteId);
    if (el) el.remove();
  });
}

// === Transaction functions ===
function deleteTx(txId) {
  if (!confirm('Supprimer ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/transactions/' + txId + '/delete', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

function editTx(txId, type, montant, dateOp, description, catId, budgetId) {
  document.getElementById('editTxId').value = txId;
  const radio = document.querySelector('#formEditTx input[name="type"][value="' + type + '"]');
  if (radio) radio.checked = true;
  document.getElementById('editMontant').value = montant;
  document.getElementById('editDate').value = dateOp;
  document.getElementById('editDesc').value = description;
  document.getElementById('editCategorie').value = catId;
  openModal('modalEditTx');
}

// === Budget functions ===
function deleteBudget(budgetId) {
  if (!confirm('Supprimer ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/budgets/' + budgetId + '/delete', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => { window.location.href = BASE_URL + '/budgets'; });
}

function removeMembre(budgetId, userId) {
  if (!confirm('Retirer ce membre ?') || typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/budgets/' + budgetId + '/membres/' + userId + '/retirer', {
    method: 'POST',
    body: new URLSearchParams({ csrf_token: CSRF_TOKEN || '' })
  }).then(() => location.reload());
}

// === Chart functions ===
function initCharts() {
  if (typeof Chart === 'undefined' || typeof evolutionData === 'undefined') return;

  const ctxEvol = document.getElementById('chartEvolution');
  if (ctxEvol) {
    const dates = [], depenses = [], revenus = [];
    const dateMap = {};
    evolutionData.forEach(d => {
      if (!dateMap[d.date_op]) {
        dateMap[d.date_op] = true;
        dates.push(d.date_op);
      }
      if (d.type === 'DEPENSE') {
        depenses[dates.indexOf(d.date_op)] = (depenses[dates.indexOf(d.date_op)] || 0) + parseFloat(d.total);
      } else {
        revenus[dates.indexOf(d.date_op)] = (revenus[dates.indexOf(d.date_op)] || 0) + parseFloat(d.total);
      }
    });

    new Chart(ctxEvol, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          { label: 'Dépenses', data: depenses, borderColor: '#f06464', backgroundColor: 'rgba(240,100,100,0.1)', tension: 0.4 },
          { label: 'Revenus', data: revenus, borderColor: '#3dd68c', backgroundColor: 'rgba(61,214,140,0.1)', tension: 0.4 }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
  }

  const ctxRepart = document.getElementById('chartRepartition');
  if (ctxRepart && typeof repartitionData !== 'undefined' && repartitionData.length) {
    new Chart(ctxRepart, {
      type: 'doughnut',
      data: {
        labels: repartitionData.map(r => r.nom),
        datasets: [{
          data: repartitionData.map(r => parseFloat(r.total)),
          backgroundColor: ['#4f9eff', '#3dd68c', '#f06464', '#f5a623', '#a78bfa', '#2dd4bf', '#7c5cfc', '#ff6b6b']
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
}

// === Invitation functions ===
function accepterInvitation(invId) {
  if (typeof BASE_URL === 'undefined') return;
  postJSON(BASE_URL + '/invitations/' + invId + '/accepter', {})
    .then(j => {
      if (j.ok) {
        const el = document.getElementById('invitation-' + invId);
        if (el) el.remove();
        loadInvitationsCount();
      } else {
        showToast(j.message || 'Erreur', 'error');
      }
    })
    .catch(err => showToast('Erreur: ' + err.message, 'error'));
}

function refuserInvitation(invId) {
  if (typeof BASE_URL === 'undefined') return;
  postJSON(BASE_URL + '/invitations/' + invId + '/refuser', {})
    .then(j => {
      if (j.ok) {
        const el = document.getElementById('invitation-' + invId);
        if (el) el.remove();
        loadInvitationsCount();
      } else {
        showToast(j.message || 'Erreur', 'error');
      }
    })
    .catch(err => showToast('Erreur: ' + err.message, 'error'));
}

function loadInvitationsCount() {
  if (typeof BASE_URL === 'undefined') return;
  fetch(BASE_URL + '/api/invitations/count')
    .then(r => r.json())
    .then(data => {
      ['invitationBadge', 'sidebarInvBadge'].forEach(id => {
        const badge = document.getElementById(id);
        if (badge) {
          badge.textContent = data.count > 0 ? data.count : '';
          badge.style.display = data.count > 0 ? 'flex' : 'none';
        }
      });
    })
    .catch(() => {});
}

// === Email tags ===
function addEmailTag() {
  const input = document.getElementById('emailInput');
  const container = document.getElementById('emailTags');
  const hidden = document.getElementById('emailsMembres');
  if (!input || !container || !hidden) return;
  const email = input.value.trim();
  if (!email || container.querySelector('[data-email="' + email + '"]')) return;
  const tag = document.createElement('span');
  tag.className = 'email-tag';
  tag.dataset.email = email;
  tag.innerHTML = email + ' <button type="button" onclick="removeEmailTag(this)">&times;</button>';
  container.appendChild(tag);
  updateEmailsMembres();
  input.value = '';
}

function removeEmailTag(btn) {
  btn.parentElement.remove();
  updateEmailsMembres();
}

function updateEmailsMembres() {
  const container = document.getElementById('emailTags');
  const hidden = document.getElementById('emailsMembres');
  if (!container || !hidden) return;
  hidden.value = Array.from(container.querySelectorAll('.email-tag')).map(t => t.dataset.email).join(',');
}