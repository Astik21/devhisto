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
    const steps = document.querySelectorAll('#validation-steps li');
    let allChecksPassed = true;

    for (const step of steps) {
        const stepName = step.getAttribute('data-step');
        const formData = (stepName === 'Connexion au serveur SQL' || stepName === 'Validation des identifiants SQL') ?
            Object.fromEntries(new FormData(document.getElementById('installation-form')).entries()) :
            null;

        const result = await performCheck(stepName, formData);
        if (!result) {
            allChecksPassed = false;
            break;
        }
    }

    if (allChecksPassed) {
        document.getElementById('form-container').style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    runInitialChecks();
});

document.getElementById('start-installation').addEventListener('click', async () => {
    await runInitialChecks();
});
