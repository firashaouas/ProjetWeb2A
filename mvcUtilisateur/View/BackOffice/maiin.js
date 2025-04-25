function scrollActivities(direction) {
    const container = document.querySelector('.activites');
    const scrollAmount = 220;
    if (direction === 'next') {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else if (direction === 'prev') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

// Fonction pour afficher/masquer le menu déroulant
function toggleDropdown() {
    var dropdown = document.getElementById('dropdownMenu');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Ferme le menu déroulant si on clique en dehors
document.addEventListener('click', function(event) {
    var dropdown = document.getElementById('dropdownMenu');
    var profileCircle = document.querySelector('.profile-circle');
    if (profileCircle && !profileCircle.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
}

// Fermer le menu si on clique ailleurs
window.addEventListener('click', function(e) {
    const profile = document.querySelector('.user-profile');
    if (!profile.contains(e.target)) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
});

function changeRole(idUser, currentRole) {
    const roleOptions = ['admin', 'user', 'moderateur', 'banni']; // tu peux ajuster
    let optionsHtml = roleOptions.map(role =>
      `<option value="${role}" ${role === currentRole ? 'selected' : ''}>${role}</option>`
    ).join('');
  
    const newRole = promptifySelect(`Changer le rôle de l'utilisateur`, optionsHtml);
  
    newRole.then(selectedRole => {
      if (selectedRole && selectedRole !== currentRole) {
        window.location.href = `index.php?action=changerRole&id=${idUser}&role=${selectedRole}`;
      }
    });
  }
  
  function promptifySelect(title, optionsHtml) {
    return new Promise(resolve => {
      const modal = document.createElement('div');
      modal.innerHTML = `
        <div class="modal-backdrop">
          <div class="modal-box">
            <h3>${title}</h3>
            <select id="role-select">${optionsHtml}</select>
            <div class="modal-actions">
              <button onclick="this.closest('.modal-backdrop').remove()">Annuler</button>
              <button onclick="confirmSelect(this)">Confirmer</button>
            </div>
          </div>
        </div>`;
      document.body.appendChild(modal);
      window.confirmSelect = (btn) => {
        const selected = btn.closest('.modal-box').querySelector('#role-select').value;
        btn.closest('.modal-backdrop').remove();
        resolve(selected);
      };
    });
  }
  

  function banUser(idUser) {
    const modal = document.createElement('div');
    modal.innerHTML = `
      <div class="modal-backdrop">
        <div class="modal-box">
          <h3>Raison du bannissement</h3>
          <textarea id="ban-reason" placeholder="Entrez la raison ici..."></textarea>
          <div class="modal-actions">
            <button onclick="this.closest('.modal-backdrop').remove()">Annuler</button>
            <button onclick="confirmBan(this, ${idUser})">Confirmer</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
  }
  
  function confirmBan(btn, idUser) {
    const reason = btn.closest('.modal-box').querySelector('#ban-reason').value.trim();
    if (reason) {
      window.location.href = `index.php?action=bannirUser&id=${idUser}&raison=${encodeURIComponent(reason)}`;
    } else {
      alert("Vous devez entrer une raison.");
    }
  }



  