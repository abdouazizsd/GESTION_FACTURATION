// Fonction pour charger du contenu dynamiquement
function loadContent(page) {
    fetch(page)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur de chargement: ${response.statusText} (${response.status})`);
            }
            return response.text();
        })
        .then(data => {
            const contentElement = document.getElementById('content');
            if (contentElement) {
                contentElement.innerHTML = data;
            } else {
                console.error("Erreur: l'élément #content est introuvable.");
            }
        })
        .catch(error => {
            console.error(error);
            const contentElement = document.getElementById('content');
            if (contentElement) {
                contentElement.innerHTML = `<p class="error-message">Erreur lors du chargement de la page. Veuillez réessayer.</p>`;
            }
        });
}

// Écouteur d'événement pour la soumission du formulaire d'ajout de client
document.getElementById("form-ajout-client")?.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("/GESTION_FACTURATION/pages/gestion_clients/ajouter_client.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur serveur: ${response.statusText} (${response.status})`);
        }
        return response.text();
    })
    .then(data => {
        console.log("Réponse serveur :", data);
        alert("Client ajouté avec succès !");
        loadContent('/GESTION_FACTURATION/pages/gestion_clients/liste_clients.php'); // Rafraîchir la liste des clients
    })
    .catch(error => {
        console.error("Erreur Fetch :", error);
        alert("Erreur lors de l'ajout du client.");
    });
});

// Fonction pour supprimer un client
window.supprimerClient = function (id) {
    if (confirm("Voulez-vous vraiment supprimer ce client ?")) {
        fetch(`gestion_clients/supprimer_client.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur lors de la suppression: ${response.statusText} (${response.status})`);
                }
                return response.text();
            })
            .then(data => {
                alert(data);
                loadContent('gestion_clients/liste_clients.php'); // Recharger la liste des clients
            })
            .catch(error => {
                console.error(error);
                alert("Erreur lors de la suppression du client.");
            });
    }
};

// Fonction pour modifier un client
window.modifierClient = function(id) {
    fetch(`gestion_clients/modifier_client.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur de chargement: ${response.statusText} (${response.status})`);
            }
            return response.text();
        })
        .then(data => {
            const clientFormElement = document.getElementById('client-form');
            if (clientFormElement) {
                clientFormElement.innerHTML = data;
            } else {
                console.error("Erreur: l'élément #client-form est introuvable.");
            }
        })
        .catch(error => {
            console.error(error);
            const clientFormElement = document.getElementById('client-form');
            if (clientFormElement) {
                clientFormElement.innerHTML = `<p class="error-message">Erreur lors du chargement du formulaire. Veuillez réessayer.</p>`;
            }
        });
}

// Fonction pour ouvrir/fermer la sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    if (sidebar && content) {
        sidebar.classList.toggle('open');
        content.classList.toggle('open');
    } else {
        console.error("Erreur: Impossible de trouver #sidebar ou #content.");
    }
}

// Fonction pour afficher des messages à l'utilisateur
function showMessage(text, type) {
    const container = document.getElementById('message-container');
    if (container) {
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${text}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    } else {
        console.error("Erreur: l'élément #message-container est introuvable.");
    }
}

// Fonction pour confirmer la suppression d'un client
function confirmDelete(id) {
    if (confirm('Confirmez la suppression définitive ?')) {
        fetch(`gestion_clients/supprimer_client.php?id=${id}`)
            .then(response => {
                if (response.ok) {
                    showMessage('Client supprimé avec succès', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(`Erreur serveur: ${response.statusText} (${response.status})`);
                }
            })
            .catch(error => {
                console.error(error);
                showMessage('Erreur de suppression', 'danger');
            });
    }
}