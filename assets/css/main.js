
// Initialisation des composants Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de la validation des formulaires
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Validation de la correspondance des mots de passe
    var password = document.getElementById("password");
    var confirm_password = document.getElementById("confirm_password");
    if (password && confirm_password) {
        function validatePassword() {
            if (password.value != confirm_password.value) {
                confirm_password.setCustomValidity("Les mots de passe ne correspondent pas");
            } else {
                confirm_password.setCustomValidity('');
            }
        }
        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    }
});

// Fonctions de confirmation
function confirmAction(message) {
    return confirm(message);
}

// Gestion des événements
async function deleteEvent(eventId) {
    if (!confirmAction('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.')) return;
    
    try {
        const response = await fetch('/sporteventultimate/admin/delete-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        });
        
        const data = await response.json();
        handleResponse(data, 'événement');
    } catch (error) {
        handleError(error);
    }
}

// Gestion des commentaires
async function deleteComment(commentId) {
    if (!confirmAction('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return;
    
    try {
        const response = await fetch('/sporteventultimate/admin/delete-comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        });
        
        const data = await response.json();
        handleResponse(data, 'commentaire');
    } catch (error) {
        handleError(error);
    }
}

// Gestion des participants
async function removeParticipant(inscriptionId) {
    if (!confirmAction('Êtes-vous sûr de vouloir retirer ce participant ?')) return;
    
    try {
        const response = await fetch('/sporteventultimate/admin/remove-participant', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `inscription_id=${inscriptionId}`
        });
        
        const data = await response.json();
        handleResponse(data, 'participant');
    } catch (error) {
        handleError(error);
    }
}

// Gestion des likes
async function toggleLike(eventId) {
    try {
        const response = await fetch('/sporteventultimate/like/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'event_id=' + eventId
        });

        if (!response.ok) {
            throw new Error('Erreur réseau');
        }

        const data = await response.json();
        if (data.success) {
            const likeButton = document.getElementById('likeButton');
            const likeCount = document.getElementById('likeCount');
            
            likeCount.textContent = data.likeCount;
            
            if (data.liked) {
                likeButton.classList.remove('btn-outline-danger');
                likeButton.classList.add('btn-danger');
            } else {
                likeButton.classList.remove('btn-danger');
                likeButton.classList.add('btn-outline-danger');
            }
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Gestion des inscriptions
async function toggleInscription(eventId) {
    try {
        const response = await fetch('/sporteventultimate/inscription/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'event_id=' + eventId
        });

        if (!response.ok) {
            throw new Error('Erreur réseau');
        }

        const data = await response.json();
        if (data.success) {
            // Mettre à jour la liste des inscrits
            const inscriptionsList = document.getElementById('inscriptionsList');
            if (data.inscrits && data.inscrits.length > 0) {
                inscriptionsList.innerHTML = data.inscrits.map(inscription => `
                    <div class="list-group-item border-0 rounded-3 mb-2 bg-light">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fs-4 text-success me-2"></i>
                            <div>
                                <strong class="d-block">${inscription.user_name}</strong>
                                <small class="text-muted">Inscrit le ${new Date(inscription.created_at).toLocaleDateString()}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                inscriptionsList.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Aucun participant pour le moment</p>
                    </div>`;
            }

            // Mettre à jour le bouton
            const button = document.getElementById('inscriptionButton');
            const isRegistered = data.inscrit;
            button.innerHTML = `
                <i class="fas ${isRegistered ? 'fa-user-minus' : 'fa-user-plus'} me-2"></i>
                ${isRegistered ? 'Se désinscrire' : 'S\'inscrire'}
            `;
            button.classList.toggle('btn-success');
            button.classList.toggle('btn-outline-success');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de l\'inscription');
    }
}

// Gestion des erreurs et réponses
function handleResponse(data, type) {
    if (data.success) {
        location.reload();
    } else {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${data.message || `Une erreur est survenue lors de la suppression du ${type}`}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(errorDiv, document.querySelector('.nav-tabs'));
    }
}

function handleError(error) {
    console.error('Erreur:', error);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle me-2"></i>
        Une erreur est survenue lors de la communication avec le serveur
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.container-fluid').insertBefore(errorDiv, document.querySelector('.nav-tabs'));
}

// Prévisualisation d'image
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}