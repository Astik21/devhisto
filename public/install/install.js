async function performCheck(stepName, formData = null) {
    const stepElement = document.querySelector(`[data-step="${stepName}"]`);
    if (!stepElement) {
        return false;
    }

    // Si l'étape est masquée, affichez-la avant de continuer
    if (stepElement.style.display === 'none') {
        stepElement.style.display = 'list-item';
    }

    const displayName = stepDisplayNames[stepName] || stepName; // Récupération depuis stepDisplayNames

    stepElement.className = 'status-in-progress';
    stepElement.innerHTML = `${displayName}: ⏳`;

    try {
        const response = await fetch('install/backend.php', {
            method: 'POST',
            body: JSON.stringify({ step: stepName, formData }),
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();

        if (result.status === 'ok') {
            stepElement.className = 'status-ok';
            stepElement.innerHTML = `${displayName}: ✅`;
            return result; // Retourner le résultat complet
        } else if (result.actionRequired) {
            const confirmAction = confirm(`${result.message}\nVoulez-vous supprimer les tables existantes et continuer ?`);
            if (confirmAction) {
                markStepAsPending('create_sql_table', displayName);
                await performCheck('delete_existing_tables', formData); // Supprimer les tables
                return await performCheck(stepName, formData); // Relancer la création des tables
            } else {
                markStepAsFailed('create_sql_tables', 'L\'utilisateur a choisi de conserver les tables.');
                markStepAsFailed('create_admin_user', 'L\'utilisateur a choisi de conserver les tables.');
                return false; // Arrêter le processus pour ces étapes
            }
        } else {
            stepElement.className = 'status-ko';
            stepElement.innerHTML = `${displayName}: ❌ (${result.message})`;

            // Pré-remplir le formulaire en cas d'erreur SQL
            if (stepName === 'test_sql_connection' || stepName === 'validate_sql_credentials') {
                const formData = result.formData || {};

                document.getElementById('db_host').value = formData.db_host || '';
                document.getElementById('db_port').value = formData.db_port || '';
                document.getElementById('db_name').value = formData.db_name || '';
                document.getElementById('db_user').value = formData.db_user || '';
                document.getElementById('db_pass').value = formData.db_pass || '';
                document.getElementById('form-container').style.display = 'block';
            }
            return result; // Retourner également le résultat pour gestion des erreurs
        }
    } catch (error) {
        stepElement.className = 'status-ko';
        stepElement.innerHTML = `${stepName}: ❌ (Erreur de communication)`;
        return false;
    }
}


function markStepAsFailed(stepName, message) {
    const stepElement = document.querySelector(`[data-step="${stepName}"]`);
    if (stepElement) {
        const displayName = stepElement.textContent || stepName;
        stepElement.className = 'status-ko';
        stepElement.innerHTML = `${displayName}: ❌ (${message})`;
    }
}

function markStepAsPending(stepName, displayName) {
    const stepElement = document.querySelector(`[data-step="${stepName}"]`);
    if (stepElement) {
        stepElement.className = 'status-pending';
        stepElement.innerHTML = `${displayName}`;
    }
}

async function runInitialChecks() {
    const steps = document.querySelectorAll('#validation-steps li[data-step^="check_write_permissions"], #validation-steps li[data-step^="check_php_"]');
    let allChecksPassed = true;

    for (const step of steps) {
        const stepName = step.getAttribute('data-step');
        const result = await performCheck(stepName);
        if (!result) {
            allChecksPassed = false;
        }
    }

    if (allChecksPassed) {
        await checkConfigFile(); // Vérification de config.php après les extensions
    }
}

async function checkConfigFile() {
    const stepName = 'check_config_file';

    // Récupération des données via performCheck
    const result = await performCheck(stepName);

    if (result && result.status === 'ok') {

        // Passer les données à handleSQLTests si config.php est valide
        const formData = result.formData || {};
        await handleSQLTests(formData);
    } else {
        document.getElementById('form-container').style.display = 'block';
    }
}



async function handleSQLTests(formData = null) {
    const steps = ['test_sql_connection', 'validate_sql_credentials'];

    for (const stepName of steps) {
        const result = await performCheck(stepName, formData);
        if (!result || result.status !== 'ok') { // Arrêt immédiat en cas d'échec
            return; // Stop si une étape échoue
        }
    }
    await handleFinalSteps(formData);
}


async function handleFinalSteps(formData = null) {
    const steps = ['save_config_file', 'create_sql_tables', 'create_admin_user', 'remove_install_directory'];

    for (const stepName of steps) {
        // Toujours exécuter la suppression du répertoire install
        if (stepName === 'remove_install_directory') {
            await performCheck(stepName, formData);
        } else {
            const result = await performCheck(stepName, formData);
            if (!result || result.status !== 'ok') { // Arrêt immédiat en cas d'échec sur save_config
                if (stepName === 'save_config_file') return; // Stop si on ne peux pas sauvegarder le fichier config.php
            }
        }
    }

    // Afficher le message final
    const nextStepContainer = document.getElementById('next-step');
    nextStepContainer.style.display = 'block';
    nextStepContainer.innerHTML = `
        <div class="final-message">
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
    const formData = Object.fromEntries(new FormData(document.getElementById('installation-form')).entries());

    // Assurez-vous que toutes les clés attendues sont présentes
    formData.db_host = formData.db_host || '';
    formData.db_port = formData.db_port || '';
    formData.db_name = formData.db_name || '';
    formData.db_user = formData.db_user || '';
    formData.db_pass = formData.db_pass || '';

    await handleSQLTests(formData);
});

