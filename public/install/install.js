async function performCheck(stepName, formData = null) {
    const stepElement = document.querySelector(`[data-step="${stepName}"]`);
    stepElement.className = 'status-in-progress';
    stepElement.innerHTML = `${stepName}: ⏳`;

    try {
        const response = await fetch('install/backend.php', {
            method: 'POST',
            body: JSON.stringify({ step: stepName, formData }),
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();

        if (result.status === 'ok') {
            stepElement.className = 'status-ok';
            stepElement.innerHTML = `${stepName}: ✅`;
            return true;
        } else {
            stepElement.className = 'status-ko';
            stepElement.innerHTML = `${stepName}: ❌ (${result.message})`;
            return false;
        }
    } catch (error) {
        stepElement.className = 'status-ko';
        stepElement.innerHTML = `${stepName}: ❌ (Erreur de communication)`;
        return false;
    }
}

async function runInitialChecks() {
    const steps = document.querySelectorAll('#validation-steps li[data-step^="Droits"], #validation-steps li[data-step^="Extensions"]');
    let allChecksPassed = true;

    for (const step of steps) {
        const stepName = step.getAttribute('data-step');
        const result = await performCheck(stepName);
        if (!result) {
            allChecksPassed = false;
        }
    }

    if (allChecksPassed) {
        document.getElementById('form-container').style.display = 'block';
    }
}

async function handleSQLTests() {
    const formData = Object.fromEntries(new FormData(document.getElementById('installation-form')).entries());
    const submitButton = document.getElementById('submit-sql');

    // Désactiver le bouton pour éviter plusieurs clics
    submitButton.disabled = true;

    // Test connexion au serveur SQL
    const serverTest = await performCheck('Connexion au serveur SQL', formData);
    if (!serverTest) {
        submitButton.disabled = false;
        return;
    }

    // Test des identifiants SQL
    const credentialsTest = await performCheck('Validation des identifiants SQL', formData);
    if (!credentialsTest) {
        submitButton.disabled = false;
        return;
    }

    // Enregistrement du fichier config.php
    const configSave = await performCheck('Enregistrement de config.php', formData);
    if (!configSave) {
        submitButton.disabled = false;
        return;
    }

    // Création des tables SQL
    const tableCreation = await performCheck('Création des tables SQL', formData);
    if (!tableCreation) {
        submitButton.disabled = false;
        return;
    }

    // Masquer le formulaire après la création des tables
    document.getElementById('form-container').style.display = 'none';

    // Création de l'utilisateur admin
    const adminCreation = await performCheck('Création de l\'utilisateur admin', formData);
    if (!adminCreation) {
        submitButton.disabled = false;
        return;
    }

    // Supprimer le répertoire install
    const removeInstall = await performCheck('Supprimer le répertoire install');
    if (!removeInstall) {
        const stepElement = document.querySelector(`[data-step="Supprimer le répertoire install"]`);
        stepElement.className = 'status-ko';
        stepElement.innerHTML = `Supprimer le répertoire install: ❌ (Erreur lors de la suppression. Supprimez manuellement.)`;
    }

    // Afficher le message final et le bouton "Suivant"
    const nextStepContainer = document.getElementById('next-step');
    nextStepContainer.style.display = 'block';
    nextStepContainer.innerHTML = `
        <div class="final-message">
            <p style="font-size: 1.2em; font-weight: bold; color: #d9534f;">
                ⚠️ Pour des raisons de sécurité, veuillez immédiatement supprimer le fichier <code>install.php</code>.
            </p>
            <p>Une fois terminé, cliquez sur "Suivant" pour accéder à votre application.</p>
            <button id="next-button" class="button">Suivant</button>
        </div>
    `;

    document.getElementById('next-button').addEventListener('click', () => {
        window.location.href = 'index.php';
    });
}



document.addEventListener('DOMContentLoaded', () => {
    runInitialChecks();
});

document.getElementById('submit-sql').addEventListener('click', async () => {
    await handleSQLTests();
});
