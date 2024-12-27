document.getElementById('start-installation').addEventListener('click', async () => {
    const formData = Object.fromEntries(new FormData(document.getElementById('installation-form')).entries());
    const steps = document.querySelectorAll('#validation-steps li');

    for (const step of steps) {
        const stepName = step.getAttribute('data-step');
        step.className = 'status-in-progress';
        step.innerHTML = `${step.innerHTML.split(':')[0]}: ⏳`;

        try {
            const response = await fetch('install/backend.php', {
                method: 'POST',
                body: JSON.stringify({ step: stepName, formData }),
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();

            if (result.status === 'ok') {
                step.className = 'status-ok';
                step.innerHTML = `${step.innerHTML.split(':')[0]}: ✅`;
            } else {
                step.className = 'status-ko';
                step.innerHTML = `${step.innerHTML.split(':')[0]}: ❌ (${result.message})`;
                break;
            }
        } catch (error) {
            step.className = 'status-ko';
            step.innerHTML = `${step.innerHTML.split(':')[0]}: ❌ (Erreur de communication)`;
            break;
        }
    }
});
