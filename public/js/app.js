// public/js/app.js
console.log('app.js loaded');

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
  }
}

document.addEventListener('click', (e) => {
  if (e.target && e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toggle-pwd').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = document.getElementById(btn.dataset.target);
      if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      if (sidebar) sidebar.classList.toggle('active');
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const tabId = btn.dataset.tab;
      const container = btn.closest('.tabs-container');
      if (container) {
        container.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        container.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        const tabContent = document.getElementById(`tab-${tabId}`);
        if (tabContent) tabContent.classList.add('active');
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const formAddTx = document.getElementById('formAddTx');
  if (formAddTx && typeof BASE_URL !== 'undefined') {
    fetch(`${BASE_URL}/api/categories`)
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
      .catch(err => console.error('Erreur catégories:', err));

    formAddTx.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = new FormData(formAddTx);
      if (typeof CSRF_TOKEN !== 'undefined') {
        data.set('csrf_token', CSRF_TOKEN);
      }
      try {
        const res = await fetch(`${BASE_URL}/transactions`, {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.ok) {
          closeModal('modalAddTx');
          location.reload();
        } else {
          alert('Erreur: ' + (json.message || 'Erreur'));
        }
      } catch (err) {
        alert('Erreur: ' + err.message);
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const formAddBudget = document.getElementById('formAddBudget');
  if (formAddBudget && typeof BASE_URL !== 'undefined') {
    document.querySelectorAll('[data-type]').forEach(label => {
      label.addEventListener('click', () => {
        const type = label.dataset.type;
        const membresSection = document.getElementById('membresSection');
        if (type === 'PARTAGE') {
          if (membresSection) membresSection.style.display = 'block';
        } else {
          if (membresSection) membresSection.style.display = 'none';
        }
      });
    });

    formAddBudget.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = new FormData(formAddBudget);
      if (typeof CSRF_TOKEN !== 'undefined') {
        data.set('csrf_token', CSRF_TOKEN);
      }
      try {
        const res = await fetch(`${BASE_URL}/budgets`, {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.ok) {
          closeModal('modalAddBudget');
          location.reload();
        } else {
          alert('Erreur: ' + (json.message || 'Erreur'));
        }
      } catch (err) {
        alert('Erreur: ' + err.message);
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const formAddCategorie = document.getElementById('formAddCategorie');
  if (formAddCategorie && typeof BASE_URL !== 'undefined') {
    formAddCategorie.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = new FormData(formAddCategorie);
      if (typeof CSRF_TOKEN !== 'undefined') {
        data.set('csrf_token', CSRF_TOKEN);
      }
      try {
        const res = await fetch(`${BASE_URL}/categories`, {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.ok) {
          closeModal('modalAddCategorie');
          location.reload();
        } else {
          alert('Erreur: ' + (json.message || 'Erreur'));
        }
      } catch (err) {
        alert('Erreur: ' + err.message);
      }
    });
  }
});

function deleteCategorie(catId) {
  if (!confirm('Supprimer ?')) return;
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/categories/${catId}/delete`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '' })
  }).then(() => location.reload());
}

function validerCompte(userId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/admin/comptes/${userId}/valider`, {
    method: 'POST',
    body: new FormData()
  }).then(r => r.json()).then(j => {
    if (j.ok) {
      const el = document.getElementById(`user-${userId}`);
      if (el) el.remove();
      alert('Compte validé');
    }
  });
}

function suspendreCompte(userId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/admin/comptes/${userId}/suspendre`, {
    method: 'POST'
  }).then(() => location.reload());
}

function changerRole(userId, role) {
  if (typeof BASE_URL === 'undefined') return;
  const formData = new FormData();
  formData.append('role', role);
  fetch(`${BASE_URL}/admin/comptes/${userId}/role`, {
    method: 'POST',
    body: formData
  }).then(() => alert('Rôle mis à jour'));
}

function loadAlertes() {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/api/alertes`)
    .then(r => r.json())
    .then(alertes => {
      const badge = document.getElementById('alertBadge');
      if (badge) {
        if (alertes.length > 0) {
          badge.textContent = alertes.length;
          badge.style.display = 'flex';
        } else {
          badge.style.display = 'none';
        }
      }
    })
    .catch(err => console.error('Erreur alertes:', err));
}

function dismissAlerte(alerteId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/api/alertes/${alerteId}/lue`, {
    method: 'POST',
    body: new FormData()
  }).then(() => {
    const el = document.getElementById(`alerte-${alerteId}`);
    if (el) el.remove();
  });
}

function deleteTx(txId) {
  if (!confirm('Supprimer ?')) return;
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/transactions/${txId}/delete`, {
    method: 'POST',
    body: new FormData()
  }).then(() => location.reload());
}

function deleteBudget(budgetId) {
  if (!confirm('Supprimer ?')) return;
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/budgets/${budgetId}/delete`, {
    method: 'POST',
    body: new FormData()
  }).then(() => location.href = `${BASE_URL}/budgets`);
}

function initCharts() {
  if (typeof Chart === 'undefined') return;
  if (typeof evolutionData === 'undefined') return;

  const ctxEvol = document.getElementById('chartEvolution');
  if (ctxEvol) {
    const dates = [];
    const depenses = [];
    const revenus = [];

    evolutionData.forEach(d => {
      if (!dates.includes(d.date_op)) dates.push(d.date_op);
      if (d.type === 'DEPENSE') {
        const idx = dates.indexOf(d.date_op);
        if (!depenses[idx]) depenses[idx] = 0;
        depenses[idx] += parseFloat(d.total);
      } else {
        const idx = dates.indexOf(d.date_op);
        if (!revenus[idx]) revenus[idx] = 0;
        revenus[idx] += parseFloat(d.total);
      }
    });

    new Chart(ctxEvol, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          {
            label: 'Dépenses',
            data: depenses,
            borderColor: '#f06464',
            backgroundColor: 'rgba(240,100,100,0.1)',
            tension: 0.4
          },
          {
            label: 'Revenus',
            data: revenus,
            borderColor: '#3dd68c',
            backgroundColor: 'rgba(61,214,140,0.1)',
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  const ctxRepart = document.getElementById('chartRepartition');
  if (ctxRepart && typeof repartitionData !== 'undefined' && repartitionData.length) {
    new Chart(ctxRepart, {
      type: 'doughnut',
      data: {
        labels: repartitionData.map(r => r.nom),
        datasets: [{
          data: repartitionData.map(r => r.total),
          backgroundColor: ['#4f9eff', '#3dd68c', '#f06464', '#f5a623', '#a78bfa', '#2dd4bf', '#7c5cfc', '#ff6b6b']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('DOMContentLoaded', loadAlertes);
document.addEventListener('DOMContentLoaded', loadInvitationsCount);
document.addEventListener('DOMContentLoaded', () => {
  const formInviter = document.getElementById('formInviterMembre');
  if (formInviter && typeof BASE_URL !== 'undefined' && typeof BUDGET_ID !== 'undefined') {
    formInviter.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = new FormData(formInviter);
      if (typeof CSRF_TOKEN !== 'undefined') {
        data.set('csrf_token', CSRF_TOKEN);
      }
      try {
        const res = await fetch(`${BASE_URL}/budgets/${BUDGET_ID}/inviter`, {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.ok) {
          closeModal('modalInviterMembre');
          location.reload();
        } else {
          alert('Erreur: ' + (json.message || 'Erreur'));
        }
      } catch (err) {
        alert('Erreur: ' + err.message);
      }
    });
  }
});

function addEmailTag() {
  const input = document.getElementById('emailInput');
  const container = document.getElementById('emailTags');
  const hidden = document.getElementById('emailsMembres');
  if (!input || !container || !hidden) return;
  const email = input.value.trim();
  if (!email) return;
  if (container.querySelector(`[data-email="${email}"]`)) return;
  const tag = document.createElement('span');
  tag.className = 'email-tag';
  tag.dataset.email = email;
  tag.innerHTML = `${email} <button type="button" onclick="removeEmailTag(this)">&times;</button>`;
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
  const emails = Array.from(container.querySelectorAll('.email-tag')).map(t => t.dataset.email);
  hidden.value = emails.join(',');
}

function accepterInvitation(invId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/invitations/${invId}/accepter`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '' })
  })
    .then(r => r.json())
    .then(j => {
      if (j.ok) {
        const el = document.getElementById(`invitation-${invId}`);
        if (el) el.remove();
        loadInvitationsCount();
      } else {
        alert('Erreur: ' + (j.message || 'Erreur'));
      }
    })
    .catch(err => alert('Erreur: ' + err.message));
}

function refuserInvitation(invId) {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/invitations/${invId}/refuser`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ csrf_token: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '' })
  })
    .then(r => r.json())
    .then(j => {
      if (j.ok) {
        const el = document.getElementById(`invitation-${invId}`);
        if (el) el.remove();
        loadInvitationsCount();
      } else {
        alert('Erreur: ' + (j.message || 'Erreur'));
      }
    })
    .catch(err => alert('Erreur: ' + err.message));
}

function loadInvitationsCount() {
  if (typeof BASE_URL === 'undefined') return;
  fetch(`${BASE_URL}/api/invitations/count`)
    .then(r => r.json())
    .then(data => {
      const badge = document.getElementById('invitationBadge');
      if (badge) {
        if (data.count > 0) {
          badge.textContent = data.count;
          badge.style.display = 'flex';
        } else {
          badge.style.display = 'none';
        }
      }
      const sidebarBadge = document.getElementById('sidebarInvBadge');
      if (sidebarBadge) {
        if (data.count > 0) {
          sidebarBadge.textContent = data.count;
          sidebarBadge.style.display = 'inline';
        } else {
          sidebarBadge.style.display = 'none';
        }
      }
    })
    .catch(() => {});
}

console.log('app.js fully loaded');